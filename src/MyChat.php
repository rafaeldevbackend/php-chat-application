<?php

namespace Rafael\Chat;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MyChat implements MessageComponentInterface {
    
    private $clients;
    private $users;

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
                $client->send(json_encode(['type' => 'system', 'message' => "Usuário {$this->users[$conn->resourceId]} desconectou"]));            
            }
        }

        $this->clients->detach($conn);
        echo "Connection {$this->users[$conn->resourceId]} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        
        $data = json_decode($msg);

        if($data->type == 'connect') {
            $this->users[$from->resourceId] = $data->username;
            $from->send(json_encode(['type' => 'system', 'message' => "Welcome, {$data->username}"]));

            foreach($this->clients as $client) {
                if($from !== $client) {
                    $client->send(json_encode(['type' => 'system', 'message' => "Usuário {$this->users[$from->resourceId]} entrou"]));            
                }
            }
            return;
        }

        foreach($this->clients as $client) {
            if($from !== $client) {
                $client->send(json_encode(['type' => 'chat', 'username' => $this->users[$from->resourceId], 'message' => $data->message]));            
            }
        }
    }
}