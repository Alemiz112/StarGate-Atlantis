<?php
namespace alemiz\sga\packets;


use alemiz\sga\utils\Convertor;

class WelcomePacket extends StarGatePacket {

    /** @var string */
    public $server;

    /** @var int */
    public $tps;

    /** @var int */
    public $players;

    /** @var int */
    public $usage;

    public function __construct(){
        parent::__construct("WELCOME_PACKET", Packets::WELCOME_PACKET);
    }

    public function decode(){
        /* This is very important! Server will try to decode packet if it will be not set correctly
        * And that can return unupdated packet*/
        $this->isEncoded = false;

        /* data[0] => ID*/
        $data = Convertor::getPacketStringData($this->encoded);
        $this->server = $data[1];
        $this->tps = (int) $data[2];
        $this->usage = (int) $data[3];
        $this->players = (int) $data[4];
    }

    /**
     * Using @class Convertor we can create packetString from custom data
     * It supports custom converting methods
     * You must create new Conventor class then using dynamic functions you can putString|putInt|or other data
     * For more docs see GitHub documentation
     */
    public function encode(){
        $convertor = new Convertor($this->getID());

        $convertor->putString($this->server);
        $convertor->putInt($this->tps);
        $convertor->putInt($this->usage);
        $convertor->putInt($this->players);

        $this->encoded = $convertor->getPacketString();
        $this->isEncoded = true;
    }
}