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
use pmmp\thread\Thread as NativeThread;
use pmmp\thread\ThreadSafeArray;
use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\thread\Thread;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Binary;
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

    private ThreadSafeLogger $logger;
    /** @var StarGateSocket */
    private StarGateSocket $starGateSocket;

    public ?\Socket $socket;

    /** @var string */
    private string $address;
    /** @var int */
    private int $port;
    /** @var HandshakeData */
    private HandshakeData $handshakeData;

    private ThreadSafeArray $input;
    private ThreadSafeArray $output;

    /** @var string */
    private string $buffer = "";

    /** @var int */
    private int $state = self::STATE_DISCONNECTED;

    /**
     * StarGateConnection constructor.
     * @param ThreadSafeLogger $logger
     * @param string $address
     * @param int $port
     * @param HandshakeData $handshakeData
     */
    public function __construct(ThreadSafeLogger $logger, string $address, int $port, HandshakeData $handshakeData){
        $this->logger = $logger;
        $this->address = $address;
        $this->port = $port;
        $this->handshakeData = $handshakeData;
        $this->starGateSocket = new StarGateSocket($this, $this->address, $this->port);

        $this->input = new ThreadSafeArray();
        $this->output = new ThreadSafeArray();
        $this->start(NativeThread::INHERIT_NONE);
    }

    public function onRun() : void {
        $this->registerClassLoaders();
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
            if (($diff = $time - $start) < 0.02) {
                time_sleep_until($time + 0.025 - $diff);
            }
        }
        $this->onTick();
        $this->shutdown();
    }

    private function onTick() : void {
        $error = socket_last_error();
        socket_clear_error($this->socket);

        $socket = $this->socket;

        if ($socket === null) {
            goto error;
        }
        if ($error === 10057 || $error === 10054 || $error === 10053){
            error:
            $this->getLogger()->info("§cConnection with StarGate server has disconnected unexpectedly!");
            $this->close();
            return;
        }

        if ($socket === null) {
            throw new AssumptionFailedError("This should never happen");
        }

        $data = @socket_read($socket, 65536, PHP_BINARY_READ);
        if ($data !== ""){
            $this->buffer .= $data;
        }

        while (($packet = $this->outRead()) !== null && $packet !== ""){
            if (@socket_write($socket, $packet) === false){
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
            if ($offset > ($len - 6)) {
                // Tried to decode invalid buffer
                break;
            }

            $magic = Binary::readShort(substr($this->buffer, $offset, 2));
            if ($magic !== ProtocolCodec::STARGATE_MAGIC){
                throw new StarGateException("'Magic does not match!");
            }
            $offset += 2;

            $length = Binary::readInt(substr($this->buffer, $offset, 4));
            $offset += 4;

            if (($offset + $length) > $len) {
                // Received incomplete packet
                $offset -= 2;
                break;
            }

            $payload = substr($this->buffer, $offset, $length);
            $offset += $length;
            $this->inputWrite($payload);
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

    public function getSocket(): ?\Socket {
        return $this->socket;
    }

    public function getLogger(): ThreadSafeLogger {
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