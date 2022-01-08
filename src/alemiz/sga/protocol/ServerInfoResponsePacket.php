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
use function explode;
use function implode;

class ServerInfoResponsePacket extends StarGatePacket {

    /** @var string */
    private string $serverName;
    /** @var bool */
    private bool $selfInfo;
    /** @var int */
    private int $onlinePlayers;
    /** @var int */
    private int $maxPlayers;
    /** @var string[] */
    private array $playerList;
    /** @var string[] */
    private array $serverList;

    public function encodePayload() : void {
        PacketHelper::writeString($this, $this->serverName);
        PacketHelper::writeBoolean($this, $this->selfInfo);

        PacketHelper::writeInt($this, $this->onlinePlayers);
        PacketHelper::writeInt($this, $this->maxPlayers);

        PacketHelper::writeArray($this, $this->playerList, static function (StarGatePacket $buf, string $playerName){
            PacketHelper::writeString($buf, $playerName);
        });

        PacketHelper::writeArray($this, $this->serverList, static function (StarGatePacket $buf, string $serverName){
            PacketHelper::writeString($buf, $serverName);
        });
    }

    public function decodePayload() : void {
        $this->serverName = PacketHelper::readString($this);
        $this->selfInfo = PacketHelper::readBoolean($this);

        $this->onlinePlayers = PacketHelper::readInt($this);
        $this->maxPlayers = PacketHelper::readInt($this);

        $this->playerList = PacketHelper::readArray($this, static function(StarGatePacket $buf){
           return PacketHelper::readString($buf);
        });

        $this->serverList = PacketHelper::readArray($this, static function(StarGatePacket $buf){
            return PacketHelper::readString($buf);
        });
    }

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler) : bool {
        return $handler->handleServerInfoResponse($this);
    }

    public function getPacketId() : int {
        return StarGatePackets::SERVER_INFO_RESPONSE_PACKET;
    }

    /**
     * @return bool
     */
    public function isResponse() : bool {
        return true;
    }

    /**
     * @param string $serverName
     */
    public function setServerName(string $serverName) : void {
        $this->serverName = $serverName;
    }

    /**
     * @return string
     */
    public function getServerName() : string {
        return $this->serverName;
    }

    /**
     * @param bool $selfInfo
     */
    public function setSelfInfo(bool $selfInfo) : void {
        $this->selfInfo = $selfInfo;
    }

    /**
     * @return bool
     */
    public function isSelfInfo() : bool {
        return $this->selfInfo;
    }

    /**
     * @param int $onlinePlayers
     */
    public function setOnlinePlayers(int $onlinePlayers) : void {
        $this->onlinePlayers = $onlinePlayers;
    }

    /**
     * @return int
     */
    public function getOnlinePlayers() : int {
        return $this->onlinePlayers;
    }

    /**
     * @param int $maxPlayers
     */
    public function setMaxPlayers(int $maxPlayers) : void {
        $this->maxPlayers = $maxPlayers;
    }

    /**
     * @return int
     */
    public function getMaxPlayers() : int {
        return $this->maxPlayers;
    }

    /**
     * @param string[] $playerList
     */
    public function setPlayerList(array $playerList) : void {
        $this->playerList = $playerList;
    }

    /**
     * @return string[]
     */
    public function getPlayerList() : array {
        return $this->playerList;
    }

    /**
     * @param string[] $serverList
     */
    public function setServerList(array $serverList) : void {
        $this->serverList = $serverList;
    }

    /**
     * @return string[]
     */
    public function getServerList() : array {
        return $this->serverList;
    }
}