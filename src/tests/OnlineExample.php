<?php
namespace tests;

use alemiz\sga\tasks\ResponseCheckTask;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class OnlineExample extends ResponseCheckTask{

    /** @var Player */
    private $executor;
    /** @var string */
    private $finding;

    public function __construct(PluginBase $plugin, string $uuid, string $expectedResult, Player $executor, string $finding){
        parent::__construct($plugin, $uuid, $expectedResult);
        $this->executor = $executor;
        $this->finding = $finding;
    }

    public function handleResult(string $response, string $expectedResult){
        $this->plugin->getLogger()->info("§bHandled OnlinePlayer response");

        if ($response === "false"){
            $this->executor->sendMessage("§ePlayer §e".$this->finding."is OFFLINE");
            return;
        }

        $data = explode("!", $response);
        $this->executor->sendMessage("§6Player §e".$this->finding." §6is ONLINE at server§e ".$data[1]);
    }

    public function error() : void {
        $this->plugin->getLogger()->warning("§cResponse for uuid §5".$this->uuid."§cwas not received!");
    }
}