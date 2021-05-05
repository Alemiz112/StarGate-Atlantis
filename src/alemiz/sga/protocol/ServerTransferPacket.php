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

class ServerTransferPacket extends StarGatePacket {

    /** @var string */
    private string $playerName;
    /** @var string */
    private string $targetServer;

    public function encodePayload() : void {
        PacketHelper::writeString($this, $this->playerName);
        PacketHelper::writeString($this, $this->targetServer);
    }

    public function decodePayload() : void {
        $this->playerName = PacketHelper::readString($this);
        $this->targetServer = PacketHelper::readString($this);
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler) : bool {
        return $handler->handleServerTransfer($this);
    }

    public function getPacketId() : int {
        return StarGatePackets::SERVER_TRANSFER_PACKET;
    }

    /**
     * @param string $playerName
     */
    public function setPlayerName(string $playerName) : void {
        $this->playerName = $playerName;
    }

    /**
     * @return string
     */
    public function getPlayerName() : string {
        return $this->playerName;
    }

    /**
     * @param string $targetServer
     */
    public function setTargetServer(string $targetServer) : void {
        $this->targetServer = $targetServer;
    }

    /**
     * @return string
     */
    public function getTargetServer() : string {
        return $this->targetServer;
    }

}