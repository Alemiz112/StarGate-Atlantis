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

namespace alemiz\sga\protocol;

use alemiz\sga\codec\StarGatePacketHandler;

class UnknownPacket extends StarGatePacket {

    /** @var int */
    private $packetId;
    /** @var string */
    private $payload;

    public function encodePayload() : void {
        $this->put($this->payload);
    }

    public function decodePayload() : void {
       $this->payload = $this->getBuffer();
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler): bool {
        return $handler->handleUnknown($this);
    }

    public function getPacketId() : int {
        return $this->packetId;
    }

    /**
     * @param int $packetId
     */
    public function setPacketId(int $packetId) : void {
        $this->packetId = $packetId;
    }

    /**
     * @return string
     */
    public function getPayload() : string {
        return $this->payload;
    }

    /**
     * @param string $payload
     */
    public function setPayload(string $payload) : void {
        $this->payload = $payload;
    }
}