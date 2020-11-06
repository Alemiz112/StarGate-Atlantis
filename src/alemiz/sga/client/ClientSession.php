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

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\handler\HandshakePacketHandler;
use alemiz\sga\protocol\DisconnectPacket;
use alemiz\sga\protocol\HandshakePacket;
use alemiz\sga\protocol\PingPacket;
use alemiz\sga\protocol\PongPacket;
use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\protocol\types\PingEntry;
use alemiz\sga\utils\LogLevel;
use alemiz\sga\utils\PacketResponse;
use alemiz\sga\utils\StarGateException;
use alemiz\sga\utils\StarGateFuture;
use Exception;
use pocketmine\plugin\PluginLogger;
use function get_class;
use function microtime;
use function var_dump;

class ClientSession {

    /** @var StarGateClient */
    private $client;
    /** @var StarGateConnection */
    private $connection;

    /** @var int */
    private $responseCounter = 0;
    /** @var PacketResponse[] */
    private $pendingResponses = [];

    /** @var StarGatePacketHandler|null */
    private $packetHandler;

    /** @var PingEntry|null */
    private $pingEntry;

    /** @var int */
    private $logInputLevel = 0;
    /** @var int */
    private $logOutputLevel = 0;

    /**
     * ClientSession constructor.
     * @param StarGateClient $client
     * @param string $address
     * @param int $port
     */
    public function __construct(StarGateClient $client, string $address, int $port){
        $this->client = $client;
        $server = $client->getServer();
        $this->packetHandler = new HandshakePacketHandler($this);
        $this->connection = new StarGateConnection($server->getLogger(), $server->getLoader(), $address, $port, $client->getHandshakeData());
    }

    public function onConnect() : void {
        $packet = new HandshakePacket();
        $packet->setHandshakeData($this->client->getHandshakeData());
        $this->sendPacket($packet);

        $this->connection->setState(StarGateConnection::STATE_AUTHENTICATING);
        $this->client->onSessionConnected();
    }

    public function onTick() : void {
        if (!$this->isConnected()){
            return;
        }

        if ($this->connection->getState() === StarGateConnection::STATE_CONNECTED){
            $this->onConnect();
        }

        while (($payload = $this->connection->inputRead()) !== null && !empty($payload)){
            $codec = $this->client->getProtocolCodec();
            try {
                $packet = $codec->tryDecode($payload);
                if ($packet !== null){
                    $this->onPacket($packet);
                }
            }catch (Exception $e){
                $this->getLogger()->error("§cCan not decode StarGate packet!");
                $this->getLogger()->logException($e);
            }
        }


        $currentTime = microtime(true) * 1000;
        if ($this->pingEntry !== null && $currentTime >= $this->pingEntry->getTimeout()){
            $this->pingEntry->getFuture()->completeExceptionally(new StarGateException("Ping Timeout!"));
            $this->pingEntry = null;
        }
    }

    /**
     * @param StarGatePacket $packet
     */
    private function onPacket(StarGatePacket $packet) : void {
        $handled = $this->packetHandler !== null && $packet->handle($this->packetHandler);

        if ($packet->isResponse() && isset($this->pendingResponses[$packet->getResponseId()])){
            $response = $this->pendingResponses[$packet->getResponseId()];
            $response->complete($packet);
            unset($this->pendingResponses[$packet->getResponseId()]);
        }

        $customHandler = $this->client->getCustomHandler();
        if ($customHandler !== null){
            try {
                if ($packet->handle($customHandler)){
                    $handled = true;
                }
            }catch (Exception $e){
                $this->getLogger()->error("Error occurred in custom packet handler!");
                $this->getLogger()->logException($e);
            }
        }

        if (!$handled){
            $this->getLogger()->debug("Unhandled packet ".get_class($packet));
        }

        if ($this->logInputLevel >= $packet->getLogLevel()){
            $this->getLogger()->debug("Received ".get_class($packet));
        }
    }

    /**
     * @param StarGatePacket $packet
     * @return PacketResponse|null
     */
    public function responsePacket(StarGatePacket $packet) : ?PacketResponse {
        if (!$packet->sendsResponse()){
            return null;
        }

        $responseId = $this->responseCounter++;
        $packet->setResponseId($responseId);
        $this->sendPacket($packet);

        if (!isset($this->pendingResponses[$responseId])){
            $this->pendingResponses[$responseId] = new PacketResponse();
        }
        return $this->pendingResponses[$responseId];
    }

    /**
     * @param StarGatePacket $packet
     */
    public function sendPacket(StarGatePacket $packet) : void {
        if (!$this->isConnected()){
            return;
        }

        $codec = $this->client->getProtocolCodec();
        try {
            $payload = $codec->tryEncode($packet);
            if (!empty($payload)){
                $this->connection->writeBuffer($payload);
            }
        }catch (Exception $e){
            $this->getLogger()->error("§cCan not encode StarGate packet ".get_class($packet)."!");
            $this->getLogger()->logException($e);
            return;
        }

        if ($this->logInputLevel >= $packet->getLogLevel()){
            $this->getLogger()->debug("Sent ".get_class($packet));
        }
    }

    /**
     * @param int $timeout
     * @return StarGateFuture
     */
    public function pingServer(int $timeout) : StarGateFuture {
        if ($this->pingEntry !== null){
            $this->pingEntry->getFuture();
        }

        $now = (int) microtime(true) * 1000;
        $entry = new PingEntry(new StarGateFuture(), $now + $timeout);

        $packet = new PingPacket();
        $packet->setPingTime($now);
        $this->sendPacket($packet);
        return ($this->pingEntry = $entry)->getFuture();
    }

    /**
     * @param PongPacket $packet
     */
    public function onPongReceive(PongPacket $packet) : void {
        if ($this->pingEntry === null){
            return;
        }
        $packet->setPongTime((int) microtime(true) * 1000);
        $this->pingEntry->getFuture()->complete($packet);
        $this->pingEntry = null;
    }

    /**
     * @param string $reason
     */
    public function onDisconnect(string $reason) : void {
        $this->getLogger()->info("§bStarGate server has been disconnected! Reason: ".$reason);
        $this->client->onSessionDisconnected();
        $this->close();
    }

    /**
     * @param string $reason
     * @param bool $send
     */
    public function reconnect(string $reason, bool $send) : void {
        if ($send){
            $packet = new DisconnectPacket();
            $packet->setReason($reason);
            $this->sendPacket($packet);
        }
        $this->getLogger()->info("§bReconnecting to server! Reason: ".$reason);
        $this->close();
        $this->client->connect();
    }

    /**
     * @param string $reason
     */
    public function disconnect(string $reason) : void {
        if ($this->connection->isClosed()){
            return;
        }
        $this->getLogger()->info("§bClosing StarGate connection! Reason: ".$reason);

        $packet = new DisconnectPacket();
        $packet->setReason($reason);
        $this->sendPacket($packet);
        $this->close();
    }

    /**
     * @return bool
     */
    public function close() : bool {
        if ($this->connection->isClosed()){
            return false;
        }

        $this->connection->close();
        return true;
    }

    /**
     * @return bool
     */
    public function isConnected() : bool {
        return !$this->connection->isClosed();
    }

    /**
     * @return StarGatePacketHandler|null
     */
    public function getPacketHandler(): ?StarGatePacketHandler {
        return $this->packetHandler;
    }

    /**
     * @param StarGatePacketHandler|null $packetHandler
     */
    public function setPacketHandler(?StarGatePacketHandler $packetHandler) : void {
        $this->packetHandler = $packetHandler;
    }

    /**
     * @return StarGateClient
     */
    public function getClient() : StarGateClient {
        return $this->client;
    }

    /**
     * @return PluginLogger
     */
    public function getLogger() : PluginLogger {
        return $this->client->getLogger();
    }

    /**
     * @return StarGateConnection
     */
    public function getConnection() : StarGateConnection {
        return $this->connection;
    }

    /**
     * @param int $logInputLevel
     */
    public function setLogInputLevel(int $logInputLevel) : void {
        $this->logInputLevel = $logInputLevel;
    }

    /**
     * @return int
     */
    public function getLogInputLevel() : int {
        return $this->logInputLevel;
    }

    /**
     * @param int $logOutputLevel
     */
    public function setLogOutputLevel(int $logOutputLevel) : void {
        $this->logOutputLevel = $logOutputLevel;
    }

    /**
     * @return int
     */
    public function getLogOutputLevel() : int {
        return $this->logOutputLevel;
    }

}