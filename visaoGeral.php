<?php
    //inicisnd sessão 
    session_start();

    if($_SESSION['tipo'] != 'Professor'){
        header('Location: index.php');
    }

    $conta = $_SESSION["login"];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver agendamentos - SENAI SmartLock Pro</title>
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
                
            ?>
            
        </div>
    </header>
    <div class="linha"></div>

    <main>
        <div class="container">
            <a href="home.php" class="buttonHome">
                <img src="imagens/icon_home.png" alt="" class="icone">
                <p>Voltar ao início</p>
            </a>

            <h1>Visão Geral de Agendamentos</h1>

            <a href="agendar.php"><div class='manualDoUsuario'><img src="imagens/manualV.png" alt=""><b>Agendar retirada</b></div></a>

            <div class="pesquisa">
                <form method="POST">

                    <input type="number" placeholder="Ano" name="ano" value="<?php echo date('Y'); ?>" min="1900" max="2100">

                    <!-- Definir o mês e ano atuais como padrão -->
                    <select name="mes">
                        <option value="1" <?php echo (date('m') == 1) ? 'selected' : ''; ?>>Janeiro</option>
                        <option value="2" <?php echo (date('m') == 2) ? 'selected' : ''; ?>>Fevereiro</option>
                        <option value="3" <?php echo (date('m') == 3) ? 'selected' : ''; ?>>Março</option>
                        <option value="4" <?php echo (date('m') == 4) ? 'selected' : ''; ?>>Abril</option>
                        <option value="5" <?php echo (date('m') == 5) ? 'selected' : ''; ?>>Maio</option>
                        <option value="6" <?php echo (date('m') == 6) ? 'selected' : ''; ?>>Junho</option>
                        <option value="7" <?php echo (date('m') == 7) ? 'selected' : ''; ?>>Julho</option>
                        <option value="8" <?php echo (date('m') == 8) ? 'selected' : ''; ?>>Agosto</option>
                        <option value="9" <?php echo (date('m') == 9) ? 'selected' : ''; ?>>Setembro</option>
                        <option value="10" <?php echo (date('m') == 10) ? 'selected' : ''; ?>>Outubro</option>
                        <option value="11" <?php echo (date('m') == 11) ? 'selected' : ''; ?>>Novembro</option>
                        <option value="12" <?php echo (date('m') == 12) ? 'selected' : ''; ?>>Dezembro</option>
                    </select>

                    <button type="submit" name="acao" value="executar_funcao">
                        <img class="lupa" src="imagens/lupa.png" alt="Buscar">
                    </button>
                    <button type="button" class='refresh' name="acao" onclick="location.href = 'visaoGeral.php';">
                        <img class="lupa" src="imagens/atualizar.png" alt="Atualizar">
                    </button>
                </form>
            </div>
            
            <div class='cardAgenda'>
                <?php
                    // Conectar ao banco de dados
                    $conexao = new mysqli("localhost", "root", "", "DBTrava");

                    if ($conexao->connect_error) {
                        die("Falha na conexão: " . $conexao->connect_error);
                    }

                    // Definir mês e ano para consulta
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'executar_funcao') {
                        // Verificar se o usuário enviou mês e ano no formulário
                        $mes = isset($_POST['mes']) ? $_POST['mes'] : date('m');  // Usar o mês atual como padrão
                        $ano = isset($_POST['ano']) ? $_POST['ano'] : date('Y');  // Usar o ano atual como padrão
                    } else {
                        // Carregar mês e ano atuais se não houver envio de formulário
                        $mes = date('m');
                        $ano = date('Y');
                    }

                    // Criar datas de início e fim para o filtro
                    $data_inicio = "$ano-$mes-01"; // Primeiro dia do mês
                    $data_fim = date('Y-m-d', strtotime("$data_inicio +1 month")); // Primeiro dia do próximo mês

                    // Consultar os agendamentos no intervalo de datas
                    $sql = "SELECT * FROM agendamentos WHERE data_agendada >= ? AND data_agendada < ? AND usuario_id = $conta";
                    $stmt = $conexao->prepare($sql);
                    $stmt->bind_param("ss", $data_inicio, $data_fim);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    // Exibir os resultados
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $date = $row['data_agendada']; // Exemplo: "2024-11-09"
                            $formattedDate = DateTime::createFromFormat('Y-m-d', $date)->format('d/m/Y');
                            $textDate = (string) $formattedDate;

                            $idAgenda = $row['id'];

                            echo "<div class='agendamentoVisao'><form method='post'>";
                            if($row['status'] == ''){
                                echo  "<h3>Dia " . $formattedDate . "<button onclick=\"cancelaAgenda(event, '$textDate', '$idAgenda')\"><img src='imagens/cancelar.png' alt=''></button></h3>";
                            }else{
                                echo  "<h3>Dia " . $formattedDate . "</h3>";
                            }
                            echo    "<div class='alinhamento'>";
                            echo        "<div>";
                            echo        "<p><b>Período: </b> " . $row['horario'] . "</p>";
                            echo        "<p><b>Carrinho: </b> " . $row['carrinho_id'] . "</p>";

                            if($row['status'] == ''){
                                echo    "<p><b>Status: </b><span style='color:  #e3a600'>Aguardando</span></p></div>";
                            }else if($row['status'] == 'Cancelado'){
                                echo    "<p><b>Status: </b><span style='color: red'> " . $row['status'] . "</span></p></div>";
                            }else if($row['status'] == 'Realizado'){
                                echo    "<p><b>Status: </b><span style='color: green'> " . $row['status'] . "</span></p></div>";
                            }
                            echo "</div></form></div>";
                        
                        }
                    } else {
                        echo "<p class='aviso'>Nenhum agendamento encontrado para o mês de $mes/$ano.</p>";
                    }
                ?>

            </div>  
        </div>
    </main>

    <section id='alertAgenda'></section>

    <script>
        function cancelaAgenda(event, data_cancel, id) {
            event.preventDefault();

            let alertAgenda = document.getElementById('alertAgenda');
            alertAgenda.innerHTML = data_cancel; // Modifica o conteúdo do elemento

            // Exibe o modal com o formulário de cancelamento
            alertAgenda.innerHTML = "<div id='myModal' class='fundoOpaco2'>" +
                "<div class='editTabela'>" +
                    "<span class='close' onclick='closeModal()'>&times;</span>" +
                    " <form method='post' action='visaoGeral.php'>" +
                        "<h3>Cancelamento</h3><hr>" +
                        "<p>Você deseja cancelar o agendamento para o dia " + data_cancel + "?</p>" +
                        "<input type='hidden' name='idAgenda' value='" + id + "'>" +
                        "<input class='submit deleteProf1' type='submit' name='cancelar' value='Cancelar'>" +
                    "</form>" +
                "</div>" +
            "</div>";
        }

        function closeModal() {
            document.getElementById('myModal').style.display = "none";
        }

        // Fecha o modal se o usuário clicar fora dele
        window.onclick = function(event) {
            const modal = document.getElementById('myModal');
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
    </script>

    <?php
        // Processamento do cancelamento
        if (isset($_POST['cancelar'])) {
            // Garantir que o ID do agendamento foi passado
            if (isset($_POST['idAgenda'])) {
                $idAgenda = $_POST['idAgenda'];

                // Atualiza o banco de dados com os novos valores
                $sql_update = "UPDATE agendamentos SET status = 'Cancelado' WHERE id = ?";
                $stmt = executarConsulta($conexao, $sql_update, [$idAgenda], "i");

                // Verifica se a atualização foi bem-sucedida
                if ($stmt->affected_rows > 0) {

                    $pegar_agendamentos = "
                    SELECT * 
                    FROM carrinhos 
                    WHERE status = 'Reservado'";

                    $resultado_busca = mysqli_query($conexao, $pegar_agendamentos);

                    if ($resultado_busca) {
                        $num_agendamentos = mysqli_num_rows($resultado_busca); // Verifica quantos resultados foram retornados
                        if ($num_agendamentos > 0) {
                            while ($linha = mysqli_fetch_assoc($resultado_busca)) {

                                // Agora, o carrinho deve ser atualizado, mas precisamos passar o parâmetro correto
                                $sql_update2 = "UPDATE carrinhos SET status = 'Disponível' WHERE id = ?";
                                $stmt2 = executarConsulta($conexao, $sql_update2, [1], "i");  // Passando o id do carrinho como parâmetro

                                if ($stmt2->affected_rows > 0) {
                                    // Redireciona para a página sem problemas de saída
                                    echo "<script type='text/javascript'>window.location.href = 'visaoGeral.php';</script>";
                                    exit();  // Garante que o script não continue após o redirecionamento
                                } else {
                                    echo "<p>Erro ao cancelar o agendamento. Tente novamente</p>";
                                }
                            }
                        }
                    }

                } else {
                    // Se algo deu errado, redireciona de volta com uma mensagem de erro
                    echo "<p>Erro ao cancelar o agendamento. Tente novamente</p>";
                    exit();
                }
            }
        }


        $stmt->close();
        $conexao->close();
    ?>


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