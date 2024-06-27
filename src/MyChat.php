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
        
        foreach($this->clients as $client) {
            if($conn !== $client) {
                $client->send(json_encode(['type' => 'system', 'message' => "Usuário {$conn->username} desconectou"]));            
            }
        }

        $this->clients->detach($conn);
        echo "Connection {$conn->username} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        
        $data = json_decode($msg);

        if($data->type == 'connect') {
            $from->username = $data->username;
            $from->send(json_encode(['type' => 'system', 'message' => "Welcome, {$from->username}"]));

            foreach($this->clients as $client) {
                if($from !== $client) {
                    $client->send(json_encode(['type' => 'system', 'message' => "Usuário {$from->username} entrou"]));            
                }
            }
            return;
        }

        foreach($this->clients as $client) {
            if($from !== $client) {
                $client->send(json_encode(['type' => 'chat', 'username' => $from->username, 'message' => $data->message]));            
            }
        }
    }
}