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

namespace alemiz\sga\protocol;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\codec\StarGatePackets;
use alemiz\sga\protocol\types\PacketHelper;
use alemiz\sga\utils\LogLevel;

class ServerHandshakePacket extends StarGatePacket {

    /** @var bool */
    private bool $success = true;


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

    /**
     * @return int
     */
    public function getLogLevel() : int {
        return LogLevel::LEVEL_ALL;
    }
}