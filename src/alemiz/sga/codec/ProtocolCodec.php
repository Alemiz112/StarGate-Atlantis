<?php

namespace alemiz\sga\codec;

use alemiz\sga\protocol\DisconnectPacket;
use alemiz\sga\protocol\HandshakePacket;
use alemiz\sga\protocol\PingPacket;
use alemiz\sga\protocol\PongPacket;
use alemiz\sga\protocol\ReconnectPacket;
use alemiz\sga\protocol\ServerHandshakePacket;
use alemiz\sga\protocol\StarGatePacket;
use pocketmine\utils\Binary;

class ProtocolCodec {

    public const STARGATE_MAGIC = 0xa20;

    /** @var StarGatePacket[] */
    private $packetPool = [];

    /**
     * ProtocolCodec constructor.
     */
    public function __construct() {
        $this->registerPacket(StarGatePackets::HANDSHAKE_PACKET, new HandshakePacket());
        $this->registerPacket(StarGatePackets::SERVER_HANDSHAKE_PACKET, new ServerHandshakePacket());
        $this->registerPacket(StarGatePackets::DISCONNECT_PACKET, new DisconnectPacket());
        $this->registerPacket(StarGatePackets::PING_PACKET, new PingPacket());
        $this->registerPacket(StarGatePackets::PONG_PACKET, new PongPacket());
        $this->registerPacket(StarGatePackets::RECONNECT_PACKET, new ReconnectPacket());
    }

    /**
     * @param int $packetId
     * @param StarGatePacket $packet
     * @return bool
     */
    public function registerPacket(int $packetId, StarGatePacket $packet) : bool {
        if (isset($this->packetPool[$packetId])){
            return false;
        }
        $this->packetPool[$packetId] = clone $packet;
        return true;
    }

    /**
     * @param int $packetId
     * @return StarGatePacket|null
     */
    public function getPacketInstance(int $packetId) : ?StarGatePacket {
        if (isset($this->packetPool[$packetId])){
            return clone $this->packetPool[$packetId];
        }
        return null;
    }

    /**
     * @param int $packetId
     * @return StarGatePacket|null
     */
    public function unregisterPacket(int $packetId) : ?StarGatePacket {
        $oldPacket = $this->packetPool[$packetId] ?? null;
        unset($this->packetPool[$packetId]);
        return $oldPacket;
    }

    /**
     * @param StarGatePacket $packet
     * @return string
     */
    public function tryEncode(StarGatePacket $packet) : string {
        $encoded = Binary::writeByte($packet->getPacketId());

        $packet->encodePayload();
        $bodyLength = strlen($packet->getBuffer());

        $encoded .= Binary::writeInt($bodyLength);
        $encoded .= $packet->getBuffer();
        return $encoded;
    }

    /**
     * @param string $encoded
     * @return StarGatePacket|null
     */
    public function tryDecode(string $encoded) : ?StarGatePacket {
        $packetId = Binary::readByte($encoded);
        $packet = $this->getPacketInstance($packetId);
        if ($packet === null){
            return null;
        }

        $bodyLength = Binary::readInt(substr($encoded, 1, 4));
        $packet->setBuffer(substr($encoded, 5, $bodyLength));
        $packet->decodePayload();
        return $packet;
    }

}