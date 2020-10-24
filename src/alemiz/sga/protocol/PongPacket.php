<?php

namespace alemiz\sga\protocol;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\codec\StarGatePackets;
use alemiz\sga\protocol\types\PacketHelper;

class PongPacket extends StarGatePacket {

    /** @var int */
    private $pingTime;
    /** @var int */
    private $pongTime;

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
        return $handler->handlePong($this);
    }

    public function getPacketId() : int {
        return StarGatePackets::PONG_PACKET;
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

    /**
     * @param int $pongTime
     */
    public function setPongTime(int $pongTime) : void {
        $this->pongTime = $pongTime;
    }

    /**
     * @return int
     */
    public function getPongTime():  int {
        return $this->pongTime;
    }

}