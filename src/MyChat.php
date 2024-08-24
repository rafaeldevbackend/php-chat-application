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

        // Obtenha o cookie da sessão do cabeçalho HTTP
        $cookies = $conn->httpRequest->getHeader('Cookie');
        $cookies = explode('; ', $cookies[0]);

        $session_id = '';
        foreach ($cookies as $cookie) {
            list($name, $value) = explode('=', $cookie, 2);
            if ($name === 'PHPSESSID') {
                $session_id = $value;
                break;
            }
        }

        if ($session_id) {
            // Restaurar a sessão com o ID encontrado
            session_id($session_id);
            session_start();

            $conn->send(
                json_encode([
                    "messageComponent" => "<div class='system'><strong>Bem vindo {$_SESSION['email']}</strong></div>"
                ])
            );

            session_write_close();
        } else {
            echo "Sessão não encontrada\n";
        }

        echo "Conexão de usuário: {$conn->resourceId}\n";
    }

    public function onClose(ConnectionInterface $conn) {
        
        foreach($this->clients as $client) {
            if($client != $conn) {
                $client->send(json_encode(["messageComponent" => "<div class='system'><strong>Usuário {$conn->resourceId} desconectou</strong></div>"]));            
            }
        }

        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        
        $data = json_decode($msg);      

        foreach($this->clients as $client) {
            if($from === $client) {
                $from->send(json_encode([
                    "messageComponent" => "<div class='chat text-right'><br><span class='message-text'>$data->message</span></div>" 
                ]));
            } else {
                $client->send(json_encode([
                    "messageComponent" => "<div class='chat text-left'><span class='message-header'>{$_SESSION['email']} disse:</span><br><span class='message-text'>$data->message</span></div>"
                ]));  
            }
        }
    }
}