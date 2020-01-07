<?php
namespace alemiz\sga\connection;

use pocketmine\Thread;

class StarGateConnection extends Thread {

    /** @var \ThreadedLogger */
    private $logger;
    /** @var StarGateSocket */
    private $starGateSocket;
    /** @var resource */
    public $socket;

    /** @var string */
    private $address;
    /** @var int */
    private $port;
    /** @var string */
    private $name;
    /** @var string */
    private $password;

    /** @var \Threaded */
    private $input;
    /** @var \Threaded */
    private $output;

    /** @var bool */
    private $canConnect = true;
    /** @var bool */
    private $isConnected = false;
    /** @var bool */
    private $shutdown = false;

    /**
     * StarGateConnection constructor.
     * @param \ThreadedLogger $logger
     * @param \ClassLoader $loader
     * @param string $address
     * @param int $port
     * @param string $name
     * @param string $password
     */
    public function __construct(\ThreadedLogger $logger, \ClassLoader $loader, string $address, int $port, string $name, string $password){
        $this->logger = $logger;
        $this->address = $address;
        $this->port = $port;
        $this->name = $name;
        $this->password = $password;

        $this->setClassLoader($loader);

        $this->input = new \Threaded();
        $this->output = new \Threaded();

        $this->start();
    }

    public function run(){
        $this->registerClassLoader();
        gc_enable();
        error_reporting(-1);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');

        register_shutdown_function([$this, 'shutdownThread']);
        //set_error_handler([$this, 'errorHandler'], E_ALL);

        /* Prepare connection*/
        $this->starGateSocket = new StarGateSocket($this, $this->address, $this->port, $this->name, $this->password);
        $this->canConnect = $this->isConnected = $this->starGateSocket->connect();

        /* Start operating*/
        $this->operate();
    }

    private function operate(){
        while (!$this->shutdown){
            $start = microtime(true);
            $this->tick();
            $time = microtime(true);
            if ($time - $start < 0.01) {
                @time_sleep_until($time + 0.01 - ($time - $start));
            }
        }
        $this->tick();
        $this->shutdown();
    }

    private function tick(){
        $this->update();
        while (($packet = $this->outRead()) !== null && strlen($packet) !== 0){
            socket_write($this->getSocket(), $packet."\r\n", strlen($packet."\r\n"));
        }
    }

    private function update(){
        if ($this->isConnected){
            $error = socket_last_error();
            socket_clear_error($this->getSocket());

            if ($error === 10057 || $error === 10054){
                $this->getLogger()->info("§cWARNING: Connection aborted! StarGate connection was unexpectedly closed!");
                $this->isConnected = false;
                return;
            }

            $readyArray = [$this->getSocket()];
            if (socket_select($readyArray, $null, $null, $waitTime = 0) > 0){

                $data = @socket_read($this->getSocket(), 65536, PHP_NORMAL_READ);
                $data = str_replace(["\n", "\r"], '', $data);
                if ($data != "" && $data != "\r" && $data != "\n"){
                    $this->inputWrite($data);
                }
            }
        }else{
            if ($this->canConnect()){
                $this->getLogger()->info("§cTrying to reconnect to StarGate...");
                $this->canConnect = $this->isConnected = $this->starGateSocket->connect();
            }
        }
    }

    public function quit(){
        $this->shutdownThread();
        parent::quit();
    }

    public function shutdown(){
        $this->isConnected = $this->canConnect = false;
        $this->starGateSocket->close();
    }

    public function shutdownThread(){
        $this->shutdown = true;
    }


    /**
     * @return string
     */
    public function inputRead(){
        return $this->input->shift();
    }
    /**
     * @param string $string
     */
    public function inputWrite(string $string){
        $this->input[] = $string;
    }
    /**
     * @return string
     */
    public function outRead(){
        return $this->output->shift();
    }

    /**
     * @param string $string
     */
    public function outWrite(string $string){
        $this->output[] = $string;
    }


    /**
     * @return bool
     */
    public function canConnect(): bool{
        return $this->canConnect;
    }
    /**
     * @param bool $canConnect
     */
    public function setCanConnect(bool $canConnect): void{
        $this->canConnect = $canConnect;
    }
    /**
     * @return bool
     */
    public function isConnected(): bool{
        return $this->isConnected;
    }
    /**
     * @param bool $isConnected
     */
    public function setConnected(bool $isConnected): void{
        $this->isConnected = $isConnected;
    }

    /**
     * @return resource
     */
    public function getSocket(){
        return $this->starGateSocket->getSocket();
    }


    /**
     * @return \ThreadedLogger
     */
    public function getLogger(): \ThreadedLogger{
        return $this->logger;
    }

    public function getThreadName(): string{
        return "StarGate-Atlantis";
    }

    public function setGarbage(){
    }
}