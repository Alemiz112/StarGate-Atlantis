<?php
/*
 * Copyright 2020 Alemiz
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

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
    public const PLAYER_PING_REQUEST_PACKET = 0x0b;
    public const PLAYER_PING_RESPONSE_PACKET = 0x0c;
    public const SERVER_MANAGE_PACKET = 0x0d;

}