PHP-UDP-Socket-Client-Server
============================

A simple stripped down PHP UDP socket server and client, that communicates in JSON format; wrapped in a handy class. 
Server will parse the JSON and call controller based on route, currently its very bare and does nothing but sends JSON back.
In later commits ill add something more interesting. 

Example:
===
**./index.php (Client)**
Client builds and sends a JSON encoded packet.

    <?php
    require('./lib/socket.class.php');
    require('./lib/socketClient.class.php');
    
    $socket = new socketClient('127.0.0.1', 54321);
    
    $packet = array('controller'    => 'index',
    				'action'	    => 'index',
    				'subaction'	    => '',
    				'subaction_id'  => '',
    				'time'		    => time(),
    				'ip'		    => $_SERVER['SERVER_ADDR'],
    				);
    
    $response = $socket->send(json_encode($packet));
    
    $socket->report();
    ?>

**./server.php (Server)**
    
    <?php
    require('./lib/socket.class.php');
    require('./lib/socketServer.class.php');
    
    new socketServer("0.0.0.0", 54321);
    ?>
