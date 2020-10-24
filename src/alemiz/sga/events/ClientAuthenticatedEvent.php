<?php

namespace alemiz\sga\events;

use pocketmine\event\Cancellable;

class ClientAuthenticatedEvent extends ClientEvent implements Cancellable {

    private $cancelMessage = "Authentication was canceled!";

    /**
     * @param string $cancelMessage
     */
    public function setCancelMessage(string $cancelMessage) : void {
        $this->cancelMessage = $cancelMessage;
    }

    /**
     * @return string
     */
    public function getCancelMessage() : string {
        return $this->cancelMessage;
    }

}