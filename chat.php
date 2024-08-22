<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>

    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/chat.css">
</head>
<body>
    <main>
        <output id="chat"></output>

        <form id="message-form">
            <textarea id="message" placeholder="type your message"></textarea>
            <button type="submit">Send</button>
        </form> 
    </main>

    <script type="text/javascript">

        const input = document.getElementById('message');
        const output = document.getElementById('chat');
        var socket = new WebSocket('ws://localhost:8081');
        
        socket.onopen = function(e) {
            console.log('Conexão com servidor bem sucedida.');
        };

        socket.onmessage = function(e) {
            const data = JSON.parse(e.data);
            const message = document.createElement('div');
            message.classList.add('message');
            message.innerHTML = data['messageComponent'];
            output.appendChild(message);
        };

        document.getElementById('message-form').addEventListener('submit', function(e) {
            e.preventDefault();
            socket.send(JSON.stringify({type: 'chat', message: input.value}));
            input.value = '';
        });
    </script>
</body>
</html>