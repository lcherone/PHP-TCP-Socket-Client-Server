PHP-UDP-Socket-Client-Server
============================

A simple stripped down PHP UDP socket server and client; wrapped in a handy class. 

Its different from the basic socket examples you see on http://www.php.net/manual/en/ref.sockets.php is that the server calls controllers, like in MVC, 
which makes it easy to implement something other then a strrev() of what the client sent, kinda useless at moment but ill
add something interesting in later commits.

Example:
===
**./index.php (Client)**

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
    
    $responce = $socket->send(json_encode($packet));
    
    $socket->report();
    ?>

**./server.php (Server)**
    
    <?php
    require('./lib/socket.class.php');
    require('./lib/socketServer.class.php');
    
    new socketServer("0.0.0.0", 54321);
    ?>


