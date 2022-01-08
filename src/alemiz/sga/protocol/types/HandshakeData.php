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

namespace alemiz\sga\protocol\types;

use alemiz\sga\protocol\HandshakePacket;

class HandshakeData {

    public const SOFTWARE_POCKETMINE = 0;
    public const SOFTWARE_PMMP4 = 1;

    /**
     * @param HandshakePacket $packet
     * @param HandshakeData $handshakeData
     */
    public static function encodeData(HandshakePacket $packet, HandshakeData $handshakeData) : void {
        PacketHelper::writeInt($packet, $handshakeData->getSoftware());
        PacketHelper::writeString($packet, $handshakeData->getClientName());
        PacketHelper::writeString($packet, $handshakeData->getPassword());
        PacketHelper::writeInt($packet, $handshakeData->getProtocolVersion());
    }

    /**
     * @param HandshakePacket $packet
     * @return HandshakeData
     */
    public static function decodeData(HandshakePacket $packet) : HandshakeData {
        $software = PacketHelper::readInt($packet);
        $clientName = PacketHelper::readString($packet);
        $password = PacketHelper::readString($packet);
        $protocolVersion = PacketHelper::readInt($packet);
        return new HandshakeData($clientName, $password, $software, $protocolVersion);
    }

    /** @var string  */
    private string $clientName;
    /** @var string  */
    private string $password;
    /** @var int */
    private int $software;
    /** @var int */
    private int $protocolVersion;

    /**
     * HandshakeData constructor.
     * @param string $clientName
     * @param string $password
     * @param int $software
     * @param int $protocolVersion
     */
    public function __construct(string $clientName, string $password, int $software, int $protocolVersion){
        $this->clientName = $clientName;
        $this->password = $password;
        $this->software = $software;
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * @return string
     */
    public function getClientName() : string {
        return $this->clientName;
    }

    /**
     * @return string
     */
    public function getPassword() : string {
        return $this->password;
    }

    /**
     * @return int
     */
    public function getSoftware() : int {
        return $this->software;
    }

    /**
     * @return int
     */
    public function getProtocolVersion() : int {
        return $this->protocolVersion;
    }
}