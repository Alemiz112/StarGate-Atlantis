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
     * StarGateClientConnectEvent constructor.
     * @param string $client
     */
    public function __construct(string $client){
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getClient(): string {
        return $this->client;
    }
}