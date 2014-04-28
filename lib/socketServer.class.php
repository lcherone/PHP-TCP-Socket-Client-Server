<?php 
Class socketServer extends socket{

	function __construct($ip = "0.0.0.0", $port = 4000, $auth = false) {
		parent::__construct($ip, $port, $auth);
		$this->start();
	}

	private function start() {
		set_time_limit(0);
		ob_implicit_flush();
		ignore_user_abort(true);

		// create socket & pool
		$socket   = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$clients  = array($socket);
		$pool     = array();
		$response = null;
		
		// set reuse address socket option
		if (socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1) === false) {
			$this->socket_error('socket_set_option', $socket);
		}

		// set nonblocking mode for file descriptor
		if (socket_set_nonblock($socket) === false) {
			$this->socket_error('socket_set_nonblock', $socket);
		}

		// bind socket to port
		if (socket_bind($socket, $this->ip, $this->port) === false) {
			$this->socket_error('socket_bind', $socket);
		}

		// start listening for connections
		if (socket_listen($socket, SOMAXCONN) === false) {
			$this->socket_error('socket_listen', $socket);
		}
		
		// Loop continuously
		while(true) {
			// re-calculate client pool
			//unset($pool);
			$client = 0;
			if (count($clients)) {
				foreach ($clients as $connection) {
					$pool[$client] = $connection;
					$client++;
				}
			}
			$clients = $pool;
			// accept a connection on created socket
			if ($spawn = @socket_accept($socket)) {
				if (is_resource($spawn)) {
					//read from client, put in $input
					if($input = @socket_read($spawn, 1048576)) {

						/* ------------------------------------------------- */
						$this->console("Client ($client): $input");
						try {
							//parse input for json - throw exception
							$input = $this->json_validate($input);

							//let do something with input object
							$response = $this->run_controller($input);

							//respond
							$this->console("Server response: ($client): $response");
							socket_write($spawn, $response, strlen($response)).chr(0);

						}catch (Exception $e) {
							socket_write($spawn, $e->getMessage(), strlen($e->getMessage())).chr(0);
							socket_close($spawn);
						}
						/* ------------------------------------------------- */

					}
					$clients[$client] = $spawn;
					$client++;
				}
			}
			if (count($clients)) {
				foreach ($clients AS $k => $v) {
					if (!defined('MSG_DONTWAIT')) define('MSG_DONTWAIT', 0x40);
					if (@socket_recv($v, $response, 1048576, MSG_DONTWAIT) === 0) {
						$this->console("Server: unsetting client: {$clients[$k]} and closing socket");
						unset($clients[$k]);
						socket_close($v);
					} else {
						if ($response) {
							$this->console("Server: $k: $response");
						}
					}
				}
			}
			//ob_flush();
			flush();
			usleep(1000);
		}
		socket_close($socket);
	}

	function console($string) {
		echo str_repeat(PHP_EOL,4092).$string.'<br>'.chr(0);
	}

	///FUNCTIONS - For Debugging
	function socket_error($error, $socket) {
		$errMsg = socket_strerror(socket_last_error($socket));
		
		echo"<div style=\"padding: 0px 10px 5px 10px;".PHP_EOL.
			"			   background-color: #f88;".PHP_EOL.
			"			   border: 1px solid #f00;\">".PHP_EOL.
			"	<h1>$errorFunction() failed!</h1>".PHP_EOL.
			"	<p> <strong>Error Message:</strong>".PHP_EOL.
			"		<span style=\"font-family: monospace;\">$errMsg</span>".PHP_EOL.
			"	</p>".PHP_EOL.
			"</div>".PHP_EOL;
	}

	/**
	 * Run controller.
	 */
	public function run_controller($route){
		/* create controllers class instance & inject core */
		$controller = './lib/serverControllers/'.$route->controller.'Controller.php';
		if(file_exists($controller)) {
			require_once($controller);
			$class = $route->controller.'Controller';
			if(class_exists($class)) {
				$controller = new $class();
			}
		} else {
			require_once('./lib/serverControllers/not_foundController.php');
			$controller = new not_foundController();
		}
		/* check the root class is callable */
		if (is_callable(array($controller, $route->action)) === false) {
			/* index() method because not found method */
			$action = 'index';
		} else {
			/* action() method is callable */
			$action = $route->action;
		}
		/* run the action method */
		return $controller->{$action}();
	}

	function json_validate($json, $assoc_array = FALSE){
		// decode the JSON data
		$result = json_decode($json, $assoc_array);
		// switch and check possible JSON errors
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				$error = ''; // JSON is valid
				break;
			case JSON_ERROR_DEPTH:
				$error = 'Maximum stack depth exceeded.';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Underflow or the modes mismatch.';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Unexpected control character found.';
				break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON.';
				break;
				// only PHP 5.3+
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
				break;
			default:
				$error = 'Unknown JSON error occured.';
				break;
		}
		if($error !== '') {
			// throw the Exception or exit
			throw new Exception($error);
		}
		// everything is OK
		return $result;
	}

}
?>