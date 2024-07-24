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

            $from->send(json_encode(["messageComponent" => "<div class='system'><span class='system-message'>Welcome, $from->username</span></div>"]));

            foreach($this->clients as $client) {
                if($from !== $client) {
                    $client->send(json_encode([
                        "messageComponent" => "<div class='system'><span class='system-message'>Usuário $from->username entrou</span></div>"
                    ]));            
                }
            }
            return;
        }

        foreach($this->clients as $client) {
            if($from === $client) {
                $from->send(json_encode([
                    "messageComponent" => "<div class='chat text-right'><br><span class='message-text'>$data->message</span></div>" 
                ]));
            } else {
                $client->send(json_encode([
                    "messageComponent" => "<div class='chat text-left'><span class='message-header'>$from->username disse:</span><br><span class='message-text'>$data->message</span></div>"
                ]));  
            }
        }
    }
}