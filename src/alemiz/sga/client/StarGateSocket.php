<?php
namespace alemiz\sga\client;

use Exception;

class StarGateSocket{

    /** @var StarGateConnection */
    private $conn;

    /** @var string */
    private $address;
    /** @var int */
    private $port;
    /** @var string */

    /**
     * StarGateSocket constructor.
     * @param StarGateConnection $conn
     * @param string $address
     * @param int $port
     */
    public function __construct(StarGateConnection $conn, string $address, int $port){
        $this->conn = $conn;
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * @return bool
     */
    public function connect() : bool {
        $this->conn->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        try {
            if (!@socket_connect($this->conn->socket, $this->address, $this->port)) {
                throw new \RuntimeException(socket_strerror(socket_last_error()));
            }

            socket_set_nonblock($this->conn->socket);
            socket_set_option($this->conn->socket, SOL_TCP, TCP_NODELAY, 1);
        }catch (Exception $e){
            $this->conn->getLogger()->error("Can not connect to StarGate server!");
            $this->conn->getLogger()->logException($e);
            return false;
        }
        return true;
    }

    public function close() : void {
        socket_close($this->conn->socket);
    }
}