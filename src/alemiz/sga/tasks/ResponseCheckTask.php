<?php
namespace alemiz\sga\tasks;

use alemiz\sga\StarGateAtlantis;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;

abstract class ResponseCheckTask extends Task {

    /** @var string */
    protected $uuid;
    /** @var string */
    protected $expectedResult;
    /** @var string */
    protected $client;

    /**
     * This should be instance of your plugin
     * @var PluginBase
     */
    protected $plugin;

    /**
     * This is maximum delay in SECONDS/2 that is tolerated by ping.
     * If delay is bigger response will be removed. Also clients will be disconnected!
     * @var int
     */
    protected $timeout = 60;

    /**
     * ResponseCheckTask constructor.
     * @param PluginBase $plugin
     * @param string $uuid
     * @param string $expectedResult
     * @param string $client
     */
    public function __construct(PluginBase $plugin, string $uuid, string $expectedResult, string $client = "default"){
        $this->uuid = $uuid;
        $this->expectedResult = $expectedResult;
        $this->plugin = $plugin;
        $this->client = $client;
    }

    public function onRun(int $currentTick) : void {
        if ($this->timeout === 0) return;

        $responses = StarGateAtlantis::getInstance()->getResponses($this->client);

        if (!isset($responses[$this->uuid]) || $responses[$this->uuid] === "unknown"){
            $this->timeout--;

            if ($this->timeout === 0){
                $this->error();
                return;
            }

            StarGateAtlantis::getInstance()->getScheduler()->scheduleDelayedTask($this, 10);
            return;
        }

        $responses = $responses[$this->uuid];
        $this->handleResult($responses, $this->expectedResult);
    }

    /**
     * Now you can do what you want with result
     * @param string $response
     * @param string $expectedResult
     * @return mixed
     */
    public abstract function handleResult(string $response, string $expectedResult);

    /** This function will be called if result will be never fetched*/
    public abstract function error() : void ;

    /**
     * Allows you to schedule task without calling Scheduler separately
     * @param int $delay
     */
    public function scheduleTask(int $delay) : void {
        $this->plugin->getScheduler()->scheduleDelayedTask($this, $delay);
    }
}