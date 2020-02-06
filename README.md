# StarGate-Atlantis
<a align="center" href="https://discord.gg/VsHXm2M"><img src="https://discordapp.com/api/guilds/574240965351571477/embed.png" alt="Discord server"/> [![](https://poggit.pmmp.io/shield.state/StarGate-Atlantis)](https://poggit.pmmp.io/p/StarGate-Atlantis)
> This is stable and fast plugin for pmmp that allows server connect to WaterDog plugin StarGate. It make easier communication between server. Includes API fur custom packets, transferring players and more 
</br> Download [here](https://poggit.pmmp.io/ci/Alemiz112/StarGate-Atlantis/)!

## 🎯Features:
- Fast communication between servers
- Custom packets
- Moving players between servers (API)

More features will be added very soon

## 🔧API
You can access StarGate-Atlantis by ``StarGateAtlantis::getInstance()``
#### Avalibe Functions
- ``transferPlayer(Player player, string server)`` This we use to transfer Player between servers
- ``RegisterPacket(StarGatePacket packet)`` Really simple method for registring Packet
- ``putPacket(StarGatePacket packet)`` This allows you to send packet. Packet must be registered first
- ``kickPlayer(Player player, string reason)``  Kick player from any server connected to StarGate network
- ``isOnline(Player player)`` Check if player is online. Sends back response 'true!server' or 'false'. Examples [here](https://github.com/Alemiz112/StarGate-Universe/tree/master/src/tests#playeronline-response).
- ``forwardPacket(string client, StarGatePacket packet)`` Using ForwardPacket you can forward packet to other client/server
##### Example:
```php
$player = PLUGIN::getInstance()->getPlayer("alemiz003");
$server = "lobby2";

StarGateAtlantis::getInstance()->transferPlayer($player, $server);
```
To more examples look [here](https://github.com/Alemiz112/StarGate-Atlantis/tree/master/src/tests)!

#### 📦Packet Handling
Received Packets are handled by ``CustomPacketEvent``. Official Packets are handled (if needed) automaticly</br></br>
Accessing Packet from Event:</br>
```php
public function getPacket() {
  return $this->packet;
}
```
#### 📞ResponseCheckTask
Response checking is useful when we want to get some data created by packet back to client.</br>
PHP allows you to use simple closures to handle result:
```php
$closure = function ($response){
    StarGateAtlantis::getInstance()->getLogger()->info("§a".$response);
};
StarGateAtlantis::getInstance()->isOnline("alemiz0003", $closure);
``` 
For more info please consider looking [here](https://github.com/Alemiz112/StarGate-Universe/tree/master/src/tests).

#### ⚙️Creating Own Packets
For better understanding please read [StarGatePacket](https://github.com/Alemiz112/StarGate-Atlantis/blob/master/src/alemiz/sga/packets/StarGatePacket.php) and [WelcomePacket](https://github.com/Alemiz112/StarGate-Atlantis/blob/master/src/alemiz/sga/packets/WelcomePacket.php)
#### Convertor
Convertor is used for ``encoding`` and ``decoding`` packets. We can use it for static and nonstatic usage</br>
Functions:</br>
- ``packetStringData(string packetString)`` Exports packetString to data array
- ``putInt(int integer)`` Pushes Integer to array
- ``putString(string string)`` Pushes String to array
- ``getString(int key)`` Returns String from array by key value
- ``getPacketString()`` Returns packetString from array data

- ``static getInt(string string)`` Returns Integer from String
- ``static getForcePacketString(array strings)`` Returns packetString from given array
- ``static getPacketStringData(string packetString)`` Returns array data from given string

##### Example (nonstatic):
```php
$convertor = new Convertor($this->getID());
$convertor->putString($this->server);
$convertor->putInt($this->tps);

$this->encoded = $convertor->getPacketString();
```
##### Example (static):
```php
$data = Convertor::getPacketStringData($packetString);
$packetId = (int) $data[0];
```
