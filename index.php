<?php
    session_start();

    //Retirando as informações salvas
    $_SESSION["login"] = '0';
    $_SESSION["tipo"] = 'nenhum';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SENAI SmartLock Pro</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="shortcut icon" href="imagens/faviconV.png" type="image/x-icon">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
        <header>
            <div><img src="imagens/senai.png" alt="Logo do SENAI" class="senai2"></div>
            <h1>Seja Bem Vindo!</h1>
            <p>Faça seu login para acessar o sistema!</p>
            <p class="acesso2">Não Tenho Acesso. <a href="oQueFazer.html" target="_blank">O que fazer?</a></p>
            <div>
                <p>R. Dom Antônio Cândido de Alvarenga, 353 - Centro, Mogi das Cruzes - SP, 08780-070</p>
                <p>(11) 4728-3900</p>
            </div>
        </header>
        
        <main>
            <div class="header">
                <img src="imagens/senai.png" alt="Logo do SENAI" class="senai">
                <h1>Login</h1>
                <p>Preencha com seus dados!</p>
            </div>

            <form method="post">
                <div class="campo">
                    <label for="login">Login:</label>
                    <input type="text" id="login" autocomplete="off" name="login">
                </div>
                <div class="campo senha-container">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" autocomplete="off" name="senha">
                    <i class="bi bi-eye-fill" id="btn-senha" onclick="mostrarSenha()"></i>
                </div>
                <div class="infoLogin">
                    <a href="redefinirSenha.php">Esqueci a Senha!</a>
                </div>

                <input class="entrar" type="submit" value="Entrar">

                <p class="acesso">Não Tenho Acesso. <a href="oQueFazer.html" target="_blank">O que fazer?</a></p>
            </form>

            <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {

                    // Conexão com o banco de dados
                    $conectar_banco = new mysqli("localhost", "root", "", "DBTrava");
                
                    // Verifica se a conexão foi bem-sucedida
                    if ($conectar_banco->connect_error) {
                        die("Falha na conexão: " . $conectar_banco->connect_error);
                    }
                
                    // Sanitizar entradas
                    $login = $conectar_banco->real_escape_string($_POST['login']);
                    $senha = $_POST['senha'];
                
                    // Consultar usuário
                    $comando_banco = "SELECT * FROM usuarios WHERE email = ?";
                    $stmt = $conectar_banco->prepare($comando_banco);
                    $stmt->bind_param("s", $login);
                    $stmt->execute();
                    $resultado_tabela = $stmt->get_result();
                
                    // Verifica se o login foi encontrado
                    if ($resultado_tabela->num_rows > 0) {
                        // Se encontrado, verifica a senha
                        $linha_registro = $resultado_tabela->fetch_assoc();
                        if (password_verify($senha, $linha_registro['senha'])) {
                            if ($linha_registro['bloqueio'] == 0) {
                                echo "<div class='loginEfetuado'>Login Liberado!</div>";
                            
                                //Definindo variável global
                                $_SESSION["login"] = $linha_registro['id'];
                                $_SESSION["tipo"] = $linha_registro['tipo_conta'];

                                echo $_SESSION["tipo"];
                                
                                if($linha_registro['primeiro_acesso'] == 0){

                                    // Redirecionar com base no tipo de conta
                                    if ($linha_registro['tipo_conta'] == "Professor") {
                                        header("Location: home.php");

                                    } else if ($linha_registro['tipo_conta'] == "Administrador") {
                                        header("Location: retiradas.php");

                                    }

                                }else if($linha_registro['primeiro_acesso'] == 1){

                                    // Atualizar o valor de primeiro_acesso para 0
                                    $sql_update = "UPDATE usuarios SET primeiro_acesso = 0 WHERE id = ?";
                                    $stmt_update = $conectar_banco->prepare($sql_update);
                                    $stmt_update->bind_param("i", $linha_registro['id']);
                                    $stmt_update->execute();

                                    // Redireciona para a página de troca de senha
                                    header("Location: trocarSenha.php");
                                    exit(); // Parar a execução após redirecionar
                                }
                                exit(); // Importante para garantir que o script pare após o redirecionamento
                            } else {
                                echo "<div class='aviso'>*Acesso Negado!</div>";
                            }
                        } else {
                            echo "<div class='aviso'>*Senha incorreta!</div>";
                        }
                    } else {
                        echo "<div class='aviso'>*Login não encontrado.</div>";
                    }
                
                    // Fecha a conexão com o banco de dados
                    $stmt->close();
                    $conectar_banco->close();
                }
            ?>
        </main>
    </div>
    <script src="script/senha.js"></script>
</body>
</html>
