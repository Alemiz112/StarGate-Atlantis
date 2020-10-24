<?php

namespace alemiz\sga\protocol;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\codec\StarGatePackets;
use alemiz\sga\protocol\types\HandshakeData;

class HandshakePacket extends StarGatePacket {

    /** @var HandshakeData */
    private $handshakeData;

    public function encodePayload() : void {
        HandshakeData::encodeData($this, $this->handshakeData);
    }

    public function decodePayload() : void {
        $this->handshakeData = HandshakeData::decodeData($this);
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler) : bool {
        return $handler->handleHandshake($this);
    }

    public function getPacketId() : int {
        return StarGatePackets::HANDSHAKE_PACKET;
    }

    /**
     * @param HandshakeData $handshakeData
     */
    public function setHandshakeData(HandshakeData $handshakeData) : void {
        $this->handshakeData = $handshakeData;
    }

    /**
     * @return HandshakeData
     */
    public function getHandshakeData() : HandshakeData{
        return $this->handshakeData;
    }
}