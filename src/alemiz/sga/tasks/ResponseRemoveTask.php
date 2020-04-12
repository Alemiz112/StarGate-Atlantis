<?php
namespace alemiz\sga\tasks;

use alemiz\sga\StarGateAtlantis;
use pocketmine\scheduler\Task;

class ResponseRemoveTask extends Task {

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $client;

    /**
     * ResponseRemoveTask constructor.
     * @param string $uuid
     * @param string $client
     */
    public function __construct(string $uuid, string $client){
        $this->uuid = $uuid;
        $this->client = $client;
    }

    public function onRun(int $currentTick) : void {
        StarGateAtlantis::getInstance()->unsetResponse($this->uuid, $this->client);
    }
}