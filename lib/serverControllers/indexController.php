<?php 
class indexController{
	
	function index(){
		
		$client = array(
			'ip'=>$_SERVER['REMOTE_ADDR'],
			'port'=>$_SERVER['REMOTE_PORT'],
			'time'=>time(),
		);
			
		return json_encode($client);
	}
	
}
?>