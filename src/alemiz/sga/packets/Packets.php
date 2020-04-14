<?php
namespace alemiz\sga\packets;

interface Packets{

    const WELCOME_PACKET = 0x01;
    const PING_PACKET = 0x02;
    const PLAYER_TRANSFER_PACKET = 0x03;
    const KICK_PACKET = 0x04;
    const PLAYER_ONLINE_PACKET = 0x05;
    const FORWARD_PACKET = 0x06;
    const CONNECTION_INFO_PACKET = 0x07;

    const SERVER_MANAGE_PACKET = 0x10;
}