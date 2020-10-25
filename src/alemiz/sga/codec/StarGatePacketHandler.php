<?php
/*
 * Copyright 2020 Alemiz
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace alemiz\sga\codec;

use alemiz\sga\protocol\DisconnectPacket;
use alemiz\sga\protocol\ForwardPacket;
use alemiz\sga\protocol\HandshakePacket;
use alemiz\sga\protocol\PingPacket;
use alemiz\sga\protocol\PongPacket;
use alemiz\sga\protocol\ReconnectPacket;
use alemiz\sga\protocol\ServerHandshakePacket;
use alemiz\sga\protocol\ServerInfoRequestPacket;
use alemiz\sga\protocol\ServerInfoResponsePacket;

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

    /**
     * @param ForwardPacket $packet
     * @return bool
     */
    public function handleForwardPacket(ForwardPacket $packet) : bool {
        return false;
    }

    /**
     * @param ServerInfoRequestPacket $packet
     * @return bool
     */
    public function handleServerInfoRequest(ServerInfoRequestPacket $packet) : bool {
        return false;
    }

    /**
     * @param ServerInfoResponsePacket $packet
     * @return bool
     */
    public function handleServerInfoResponse(ServerInfoResponsePacket $packet) : bool {
        return false;
    }
}