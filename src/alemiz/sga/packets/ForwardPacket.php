<?php
namespace alemiz\sga\packets;

use alemiz\sga\utils\Convertor;

class ForwardPacket extends StarGatePacket {

    /** @var string */
    public $client;

    /** @var string */
    public $encodedPacket = "";

    public function __construct(){
        parent::__construct("FORWARD_PACKET", Packets::FORWARD_PACKET);
    }

    public function decode(){
        $this->isEncoded = false;

        $data = Convertor::getPacketStringData($this->encoded);
        $this->client = $data[1];

        unset($data[0]);
        $this->encodedPacket = implode("!", $data);
    }

    public function encode(){
        $convertor = new Convertor($this->getID());
        $convertor->putString($this->client);

        $forwardedPacketData = Convertor::getPacketStringData($this->encodedPacket);
        foreach ($forwardedPacketData as $data){
            $convertor->putString($data);
        }

        $this->encoded = $convertor->getPacketString();
        $this->isEncoded = true;
    }
}