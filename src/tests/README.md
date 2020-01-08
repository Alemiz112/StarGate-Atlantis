# Example Files
This folder can be used for educational purposes. You will find here some helpful examples.

## ResponseCheckTask
Can be used for checking response requested by packet. For that we can use own ResponseCheck class
 or official class: ``EnchanedResponseCheckTask``.
  - EnchanedResponseCheckTask ([example](https://github.com/Alemiz112/StarGate-Atlantis/blob/master/src/tests/EnchanedResponseCheckTask.php)) is more clearer and in separated file
##### Own Class Response Check:
If you want to create your own method of response checking [CustomResponseCheckTask](https://github.com/Alemiz112/StarGate-Atlantis/blob/master/src/tests/CustomResponseCheckTask.php) can help you.
##  PlayerOnline Response
For checking players status and server we have official PlayerOnlinePacket.
Here we will show how to handle response created by this packet.</br> It can be also handled by closure as [here](https://github.com/Alemiz112/StarGate-Atlantis/blob/master/src/tests/OnlineCommand.php#L25).
We will again use [ResponseCheckTask](https://github.com/Alemiz112/StarGate-Atlantis/blob/master/src/alemiz/sga/tasks/ResponseCheckTask.php) for extending our class. 
Our Example class is named [OnlineExample](https://github.com/Alemiz112/StarGate-Atlantis/blob/master/src/tests/OnlineExample.php). This time we will create command that will send PlayerOnlinePacket 
and then will check for response. Command class can be found [here](https://github.com/Alemiz112/StarGate-Atlantis/blob/master/src/tests/OnlineCommand.php).