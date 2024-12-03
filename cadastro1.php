<?php
    //inicisnd sessão 
    session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de usuários - SENAI SmartLock Pro</title>

    <link rel="stylesheet" href="css/styleAzul.css">
    <link rel="shortcut icon" href="imagens/faviconA.png" type="image/x-icon">
</head>
<body>
<header>
        <div class="cabecalho">
            <img src="imagens/senai_branco.png" alt="" class="logo">
            <div></div>
            <h1>SmartLock Pro</h1>
        </div>
        <div class="divPerfil">
        <?php
                // Função para conectar ao banco de dados
                function conectarBanco() {
                    $conexao = mysqli_connect("localhost", "root", "", "dbtrava");
                    if (!$conexao) {
                        die("Falha na conexão: " . mysqli_connect_error());
                    }
                    return $conexao;
                }
                
                // Função para executar uma consulta preparada
                function executarConsulta($conexao, $sql, $parametros, $tipos) {
                    $stmt = $conexao->prepare($sql);
                    $stmt->bind_param($tipos, ...$parametros);
                    $stmt->execute();
                    return $stmt;
                }
                
                // Exibir dados da tabela
                $conexao = conectarBanco();

                $conta = $_SESSION["login"];

                $comando_banco = "SELECT * FROM usuarios WHERE id = '$conta'";
                $resultado_tabela = mysqli_query($conexao, $comando_banco);
                
                if ($resultado_tabela) {
                    while ($linha_registro = mysqli_fetch_assoc($resultado_tabela)) {
                        if(empty(trim($linha_registro['imagem']))){
                            echo "<a href='perfilAdm.php' ><img src='imagens/icon_perfil.png' alt='' class='icon'></a>";
                        }else{
                            echo "<a href='perfilAdm.php' ><img src='".$linha_registro['imagem']."' alt='' class='icon'></a>";
                        }
                        
                    }
                }
                
                // Fecha a conexão
                mysqli_close($conexao);
            ?>
            
        </div>
    </header>
    <div class="linha"></div>

    <main>
        <div class="container">
            <a href="usuarios.php">Voltar página</a>
            <form method="post">
                <h1>Cadastro</h1>
                <h3>Nome:</h3>
                <input type="text" autocomplete="off" name="campo1">
                <h3>Sobrenome:</h3>
                <input type="text" autocomplete="off" name="campo2">
                <h3>E-mail:</h3>
                <input type="email" autocomplete="off" name="campo3">
                <h3>Tipo de Conta</h3>
                <div class="divRadio">
                    <input type="radio" id="1" name="radio" value="Professor" class="radioSimples">
                    <label for="1">Professor</label>
                </div>
                <div class="divRadio">
                    <input type="radio" id="2" name="radio" value="Administrador" class="radioSimples">
                    <label for="2">Administrador</label>
                </div>
                
                <input class="submit" type="submit" value="Cadastrar">
            </form>

            <?php
                // Conectar ao banco de dados
                $conectar_banco = mysqli_connect("localhost", "root", "", "DBTrava");

                // Verificar a conexão
                if (!$conectar_banco) {
                    die("Falha na conexão: " . mysqli_connect_error());
                }

                // Verificar se o formulário foi enviado pelo método POST
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Sanitizar e validar os dados do formulário
                    $campo1 = $_POST['campo1'];
                    $campo2 = $_POST['campo2'];
                    $campo3 = $_POST['campo3'];
                    $campo5 = $_POST['radio'];

                    $campo4 = 'Senai117';

                    $senha = $senhaHash = password_hash($campo4, PASSWORD_BCRYPT);
                    // Criar e preparar a consulta SQL
                    $comando_banco = "INSERT INTO usuarios (nome, email, tipo_conta, senha, primeiro_acesso) VALUE ('$campo1 $campo2', '$campo3', '$campo5', '$senha', 1)";
                    

                    if(mysqli_query($conectar_banco, $comando_banco)){
                        echo "<p class='aviso'>Registro de {$campo1} {$campo2} inserido com sucesso!</p>";
                    } else {
                        echo "<p class='aviso'>Erro ao inserir registro: " . $stmt->error . "</p>";
                    }
                }

                mysqli_close($conectar_banco);
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
                <p>Feito por: Lucas Siqueira, Maria Fernanda, Stela Amorim e Ulisses Almeida</p>
            </div>
        </div>
    </footer>

</body>
</html>