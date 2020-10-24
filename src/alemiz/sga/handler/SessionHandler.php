<?php

namespace alemiz\sga\handler;

use alemiz\sga\client\ClientSession;
use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\protocol\DisconnectPacket;

class SessionHandler extends StarGatePacketHandler {

    /** @var ClientSession */
    protected $session;

    public function __construct(ClientSession $session) {
        $this->session = $session;
    }

    /**
     * @return ClientSession
     */
    public function getSession() : ClientSession {
        return $this->session;
    }

    /**
     * @param DisconnectPacket $packet
     * @return bool
     */
    public function handleDisconnect(DisconnectPacket $packet) : bool {
        $this->session->onDisconnect($packet->getReason());
        return true;
    }

}