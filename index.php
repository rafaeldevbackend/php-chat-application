<?php

    ini_set('display_errors', true);
    ini_set('display_startup_errors', true);
    error_reporting(E_ALL);

    session_start();

    if(isset($_POST['login'])) {

        $email    = $_POST['email'];
        $password = $_POST['password'];

        $conn = new PDO("mysql:host=localhost;dbname=chat;", "root", "123456");
        
        $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = :email and password = :password LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(is_array($user)) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            
            setcookie(
                'PHPSESSID',           // Nome do cookie
                session_id(),          // Valor do cookie
                0,                     // Tempo de expiração (0 para expirar ao fechar o navegador)
                '/',                   // Caminho (usualmente '/' para todo o site)
                '',                    // Domínio (string vazia para usar o domínio atual)
                false,                 // Secure (false para não usar SSL)
                true                   // HttpOnly (true para limitar acesso ao cookie via JavaScript)
            );

            header('location: chat.php');
            exit();
        }        
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My chat</title>
    
    <link rel="stylesheet" href="./assets/css/style.css">
    <style type="text/css">
        h1 {
            margin: 5px 0;
        }

        form#user-form {
            text-align: center;
        }

        label {
            color: #ffffff;
        }

        input#user-email {
        }
        
        input {
            width: 320px;
            padding: 15px;
            border-radius: 5px;
        }

        button {
            margin-top: 8px;
            padding: 15px;
            border: 1px solid #333;
            background-color: #1a1919;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <main>
        <form id="login" method="POST">
            <label for="user-email">E-mail</label><br>
            <input type="email" name="email" id="user-email" placeholder="user@provider.com" value="rafaelrat2019@gmail.com"><br>

            <label for="user-password">Password</label><br>
            <input type="password" name="password" id="user-password" placeholder="******" value="123456"><br>
            
            <button type="submit" name="login">Login</button>
        </form>
    </main>
</body>
</html>