<?php
namespace alemiz\sga\packets;

use alemiz\sga\utils\Convertor;

class PlayerTransferPacket extends StarGatePacket {

    /** @var string */
    public $player;
    /** @var string */
    public $destination;

    public function __construct(){
        parent::__construct("PLAYER_TRANSFER_PACKET", Packets::PLAYER_TRANSFER_PACKET);
    }

    public function decode() : void {
        $this->isEncoded = false;

        $data = Convertor::getPacketStringData($this->encoded);
        $this->player = $data[1];
        $this->destination = $data[2];
    }

    public function encode() : void {
        if (is_null($this->player)) return;

        $convertor = new Convertor($this->getID());
        $convertor->putString($this->player);
        $convertor->putString($this->destination);

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
    public function getDestination(): string {
        return $this->destination;
    }
}