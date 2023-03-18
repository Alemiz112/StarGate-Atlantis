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

namespace alemiz\sga\handler;

use alemiz\sga\client\StarGateConnection;
use alemiz\sga\protocol\ServerHandshakePacket;

class HandshakePacketHandler extends CommonSessionHandler
{

    /**
     * @param ServerHandshakePacket $packet
     * @return bool
     */
    public function handleServerHandshake(ServerHandshakePacket $packet): bool
    {
        $this->session->getClient()->onSessionAuthenticated();
        $this->session->getConnection()->setState(StarGateConnection::STATE_AUTHENTICATED);
        $this->session->setPacketHandler(new ConnectedPacketHandler($this->session));
        return true;
    }

}