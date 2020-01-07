<?php
namespace alemiz\sga\packets;

use alemiz\sga\StarGateAtlantis;
use alemiz\sga\utils\Convertor;
use pocketmine\Player;

class PlayerTransferPacket extends StarGatePacket {

    /** @var Player */
    public $player;
    /** @var string */
    public $destination;

    public function __construct(){
        parent::__construct("PLAYER_TRANSFORM_PACKET", Packets::PLAYER_TRANSFORM_PACKET);
    }

    public function decode(){
        $this->isEncoded = false;

        $data = Convertor::getPacketStringData($this->encoded);
        $this->player = StarGateAtlantis::getInstance()->getServer()->getPlayer($data[1]);
        $this->destination = $data[2];
    }

    public function encode(){
        $convertor = new Convertor($this->getID());

        $convertor->putString($this->player->getName());
        $convertor->putString($this->destination);

        $this->encoded = $convertor->getPacketString();
        $this->isEncoded = true;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player{
        return $this->player;
    }

    /**
     * @return string
     */
    public function getDestination(): string{
        return $this->destination;
    }
}