<?php
namespace alemiz\sga\events;

use alemiz\sga\packets\StarGatePacket;
use pocketmine\event\Event;

class CustomPacketEvent extends Event {

    /** @var StarGatePacket */
    private $packet;

    /**
     * CustomPacketEvent constructor.
     * @param StarGatePacket $packet
     */
    public function __construct(StarGatePacket $packet){
        $this->packet = $packet;
    }

    /**
     * @return StarGatePacket
     */
    public function getPacket(): StarGatePacket {
        return $this->packet;
    }
}