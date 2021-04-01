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

use alemiz\sga\protocol\PingPacket;
use alemiz\sga\protocol\PongPacket;
use alemiz\sga\protocol\ReconnectPacket;

class ConnectedPacketHandler extends CommonSessionHandler {

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