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

use alemiz\sga\protocol\StarGatePacket;
use Closure;
use function count;
use function strlen;

class PacketHelper {

    /**
     * @param StarGatePacket $buf
     * @param string $array
     */
    public static function writeByteArray(StarGatePacket $buf, string $array) : void {
        $buf->putInt(strlen($array));
        $buf->put($array);
    }

    /**
     * @param StarGatePacket $buf
     * @return string
     */
    public static function readByteArray(StarGatePacket $buf) : string {
        return $buf->get($buf->getInt());
    }

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
        self::writeByteArray($buf, $string);
    }

    /**
     * @param StarGatePacket $buf
     * @return string
     */
    public static function readString(StarGatePacket $buf) : string {
        return self::readByteArray($buf);
    }

    /**
     * @param StarGatePacket $buf
     * @param bool $bool
     */
    public static function writeBoolean(StarGatePacket $buf, bool $bool) : void {
        $buf->putByte($bool? 1 : 0);
    }

    /**
     * @param StarGatePacket $buf
     * @return bool
     */
    public static function readBoolean(StarGatePacket $buf) : bool {
        return $buf->getByte() === 1;
    }

    /**
     * @param StarGatePacket $buf
     * @param Closure $function
     * @return array
     */
    public static function readArray(StarGatePacket $buf, Closure $function) : array {
        $length = self::readInt($buf);
        $array = [];
        for ($i = 0; $i < $length; $i++){
            $array[] = $function($buf);
        }
        return $array;
    }

    /**
     * @param StarGatePacket $buf
     * @param $array
     * @param Closure $consumer
     */
    public static function writeArray(StarGatePacket $buf, $array, Closure $consumer) : void {
        self::writeInt($buf, count($array));
        foreach ($array as $value){
            $consumer($buf, $value);
        }
    }
}