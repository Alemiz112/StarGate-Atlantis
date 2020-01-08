<?php
namespace tests;

use alemiz\sga\StarGateAtlantis;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class KickExample extends Command {

    public function __construct(){
        parent::__construct("kick","StarGate test", "");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if (!($sender instanceof Player)) return true;

        StarGateAtlantis::getInstance()->kickPlayer($sender, "KICK_REASON");
    }


}