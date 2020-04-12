<?php

namespace alemiz\sga\packets;

use alemiz\sga\utils\Convertor;

class ServerManagePacket extends StarGatePacket {

    public const SERVER_ADD = 0;
    public const SERVER_REMOVE = 1;

    public const DOCKER_ADD = 2;
    public const DOCKER_REMOVE = 3;
    public const DOCKER_START = 4;
    public const DOCKER_STOP = 5;

    /**
     * @var int
     */
    public $packetType;
    /**
     * @var string
     */
    public $serverAddress;
    /**
     * @var string
     */
    public $serverPort;
    /**
     * @var string
     */
    public $serverName;

    /**
     * Name of host defined in proxy config.yml
     * @var string
     */
    public $dockerHost;
    /**
     * @var string
     */
    public $containerImage;
    /**
     * @var string
     */
    public $containerId;
    /**
     * 19135/19132,25566/25565...
     * @var array
     */
    public $exposedPorts;
    /**
     * VARIABLE=VALUE,VARIABLE2...
     * @var array
     */
    public $envVariables;

    public function __construct(){
        parent::__construct("SERVER_MANAGE_PACKET", Packets::SERVER_MANAGE_PACKET);
    }

    /**
     * @inheritDoc
     */
    public function encode() : void {
        $this->isEncoded = false;

        $data = Convertor::getPacketStringData($this->encoded);
        $this->packetType = (int) $data[1];

        switch ($this->packetType){
            case self::SERVER_ADD:
                $this->serverAddress = $data[2];
                $this->serverPort = $data[3];
                $this->serverName = $data[4];
                break;
            case self::SERVER_REMOVE:
                $this->serverName = $data[2];
                break;
            case self::DOCKER_ADD:
                $this->serverAddress = $data[2];
                $this->serverPort = $data[3];
                $this->serverName = $data[4];
                $this->containerImage = $data[5];
                $this->exposedPorts = explode(",", $data[6]);
                if (count($data) > 8) $this->envVariables = explode(",", $data[7]);
                if (count($data) > 9) $this->dockerHost = $data[8];
                break;
            case self::DOCKER_REMOVE:
            case self::DOCKER_START:
            case self::DOCKER_STOP:
                $this->containerId = $data[2];
                if (count($data) > 4) $this->dockerHost = $data[3];
                break;
        }
    }

    public function decode() : void {
        $convertor = new Convertor($this->getID());
        $convertor->putInt($this->packetType);

        switch ($this->packetType){
            case self::SERVER_ADD:
                $convertor->putString($this->serverAddress);
                $convertor->putString($this->serverPort);
                $convertor->putString($this->serverName);
                break;
            case self::SERVER_REMOVE:
                $convertor->putString($this->serverName);
                break;
            case self::DOCKER_ADD:
                $convertor->putString($this->serverAddress);
                $convertor->putString($this->serverPort);
                $convertor->putString($this->serverName);
                $convertor->putString($this->containerImage);
                $convertor->putString(implode(",", $this->exposedPorts));
                if ($this->envVariables != null) $convertor->putString(implode(",", $this->envVariables));
                if ($this->dockerHost != null) $convertor->putString($this->dockerHost);
                break;
            case self::DOCKER_REMOVE:
            case self::DOCKER_START:
            case self::DOCKER_STOP:
                $convertor->putString($this->containerId);
                if ($this->dockerHost != null) $convertor->putString($this->dockerHost);
                break;
        }

        $this->encoded = $convertor->getPacketString();
        $this->isEncoded = true;
    }
}