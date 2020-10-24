<?php
namespace alemiz\sga\client;

use alemiz\sga\codec\ProtocolCodec;
use alemiz\sga\events\ClientAuthenticatedEvent;
use alemiz\sga\events\ClientConnectedEvent;
use alemiz\sga\events\ClientDisconnectedEvent;
use alemiz\sga\handler\SessionHandler;
use alemiz\sga\protocol\DisconnectPacket;
use alemiz\sga\protocol\types\HandshakeData;
use alemiz\sga\StarGateAtlantis;

use pocketmine\plugin\PluginLogger;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class StarGateClient extends Task {

    /** @var StarGateAtlantis */
    private $loader;
    /** @var Server */
    private $server;
    /** @var PluginLogger */
    private $logger;

    /** @var ProtocolCodec */
    private $protocolCodec;

    /** @var HandshakeData */
    private $handshakeData;
    /** @var string */
    protected $address;
    /** @var int */
    protected $port;

    /** @var ClientSession|null */
    private $session;

    /** @var SessionHandler|null */
    private $customHandler;

    /**
     * StarGateClient constructor.
     * @param string $address
     * @param int $port
     * @param HandshakeData $handshakeData
     * @param StarGateAtlantis $plugin
     */
    public function __construct(string $address, int $port, HandshakeData $handshakeData, StarGateAtlantis $plugin){
        $this->loader = $plugin;
        $this->server = $plugin->getServer();
        $this->logger = $plugin->getLogger();
        $this->protocolCodec = new ProtocolCodec();

        $this->address = $address;
        $this->port = $port;
        $this->handshakeData = $handshakeData;
        $this->loader->getScheduler()->scheduleDelayedRepeatingTask($this, 20, $this->loader->getTickInterval());
    }

    public function connect() : void {
        if ($this->isConnected()){
            return;
        }
        $this->session = new ClientSession($this, $this->address, $this->port);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) : void {
        if ($this->isConnected()){
            $this->session->onTick();
        }
    }

    public function onSessionConnected() : void {
        $this->logger->info("§bClient ".$this->getClientName()." has connected!");
        $event = new ClientConnectedEvent($this, $this->loader);
        $event->call();
    }

    public function onSessionAuthenticated() : void {
        $event = new ClientAuthenticatedEvent($this, $this->loader);
        $event->call();

        if ($this->session !== null && $event->isCancelled()){
            $this->session->disconnect($event->getCancelMessage());
        }
    }

    public function onSessionDisconnected() : void {
        $event = new ClientDisconnectedEvent($this, $this->loader);
        $event->call();
    }

    public function shutdown() : void {
        if (!$this->isConnected()){
            return;
        }

        if ($this->session !== null){
            $this->session->disconnect(DisconnectPacket::CLIENT_SHUTDOWN);
        }
    }

    public function isConnected() : bool {
        return $this->session !== null && $this->session->isConnected();
    }

    /**
     * @return HandshakeData
     */
    public function getHandshakeData() : HandshakeData {
        return $this->handshakeData;
    }

    /**
     * @return string
     */
    public function getClientName() : string {
        return $this->handshakeData->getClientName();
    }

    /**
     * @return ClientSession|null
     */
    public function getSession() : ?ClientSession {
        return $this->session;
    }

    /**
     * @return Server
     */
    public function getServer() : Server {
        return $this->server;
    }

    /**
     * @return PluginLogger
     */
    public function getLogger() : PluginLogger {
        return $this->logger;
    }

    /**
     * @return ProtocolCodec
     */
    public function getProtocolCodec() : ProtocolCodec {
        return $this->protocolCodec;
    }

    /**
     * @return SessionHandler|null
     */
    public function getCustomHandler() : ?SessionHandler {
        return $this->customHandler;
    }

    /**
     * @param SessionHandler $customHandler
     */
    public function setCustomHandler(SessionHandler $customHandler) : void {
        $this->customHandler = $customHandler;
    }
}