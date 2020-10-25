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

class ForwardPacket extends StarGatePacket {

    /**
     * @param string $clientName
     * @param StarGatePacket $packet
     * @return ForwardPacket
     */
    public static function from(string $clientName, StarGatePacket $packet) : ForwardPacket {
        $forwardPacket = new ForwardPacket();
        $forwardPacket->setClientName($clientName);
        $forwardPacket->setForwardPacketId($packet->getPacketId());

        $packet->reset();
        $packet->encodePayload();
        $forwardPacket->setPayload($packet->getBuffer());
        return $forwardPacket;
    }

    /** @var string */
    private $clientName;
    /** @var int */
    private $forwardPacketId;
    /** @var string */
    private $payload;

    public function encodePayload() : void {
        PacketHelper::writeString($this, $this->clientName);
        $this->putByte($this->forwardPacketId);
        PacketHelper::writeByteArray($this, $this->payload);
    }

    public function decodePayload() : void {
        $this->clientName = PacketHelper::readString($this);
        $this->forwardPacketId = PacketHelper::readInt($this);
        $this->payload = PacketHelper::readByteArray($this);
    }

    /**
     * @return int
     */
    public function getPacketId() : int {
        return StarGatePackets::FORWARD_PACKET;
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler) : bool {
        return $handler->handleForwardPacket($this);
    }

    /**
     * @param string $clientName
     */
    public function setClientName(string $clientName) : void {
        $this->clientName = $clientName;
    }

    /**
     * @return string
     */
    public function getClientName() : string {
        return $this->clientName;
    }

    /**
     * @param int $forwardPacketId
     */
    public function setForwardPacketId(int $forwardPacketId) : void {
        $this->forwardPacketId = $forwardPacketId;
    }

    /**
     * @return int
     */
    public function getForwardPacketId() : int {
        return $this->forwardPacketId;
    }

    /**
     * @param string $payload
     */
    public function setPayload(string $payload) : void {
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getPayload() : string {
        return $this->payload;
    }
}