<?php
namespace alemiz\sga\client;

use alemiz\sga\connection\StarGateConnection;
use alemiz\sga\events\CustomPacketEvent;
use alemiz\sga\packets\ConnectionInfoPacket;
use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\packets\WelcomePacket;
use alemiz\sga\StarGateAtlantis;
use alemiz\sga\utils\Convertor;
use pocketmine\scheduler\Task;

class ClientInterface{

    /** @var Client */
    private $client;
    /** @var StarGateConnection */
    private $connection;

    /** @var bool */
    private $read = false;

    /**
     * ClientInterface constructor.
     * @param Client $client
     * @param string $address
     * @param int $port
     * @param string $name
     * @param string $password
     */
    public function __construct(Client $client, string $address, int $port, string $name, string $password){
        $this->client = $client;

        $server = $client->getServer();
        $this->connection = new StarGateConnection($server->getLogger(), $server->getLoader(), $address, $port, $name, $password);
    }

    /**
     * @return bool
     */
    public function process(){
        if (!$this->connection->canConnect()) return false;

        if (!$this->read){
            try {
                $iris = $this->readPacket();
                if (is_null($iris)) return false;

                /** @var StarGatePacket $irisPacket */
                $irisPacket = $this->client->getSga()->processPacket($iris);
                if ($irisPacket instanceof ConnectionInfoPacket){
                    $type = $irisPacket->getPacketType();

                    if ($type == ConnectionInfoPacket::CONNECTION_ABORTED){
                        $reason = $irisPacket->getReason();

                        $this->client->getLogger()->warning("§cERROR: StarGate client was not authenticated! Reason: §4".(($reason == null) ? "unknown" : $reason));
                        $this->forceClose();
                        return false;
                    }

                    if ($type == ConnectionInfoPacket::CONNECTION_CONNECTED){
                        $this->connection->setConnected(true);
                        $this->read = true;
                        $this->welcome();
                    }
                }
            }catch (\Exception $e){
                $this->client->getLogger()->info("§cWARNING: Error while opening iris!");
                $this->client->getLogger()->info("§c".$e->getMessage());

                $this->read = false;
                return false;
            }
        }
        return true;
    }

    /**
     * This function we use to send packet to Clients
     * @param StarGatePacket $packet
     * @return string
     */
    public function gatePacket(StarGatePacket $packet){
        if (!$packet->isEncoded){
            $packet->encode();
        }

        $packetString = $packet->encoded;
        $uuid = uniqid();
        $this->connection->outWrite($packetString."!".$uuid);
        return $uuid;
    }

    /**
     * @return string
     */
    public function readPacket(){
        return $this->connection->inputRead();
    }


    public function welcome(){
        /* Sending WelcomePacket*/
        $packet = new WelcomePacket();

        $packet->server = StarGateAtlantis::getInstance()->cfg->get("Client");
        $packet->players = count(StarGateAtlantis::getInstance()->getServer()->getOnlinePlayers());
        $packet->tps = (int) StarGateAtlantis::getInstance()->getServer()->getTicksPerSecondAverage();
        $packet->usage = (int) StarGateAtlantis::getInstance()->getServer()->getTickUsageAverage();

        $this->gatePacket($packet);
    }

    public function reconnect(){
        $this->connection->setConnected(false);
        $this->connection->setCanConnect(true);
        $this->read = false;
    }

    /**
     * @param string|null $reason
     */
    public function close(string $reason = null){
        if (!$this->connection->isConnected()) return;

        $packet = new ConnectionInfoPacket();
        $packet->packetType = ConnectionInfoPacket::CONNECTION_CLOSED;
        $packet->reason = $reason;
        $this->gatePacket($packet);
    }

    public function forceClose(){
        $this->connection->shutdownThread();
    }
}