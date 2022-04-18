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

namespace alemiz\sga;

use alemiz\sga\client\StarGateClient;
use alemiz\sga\events\ClientCreationEvent;
use alemiz\sga\protocol\ServerInfoRequestPacket;
use alemiz\sga\protocol\ServerTransferPacket;
use alemiz\sga\protocol\types\HandshakeData;
use alemiz\sga\utils\PacketResponse;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class StarGateAtlantis extends PluginBase {

    public const STARGATE_VERSION = 2;

    /** @var StarGateAtlantis */
    private static StarGateAtlantis $instance;

    /** @var StarGateClient[] */
    protected array $clients = [];

    /** @var int */
    private int $tickInterval;

    /** @var string */
    private string $defaultClient;

    /** @var int */
    private int $logLevel;

    /** @var bool */
    private bool $autoStart;

    public function onEnable() : void {
        self::$instance = $this;
		$this->tickInterval = $this->getConfig()->get("tickInterval");
		$this->defaultClient = $this->getConfig()->get("defaultClient");
		$this->logLevel = $this->getConfig()->get("logLevel");
		$this->autoStart = $this->getConfig()->get("autoStart");

		$this->clients = [];
        foreach ($this->getConfig()->get("connections") as $clientName => $ignore){
            $this->createClient($clientName);
        }
    }

    public function onDisable() : void {
        foreach ($this->clients as $client){
            $client->shutdown();
        }
    }

    /**
     * @param string $clientName
     */
    private function createClient(string $clientName) : void {
        if (!isset($this->getConfig()->get("connections")[$clientName])){
            $this->getLogger()->warning("§cCan not load client ".$clientName."! Wrong config!");
            return;
        }

        $config = $this->getConfig()->get("connections")[$clientName];
        $handshakeData = new HandshakeData($clientName, $config["password"], HandshakeData::SOFTWARE_POCKETMINE, self::STARGATE_VERSION);
        $client = new StarGateClient($config["address"], (int) $config["port"], $handshakeData, $this);
        $this->onClientCreation($clientName, $client);
    }

    /**
     * @param string $clientName
     * @param StarGateClient $client
     */
    public function onClientCreation(string $clientName, StarGateClient $client) : void {
        if (isset($this->clients[$clientName])){
            return;
        }

        $event = new ClientCreationEvent($client, $this);
        $event->call();

        if ($event->isCancelled()){
            return;
        }
        if ($this->autoStart){
            $client->connect();
        }
        $this->clients[$clientName] = $client;
    }

    /**
     * @return StarGateAtlantis
     */
    public static function getInstance() : StarGateAtlantis {
        return self::$instance;
    }

    /**
     * @return int
     */
    public function getTickInterval() : int {
        return $this->tickInterval;
    }

    public function getClient(string $clientName) : ?StarGateClient {
        return $this->clients[$clientName] ?? null;
    }

    /**
     * @return StarGateClient|null
     */
    public function getDefaultClient() : ?StarGateClient {
        return $this->getClient($this->defaultClient);
    }

    /**
     * @return StarGateClient[]
     */
    public function getClients() : array {
        return $this->clients;
    }

    /**
     * @param int $logLevel
     */
    public function setLogLevel(int $logLevel) : void {
        $this->logLevel = $logLevel;
    }

    /**
     * @return int
     */
    public function getLogLevel() : int {
        return $this->logLevel;
    }

    /**
     * Transfer player to another server.
     * @param Player $player instance to be transferred.
     * @param string $targetServer server where player will be sent.
     * @param string|null $clientName client name that will be used.
     */
    public function transferPlayer(Player $player, string $targetServer, ?string $clientName = null) : void {
        $client = $this->getClient($clientName ?? $this->defaultClient);
        if ($client === null){
            return;
        }
        $packet = new ServerTransferPacket();
        $packet->setPlayerName($player->getName());
        $packet->setTargetServer($targetServer);
        $client->sendPacket($packet);
    }

    /**
     * Get info about another server or master server.
     * @param string $serverName name of server that info will be send. In selfMode it can be custom.
     * @param bool $selfMode if send info of master server, StarGate server.
     * @param string|null $clientName client name that will be used.
     * @return PacketResponse|null future that can be used to get response data.
     */
    public function serverInfo(string $serverName, bool $selfMode, ?string $clientName = null) : ?PacketResponse {
        $client = $this->getClient($clientName ?? $this->defaultClient);
        if ($client === null){
            return null;
        }
        $packet = new ServerInfoRequestPacket();
        $packet->setServerName($serverName);
        $packet->setSelfInfo($selfMode);
        return $client->responsePacket($packet);
    }
}
