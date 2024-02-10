<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Rafael\Chat\MyChat;

require('./vendor/autoload.php');

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MyChat
        )
    ),
    8081
);

$server->run();