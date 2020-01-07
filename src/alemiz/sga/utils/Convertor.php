<?php
namespace alemiz\sga\utils;

class Convertor{

    /** @var string[]  */
    private $data = [];

    /**
     * Convertor constructor.
     * @param int $id
     */
    public function __construct(int $id){
        $this->data[0] = (string) $id;
    }

    /**
     * @param int $interger
     */
    public function putInt(int $interger){
        $this->data[] = (string) $interger;
    }

    /**
     * @param string $string
     */
    public function putString(string $string){
        $this->data[] = $string;
    }

    /**
     * @param int $key
     * @return string
     */
    public function getString(int $key) : string {
        return $this->data[$key];
    }

    /**
     * @param array $strings
     * @return string
     */
    public static function getForcePacketString(array $strings) : string {
        return implode("!", $strings);
    }

    /**
     * @param string $packetString
     * @return array
     */
    public static function getPacketStringData(string $packetString) : array {
        return explode("!", $packetString);
    }

    public function getPacketString() : string {
        return implode("!", $this->data);
    }
}