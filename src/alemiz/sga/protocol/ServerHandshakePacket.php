<?php

namespace alemiz\sga\protocol;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\codec\StarGatePackets;
use alemiz\sga\protocol\types\PacketHelper;

class ServerHandshakePacket extends StarGatePacket {

    /** @var bool */
    private $success;


    public function encodePayload() : void {
        PacketHelper::writeBoolean($this, $this->success);
    }

    public function decodePayload() : void {
        $this->success = PacketHelper::readBoolean($this);
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler) : bool {
        return $handler->handleServerHandshake($this);
    }

    public function getPacketId() : int {
        return StarGatePackets::SERVER_HANDSHAKE_PACKET;
    }

}