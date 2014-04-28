<?php 
Class socketClient extends socket{

	function __construct($ip = "127.0.0.1", $port = 4000, $auth = false){
		parent::__construct($ip, $port, $auth);
	}

	private function connect(){
		try{
			// create socket
			if($this->socket = $this->socket_create(AF_INET, SOCK_STREAM, SOL_TCP)){
				$this->log("Client: socket::socket_create()", "socket created");
			}else{
				$this->log("Client: socket::socket_create()", "error creating socket");
			}
			// connect to server
			if($this->socket_connect($this->socket, $this->ip, $this->port)){
				$this->log("Client: socket::socket_connect()", "socket connected");
			}else{
				$this->log("Client: socket::socket_connect()", "error connecting to server");
			}
		}catch(Exception $e){
			$this->log("Error:", $e->getMessage());
		}
	}

	function send($message){
		//
		$this->connect();
		try{
			// send string to server
			if($this->socket_write($this->socket, $message, strlen($message))){
				$this->log("Client: socket::socket_write()", "message sent: $message");
			}else{
				$this->log("Client: socket::socket_write()", "could not send data to server");
			}
			// get server response
			if($response = $this->socket_read($this->socket, 1048576)){
				$this->log("Client: socket::socket_read()", "response read: $response");
				$this->response[] = $response;
				return $response;
			}else{
				$this->log("Client: socket::socket_read()", "could not read server responce");
				return false;
			}
		}catch(Exception $e){
			$this->log("Error:", $e->getMessage());
		}
		return false;
	}

}
?>