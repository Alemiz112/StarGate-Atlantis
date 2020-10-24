<?php

namespace alemiz\sga\protocol;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\codec\StarGatePackets;
use alemiz\sga\protocol\types\PacketHelper;

class DisconnectPacket extends StarGatePacket {

    public const CLIENT_SHUTDOWN = "StarGate client shutdown!";

    /** @var string */
    private $reason;

    public function encodePayload() : void {
        PacketHelper::writeString($this, $this->reason);
    }

    public function decodePayload() : void {
        $this->reason = PacketHelper::readString($this);
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler) : bool {
        return $handler->handleDisconnect($this);
    }

    public function getPacketId() : int {
        return StarGatePackets::DISCONNECT_PACKET;
    }

    /**
     * @param string $reason
     */
    public function setReason(string $reason) : void {
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getReason() : string {
        return $this->reason;
    }
}