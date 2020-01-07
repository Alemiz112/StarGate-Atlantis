<?php
namespace alemiz\sga\connection;

class StarGateSocket{

    /** @var StarGateConnection */
    private $conn;

    /** @var string */
    private $address;
    /** @var int */
    private $port;
    /** @var string */
    private $name;
    /** @var string */
    private $password;

    /**
     * StarGateSocket constructor.
     * @param StarGateConnection $conn
     * @param string $address
     * @param int $port
     * @param string $name
     * @param string $password
     */
    public function __construct(StarGateConnection $conn, string $address, int $port, string $name, string $password){
        $this->conn = $conn;
        $this->address = $address;
        $this->port = $port;
        $this->name = $name;
        $this->password = $password;
    }

    /**
     * @return bool
     */
    public function connect(){
        $this->conn->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        try {
            if ($this->getSocket() === false || !@socket_connect($this->getSocket(), $this->address, $this->port)) {
                throw new \Exception(socket_strerror(socket_last_error()));
            }

            socket_set_nonblock($this->getSocket());
            socket_set_option($this->getSocket(), SOL_TCP, TCP_NODELAY, 1);

            $this->authenticate();
        }catch (\Exception $e){
            $this->conn->getLogger()->info("§cERROR: Unable to connect to StarGate server!");
            $this->conn->getLogger()->info("§c".$e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * @throws \Exception
     */
    public function authenticate(){
        $handShakeData = [];
        $handShakeData[0] = "CHEVRON";
        $handShakeData[1] = $this->name;
        $handShakeData[2] = $this->password;

        $message = implode(":", $handShakeData);

        $data = socket_write($this->getSocket(), $message."\r\n", strlen($message."\r\n"));
        if ($data === false) {
            $this->conn->getLogger()->info("§cWARNING: Unable to authenticate StarGate client! Please try to restart server");
            throw new \Exception(socket_strerror(socket_last_error()));
        }

        $this->conn->getLogger()->info("§aDone! Successfully connected to StarGate server! Authenticating ...");
    }

    public function getSocket(){
        return $this->conn->socket;
    }

    public function close(){
        socket_close($this->conn->socket);
    }

    /**
     * @return string
     */
    public function getAddress(): string{
        return $this->address;
    }
}