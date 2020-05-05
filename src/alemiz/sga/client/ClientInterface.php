<?php
namespace alemiz\sga\client;

use alemiz\sga\connection\StarGateConnection;
use alemiz\sga\events\CustomPacketEvent;
use alemiz\sga\events\StarGateClientConnectEvent;
use alemiz\sga\packets\ConnectionInfoPacket;
use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\packets\WelcomePacket;
use alemiz\sga\StarGateAtlantis;
use alemiz\sga\utils\Convertor;
use Closure;
use pocketmine\scheduler\Task;

class ClientInterface{

    /** @var Client */
    private $client;
    /** @var StarGateConnection */
    private $connection;

    /** @var bool */
    private $read = false;

    /** @var array  */
    protected $responses = [];
    /** @var Closure[]  */
    protected $responseHandlers = [];

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
        $this->connection = new StarGateConnection($server->getLogger(), $server->getLoader(), $address, $port, $name, $client->getConfigName(), $password);
    }

    /**
     * @return bool
     */
    public function process() : bool {
        if (!$this->connection->canConnect()) return false;

        if (!$this->read){
            try {
                $iris = $this->readPacket();
                if (is_null($iris)) return false;

                /** @var StarGatePacket $irisPacket */
                $irisPacket = $this->client->getSga()->processPacket($iris);
                if ($irisPacket instanceof ConnectionInfoPacket){
                    $type = $irisPacket->getPacketType();

                    if ($type === ConnectionInfoPacket::CONNECTION_ABORTED){
                        $reason = $irisPacket->getReason();

                        $this->client->getLogger()->warning("§cERROR: StarGate client was not authenticated! Reason: §4".(($reason == null) ? "unknown" : $reason));
                        $this->forceClose();
                        return false;
                    }

                    if ($type === ConnectionInfoPacket::CONNECTION_CONNECTED){
                        $this->connection->setConnected(true);
                        $this->read = true;
                        $this->welcome();

                        try {
                            $event = new StarGateClientConnectEvent($this->client->getClientName());
                            $event->call();
                        }catch (\RuntimeException $e){
                            $this->client->getLogger()->critical("§cError: Unable to call connection event!");
                            $this->client->getLogger()->critical("§c".$e->getMessage());
                        }
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
    public function gatePacket(StarGatePacket $packet) : string {
        if (!$packet->isEncoded){
            $packet->encode();
        }

        $packetString = $packet->encoded;
        $uuid = uniqid('', true);

        if (!is_null($packet->getResponseHandler())){
            $this->setResponseHandler($uuid, $packet->getResponseHandler());
        }

        $this->connection->outWrite($packetString."!".$uuid);
        return $uuid;
    }

    /**
     * @return string|null
     */
    public function readPacket() : ?string {
        return $this->connection->inputRead();
    }

    /**
     * @param string $out
     */
    public function writeString(string $out) : void {
        $this->connection->outWrite($out);
    }


    public function welcome() : void {
        /* Sending WelcomePacket*/
        $packet = new WelcomePacket();

        $packet->server = $this->client->getClientName();
        $packet->players = count(StarGateAtlantis::getInstance()->getServer()->getOnlinePlayers());
        $packet->tps = (int) StarGateAtlantis::getInstance()->getServer()->getTicksPerSecondAverage();
        $packet->usage = (int) StarGateAtlantis::getInstance()->getServer()->getTickUsageAverage();

        $this->gatePacket($packet);
    }

    public function reconnect() : void {
        $this->connection->setConnected(false);
        $this->connection->setCanConnect(true);
        $this->read = false;
    }

    /**
     * @param string|null $reason
     */
    public function close(string $reason = null) : void {
        if (!$this->connection->isConnected()) return;

        $packet = new ConnectionInfoPacket();
        $packet->packetType = ConnectionInfoPacket::CONNECTION_CLOSED;
        $packet->reason = $reason;
        $this->gatePacket($packet);
    }

    public function forceClose() : void {
        $this->connection->shutdownThread();
    }

    /**
     * @param string $uuid
     * @return Closure|null
     */
    public function getResponseHandler(string $uuid) : ?Closure {
        return ($this->responseHandlers[$uuid] ?? null);
    }

    /**
     * @param string $uuid
     * @param Closure $responseHandler
     */
    public function setResponseHandler(string $uuid, Closure $responseHandler) : void {
        $this->responseHandlers[$uuid] = $responseHandler;
    }

    /**
     * @param string $uuid
     */
    public function unsetResponseHandler(string $uuid) : void {
        unset($this->responseHandlers[$uuid]);
    }

    /**
     * @return array
     */
    public function getResponses(): array {
        return $this->responses;
    }

    /**
     * @param string $uuid
     * @param string $response
     */
    public function setResponse(string $uuid, string $response) : void {
        $this->responses[$uuid] = $response;
    }

    /**
     * @param string $uuid
     */
    public function unsetResponse(string $uuid) : void {
        unset($this->responses[$uuid]);
    }


    /**
     * @return bool
     */
    public function canConnect() : bool {
        return $this->connection->canConnect();
    }

    /**
     * @return bool
     */
    public function isConnected() : bool {
        return $this->connection->isConnected();
    }

    /**
     * @return bool
     */
    public function isShutdown() : bool {
        return $this->connection->isShutdown();
    }
}