# BedrockServerQuery
## What is it?
BedrockServerQuery is a virion which allows plugins to query the information from other Minecraft: Bedrock Edition servers.
## What informtaion can it query?
It can query anything that the server's software sends in UnconnectedPong, but the least it will query is:
 - Online or Offline
 - Game Type
 - Message of the Day
 - Protocol Version
 - Minecraft Version
 - Player Count
 - Max Player Count
 - Server Identifier
 - Server Software
 - Gamemode
> Any extra data provided by some softwares is accessible from the extraData array in ServerQueryInformation  

## How do I use it?
1. Download the PHAR from poggit or the source from github
2. Drop the downloaded PHAR or source inside of your virions folder
3. Import the ``twisted\bedrockserverquery\BedrockServerQuery`` class
4. Use the static ``getInfo(string ip, [int port = 19132])`` method
5. Use the returned ``twisted\bedrockserverquery\ServerQueryInformation`` object to get the information you need.

## Example
In this example, the player count of another server is broadcasted every 5 minutes to all online players, unless it is offline it will say the server is offline.
```php
<?php
declare(strict_types=1);

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use twisted\bedrockserverquery\BedrockServerQuery;

class ExamplePlugin extends PluginBase {

    public function onEnable() : void{
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(int $currentTick) : void{
            $info = BedrockServerQuery::getInfo("example.com", 19133);
            if($info->isOnline()){
                $this->getServer()->broadcastMessage("There are " . $info->getPlayerCount() . "/" . $info->getMaxPlayerCount() . " players on example.com:19132!");
            }else{
                $this->getServer()->broadcastMessage("example.com:19132 is offline!");
            }
        }), 5 * 60 * 20, 5 * 60 * 20);
    }
}
```