<?php
namespace alemiz\sga\tasks;

use alemiz\sga\StarGateAtlantis;
use pocketmine\scheduler\Task;

class ResponseRemoveTask extends Task {

    /** @var string */
    private $uuid;

    /**
     * ResponseRemoveTask constructor.
     * @param string $uuid
     */
    public function __construct(string $uuid){
        $this->uuid = $uuid;
    }

    public function onRun(int $currentTick){
        unset(StarGateAtlantis::getInstance()->getResponses()[$this->uuid]);
    }
}