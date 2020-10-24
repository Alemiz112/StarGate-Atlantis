<?php

namespace alemiz\sga\protocol;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\codec\StarGatePackets;
use alemiz\sga\protocol\types\PacketHelper;

class PingPacket extends StarGatePacket {

    /** @var int */
    private $pingTime;

    public function encodePayload() : void {
        PacketHelper::writeLong($this, $this->pingTime);
    }

    public function decodePayload() : void {
        $this->pingTime = PacketHelper::readLong($this);
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler) : bool {
        return $handler->handlePing($this);
    }

    public function getPacketId() : int {
        return StarGatePackets::PING_PACKET;
    }

    /**
     * @param int $pingTime
     */
    public function setPingTime(int $pingTime) : void {
        $this->pingTime = $pingTime;
    }

    /**
     * @return int
     */
    public function getPingTime() : int {
        return $this->pingTime;
    }

}