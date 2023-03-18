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

namespace alemiz\sga\codec;

use alemiz\sga\protocol\DisconnectPacket;
use alemiz\sga\protocol\ForwardPacket;
use alemiz\sga\protocol\HandshakePacket;
use alemiz\sga\protocol\PingPacket;
use alemiz\sga\protocol\PlayerPingRequestPacket;
use alemiz\sga\protocol\PlayerPingResponsePacket;
use alemiz\sga\protocol\PongPacket;
use alemiz\sga\protocol\ReconnectPacket;
use alemiz\sga\protocol\ServerHandshakePacket;
use alemiz\sga\protocol\ServerInfoRequestPacket;
use alemiz\sga\protocol\ServerInfoResponsePacket;
use alemiz\sga\protocol\ServerTransferPacket;
use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\protocol\UnknownPacket;
use pocketmine\utils\Binary;
use function strlen;
use function substr;

class ProtocolCodec
{

    public const STARGATE_MAGIC = 0xa20;

    /** @var StarGatePacket[] */
    private array $packetPool = [];

    /**
     * ProtocolCodec constructor.
     */
    public function __construct()
    {
        $this->registerPacket(StarGatePackets::HANDSHAKE_PACKET, new HandshakePacket());
        $this->registerPacket(StarGatePackets::SERVER_HANDSHAKE_PACKET, new ServerHandshakePacket());
        $this->registerPacket(StarGatePackets::DISCONNECT_PACKET, new DisconnectPacket());
        $this->registerPacket(StarGatePackets::PING_PACKET, new PingPacket());
        $this->registerPacket(StarGatePackets::PONG_PACKET, new PongPacket());
        $this->registerPacket(StarGatePackets::RECONNECT_PACKET, new ReconnectPacket());
        $this->registerPacket(StarGatePackets::FORWARD_PACKET, new ForwardPacket());
        $this->registerPacket(StarGatePackets::SERVER_INFO_REQUEST_PACKET, new ServerInfoRequestPacket());
        $this->registerPacket(StarGatePackets::SERVER_INFO_RESPONSE_PACKET, new ServerInfoResponsePacket());
        $this->registerPacket(StarGatePackets::SERVER_TRANSFER_PACKET, new ServerTransferPacket());
        $this->registerPacket(StarGatePackets::PLAYER_PING_REQUEST_PACKET, new PlayerPingRequestPacket());
        $this->registerPacket(StarGatePackets::PLAYER_PING_RESPONSE_PACKET, new PlayerPingResponsePacket());
    }

    /**
     * @param int $packetId
     * @param StarGatePacket $packet
     * @return bool
     */
    public function registerPacket(int $packetId, StarGatePacket $packet): bool
    {
        if (isset($this->packetPool[$packetId])) {
            return false;
        }
        $this->packetPool[$packetId] = clone $packet;
        return true;
    }

    /**
     * @param int $packetId
     * @return StarGatePacket|null
     */
    public function unregisterPacket(int $packetId): ?StarGatePacket
    {
        $oldPacket = $this->packetPool[$packetId] ?? null;
        unset($this->packetPool[$packetId]);
        return $oldPacket;
    }

    /**
     * @param StarGatePacket $packet
     * @return string
     */
    public function tryEncode(StarGatePacket $packet): string
    {
        $buffer = Binary::writeByte($packet->getPacketId());
        $supportsResponse = $packet->isResponse() || $packet->sendsResponse();
        $buffer .= Binary::writeBool($supportsResponse);
        if ($supportsResponse) {
            $buffer .= Binary::writeInt($packet->getResponseId());
        }

        $packet->rewind();
        $packet->encodePayload();
        $buffer .= $packet->getBuffer();

        $encoded = Binary::writeInt(strlen($buffer));
        $encoded .= $buffer;
        return $encoded;
    }

    /**
     * @param string $encoded
     * @return StarGatePacket|null
     */
    public function tryDecode(string $encoded): ?StarGatePacket
    {
        $packetId = Binary::readByte($encoded);
        $offset = 1;

        $packet = $this->getPacketInstance($packetId);
        if ($packet === null) {
            $packet = new UnknownPacket();
            $packet->setPacketId($packetId);
        }

        if (Binary::readBool($encoded[$offset++])) {
            $packet->setResponseId(Binary::readInt(substr($encoded, $offset, 4)));
            $offset += 4;
        }

        $packet->setBuffer(substr($encoded, $offset));
        $packet->decodePayload();
        return $packet;
    }

    /**
     * @param int $packetId
     * @return StarGatePacket|null
     */
    public function getPacketInstance(int $packetId): ?StarGatePacket
    {
        if (isset($this->packetPool[$packetId])) {
            return clone $this->packetPool[$packetId];
        }
        return null;
    }

}