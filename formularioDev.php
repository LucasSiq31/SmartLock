<?php
    // Iniciar sessão
    session_start();

    // Verificar se o usuário é um professor
    if ($_SESSION['tipo'] != 'Professor') {
        header('Location: index.php');
        exit();
    }

    // Conectar ao banco de dados
    $conectar_banco2 = mysqli_connect("localhost", "root", "", "DBTrava");

    // Verificar a conexão
    if (!$conectar_banco2) {
        die("Falha na conexão: " . mysqli_connect_error());
    }

    // Inicializar variáveis
    $notebook = '';
    $mouse = '';
    $observacao = '';

    // Buscar a última retirada registrada
    $result = mysqli_query($conectar_banco2, "SELECT * FROM retiradas WHERE idProfessor = '".$_SESSION['login']."' ORDER BY dia DESC, id DESC LIMIT 1");

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $notebook = $data['notebook'];
        $mouse = $data['mouse'];
        $observacao = $data['observacao'];
        $carrinho = $data['idCarrinho'];
        $carrinho_aberto = $carrinho;
    } else { 
        echo "<p class='aviso'>Nenhuma retirada encontrada.</p>";
        exit();
    }

    // Função para atualizar o arquivo JSON de comandos
    function atualizarJson($carrinhoN, $status) {
        // Caminho para o arquivo JSON
        $jsonFile = 'comandos.json';
    
        // Carregar os dados existentes do JSON, caso o arquivo exista
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $data = json_decode($jsonData, true);
        } else {
            // Se o arquivo não existe, criar uma estrutura vazia
            $data = array("carrinhos" => array());
        }
    
        // Verificar se já existe um carrinho com o id fornecido ($carrinhoN)
        $encontrado = false;
        foreach ($data['carrinhos'] as &$carrinho) {
            if ($carrinho['id'] == $carrinhoN) {
                // Atualizar o carrinho existente com o novo status e senha
                $carrinho['status'] = $status;
                $carrinho['senha'] = '000000';
                $encontrado = true;
                break;
            }
        }
    
        // Salvar os dados atualizados de volta no arquivo JSON
        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    // Inicializar comando padrão de sessão para o controle de LED
    if (!isset($_SESSION['comando'])) {
        $_SESSION['comando'] = "fechado";
    }

    // Verificar se o formulário foi enviado pelo método POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitizar e validar os dados do formulário
        $notebook = mysqli_real_escape_string($conectar_banco2, $_POST['notebook']);
        $mouse = mysqli_real_escape_string($conectar_banco2, $_POST['mouse']);
        $observacao = mysqli_real_escape_string($conectar_banco2, $_POST['observacao']);
        
        // Atualizar os dados na tabela 'retiradas'
        $comando_banco = "UPDATE retiradas SET notebook='$notebook', mouse='$mouse', observacao='$observacao', situacao='Devolvido' WHERE id=" . intval($data['id']);
        
        if (mysqli_query($conectar_banco2, $comando_banco)) {
            // Atualizar a tabela 'usuarios'

            $comando_banco2 = "UPDATE carrinhos SET status='Disponível', notebook='$notebook', mouses='$mouse' WHERE id = '$carrinho'";

            if (mysqli_query($conectar_banco2, $comando_banco2)) {
                $comando_banco3 = "UPDATE usuarios SET retirada = 0 WHERE id = '".$_SESSION['login']."'";
                
                // Executa as consultas SQL
                if (mysqli_query($conectar_banco2, $comando_banco3)) {
                    $_SESSION['comando'] = "fechado";
                    atualizarJson($carrinho_aberto, $_SESSION['comando']);

                    header("Location: home.php");
                    exit();
                }
            }
            exit();
        } else {
            echo "<p class='aviso'>Erro ao atualizar retiradas: " . mysqli_error($conectar_banco2) . "</p>";
        }


    }

    // Fechar a conexão com o banco de dados
    mysqli_close($conectar_banco2);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Devolução - SENAI SmartLock Pro</title>

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
                            echo "<a href='perfilProf.php' ><img src='imagens/icon_perfil.png' alt='' class='icon'></a>";
                        }else{
                            echo "<a href='perfilProf.php' ><img src='".$linha_registro['imagem']."' alt='' class='icon'></a>";
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
            <a href="home.php" class="buttonHome">
                <img src="imagens/seta.png" alt="" class="icone">
                <p>Voltar</p>
            </a>

            <h2>Responda o formulário apenas quando fechar o carrinho!</h2>

            <form action="" method="POST">
                <h3>Este formulário é de:</h3>
                <div class="botao">
                    <p>Retirada</p>
                    <p class="slider">Devolução</p>
                </div>

                <h3>Quantos notebooks?</h3>
                <input type="number" name="notebook" value="<?php echo htmlspecialchars($notebook); ?>" required>

                <h3>Quantos mouses?</h3>
                <input type="number" name="mouse" value="<?php echo htmlspecialchars($mouse); ?>" required>

                <h3>Observação</h3>
                <input type="text" name="observacao" style="height: 120px;" value="<?php echo htmlspecialchars($observacao); ?>">

                <button type="submit">Atualizar</button>
            </form>
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