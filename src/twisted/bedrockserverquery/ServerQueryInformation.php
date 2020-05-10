<?php
declare(strict_types=1);

namespace twisted\bedrockserverquery;

use function array_splice;
use function count;
use function preg_split;

final class ServerQueryInformation{

	/** @var bool */
	private $online;
	/** @var string */
	private $gameType;
	/** @var string */
	private $messageOfTheDay;
	/** @var int */
	private $protocolVersion;
	/** @var string */
	private $minecraftVersion;
	/** @var int */
	private $playerCount;
	/** @var int */
	private $maxPlayerCount;
	/** @var int */
	private $serverId;
	private $serverSoftware;
	/** @var string */
	private $gamemode;
	/** @var array */
	private $extraData;

	private function __construct(bool $online = false, string $gameType = "", string $messageOfTheDay = "", int $protocolVersion = -1, string $minecraftVersion = "", int $playerCount = -1, int $maxPlayerCount = -1, int $serverId = -1, string $serverSoftware = "", string $gamemode = "", array $extraData = []){
		$this->online = $online;
		$this->gameType = $gameType;
		$this->messageOfTheDay = $messageOfTheDay;
		$this->protocolVersion = $protocolVersion;
		$this->minecraftVersion = $minecraftVersion;
		$this->playerCount = $playerCount;
		$this->maxPlayerCount = $maxPlayerCount;
		$this->serverId = $serverId;
		$this->serverSoftware = $serverSoftware;
		$this->gamemode = $gamemode;
		$this->extraData = $extraData;
	}

	/**
	 * Creates a ServerQueryInformation object with placeholder values.
	 *
	 * @return ServerQueryInformation
	 */
	public static function createPlaceholder() : ServerQueryInformation{
		return new self;
	}

	/**
	 * Creates a ServerQueryInformation object with the server name received from
	 *  UnconnectedPong. Any missing data is replaced by placeholder empty values.
	 *
	 * @param string $serverName
	 *
	 * @return ServerQueryInformation
	 */
	public static function fromServerName(string $serverName) : ServerQueryInformation{
		$parts = preg_split("/(?<!\\\\);/", $serverName);
		$extra = array_splice($parts, 9);

		return new self(
			count($parts) > 0, // Online
			(string) ($parts[0] ?? ""), // Game Type
			(string) ($parts[1] ?? ""), // Message of the Day
			(int) ($parts[2] ?? -1), // Protocol Version
			(string) ($parts[3] ?? ""), // Minecraft Version
			(int) ($parts[4] ?? -1), // Player Count
			(int) ($parts[5] ?? -1), // Maximum Player Count
			(int) ($parts[6] ?? -1), // Server Identifier
			(string) ($parts[7] ?? ""), // Server Software
			(string) ($parts[8] ?? ""), // Gamemode,
			$extra // Any extra data provided by the server software
		);
	}

	/**
	 * Returns whether or not the server is online.
	 *
	 * @return bool
	 */
	public function isOnline() : bool{
		return $this->online;
	}

	/**
	 * Returns the game type of the server, e.g. "MCPE".
	 *
	 * @return string
	 */
	public function getGameType() : string{
		return $this->gameType;
	}

	/**
	 * Returns the message of the day which is displayed to clients in the server list.
	 *
	 * @return string
	 */
	public function getMessageOfTheDay() : string{
		return $this->messageOfTheDay;
	}

	/**
	 * Returns the protocol version supported by the server, e.g. 388, 389, 390.
	 *
	 * @return int
	 */
	public function getProtocolVersion() : int{
		return $this->protocolVersion;
	}

	/**
	 * Returns the minecraft version supported by the server, e.g. "1.14.60".
	 *
	 * @return string
	 */
	public function getMinecraftVersion() : string{
		return $this->minecraftVersion;
	}

	/**
	 * Returns the number of players that are currently on the server,
	 *
	 * @return int
	 */
	public function getPlayerCount() : int{
		return $this->playerCount;
	}

	/**
	 * Returns the maiximum number of players that can join the server.
	 *
	 * @return int
	 */
	public function getMaxPlayerCount() : int{
		return $this->maxPlayerCount;
	}

	/**
	 * Returns the internal id of the server which is randomized on start up.
	 *
	 * @return int
	 */
	public function getServerId() : int{
		return $this->serverId;
	}

	/**
	 * Returns the name of the server software used by the server.
	 *
	 * @return string
	 */
	public function getServerSoftware() : string{
		return $this->serverSoftware;
	}

	/**
	 * Returns the server's gamemode, e.g. "Survival" or "Creative".
	 *
	 * @return string
	 */
	public function getGamemode() : string{
		return $this->gamemode;
	}

	/**
	 * Returns any extra data provided by the server software.
	 *
	 * @return array
	 */
	public function getExtraData() : array{
		return $this->extraData;
	}
}