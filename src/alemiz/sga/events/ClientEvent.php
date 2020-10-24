<?php

namespace alemiz\sga\events;

use alemiz\sga\client\ClientSession;
use alemiz\sga\client\StarGateClient;
use alemiz\sga\StarGateAtlantis;
use pocketmine\event\Event;

abstract class ClientEvent extends Event {

    /** @var StarGateAtlantis */
    private $plugin;

    /** @var StarGateClient */
    private $client;

    /**
     * ClientEvent constructor.
     * @param StarGateClient $client
     * @param StarGateAtlantis $plugin
     */
    public function __construct(StarGateClient $client, StarGateAtlantis $plugin){
        $this->client = $client;
        $this->plugin = $plugin;
    }


    /**
     * @return StarGateClient
     */
    public function getClient() : StarGateClient {
        return $this->client;
    }

    /**
     * @return ClientSession|null
     */
    public function getSession() : ?ClientSession {
        return $this->client->getSession();
    }

    /**
     * @return StarGateAtlantis
     */
    public function getPlugin() : StarGateAtlantis {
        return $this->plugin;
    }

}