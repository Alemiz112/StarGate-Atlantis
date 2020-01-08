<?php
namespace tests;

use alemiz\sga\StarGateAtlantis;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class OnlineCommand extends Command {

    public function __construct(){
        parent::__construct("online","Online test", "");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if (!($sender instanceof Player)) return true;

        $uuid = StarGateAtlantis::getInstance()->isOnline($sender);
        $plugin = StarGateAtlantis::getInstance();

        /* Example using ResponseCheckTask*/
        $task = new OnlineExample($plugin, $uuid, "true", $sender, "alemiz003");
        $task->scheduleTask(10);

        /* Example using closure. More simpler*/
        //$closure = function ($response){
        //    StarGateAtlantis::getInstance()->getLogger()->info("§a".$response);
        //};
        //StarGateAtlantis::getInstance()->isOnline("alemiz0003", $closure);
    }


}