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

class DisconnectPacket extends StarGatePacket
{

    public const CLIENT_SHUTDOWN = "StarGate client shutdown!";

    /** @var string */
    private string $reason;

    public function encodePayload(): void
    {
        PacketHelper::writeString($this, $this->reason);
    }

    public function decodePayload(): void
    {
        $this->reason = PacketHelper::readString($this);
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler): bool
    {
        return $handler->handleDisconnect($this);
    }

    public function getPacketId(): int
    {
        return StarGatePackets::DISCONNECT_PACKET;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return int
     */
    public function getLogLevel(): int
    {
        return LogLevel::LEVEL_ALL;
    }
}