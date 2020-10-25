<?php
/*
 * Copyright 2020 Alemiz
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace alemiz\sga\client;

use alemiz\sga\codec\ProtocolCodec;
use alemiz\sga\protocol\types\HandshakeData;
use alemiz\sga\utils\StarGateException;
use ClassLoader;
use pocketmine\Thread;
use pocketmine\utils\Binary;
use Threaded;
use ThreadedLogger;
use function socket_getpeername;
use function socket_read;
use function strlen;
use function substr;
use const PHP_BINARY_READ;

class StarGateConnection extends Thread {

    public const STATE_DISCONNECTED = 0;
    public const STATE_CONNECTING = 1;
    public const STATE_CONNECTED = 2;
    public const STATE_AUTHENTICATING = 3;
    public const STATE_AUTHENTICATED = 4;
    public const STATE_SHUTDOWN = 5;

    /** @var ThreadedLogger */
    private $logger;
    /** @var StarGateSocket */
    private $starGateSocket;

    /** @var resource */
    public $socket;

    /** @var string */
    private $address;
    /** @var int */
    private $port;
    /** @var HandshakeData */
    private $handshakeData;

    /** @var Threaded */
    private $input;
    /** @var Threaded */
    private $output;

    /** @var string */
    private $buffer = "";

    private $state = self::STATE_DISCONNECTED;

    /**
     * StarGateConnection constructor.
     * @param ThreadedLogger $logger
     * @param ClassLoader $loader
     * @param string $address
     * @param int $port
     * @param HandshakeData $handshakeData
     */
    public function __construct(ThreadedLogger $logger, ClassLoader $loader, string $address, int $port, HandshakeData $handshakeData){
        $this->logger = $logger;
        $this->address = $address;
        $this->port = $port;
        $this->handshakeData = $handshakeData;
        $this->setClassLoader($loader);
        $this->starGateSocket = new StarGateSocket($this, $this->address, $this->port);

        $this->input = new Threaded();
        $this->output = new Threaded();
        $this->start();
    }

    public function run() : void {
        $this->registerClassLoader();
        gc_enable();
        error_reporting(-1);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');

        register_shutdown_function([$this, 'shutdown']);
        //set_error_handler([$this, 'errorHandler'], E_ALL);

        $this->state = self::STATE_CONNECTING;
        $this->logger->debug("Connecting to StarGate server ".$this->address);

        if (!$this->starGateSocket->connect()){
            $this->state = self::STATE_DISCONNECTED;
            return;
        }
        //socket_getpeername($this->socket, $this->address, $this->port);

        $this->state = self::STATE_CONNECTED;
        $this->operate();
    }

    private function operate() : void {
        while ($this->state !== self::STATE_DISCONNECTED){
            $start = microtime(true);
            $this->onTick();
            $time = microtime(true);
            if ($time - $start < 0.01) {
                time_sleep_until($time + 0.01 - ($time - $start));
            }
        }
        $this->onTick();
        $this->shutdown();
    }

    private function onTick() : void {
        $error = socket_last_error();
        socket_clear_error($this->socket);

        if ($error === 10057 || $error === 10054 || $error === 10053){
            error:
            $this->getLogger()->info("§cConnection with StarGate server has disconnected unexpectedly!");
            $this->close();
            return;
        }

        $data = @socket_read($this->socket, 65536, PHP_BINARY_READ);
        if ($data !== ""){
            $this->buffer .= $data;
        }

        while (($packet = $this->outRead()) !== null && $packet !== ""){
            if (@socket_write($this->socket, $packet) === false){
                goto error;
            }
        }

        $this->readBuffer();
    }

    private function readBuffer() : void {
        if (empty($this->buffer)){
            return;
        }

        $offset = 0;
        $len = strlen($this->buffer);
        while ($offset < $len){
            // Packet header consists of 7 bytes
            if ($offset > ($len - 7)) {
                break;
            }

            $magic = Binary::readShort(substr($this->buffer, $offset, 2));
            if ($magic !== ProtocolCodec::STARGATE_MAGIC){
                throw new StarGateException("'Magic does not match!");
            }

            $bodyLength = Binary::readInt(substr($this->buffer, $offset + 3, 4));
            $offset += 2;

            if (($len - $offset) <= $bodyLength){
                $offset -= 2;
                break;
            }

            // packetId + body length + buf
            $payload = substr($this->buffer, $offset, ($payloadLen = $bodyLength + 5));
            $this->inputWrite($payload);
            $offset .= $payloadLen;
        }

        if ($offset < $len){
            $this->buffer = substr($this->buffer, $offset);
        }else {
            $this->buffer = "";
        }
    }

    /**
     * @param string $payload
     */
    public function writeBuffer(string $payload) : void {
        $buf = Binary::writeShort(ProtocolCodec::STARGATE_MAGIC);
        $buf .= $payload;
        $this->outWrite($buf);
    }

    public function close() : void {
        if ($this->state === self::STATE_DISCONNECTED){
            return;
        }
        $this->state = self::STATE_DISCONNECTED;
        $this->logger->debug("Closed StarGate session ".$this->address);
    }

    public function shutdown() : void {
        if ($this->state === self::STATE_SHUTDOWN){
            return;
        }
        $this->state = self::STATE_SHUTDOWN;
        $this->starGateSocket->close();
    }

    /**
     * @return bool
     */
    public function isClosed() : bool {
        return $this->state === self::STATE_DISCONNECTED || $this->state === self::STATE_SHUTDOWN;
    }

    /**
     * @return int
     */
    public function getState() : int {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state) : void {
        $this->state = $state;
    }

    /**
     * @return string|null
     */
    public function inputRead() : ?string {
        return $this->input->shift();
    }

    /**
     * @param string $string
     */
    public function inputWrite(string $string) : void {
        $this->input[] = $string;
    }

    /**
     * @return string|null
     */
    public function outRead() : ?string {
        return $this->output->shift();
    }

    /**
     * @param string $string
     */
    public function outWrite(string $string) : void {
        $this->output[] = $string;
    }

    public function quit() : void {
        $this->close();
        parent::quit();
    }

    /**
     * @return StarGateSocket
     */
    public function getStarGateSocket(): StarGateSocket {
        return $this->starGateSocket;
    }

    /**
     * @return resource
     */
    public function getSocket(){
        return $this->socket;
    }

    /**
     * @return ThreadedLogger
     */
    public function getLogger(): ThreadedLogger {
        return $this->logger;
    }

    public function getClientName() : string {
        return $this->handshakeData->getClientName();
    }

    public function getThreadName(): string {
        return "StarGate-Atlantis";
    }

    public function setGarbage() : void {
    }
}