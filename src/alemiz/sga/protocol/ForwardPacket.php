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

class ForwardPacket extends StarGatePacket
{

    /** @var UnknownPacket */
    public UnknownPacket $packet;
    /** @var string */
    private string $clientName;

    /**
     * @param string $clientName
     * @param StarGatePacket $packet
     * @return ForwardPacket
     */
    public static function from(string $clientName, StarGatePacket $packet): ForwardPacket
    {
        $forwardPacket = new ForwardPacket();
        $forwardPacket->setClientName($clientName);

        $unknownPacket = new UnknownPacket();
        $unknownPacket->setPacketId($packet->getPacketId());
        $packet->rewind();
        $packet->encodePayload();
        $unknownPacket->setPayload($packet->getBuffer());

        $forwardPacket->setPacket($unknownPacket);
        return $forwardPacket;
    }

    /**
     * @return int
     */
    public function getPacketId(): int
    {
        return StarGatePackets::FORWARD_PACKET;
    }

    public function encodePayload(): void
    {
        PacketHelper::writeString($this, $this->clientName);
        $this->putByte($this->packet->getPacketId());
        PacketHelper::writeByteArray($this, $this->packet->getPayload());
    }

    public function decodePayload(): void
    {
        $this->clientName = PacketHelper::readString($this);

        $packet = new UnknownPacket();
        $packet->setPacketId($this->getByte());
        $packet->setPayload(PacketHelper::readByteArray($this));
        $this->packet = $packet;
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler): bool
    {
        return $handler->handleForwardPacket($this);
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        return $this->clientName;
    }

    /**
     * @param string $clientName
     */
    public function setClientName(string $clientName): void
    {
        $this->clientName = $clientName;
    }

    /**
     * @return UnknownPacket
     */
    public function getPacket(): UnknownPacket
    {
        return $this->packet;
    }

    /**
     * @param UnknownPacket $packet
     */
    public function setPacket(UnknownPacket $packet): void
    {
        $this->packet = $packet;
    }
}