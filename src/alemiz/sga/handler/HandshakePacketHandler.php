<?php

namespace alemiz\sga\handler;

use alemiz\sga\client\StarGateConnection;
use alemiz\sga\protocol\ServerHandshakePacket;

class HandshakePacketHandler extends SessionHandler {

    /**
     * @param ServerHandshakePacket $packet
     * @return bool
     */
    public function handleServerHandshake(ServerHandshakePacket $packet) : bool {
        $this->session->getClient()->onSessionAuthenticated();
        $this->session->getConnection()->setState(StarGateConnection::STATE_AUTHENTICATED);
        $this->session->setPacketHandler(new ConnectedPacketHandler($this->session));
        return true;
    }

}