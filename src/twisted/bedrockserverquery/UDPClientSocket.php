<?php
declare(strict_types=1);

namespace twisted\bedrockserverquery;

use pocketmine\utils\Binary;
use raklib\protocol\UnconnectedPing;
use raklib\protocol\UnconnectedPong;
use raklib\utils\InternetAddress;
use RuntimeException;
use function mt_rand;
use function socket_close;
use function socket_connect;
use function socket_create;
use function socket_last_error;
use function socket_recvfrom;
use function socket_sendto;
use function socket_set_option;
use function socket_strerror;
use function stream_set_blocking;
use function stream_set_timeout;
use function strlen;
use function trim;
use const AF_INET;
use const AF_INET6;
use const IPPROTO_IPV6;
use const IPV6_V6ONLY;
use const SOCK_DGRAM;
use const SOL_UDP;

class UDPClientSocket{

	/** @var InternetAddress */
	private $connectAddress;
	/** @var resource */
	private $socket;

	public function __construct(InternetAddress $connectAddress){
		// Set the bind address to be used in writeUnconnectedPing and readUnconnectedPong
		$this->connectAddress = $connectAddress;

		// Create the UDP socket connection using the correct IP family and allow datagrams
		$socket = @socket_create($connectAddress->version === 4 ? AF_INET : AF_INET6, SOCK_DGRAM, SOL_UDP);
		// Check if the socket server connected properly
		if($socket === false){
			// Throw an error if the socket server failed to connect
			throw new RuntimeException("Failed to create socket: " . trim(socket_strerror(socket_last_error())));
		}
		$this->socket = $socket;

		if($connectAddress->version === 6){
			//Don't map IPv4 to IPv6, the implementation can create another RakLib instance to handle IPv4
			if(!@socket_set_option($socket, IPPROTO_IPV6, IPV6_V6ONLY, 1) && socket_last_error($socket) !== 0){
				// Throw an error if the socket server could not use IPv6
				throw new RuntimeException("Failed to set socket to IPv6: " . trim(socket_strerror(socket_last_error($socket))));
			}
		}

		// Connect the socket connection to the bind address
		@socket_connect($socket, $connectAddress->ip, $connectAddress->port);

		// Set a 10 second timeout and enable stream blocking
		if((!@stream_set_timeout($this->socket, 10) || !@stream_set_blocking($this->socket, true)) && socket_last_error() !== 0){
			// Throw an error if the socket connection failed to configure
			throw new RuntimeException("Failed to configure socket: " . trim(socket_strerror(socket_last_error($socket))));
		}
	}

	/**
	 * Reads an UnconnectedPong packet from the socket connection.
	 * If failed, an UnconnectedPong packet with placeholder values is returned.
	 *
	 * @return UnconnectedPong
	 */
	public function readUnconnectedPong() : UnconnectedPong{
		// Variables needed for socket_recvfrom
		$ip = $this->connectAddress->getIp();
		$port = $this->connectAddress->getPort();

		// Read a buffer from the socket connection
		$length = @socket_recvfrom($this->socket, $buffer, 65535, 0, $ip, $port);

		// Create an UnconnectedPong with placeholder variables
		$pong = new UnconnectedPong();
		$pong->pingID = -1;
		$pong->serverID = -1;
		$pong->serverName = "";

		// Basic checks to the buffer length
		if($length === false || $length < 1){
			// Return the packet with placeholder values
			return $pong;
		}

		// Get packet ID from the first byte in the buffer
		$packetId = Binary::readByte($buffer);
		// Validate that the received packet is UnconnectedPong
		if($packetId !== 0x1c){
			// Return the packet with placeholder values
			return $pong;
		}

		// Create a new UnconnectedPong with the read buffer
		$packet = new UnconnectedPong($buffer);
		// Decode the buffer to get the correct data
		$packet->decode();

		// Return the packet with the correct data
		return $packet;
	}

	/**
	 * Writes an UnconnectedPing packet to the socket connection.
	 *
	 * @return int|bool @link https://www.php.net/manual/en/function.socket-sendto.php
	 */
	public function writeUnconnectedPing(){
		// Create an UnconnectedPing packet to act as a client on the menu screen
		$ping = new UnconnectedPing();
		$ping->pingID = mt_rand();
		$ping->encode();

		// Sends the buffer to the socket connection and return the result
		return socket_sendto($this->socket, $ping->getBuffer(), strlen($ping->getBuffer()), 0, $this->connectAddress->getIp(), $this->connectAddress->getPort());
	}

	/**
	 * "Finish" the sequence and close the socket connection.
	 */
	public function finish() : void{
		socket_close($this->socket);
	}
}