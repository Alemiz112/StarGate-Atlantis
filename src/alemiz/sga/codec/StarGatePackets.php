<?php

namespace alemiz\sga\codec;

interface StarGatePackets {

    public const HANDSHAKE_PACKET = 0x01;
    public const SERVER_HANDSHAKE_PACKET = 0x02;
    public const DISCONNECT_PACKET = 0x03;
    public const PING_PACKET = 0x04;
    public const PONG_PACKET = 0x05;
    public const RECONNECT_PACKET = 0x06;
    public const FORWARD_PACKET = 0x07;

    /**
     * This packets are not registered in codec by default.
     * Register this packet manually after client connects or on server startup if you need them
     */

    public const SERVER_INFO_REQUEST_PACKET = 0x08;
    public const SERVER_INFO_RESPONSE_PACKET = 0x09;
    public const SERVER_TRANSFER_PACKET = 0x0a;

}