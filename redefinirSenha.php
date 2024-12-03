<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinição de senha - SENAI SmartLock Pro</title>
    <link rel="stylesheet" href="css/styleVerm.css">
    <link rel="shortcut icon" href="imagens/faviconV.png" type="image/x-icon">
</head>
<body>
    <header>
        <div class="cabecalho">
            <img src="imagens/senai_branco.png" alt="" class="logo">
            <div></div>
            <h1>SmartLock Pro</h1>
        </div>
    </header>
    <div class="linha"></div>

    <main>
        <div class="container">
            <form method="post">
                <h1>Redefinir Senha</h1>
                <h3 for="novaSenha">Insira seu email para redefinir a senha</h3>
                <input type="text" autocomplete="off" name="email">

                <input class="submit" type="submit" value="Enviar email">
            </form>

            <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $email = $_POST['email'];

                    function conectarBanco() {
                        $conexao = mysqli_connect("localhost", "root", "", "dbtrava");
                        if (!$conexao) {
                            die("Falha na conexão: " . mysqli_connect_error());
                        }
                        return $conexao;
                    }

                    $conexao = conectarBanco();

                    $comando_banco = "SELECT * FROM usuarios WHERE email = '$email'";
                    $resultado_tabela = mysqli_query($conexao, $comando_banco);
                
                    if ($resultado_tabela ->num_rows > 0) {
                        while ($linha_registro = mysqli_fetch_assoc($resultado_tabela)) {

                            $senha = "Senai117";
                            $hash = password_hash($senha, PASSWORD_DEFAULT);
                            $acesso = 1;

                            $sql = "UPDATE usuarios SET senha=?, primeiro_acesso=? WHERE email=?";
                            $stmt = $conexao->prepare($sql);
                            $stmt->bind_param("sis", $hash, $acesso, $email);

                            // Executando a consulta
                            if ($stmt->execute()) {
                                echo "<div class='aviso2'>";
                                echo "<h3>Sua senha foi redefinida com sucesso!</h3>";
                                echo "<p>Sua senha foi redefinida para um código padrão, faça seu login novamente para escolher uma nova senha.</p>";
                                echo "<p>Caso não saiba a senha padrão, entre em contato com os Administradores</p>";
                                echo "<a href='index.php'>Voltar para o login</a>";
                                echo "</div>";
                            } else {
                                echo "<div class='aviso2'>Erro ao atualizar senha: " . $stmt->error . "</div>";
                            }
                        }
                    }else{
                        echo "<div class='aviso2'>Nenhum usuário encontrado!</div>";
                    }
                } 
                
                
            ?>
            
        </div>
    </main>

    <footer>
        
        <div class="container">
            <div class="endereco">
                <div>
                    <img class="icones" src="imagens/icon_local.png" alt="">
                    <div>
                        <h4>Endereço:</h4>
                        <p>R. Dom Antônio Cândido de Alvarenga, 353 - Centro, Mogi das Cruzes - SP, 08780-070</p>
                    </div>
                </div>
                <div>
                    <img class="icones" src="imagens/icon_telefone.png" alt="">
                    <div>
                        <h4>Telefone:</h4>
                        <p>(11) 4728-3900</p>
                    </div>
                </div>
                <img class="senaiFooter" src="imagens/senai_branco.png" alt="" class="logo">
            </div>
            <div class="copyright">
                <p>Copyright 2024 © Todos os direitos reservados.</p>
            </div>
            
            <div class="feito">
                <p>Feito por: Lucas Siqueira, Maria Fernanda, Ulisses Almeida e Stela Amorim</p>
            </div>
        </div>
    </footer>
    
</body>
</html>