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

namespace alemiz\sga\events;

use alemiz\sga\client\ClientSession;
use alemiz\sga\client\StarGateClient;
use alemiz\sga\StarGateAtlantis;
use pocketmine\event\Event;

abstract class ClientEvent extends Event
{

    /** @var StarGateAtlantis */
    private StarGateAtlantis $plugin;

    /** @var StarGateClient */
    private StarGateClient $client;

    /**
     * ClientEvent constructor.
     * @param StarGateClient $client
     * @param StarGateAtlantis $plugin
     */
    public function __construct(StarGateClient $client, StarGateAtlantis $plugin)
    {
        $this->client = $client;
        $this->plugin = $plugin;
    }


    /**
     * @return StarGateClient
     */
    public function getClient(): StarGateClient
    {
        return $this->client;
    }

    /**
     * @return ClientSession|null
     */
    public function getSession(): ?ClientSession
    {
        return $this->client->getSession();
    }

    /**
     * @return StarGateAtlantis
     */
    public function getPlugin(): StarGateAtlantis
    {
        return $this->plugin;
    }

}