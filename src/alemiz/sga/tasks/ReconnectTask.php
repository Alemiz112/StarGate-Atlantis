<?php
namespace alemiz\sga\tasks;

use alemiz\sga\StarGateAtlantis;
use pocketmine\scheduler\Task;

class ReconnectTask extends Task {

    /** @var StarGateAtlantis */
    private $plugin;

    /**
     * ReconnectTask constructor.
     * @param StarGateAtlantis $plugin
     */
    public function __construct(StarGateAtlantis $plugin){
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick){
        $clients = $this->plugin->getClients();

        foreach ($clients as $name => $client){
            if ($client->getInterface()->isShutdown() || $client->getInterface()->isConnected()) continue;
            $this->plugin->restart($name);
        }
    }
}