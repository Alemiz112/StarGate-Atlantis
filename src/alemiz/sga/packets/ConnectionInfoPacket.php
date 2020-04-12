<?php
namespace alemiz\sga\packets;

use alemiz\sga\utils\Convertor;

class ConnectionInfoPacket extends StarGatePacket {

    const CONNECTION_CONNECTED = 0;
    const CONNECTION_CLOSED = 1;
    const CONNECTION_RECONNECT = 2;

    const CONNECTION_ABORTED = 5;

    const ABORTED = "Connection unacceptable closed";
    const WRONG_PASSWORD = "Wrong password";
    const CLIENT_SHUTDOWN = "Client was shutdown";
    const SERVER_SHUTDOWN = "Server was shutdown";

    /** @var int */
    public $packetType;

    /** @var string|null */
    public $reason = null;

    public function __construct(){
        parent::__construct("CONNECTION_INFO_PACKET", Packets::CONNECTION_INFO_PACKET);
    }

    public function decode() : void {
        $this->isEncoded = false;

        $data = Convertor::getPacketStringData($this->encoded);
        $this->packetType = (int) $data[1];

        if (count($data) > 3){
            $this->reason = $data[2];
        }
    }

    public function encode() : void {
        $convertor = new Convertor($this->getID());

        $convertor->putInt($this->packetType);
        if ($this->reason != null){
            $convertor->putString($this->reason);
        }

        $this->encoded = $convertor->getPacketString();
        $this->isEncoded = true;
    }

    /**
     * @return int
     */
    public function getPacketType(): int {
        return $this->packetType;
    }

    /**
     * @return string|null
     */
    public function getReason(){
        return $this->reason;
    }
}