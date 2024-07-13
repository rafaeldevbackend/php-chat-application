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
                $client->send(json_encode(["messageComponent" => "<div class='system'><strong>Usuário $conn->username desconectou</strong></div>"]));            
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

            $from->send(json_encode(["messageComponent" => "<div class='system'><strong>Welcome, $from->username</strong></div>"]));

            foreach($this->clients as $client) {
                if($from !== $client) {
                    $client->send(json_encode([
                        "messageComponent" => "<div class='system'><strong>Usuário $from->username entrou</strong></div>"
                    ]));            
                }
            }
            return;
        }

        $from->send(json_encode([
            "messageComponent" => "<div class='chat text-right'><br>$data->message</div>" 
        ]));

        foreach($this->clients as $client) {
            if($from !== $client) {
                $client->send(json_encode([
                    "messageComponent" => "<div class='chat text-left'><strong>$from->username disse:</strong><br>$data->message</div>"
                ]));            
            }
        }
    }
}