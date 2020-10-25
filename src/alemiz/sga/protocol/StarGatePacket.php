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
use pocketmine\utils\BinaryStream;

abstract class StarGatePacket extends BinaryStream {

    /** @var int */
    private $responseId;

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

    /**
     * @param int $responseId
     */
    public function setResponseId(int $responseId) : void {
        $this->responseId = $responseId;
    }

    /**
     * @return int
     */
    public function getResponseId() : int {
        return $this->responseId;
    }

    /**
     * @return bool
     */
    public function sendsResponse() : bool {
        return false;
    }

    /**
     * @return bool
     */
    public function isResponse() : bool {
        return false;
    }
}