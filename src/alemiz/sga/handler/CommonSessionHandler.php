<?php
/*
 * Copyright 2021 Alemiz
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

use alemiz\sga\protocol\DisconnectPacket;
use alemiz\sga\protocol\UnknownPacket;

class CommonSessionHandler extends SessionHandler {

    /**
     * @param DisconnectPacket $packet
     * @return bool
     */
    public function handleDisconnect(DisconnectPacket $packet) : bool {
        $this->session->onDisconnect($packet->getReason());
        return true;
    }

    /**
     * @param UnknownPacket $packet
     * @return bool
     */
    public function handleUnknown(UnknownPacket $packet): bool {
        $this->session->getLogger()->info("Received UnknownPacket packetId=".$packet->getPacketId()." payload=".$packet->getPayload());
        return true;
    }
}