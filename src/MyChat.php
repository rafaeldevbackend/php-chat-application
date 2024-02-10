<?php

namespace Rafael\Chat;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MyChat implements MessageComponentInterface {
    
    private $clients;

    public function __construct() {
        $this->clients = new \SPLObjectStorage;
    }
 
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Conexão de usuário: {$conn->resourceId}\n";
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        foreach($this->clients as $client) {
            if($from !== $client) {
                $client->send($msg);            
            }
        }
    }
}