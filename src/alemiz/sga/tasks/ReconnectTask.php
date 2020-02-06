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
        $client = $this->plugin->getClient();
        if ($client->getInterface()->isShutdown() ||
            ($client->getInterface()->canConnect() && $client->getInterface()->isConnected())) return;

        $client->getLogger()->info("§eReloading StarGate Client");
        $client->getInterface()->reconnect();
    }
}