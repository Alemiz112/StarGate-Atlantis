<?php
namespace alemiz\sga\packets;

use alemiz\sga\utils\Convertor;

class KickPacket extends StarGatePacket {

    /** @var string */
    public $player;
    /** @var string */
    public $reason;

    public function __construct(){
        parent::__construct("KICK_PACKET", Packets::KICK_PACKET);
    }

    public function decode() : void {
        $this->isEncoded = false;

        $data = Convertor::getPacketStringData($this->encoded);
        $this->player = $data[1];
        $this->reason = $data[2];
    }

    public function encode() : void {
        $convertor = new Convertor($this->getID());
        $convertor->putString($this->player);
        $convertor->putString($this->reason);

        $this->encoded = $convertor->getPacketString();
        $this->isEncoded = true;
    }

    /**
     * @return string
     */
    public function getPlayer(): string {
        return $this->player;
    }

    /**
     * @return string
     */
    public function getReason(): string {
        return $this->reason;
    }
}