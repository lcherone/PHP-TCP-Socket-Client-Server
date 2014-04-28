<?php
require('./lib/socket.class.php');
require('./lib/socketServer.class.php');

new socketServer("0.0.0.0", 54321);
?>