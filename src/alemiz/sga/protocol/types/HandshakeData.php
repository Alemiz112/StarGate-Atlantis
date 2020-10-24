<?php

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
    }

    /**
     * @param HandshakePacket $packet
     * @return HandshakeData
     */
    public static function decodeData(HandshakePacket $packet) : HandshakeData {
        $software = PacketHelper::readInt($packet);
        $clientName = PacketHelper::readString($packet);
        $password = PacketHelper::readString($packet);
        return new HandshakeData($clientName, $password, $software);
    }

    /**
     * @var string
     */
    private $clientName;
    /**
     * @var string
     */
    private $password;
    /***
     * @var string
     */
    private $software;

    public function __construct(string $clientName, string $password, int $software){
        $this->clientName = $clientName;
        $this->password = $password;
        $this->software = $software;
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
     * @return string
     */
    public function getSoftware() : string {
        return $this->software;
    }

}