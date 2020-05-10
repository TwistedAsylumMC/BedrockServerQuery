<?php
declare(strict_types=1);

namespace twisted\bedrockserverquery;

use raklib\utils\InternetAddress;
use RuntimeException;

final class BedrockServerQuery{

	public static function getInfo(string $address, int $port = 19132) : ServerQueryInformation{
		// Attemp to create a new socket connection to the server
		try{
			$socket = new UDPClientSocket(new InternetAddress($address, $port, 4));
		}catch(RuntimeException $exception){
			// Return a ServerQueryInformation object with placeholder values
			return ServerQueryInformation::createPlaceholder();
		}

		// After we send UnconnectedPing, the server will reply with UnconnectedPong
		$socket->writeUnconnectedPing();

		// Get the UnconnectedPong from the server
		$pong = $socket->readUnconnectedPong();
		// Check if the socket connection managed to read the UnconnectedPong
		if($pong->pingID === -1 && $pong->serverID === -1 && $pong->serverName === ""){
			// Return a ServerQueryInformation object with placeholder values
			return ServerQueryInformation::createPlaceholder();
		}

		// Finish the sequence and close the socket connection since it is no longer needed
		$socket->finish();

		// Create a ServerQueryInformation object from the server name and return it
		return ServerQueryInformation::fromServerName($pong->serverName);
	}
}