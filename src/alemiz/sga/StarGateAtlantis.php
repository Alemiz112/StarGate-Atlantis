<?php

namespace alemiz\sga;

use alemiz\sga\client\Client;
use alemiz\sga\events\CustomPacketEvent;
use alemiz\sga\packets\ConnectionInfoPacket;
use alemiz\sga\packets\ForwardPacket;
use alemiz\sga\packets\KickPacket;
use alemiz\sga\packets\PingPacket;
use alemiz\sga\packets\PlayerOnlinePacket;
use alemiz\sga\packets\PlayerTransferPacket;
use alemiz\sga\packets\ServerManagePacket;
use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\packets\WelcomePacket;
use alemiz\sga\tasks\ReconnectTask;
use alemiz\sga\utils\Convertor;
use Closure;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class StarGateAtlantis extends PluginBase{

    /**
     * @var Config
     */
    public $cfg;
    /**
     * @var StarGateAtlantis
     */
    private static $instance;
    /**
     * @var Client[]
     */
    protected $clients = [];
    /**
     * @var StarGatePacket[]
     */
    protected static $packets = [];
    /**
     * @var Closure[]
     */
    private $shutdownHandlers = [];

    public function onEnable() : void {
        self::$instance = $this;
        @mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->cfg = $this->getConfig();


        $this->initPackets();
        foreach ($this->cfg->get("connections") as $clientName => $ignore){
            $this->start($clientName);
        }

        $this->getScheduler()->scheduleDelayedRepeatingTask(new ReconnectTask($this), 20*30, 20*60*3);
    }

    public function onDisable() : void {
        foreach ($this->shutdownHandlers as $handler){
            $handler();
        }

        foreach ($this->clients as $client){
            $client->shutdown(ConnectionInfoPacket::CLIENT_SHUTDOWN);
        }
    }

    /**
     * @return StarGateAtlantis
     */
    public static function getInstance() : StarGateAtlantis {
        return self::$instance;
    }

    /**
     * @param string $configName
     */
    private function start(string $configName) : void {
        if (!isset($this->cfg->get("connections")[$configName])) return;
        $data = $this->cfg->get("connections")[$configName];

        $tickInterval = (int) $this->cfg->get("TickInterval");
        $clientName = $data["name"];
        $address = $data["address"];
        $port = (int) $data["port"];
        $password = $data["password"];

        $this->clients[$configName] = new Client($this, $address, $port, $clientName, $password, $configName, $tickInterval);
    }

    /**
     * @param string $configName
     */
    public function restart(string $configName) : void {
        $this->getLogger()->info("§eReloading StarGate Client ".$configName);
        $client = null;

        if (!isset($this->clients[$configName]) || (($client = $this->clients[$configName]))->getInterface()->isShutdown()){
            $this->start($configName);
            return;
        }

        if ($client->getInterface()->isConnected()){
            return; //client is connected
        }

        $client->getInterface()->reconnect();
    }

    /**
     * @param string $configName
     * @return bool
     */
    public function removeClient(string $configName) : bool {
        if (!isset($this->clients[$configName])){
            return false;
        }

        unset($this->clients[$configName]);
        return true;
    }

    /**
     * Register simple Runnable task which will be run before connection closes
     * @param Closure $handler
     */
    public function registerShutDownHandler(Closure $handler) : void {
        $this->shutdownHandlers[] = $handler;
    }

    private function initPackets() : void {
        self::RegisterPacket(new WelcomePacket());
        self::RegisterPacket(new PingPacket());
        self::RegisterPacket(new ConnectionInfoPacket());
        self::RegisterPacket(new PlayerTransferPacket());
        self::RegisterPacket(new KickPacket());
        self::RegisterPacket(new PlayerOnlinePacket());
        self::RegisterPacket(new ForwardPacket());
        self::RegisterPacket(new ServerManagePacket());
    }

    /**
     * Using these function we can process packet from string to data
     * After packet is successfully created we can handle that Packet
     * @param string $packetString
     * @return StarGatePacket|null
     */
    public function processPacket(string $packetString) : ?StarGatePacket {
        $data = Convertor::getPacketStringData($packetString);
        $packetId = (int) $data[0];

        if (!isset(self::getPackets()[$packetId])) return null;

        /* Here we decode Packet. Create from String Data*/
        $packet = clone self::getPackets()[$packetId];
        $uuid = end($data);

        $packet->uuid = $uuid;

        $packet->encoded = $packetString;
        $packet->decode();

        if (!($packet instanceof ConnectionInfoPacket)){
            $this->handlePacket($packet);
        }
        return $packet;
    }

    /**
     * @param StarGatePacket $packet
     */
    public function handlePacket(StarGatePacket $packet) : void {
        $type = $packet->getID();

        switch ($type){
            default:
                try {
                    /** Here we call Event that will send packet to DEVs plugin*/
                    $event = new CustomPacketEvent($packet);
                    $event->call();
                }catch (\ReflectionException $e){
                    $this->getLogger()->critical("§cError: Unable to handle custom packet!");
                    $this->getLogger()->critical("§c".$e->getMessage());
                }
                break;
        }
    }

    /**
     * @param string $clientConfigName
     * @return array
     */
    public function getResponses(string $clientConfigName = "default"): array {
        return isset($this->clients[$clientConfigName])? $this->clients[$clientConfigName]->getInterface()->getResponses() : [];
    }

    /**
     * @param string $uuid
     * @param string $clientConfigName
     */
    public function unsetResponse(string $uuid, string $clientConfigName) : void {
        if (!isset($this->clients[$clientConfigName])) return;

        $clientConfigName = $this->clients[$clientConfigName];
        $clientConfigName->getInterface()->unsetResponse($uuid);
    }

    /**
     * @return Client[]
     */
    public function getClients(): array {
        return $this->clients;
    }

    /**
     * @param string $configName
     * @return string|null
     */
    public function getClientName(string $configName = "default"): ?string{
        return isset($this->clients[$configName])? $this->clients[$configName]->getClientName() : null;
    }


    /* Beginning of API section*/
    /**
     * This allows you to send packet. Returns packets UUID.
     * @param StarGatePacket $packet
     * @param string         $clientConfigName
     * @return string|null
     */
    public function putPacket(StarGatePacket $packet, string $clientConfigName = "default") : ?string {
        return isset($this->clients[$clientConfigName])? $this->clients[$clientConfigName]->getInterface()->gatePacket($packet) : null;
    }

    /**
     * @return StarGatePacket[]
     */
    public static function getPackets() : array {
        return self::$packets;
    }

    public static function RegisterPacket(StarGatePacket $packet) : void {
        self::$packets[$packet->getID()] = $packet;
    }

    /**
     * @param Player|string|null $player
     * @param string $server
     * @param string $client
     */
    public function transferPlayer($player, string $server, string $client = "default") : void {
        if (empty($player)) return;

        $packet = new PlayerTransferPacket();
        $packet->player = ($player instanceof Player)? $player->getName() : $player;
        $packet->destination = $server;
        $this->putPacket($packet, $client);
    }

    /**
     * Kick player from any server connected to StarGate network
     * @param Player|string|null $player
     * @param string $reason
     * @param string $client
     */
    public function kickPlayer($player, string $reason, string $client = "default") : void {
        if (empty($player)) return;

        $packet = new KickPacket();
        $packet->player = ($player instanceof Player)? $player->getName() : $player;
        $packet->reason = $reason;
        $this->putPacket($packet, $client);
    }

    /**
     * We can check if player is online somewhere in network
     * After sending packet we must handle response by UUID
     * Example can be found in /tests/OnlineExample.java
     * @param Player|string|null $player
     * @param Closure|null $responseHandler
     * @param string $client
     * @return string|null
     */
    public function isOnline($player, Closure $responseHandler = null, string $client = "default") : ?string {
        if (empty($player)) return null;

        $packet = new PlayerOnlinePacket();

        if ($player instanceof Player){
            $packet->player = $player;
        }else $packet->customPlayer = $player;

        if (!is_null($responseHandler)){
            $packet->setResponseHandler($responseHandler);
        }

        return $this->putPacket($packet, $client);
    }

    /**
     * Using ForwardPacket you can forward packet to other client
     * @param string $destClient
     * @param string $proxyServer
     * @param StarGatePacket $packet
     */
    public function forwardPacket(string $destClient, string $proxyServer, StarGatePacket $packet) : void {
        $forwardPacket = new ForwardPacket();
        $forwardPacket->client = $destClient;

        if (!$packet->isEncoded){
            $packet->encode();
        }

        $forwardPacket->encodedPacket = $packet->encoded;
        $this->putPacket($forwardPacket, $proxyServer);
    }

    /**
     * Proxy will send response with status: "STATUS_FAILED" or "STATUS_SUCCESS,server_name"
     * Dont forget. Response can be handled using ResponseCheckTask
     * Returned variable is UUID or packets used to handle response NOT response
     * @param string $address
     * @param string $port
     * @param string $name
     * @param string $client
     * @return string|null
     */
    public function addServer(string $address, string $port, string $name, string $client = "default") : ?string {
        $packet = new ServerManagePacket();
        $packet->packetType = ServerManagePacket::SERVER_ADD;
        $packet->serverAddress = $address;
        $packet->serverPort = $port;
        $packet->serverName = $name;

        return $this->putPacket($packet, $client);
    }

    /**
     * Response: "STATUS_SUCCESS" or "STATUS_NOT_FOUND"
     * @param string $name
     * @param string $client
     * @return string|null
     */
    public function removeServer(string $name, string  $client = "default") : ?string {
        $packet = new ServerManagePacket();
        $packet->packetType = ServerManagePacket::SERVER_REMOVE;
        $packet->serverName = $name;

        return $this->putPacket($packet, $client);
    }
}
