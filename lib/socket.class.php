<?php 
/*
* Simple PHP Sockets client class
*/
class socket{
	public $ip 		 = "0.0.0.0";
	public $port 	 = 4000;
	public $action   = array();
	public $log      = array();
	public $response = array();
	public $socket;
	public $clients  = array();
	public $pool     = array();

	function __construct($ip = "0.0.0.0", $port = 4000){
		$this->ip 	= $ip;
		$this->port = $port;
	}

	public function __call($method, $params) {
		// check method
		if (!is_scalar($method)){
			throw new Exception('Method name ('.$method.') is not scalar value');
		}
		// check params
		if(is_array($params)){
			$params = array_values($params);
		}else{
			throw new Exception('Params must be given as array');
		}
		return @call_user_func_array($method, $params);
	}

	function __destruct(){
		if(is_resource($this->socket)) $this->socket_close($this->socket);
	}
	
	function report(){
		echo '
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>'.__CLASS__.' report</title>
<style type="text/css">
* {
	padding:0;
	margin:0;
}
div.log {
	width:700px;
	padding: 4px 10px 4px 10px;
	background-color: #f88;
	border: 1px solid #f00;
	margin:25px auto;
}
div.log span {
	font-family: monospace;
}
</style>
</head>

<body>
'.implode(PHP_EOL, $this->log).'
</body>
</html>';
	}

	protected function log($reason, $extra, $return = false){
		$errMsg = $this->socket_strerror($this->socket_last_error($this->socket));

		$log = '
	<div class="log">
		<h1>'.$reason.'</h1>
		<p><strong>Message:</strong>
			<span>'.$extra.'</span>
			<br>
			<span>'.$errMsg.'</span>
		</p>
	</div>'.PHP_EOL;
		
		if($return == true){
			 return $log; 
		}else{
			$this->log[] = $log;
		}
	}

}
?>