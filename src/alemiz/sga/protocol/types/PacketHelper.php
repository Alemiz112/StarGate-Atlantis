<?php

namespace alemiz\sga\protocol\types;

use alemiz\sga\protocol\StarGatePacket;
use function strlen;

class PacketHelper {

    /**
     * @param StarGatePacket $buf
     * @param int $int
     */
    public static function writeInt(StarGatePacket $buf, int $int) : void {
        $buf->putInt($int);
    }

    /**
     * @param StarGatePacket $packet
     * @return int
     */
    public static function readInt(StarGatePacket $packet) : int {
        return $packet->getInt();
    }

    /**
     * @param StarGatePacket $buf
     * @param int $int
     */
    public static function writeLong(StarGatePacket $buf, int $int) : void {
        $buf->putLong($int);
    }

    /**
     * @param StarGatePacket $packet
     * @return int
     */
    public static function readLong(StarGatePacket $packet) : int {
        return $packet->getLong();
    }

    /**
     * @param StarGatePacket $buf
     * @param string $string
     */
    public static function writeString(StarGatePacket $buf, string $string) : void {
        $buf->putInt(strlen($string));
        $buf->put($string);
    }

    /**
     * @param StarGatePacket $buf
     * @return string
     */
    public static function readString(StarGatePacket $buf) : string {
        return $buf->get($buf->getInt());
    }

    /**
     * @param StarGatePacket $buf
     * @param bool $bool
     */
    public static function writeBoolean(StarGatePacket $buf, bool $bool) : void {
        $buf->putByte($bool? 1 : 0);
    }

    /**
     * @param StarGatePacket $packet
     * @return bool
     */
    public static function readBoolean(StarGatePacket $packet) : bool {
        return $packet->getByte() === 1;
    }

}