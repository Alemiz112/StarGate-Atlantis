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

    public function onEnable(){
        self::$instance = $this;
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->cfg = $this->getConfig();


        $this->initPackets();
        foreach ($this->cfg->get("connections") as $clientName => $ignore){
            $this->start($clientName);
        }

        $this->getScheduler()->scheduleDelayedRepeatingTask(new ReconnectTask($this), 30*20, 20*60*5);
    }

    public function onDisable(){
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
     * @param string $name
     */
    private function start(string $name) : void {
        if (!isset($this->cfg->get("connections")[$name])) return;
        $data = $this->cfg->get("connections")[$name];

        $tickInterval = (int) $this->cfg->get("TickInterval");
        $clientName = $data["name"];
        $address = $data["address"];
        $port = (int) $data["port"];
        $password = $data["password"];

        $this->clients[$name] = new Client($this, $address, $port, $clientName, $password, $name, $tickInterval);
    }

    /**
     * @param string $name
     */
    public function restart(string $name) : void {
        $this->getLogger()->info("§eReloading StarGate Client ".$name);
        $client = null;

        if (!isset($this->clients[$name]) || (($client = $this->clients[$name]))->getInterface()->isShutdown()){
            $this->start($name);
            return;
        }

        if ($client->getInterface()->canConnect() && $client->getInterface()->isConnected()){
            return; //client is connected
        }

        $client->getInterface()->reconnect();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function removeClient(string $name) : bool {
        if (!isset($this->clients[$name])){
            return false;
        }

        unset($this->clients[$name]);
        return true;
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

        if (!isset(StarGateAtlantis::getPackets()[$packetId])) return null;

        /* Here we decode Packet. Create from String Data*/
        $packet = clone StarGateAtlantis::getPackets()[$packetId];
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
     * @param string $client
     * @return array
     */
    public function getResponses(string $client): array {
        return isset($this->clients[$client])? $this->clients[$client]->getInterface()->getResponses() : [];
    }

    /**
     * @return Client[]
     */
    public function getClients(): array {
        return $this->clients;
    }

    /**
     * @param string $client
     * @return string|null
     */
    public function getClientName(string $client = "default"): ?string{
        return isset($this->clients[$client])? $this->clients[$client]->getName() : null;
    }


    /* Beginning of API section*/
    /**
     * This allows you to send packet. Returns packets UUID.
     * @param StarGatePacket $packet
     * @param string $client
     * @return string|null
     */
    public function putPacket(StarGatePacket $packet, string $client = "default") : ?string {
        return isset($this->clients[$client])? $this->clients[$client]->getInterface()->gatePacket($packet) : null;
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
     * Transferring player to other server
     * @param $player
     * @param string $server
     * @param string $client
     */
    public function transferPlayer($player, string $server, string $client = "default") : void {
        if (is_null($player)) return;

        $packet = new PlayerTransferPacket();
        $packet->player = $player;
        $packet->destination = $server;
        $this->putPacket($packet, $client);
    }

    /**
     * Kick player from any server connected to StarGate network
     * @param $player
     * @param string $reason
     * @param string $client
     */
    public function kickPlayer($player, string $reason, string $client = "default") : void {
        if (is_null($player)) return;

        $packet = new KickPacket();
        $packet->player = $player;
        $packet->reason = $reason;
        $this->putPacket($packet, $client);
    }

    /**
     * We can check if player is online somewhere in network
     * After sending packet we must handle response by UUID
     * Example can be found in /tests/OnlineExample.java
     * @param Player|string $player
     * @param \Closure|null $responseHandler
     * @param string $client
     * @return string|null
     */
    public function isOnline($player, \Closure $responseHandler = null, string $client = "default") : ?string {
        if (is_null($player) || $player == "") return null;

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
     * @param string $localClient
     * @param StarGatePacket $packet
     */
    public function forwardPacket(string $destClient, string $localClient, StarGatePacket $packet) : void {
        $forwardPacket = new ForwardPacket();
        $forwardPacket->client = $destClient;

        if (!$packet->isEncoded){
            $packet->encode();
        }

        $forwardPacket->encodedPacket = $packet->encoded;
        $this->putPacket($forwardPacket, $localClient);
    }
}
