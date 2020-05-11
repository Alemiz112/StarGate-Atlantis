<?php
namespace alemiz\sga\packets;

use alemiz\sga\utils\Convertor;

class PingPacket extends StarGatePacket {


    /** @var string */
    public $client;

    public function __construct(){
        parent::__construct("PING_PACKET", Packets::PING_PACKET);
    }

    public function decode() : void {
        $this->isEncoded = false;

        $data = Convertor::getPacketStringData($this->encoded);
        $this->client = $data[2];
    }

    public function encode() : void {
        $convertor = new Convertor($this->getID());
        $convertor->putString($this->client);

        $this->encoded = $convertor->getPacketString();
        $this->isEncoded = true;
    }
}