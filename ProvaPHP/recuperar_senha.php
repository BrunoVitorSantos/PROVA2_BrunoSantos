<?php
    session_start();
    require_once 'conexao.php';
    require_once 'funcoes_email.php'; //arquivo com as funções que geram e simulam o envio da senha

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $email = $_POST['email'];

        //VERIFICA SE O EMAIL EXISTE NO BANCO
    $sql="SELECT * FROM usuario WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if($usuario){
        //GERA A SENHA TEMPORÁRIA
        $senha_temporaria = gerarSenhaTemporaria();
        $senha_hash = password_hash($senha_temporaria, PASSWORD_DEFAULT);

        //ATUALIZA A SENHA DO USUARIO NO BANCO
        $sql = "UPDATE usuario SET senha = :senha, senha_temporaria = TRUE WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':senha', $senha_hash);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        //SIMULA O ENVIO DO EMAIL (GRAVA EM TXT)
        simularEnvioEmail($email, $senha_temporaria);

        echo "<script>alert('Uma senha temporaria foi gerada e enviada (simulação).Verifique o arquivo emails_simulados.txt');window.location.href='login.php';</script>";

    } else{
        echo "<script>alert('Email não encontrado!');</script>";
        }   
    }
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Recuperar Senha</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <h2>Recuperar Senha</h2>
        <form action="recuperar_senha.php" method="POST">
            <label for="email"> Digite o seu Email Cadastrado:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Enviar</button>
        </form>

        <p><a href="login.php">Voltar para o Login</a></p>
        
    </body>
    </html>