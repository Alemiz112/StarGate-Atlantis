<?php

namespace alemiz\sga\client;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\handler\HandshakePacketHandler;
use alemiz\sga\protocol\DisconnectPacket;
use alemiz\sga\protocol\HandshakePacket;
use alemiz\sga\protocol\PongPacket;
use alemiz\sga\protocol\StarGatePacket;
use Exception;
use pocketmine\plugin\PluginLogger;
use function get_class;

class ClientSession {

    /** @var StarGateClient */
    private $client;
    /** @var StarGateConnection */
    private $connection;

    /** @var StarGatePacketHandler|null */
    private $packetHandler;

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
    }

    /**
     * @param StarGatePacket $packet
     */
    private function onPacket(StarGatePacket $packet) : void {
        $handled = $this->packetHandler !== null && $packet->handle($this->packetHandler);

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
        }
    }

    /**
     * @param PongPacket $packet
     */
    public function onPongReceive(PongPacket $packet) : void {
        //TODO:
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

}