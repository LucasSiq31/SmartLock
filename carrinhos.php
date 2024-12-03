<?php
    //inicisnd sessão 
    session_start();

    if($_SESSION['tipo'] != 'Administrador'){
        header('Location: index.php');
    }

    date_default_timezone_set('America/Sao_Paulo');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinhos - SENAI SmartLock Pro</title>

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
                
                // Exibir dados da tabela
                $conexao = conectarBanco();

                $conta = $_SESSION["login"];

                $comando_banco = "SELECT * FROM usuarios WHERE id = '$conta'";
                $resultado_tabela = mysqli_query($conexao, $comando_banco);
                
                if ($resultado_tabela) {
                    while ($linha_registro = mysqli_fetch_assoc($resultado_tabela)) {
                        if (empty(trim($linha_registro['imagem']))) {
                            echo "<a href='perfilAdm.php' ><img src='imagens/icon_perfil.png' alt='' class='icon'></a>";
                        } else {
                            echo "<a href='perfilAdm.php' ><img src='".htmlspecialchars($linha_registro['imagem'])."' alt='' class='icon'></a>";
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
            <nav class=" paginas menu-hamburger">
                <input id="menu-hamburguer" type="checkbox" />
                <label for="menu-hamburguer">
                    <div class="menuHamb">
                        <div class="menu">
                            <span class="hamburguer"></span>
                        </div>
                        <p>Menu</p>
                    </div>
                </label>

                <ul class="menu-hamburguer-elements show">
                    <li>
                        <a href="retiradas.php">Retiradas</a>
                    </li>

                    <li>
                        <a href="agendamentos.php">Agendamentos</a>
                    </li>

                    <li>
                        <a href="carrinhos.php" class="destaque">Carrinhos</a>
                    </li>

                    <li>
                        <a href="usuarios.php">Usuários</a>
                    </li>
                </ul>
            </nav>
            <hr>

            <form method='post'><button name='cadastro' class="cadastroNovo cadastroNovo2">Criar novo carrinho</button></form>

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

                $maxCarrinho = 0;

                $comando_banco = "
                    SELECT 
                        carrinhos.*, 
                        usuarios.nome AS usuario_nome
                    FROM 
                        carrinhos 
                    LEFT JOIN 
                        usuarios ON carrinhos.retirada = usuarios.id
                    ORDER BY
                        id
                ";

                $resultado_tabela = mysqli_query($conexao, $comando_banco);

                if ($resultado_tabela) {
                    while ($linha_registro = mysqli_fetch_assoc($resultado_tabela)) {

                        $retirada = dadosRetirada($linha_registro["id"]);

                        echo "<div class='info'>";
                        echo "<h4>Carrinho ".$linha_registro["id"]."</h4>";
                        echo "<div class='carrinho'>";

                        $agendado = reserva($linha_registro["id"], $conexao);

                        if(!empty($agendado)) {
                             echo "<p><b>Status: </b><span style='color: #007cc1'>Reservado</span></p>";
                        }else if ($linha_registro["status"] == 'Disponível') {
                            echo "<p><b>Status: </b><span style='color: #27cc1f'>".$linha_registro["status"]."</span></p>";
                        }else if ($linha_registro["status"] == 'Ocupado') {
                            echo "<p><b>Status: </b><span style='color: red'>".$linha_registro["status"]."</span></p>";
                        }else if ($linha_registro["status"] == 'Aberto') {
                            echo "<p><b>Status: </b><span style='color: #e3a600'>".$linha_registro["status"]."</span></p>";
                        } 
                        
                        echo "<p><b>Notebooks: </b>".$linha_registro["notebook"]."</p>";
                        echo "<p><b>Mouses: </b>".$linha_registro["mouses"]."</p>";

                        if(!empty($agendado)) {
                            echo "<p><b>Agendado por: </b>" . $agendado . "</p>";
                        }else if (is_null($linha_registro["retirada"]) || $linha_registro["retirada"] == 0) {
                            echo "<p><b>Última retirada: </b>Não há retiradas cadastradas nesse carrinho</p>";
                        }else{
                            echo "<p><b>Última retirada: </b>" . htmlspecialchars($linha_registro["usuario_nome"]) . "</p>";
                        }

                        echo "</div>";
                        echo "</div>";

                        $maxCarrinho = $linha_registro["id"];

                    }
                } else {
                    echo "<p>Erro ao recuperar dados.</p>";
                }

                function reserva($id, $conexao) {

                    $pegar_agendamentos = "
                        SELECT 
                            agendamentos.*, 
                            usuarios.nome AS usuario_nome
                        FROM 
                            agendamentos 
                        LEFT JOIN 
                            usuarios ON agendamentos.usuario_id = usuarios.id
                        WHERE 
                            carrinho_id = ".($id)." 
                        ORDER BY 
                            data_agendada, horario 
                        LIMIT 1";
                
                    $resultado_busca = mysqli_query($conexao, $pegar_agendamentos);
                
                    if ($resultado_busca) {
                        while ($linha = mysqli_fetch_assoc($resultado_busca)) {
                            $data = $linha['data_agendada'];
                            $horario = $linha['horario'];

                            $agora = new DateTime(); // Hora e data atuais
                
                            // Define os períodos do dia
                            $manha_inicio = new DateTime($data . ' 07:30'); // 07:30
                            $manha_fim = new DateTime($data . ' 11:30');    // 11:30
                            $tarde_inicio = new DateTime($data . ' 13:00');  // 13:00
                            $tarde_fim = new DateTime($data . ' 17:00');    // 17:00
                            $noite_inicio = new DateTime($data . ' 18:00');  // 18:00
                            $noite_fim = new DateTime($data . ' 22:00');    // 22:00
                            
                            // Verifica em qual período a reserva se encaixa
                            if ($agora >= $manha_inicio && $agora < $manha_fim) {
                                $periodo = 'Manhã';
                            } elseif ($agora >= $tarde_inicio && $agora < $tarde_fim) {
                                $periodo = 'Tarde';
                            } elseif ($agora >= $noite_inicio && $agora <= $noite_fim) {
                                $periodo = 'Noite';
                            } else {
                                $periodo = 'Fora de horário';
                            }
                            
                            if($periodo == $horario && $linha['status'] == ''){
                                // Retorna o nome do usuário com base no período
                                return $linha['usuario_nome'];
                            }
                        }
                    }
                
                    // Se não encontrar agendamento, retorna null
                    return null;
                }
                


                function dadosRetirada($idProf){

                    $conexao = mysqli_connect("localhost", "root", "", "dbtrava");

                    $comando_banco = "SELECT * FROM carrinhos LEFT OUTER JOIN usuarios ON carrinhos.retirada = usuarios.nome WHERE carrinhos.id = '".$idProf."'";
                    $resultado_tabela = mysqli_query($conexao, $comando_banco);

                    if ($resultado_tabela) {
                        while ($linha_registro = mysqli_fetch_assoc($resultado_tabela)) {
                            return $linha_registro["nome"];
                        }
                    }
                    return null;
                }

                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastro'])) {
                    
                    if($maxCarrinho == 0){
                        $comando_banco2 = "INSERT INTO carrinhos (id, status, notebook, mouses, retirada) VALUES (1, 'Disponível', 0, 0, 0)";
                    }else{
                        $comando_banco2 = "INSERT INTO carrinhos (status, notebook, mouses, retirada) VALUES ('Disponível', 0, 0, 0)";
                    }

                    if (mysqli_query($conexao, $comando_banco2)) {

                        // Caminho do arquivo comandos.json
                        $jsonFile = 'comandos.json';

                        // Verifique se o arquivo comandos.json existe
                        if (file_exists($jsonFile)) {
                            // Lê o conteúdo atual do arquivo JSON
                            $jsonData = file_get_contents($jsonFile);
                            
                            // Converte o JSON em um array PHP
                            $dados = json_decode($jsonData, true);

                            // Verifica se a chave 'carrinhos' existe no JSON
                            if (isset($dados['carrinhos'])) {
                                
                                // Cria um novo carrinho
                                $novoCarrinho = array(
                                    'id' => (string)(count($dados['carrinhos']) + 1), // Incrementa o ID automaticamente
                                    'status' => 'fechado',
                                    'senha' => '000000'
                                );

                                // Adiciona o novo carrinho ao array
                                $dados['carrinhos'][] = $novoCarrinho;

                                // Converte os dados de volta para JSON
                                $jsonAtualizado = json_encode($dados, JSON_PRETTY_PRINT);

                                // Grava o JSON atualizado de volta no arquivo
                                if (file_put_contents($jsonFile, $jsonAtualizado)) {
                                    echo 'Carrinho adicionado e JSON atualizado com sucesso!';
                                } else {
                                    echo 'Erro ao atualizar o arquivo JSON.';
                                }
                            } else {
                                echo 'Erro: A chave "carrinhos" não existe no JSON.';
                            }
                        } else {
                            echo 'Erro: O arquivo comandos.json não foi encontrado.';
                        }


                        header("Location: carrinhos.php");
                        exit;
                    } else {
                        echo "<p class='aviso'>Erro ao inserir registro: " . mysqli_error($conexao) . "</p>";
                    }
                } 

                // Fecha a conexão
                mysqli_close($conexao);
            
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