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
use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\packets\WelcomePacket;
use alemiz\sga\utils\Convertor;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class StarGateAtlantis extends PluginBase{

    /** @var Config */
    public $cfg;
    /** @var StarGateAtlantis */
    private static $instance;
    /** @var Client */
    private $client;

    /** @var StarGatePacket[]  */
    protected static $packets = [];

    public function onEnable(){
        self::$instance = $this;
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->cfg = $this->getConfig();

        $this->getLogger()->info("§aEnabling StarGate Universe: Client");

        /* Starting Client for StarGate*/
        $name = $this->cfg->get("Client");
        $address = $this->cfg->get("Address");
        $port = $this->cfg->get("Port");
        $password = $this->cfg->get("Password");
        $tickInterval = (int) $this->cfg->get("TickInterval");

        $this->initPackets();

        $this->client = new Client($this, $address, $port, $name, $password, $tickInterval);
    }

    public function onDisable(){
        $this->client->shutdown(ConnectionInfoPacket::CLIENT_SHUTDOWN);
    }

    /**
     * @return StarGateAtlantis
     */
    public static function getInstance(){
        return self::$instance;
    }

    /**
     * @return Client
     */
    public function getClient(): Client{
        return $this->client;
    }

    /**
     * @return array
     */
    public function getResponses(): array{
        return $this->client->getInterface()->getResponses();
    }

    private function initPackets(){
        self::RegisterPacket(new WelcomePacket());
        self::RegisterPacket(new PingPacket());
        self::RegisterPacket(new ConnectionInfoPacket());
        self::RegisterPacket(new PlayerTransferPacket());
        self::RegisterPacket(new KickPacket());
        self::RegisterPacket(new PlayerOnlinePacket());
        self::RegisterPacket(new ForwardPacket());
    }

    /**
     * Using these function we can process packet from string to data
     * After packet is successfully created we can handle that Packet
     * @param string $packetString
     * @return StarGatePacket|null
     */
    public function processPacket(string $packetString){
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
    public function handlePacket(StarGatePacket $packet){
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


    /* Beginning of API section*/
    /**
     * This allows you to send packet. Returns packets UUID.
     * @param StarGatePacket $packet
     * @return string
     */
    public function putPacket(StarGatePacket $packet) : string {
        return $this->client->getInterface()->gatePacket($packet);
    }

    /**
     * @return StarGatePacket[]
     */
    public static function getPackets(){
        return self::$packets;
    }

    public static function RegisterPacket(StarGatePacket $packet){
        self::$packets[$packet->getID()] = $packet;
    }

    /**
     * Transferring player to other server
     * @param $player
     * @param string $server
     */
    public function transferPlayer($player, string $server){
        if (is_null($player)) return;

        $packet = new PlayerTransferPacket();
        $packet->player = $player;
        $packet->destination = $server;
        $this->putPacket($packet);
    }

    /**
     * Kick player from any server connected to StarGate network
     * @param $player
     * @param string $reason
     */
    public function kickPlayer($player, string $reason){
        if (is_null($player)) return;

        $packet = new KickPacket();
        $packet->player = $player;
        $packet->reason = $reason;
        $this->putPacket($packet);
    }

    //TODO: Edit desc

    /**
     * We can check if player is online somewhere in network
     * After sending packet we must handle response by UUID
     * Example can be found in /tests/OnlineExample.java
     * @param Player|string $player
     * @param \Closure|null $responseHandler
     * @return string|null
     */
    public function isOnline($player, \Closure $responseHandler = null){
        if (is_null($player) || $player == "") return null;

        $packet = new PlayerOnlinePacket();

        if ($player instanceof Player){
            $packet->player = $player;
        }else $packet->customPlayer = $player;

        if (!is_null($responseHandler)){
            $packet->setResponseHandler($responseHandler);
        }

        return $this->putPacket($packet);
    }

    /**
     * Using ForwardPacket you can forward packet to other client
     * @param string $client
     * @param StarGatePacket $packet
     */
    public function forwardPacket(string $client, StarGatePacket $packet){
        $forwardPacket = new ForwardPacket();
        $forwardPacket->client = $client;

        if (!$packet->isEncoded){
            $packet->encode();
        }

        $forwardPacket->encodedPacket = $packet->encoded;
        $this->putPacket($forwardPacket);
    }
}