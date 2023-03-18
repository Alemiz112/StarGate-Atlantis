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

use alemiz\sga\utils\StarGateFuture;

class PingEntry
{

    /** @var StarGateFuture */
    private StarGateFuture $future;
    /** @var int */
    private int $timeout;

    /**
     * PingEntry constructor.
     * @param StarGateFuture $future
     * @param int $timeout
     */
    public function __construct(StarGateFuture $future, int $timeout)
    {
        $this->future = $future;
        $this->timeout = $timeout;
    }

    /**
     * @return StarGateFuture
     */
    public function getFuture(): StarGateFuture
    {
        return $this->future;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

}