<?php

namespace alemiz\sga\handler;

use alemiz\sga\protocol\PingPacket;
use alemiz\sga\protocol\PongPacket;
use alemiz\sga\protocol\ReconnectPacket;

class ConnectedPacketHandler extends SessionHandler {

    /**
     * @param PingPacket $packet
     * @return bool
     */
    public function handlePing(PingPacket $packet) : bool {
        $pongPacket = new PongPacket();
        $pongPacket->setPingTime($packet->getPingTime());
        $this->session->sendPacket($pongPacket);
        return true;
    }

    /**
     * @param PongPacket $packet
     * @return bool
     */
    public function handlePong(PongPacket $packet) : bool {
        $this->session->onPongReceive($packet);
        return true;
    }

    /**
     * @param ReconnectPacket $packet
     * @return bool
     */
    public function handleReconnect(ReconnectPacket $packet) : bool {
        $this->session->reconnect($packet->getReason(), false);
        return true;
    }
}