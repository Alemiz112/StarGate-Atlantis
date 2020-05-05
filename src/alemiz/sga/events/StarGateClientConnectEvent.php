<?php
namespace alemiz\sga\events;

use alemiz\sga\packets\StarGatePacket;
use pocketmine\event\Event;

class StarGateClientConnectEvent extends Event {

    /**
     * @var string
     */
    private $client;

    /**
     * @var string
     */
    private $configName;

    /**
     * StarGateClientConnectEvent constructor.
     * @param string $client
     * @param string $configName
     */
    public function __construct(string $client, string $configName){
        $this->client = $client;
        $this->configName = $configName;
    }

    /**
     * @return string
     */
    public function getClient(): string {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getConfigName(): string {
        return $this->configName;
    }
}