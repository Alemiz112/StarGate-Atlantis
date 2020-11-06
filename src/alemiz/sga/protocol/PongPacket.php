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

class PongPacket extends StarGatePacket {

    /** @var int */
    private $pingTime;
    /** @var int */
    private $pongTime;

    public function encodePayload() : void {
        PacketHelper::writeLong($this, $this->pingTime);
    }

    public function decodePayload() : void {
        $this->pingTime = PacketHelper::readLong($this);
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler) : bool {
        return $handler->handlePong($this);
    }

    public function getPacketId() : int {
        return StarGatePackets::PONG_PACKET;
    }

    /**
     * @param int $pingTime
     */
    public function setPingTime(int $pingTime) : void {
        $this->pingTime = $pingTime;
    }

    /**
     * @return int
     */
    public function getPingTime() : int {
        return $this->pingTime;
    }

    /**
     * @param int $pongTime
     */
    public function setPongTime(int $pongTime) : void {
        $this->pongTime = $pongTime;
    }

    /**
     * @return int
     */
    public function getPongTime():  int {
        return $this->pongTime;
    }

    /**
     * @return int
     */
    public function getLogLevel() : int {
        return LogLevel::LEVEL_ALL;
    }
}