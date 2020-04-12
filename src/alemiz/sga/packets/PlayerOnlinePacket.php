<?php
namespace alemiz\sga\packets;

use alemiz\sga\StarGateAtlantis;
use alemiz\sga\utils\Convertor;
use pocketmine\Player;

class PlayerOnlinePacket extends StarGatePacket {

    /** @var Player|null */
    public $player;
    /** @var string */
    public $customPlayer;

    public function __construct(){
        parent::__construct("PLAYER_ONLINE_PACKET", Packets::PLAYER_ONLINE_PACKET);
    }

    public function decode() : void {
        $this->isEncoded = false;

        $data = Convertor::getPacketStringData($this->encoded);
        $player = StarGateAtlantis::getInstance()->getServer()->getPlayer($data[1]);

        if (is_null($player)){
            $this->customPlayer = $data[1];
        }else $this->player = $player;
    }

    public function encode() : void {
        $convertor = new Convertor($this->getID());

        if (!is_null($this->player)){
            $convertor->putString($this->player->getName());
        }else $convertor->putString($this->customPlayer);

        $this->encoded = $convertor->getPacketString();
        $this->isEncoded = true;
    }

    /**
     * @return Player|null
     */
    public function getPlayer(): ?Player {
        return $this->player;
    }

    /**
     * @return string
     */
    public function getCustomPlayer(): string {
        return $this->customPlayer;
    }
}