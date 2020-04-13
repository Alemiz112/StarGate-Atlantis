<?php

namespace tests;

use alemiz\sga\packets\ServerManagePacket;

class DockerContainerCreate{

    /**
     * @param string $serverName
     * @param string $dockerImage
     */
    public function startNewLobby(string $serverName, string $dockerImage) : void {
        $packet = new ServerManagePacket();
        $packet->packetType = ServerManagePacket::DOCKER_ADD;

        $packet->serverAddress = "192.168.0.100";
        $packet->serverPort = "19134";
        $packet->serverName = $serverName; //lobby-1
        $packet->containerImage = $dockerImage; //nukkit-custom
        $packet->exposedPorts = ["19134/19134"]; //own port bindings (UDP & TCP is bind)
        $packet->envVariables = ["JAVA_RAM=2048M"];
        $packet->dockerHost = "default"; //if you have more hosts

        $closure = function (string $response){
            $logger = StarGateAtlantis::getInstance()->getLogger();
            $logger->info("§eHandled docker response!");

            $data = explode(",", $response);
            $logger->info("§eStatus: ".$data[0]. (count($data) > 1? " Container ID: ".$data[1] : ""));
        };

        $packet->setResponseHandler($closure);
        $packet->putPacket("us");
    }

    /**
     * @param string $id
     */
    public function removeContainer(string $id) : void {
        $packet = new ServerManagePacket();
        $packet->packetType = ServerManagePacket::DOCKER_REMOVE;
        $packet->containerId = $id;
        $packet->dockerHost = "default";
        $packet->putPacket("us");
    }
}