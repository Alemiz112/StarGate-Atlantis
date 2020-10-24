<?php

namespace alemiz\sga\protocol;

use alemiz\sga\codec\StarGatePacketHandler;
use pocketmine\utils\BinaryStream;

abstract class StarGatePacket extends BinaryStream {

    abstract public function encodePayload() : void;
    abstract public function decodePayload() : void;

    /**
     * @param StarGatePacketHandler $handler
     * @return bool
     */
    public function handle(StarGatePacketHandler $handler) : bool {
        return false;
    }

    /**
     * @return int
     */
    abstract public function getPacketId() : int;
}