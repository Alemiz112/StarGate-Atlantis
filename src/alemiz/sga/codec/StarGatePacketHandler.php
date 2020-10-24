<?php

namespace alemiz\sga\codec;

use alemiz\sga\protocol\DisconnectPacket;
use alemiz\sga\protocol\HandshakePacket;
use alemiz\sga\protocol\PingPacket;
use alemiz\sga\protocol\PongPacket;
use alemiz\sga\protocol\ReconnectPacket;
use alemiz\sga\protocol\ServerHandshakePacket;

abstract class StarGatePacketHandler {

    /**
     * @param HandshakePacket $packet
     * @return bool
     */
    public function handleHandshake(HandshakePacket $packet) : bool {
        return false;
    }

    /**
     * @param ServerHandshakePacket $packet
     * @return bool
     */
    public function handleServerHandshake(ServerHandshakePacket $packet) : bool {
        return false;
    }

    /**
     * @param DisconnectPacket $packet
     * @return bool
     */
    public function handleDisconnect(DisconnectPacket $packet) : bool {
        return false;
    }

    /**
     * @param PingPacket $packet
     * @return bool
     */
    public function handlePing(PingPacket $packet) : bool {
        return false;
    }

    /**
     * @param PongPacket $packet
     * @return bool
     */
    public function handlePong(PongPacket $packet) : bool {
        return false;
    }

    /**
     * @param ReconnectPacket $packet
     * @return bool
     */
    public function handleReconnect(ReconnectPacket $packet) : bool {
        return false;
    }
}