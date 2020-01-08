<?php
namespace tests;

use alemiz\sga\StarGateAtlantis;
use alemiz\sga\tasks\ResponseCheckTask;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class EnchanedResponseCheckTask extends ResponseCheckTask{

    /** @var Player */
    private $executor;
    /** @var string */
    private $finding;

    public function __construct(PluginBase $plugin, string $uuid, string $expectedResult){
        parent::__construct($plugin, $uuid, $expectedResult);
    }

    /* After successfully received response this function will be called*/
    public function handleResult(string $response, string $expectedResult){
        if ($response === $expectedResult){
            $this->plugin->getLogger()->info("§bRight Response handled!");
        }else{
            $this->plugin->getLogger()->info("§c".$response);
        }
    }

    public function error(){
        $this->plugin->getLogger()->info("§cERROR!");
    }
}