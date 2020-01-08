<?php
namespace tests;

use alemiz\sga\StarGateAtlantis;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;

class CustomResponseCheckTask extends Task {

    /** @var string */
    private $uuid, $expectedResult;

    /**
     * This should be instance of your plugin.
     * @var PluginBase
     */
    private $plugin;

    /**
     * This is maximum delay in SECONDS/2 that is tolerated by ping.
     * If delay is bigger response will be removed. Also clients will be disconnected!
     * @var int
     */
    protected $timeout = 30;

    /**
     * ResponseCheckTask constructor.
     * @param PluginBase $plugin
     * @param string $uuid
     * @param string $expectedResult
     */
    public function __construct(PluginBase $plugin, string $uuid, string $expectedResult){
        $this->uuid = $uuid;
        $this->expectedResult = $expectedResult;
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick){
        if ($this->timeout === 0) return;

        $responses = StarGateAtlantis::getInstance()->getResponses();

        /*Here we check if response is already handled/received*/
        if (!isset($responses[$this->uuid]) || $responses[$this->uuid] === "unknown"){
            $this->timeout--;
            StarGateAtlantis::getInstance()->getScheduler()->scheduleDelayedTask($this, 20);
            return;
        }

        /* Now you can do what you want with result*/
        $responses = $responses[$this->uuid];
    }
}