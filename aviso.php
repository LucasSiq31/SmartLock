<?php
    // Iniciar sessão
    session_start();

    // Verificar se o usuário é um professor
    if ($_SESSION['tipo'] != 'Professor') {
        header('Location: index.php');
        exit();
    }
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

                function conectarBanco() {
                    $conexao = mysqli_connect("localhost", "root", "", "dbtrava");
                    if (!$conexao) {
                        die("Falha na conexão: " . mysqli_connect_error());
                    }
                    return $conexao;
                }

                // Inicializar o comando padrão
                if (!isset($_SESSION['comando'])) {
                    $_SESSION['comando'] = "fechado"; // Comando padrão
                }
                $carrinho = "";

                $conectar_banco = conectarBanco();
                $result = mysqli_query($conectar_banco, "SELECT * FROM retiradas WHERE idProfessor = '".$_SESSION['login']."' ORDER BY dia DESC, id DESC LIMIT 1");
                     if ($result && mysqli_num_rows($result) > 0) {
                        $data = mysqli_fetch_assoc($result);
                        $carrinho = $data['idCarrinho'];
                     }

                // Função para atualizar o arquivo JSON
                function atualizarJson($status, $carrinhoN) {
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
                            $encontrado = true;
                            break;
                        }
                    }
                
                    // Salvar os dados atualizados de volta no arquivo JSON
                    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
                }


                // Verificar se um botão foi pressionado e armazenar o comando correspondente
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    if (isset($_POST['ligar'])) {
                        $_SESSION['comando'] = "aberto";
                        atualizarJson($_SESSION['comando'],$carrinho); // Atualizar o JSON
                    } elseif (isset($_POST['desligar'])) {
                        $_SESSION['comando'] = "fechado"; // Comando correto
                        atualizarJson($_SESSION['comando'],$carrinho); // Atualizar o JSON
                    }

                    // Redirecionar para a mesma página para evitar reenvio do formulário
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit(); // Parar a execução do script após o redirecionamento
                }

                // Verificar se a requisição foi feita pelo ESP32
                if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['esp'])) {
                    // Retornar o comando em formato JSON para o ESP32
                    $comando_atual = $_SESSION['comando'];
                    $data = array('status' => $comando_atual);

                    // Definir o cabeçalho como JSON e enviar a resposta
                    header('Content-Type: application/json');
                    echo json_encode($data);
                    exit(); // Finalizar o script após enviar o comando ao ESP32
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
            <h1>O carrinho está em sua responsabilidade!</h1>

            <p>Você já realizou a etapa de retirada e verificação do carrinho, agora você deve realizar a etapa de devolução do carrinho.</p>
            <p>Seu acesso ao resto da aplicação está bloqueado enquanto você não preencher o formulário.</p>
            <p>Responda o formulário de devolução apenas quando você for devolver ou fechar o carrinho</p>

            <a href="formularioDev.php"><div class='formDevBtn'>Acessar Formulário de Devolução</div></a>

            <div class="boxBtn">
                <h3>Você pode abrir e fechar o carrinho a qualquer momento.</h3>
                <p class='statusCarrinho'>Status: <?php echo $_SESSION['comando'];?></p>
                <hr>

                <form method='post' class='abreTrava'>
                    <button name="ligar" type="submit" class='verde'><img src="imagens/cadeado-desbloqueado.png" alt=""><p>Abrir</p></button>
                    <button name="desligar" type="submit"><img src="imagens/cadeado-trancado.png" alt=""><p>Fechar</p></button>
                </form>
            </div>
            
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