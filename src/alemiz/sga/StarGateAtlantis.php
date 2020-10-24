<?php

namespace alemiz\sga;

use alemiz\sga\client\StarGateClient;
use alemiz\sga\events\ClientCreationEvent;
use alemiz\sga\protocol\types\HandshakeData;
use pocketmine\plugin\PluginBase;
use function var_dump;

class StarGateAtlantis extends PluginBase{

    /** @var StarGateAtlantis */
    private static $instance;

    /** @var StarGateClient[] */
    protected $clients = [];

    /** @var int */
    private $tickInterval;

    public function onEnable() : void {
        self::$instance = $this;
		$this->tickInterval = $this->getConfig()->get("tickInterval");

		$this->clients = [];
        foreach ($this->getConfig()->get("connections") as $clientName => $ignore){
            $this->createClient($clientName);
        }
    }

    public function onDisable() : void {
        foreach ($this->clients as $client){
            $client->shutdown();
        }
    }

    /**
     * @param string $clientName
     */
    private function createClient(string $clientName) : void {
        if (!isset($this->getConfig()->get("connections")[$clientName])){
            $this->getLogger()->warning("§cCan not load client ".$clientName."! Wrong config!");
            return;
        }

        $config = $this->getConfig()->get("connections")[$clientName];
        $handshakeData = new HandshakeData($clientName, $config["password"], HandshakeData::SOFTWARE_POCKETMINE);
        $client = new StarGateClient($config["address"], (int) $config["port"], $handshakeData, $this);
        $this->onClientCreation($clientName, $client);
    }

    /**
     * @param string $clientName
     * @param StarGateClient $client
     */
    public function onClientCreation(string $clientName, StarGateClient $client) : void {
        //TODO: register packets

        $event = new ClientCreationEvent($client, $this);
        $event->call();

        if (!$event->isCancelled()){
            $client->connect();
            $this->clients[$clientName] = $client;
        }
    }

    /**
     * @return StarGateAtlantis
     */
    public static function getInstance() : StarGateAtlantis {
        return self::$instance;
    }

    /**
     * @return int
     */
    public function getTickInterval() : int {
        return $this->tickInterval;
    }
}
