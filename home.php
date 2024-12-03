<?php
    //iniciando sessão 
    session_start();

    if($_SESSION['tipo'] != 'Professor'){
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
    <title>Tela Inicial - SENAI SmartLock Pro</title>

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

            <?php
                // A consulta para pegar os dados do usuário
                $comando_banco = "SELECT * FROM usuarios WHERE id = '$conta'"; // Corrigir para usar a variável $conta
                $resultado_tabela = mysqli_query($conexao, $comando_banco);

                if ($resultado_tabela) {
                    while ($linha_registro = mysqli_fetch_assoc($resultado_tabela)) {

                        // Verificar a condição de 'retirada' antes de exibir qualquer mensagem
                        if($linha_registro['retirada'] == 1){
                            // Redireciona para a página de aviso se a retirada for 1
                            header('Location: aviso.php');
                            exit; // Garantir que o script pare de executar após o redirecionamento
                        }

                        if($linha_registro['retirada'] == 2){
                            // Redireciona para a página de formulário se a retirada for 2
                            header('Location: formulario.php');
                            exit; // Garantir que o script pare de executar após o redirecionamento
                        }

                        // Exibe a mensagem de boas-vindas
                        echo "<h1>Bem Vindo ao SENAI SmartLock Pro, " . htmlspecialchars($linha_registro['nome']) . "!</h1>";
                    }
                }

                // Consulta de agendamentos - geral
                $pegar_agendamentos = "
                SELECT * 
                FROM agendamentos 
                ";

                $resultado_busca = mysqli_query($conexao, $pegar_agendamentos);

                if ($resultado_busca) {
                    $num_agendamentos = mysqli_num_rows($resultado_busca); // Verifica quantos resultados foram retornados
                    if ($num_agendamentos > 0) {
                        while ($linha = mysqli_fetch_assoc($resultado_busca)) {
                            
                            $data = $linha['data_agendada'];
                            $horario = $linha['horario']; // O horário que está armazenado no banco de dados (pode ser uma string como 'Manhã', 'Tarde', 'Noite')

                            // Hora atual
                            $agora = new DateTime(); // Hora e data atuais

                            // Define os períodos do dia como objetos DateTime
                            $manha_inicio = new DateTime($data . ' 07:30'); // 07:30
                            $manha_fim = new DateTime($data . ' 11:30');    // 11:30

                            $tarde_inicio = new DateTime($data . ' 13:00');  // 13:00
                            $tarde_fim = new DateTime($data . ' 17:00');    // 17:00

                            $noite_inicio = new DateTime($data . ' 18:00');  // 18:00
                            $noite_fim = new DateTime($data . ' 22:00');    // 22:00

                            // Verifica o período atual
                            if ($agora >= $manha_inicio && $agora < $manha_fim) {
                                $periodo = 'Manhã';
                            } elseif ($agora >= $tarde_inicio && $agora < $tarde_fim) {
                                $periodo = 'Tarde';
                            } elseif ($agora >= $noite_inicio && $agora <= $noite_fim) {
                                $periodo = 'Noite';
                            } else {
                                $periodo = 'Fora de horário';
                            }


                            // Verifica se o período atual é igual ao período do agendamento
                            if ($periodo == $horario && $linha['status'] == '') {

                                
                                $atualiza_Status = "UPDATE carrinhos SET status = 'Reservado' WHERE id = '".$linha['carrinho_id']."'";

                                // Executa as consultas SQL
                                if (mysqli_query($conexao, $atualiza_Status)) {

                                }
                            }
                        }
                    }
                }

                // Consulta de agendamentos - usuario
                $pegar_agendamentos = "
                SELECT * 
                FROM agendamentos 
                WHERE usuario_id = '$conta'";

                $resultado_busca = mysqli_query($conexao, $pegar_agendamentos);

                if ($resultado_busca) {
                    $num_agendamentos = mysqli_num_rows($resultado_busca); // Verifica quantos resultados foram retornados
                    if ($num_agendamentos > 0) {
                        while ($linha = mysqli_fetch_assoc($resultado_busca)) {
                            $data = $linha['data_agendada'];
                            $horario = $linha['horario']; // O horário que está armazenado no banco de dados (pode ser uma string como 'Manhã', 'Tarde', 'Noite')

                            // Hora atual
                            $agora = new DateTime(); // Hora e data atuais

                            // Define os períodos do dia como objetos DateTime
                            $manha_inicio = new DateTime($data . ' 07:30'); // 07:30
                            $manha_fim = new DateTime($data . ' 11:30');    // 11:30
                            $tarde_inicio = new DateTime($data . ' 13:00');  // 13:00
                            $tarde_fim = new DateTime($data . ' 17:00');    // 17:00
                            $noite_inicio = new DateTime($data . ' 18:00');  // 18:00
                            $noite_fim = new DateTime($data . ' 22:00');    // 22:00

                            // Verifica o período atual
                            if ($agora >= $manha_inicio && $agora < $manha_fim) {
                                $periodo = 'Manhã';
                            } elseif ($agora >= $tarde_inicio && $agora < $tarde_fim) {
                                $periodo = 'Tarde';
                            } elseif ($agora >= $noite_inicio && $agora <= $noite_fim) {
                                $periodo = 'Noite';
                            } else {
                                $periodo = 'Fora de horário';
                            }

                            // Verifica se o período atual é igual ao período do agendamento
                            if ($periodo == $horario && $linha['status'] == '') {
                                echo '<div class="agendado">';
                                echo   '<p>Você tem um agendamento para este período!</p>';
                                echo   '<a href="senha.php">Retirar agora</a>';
                                echo '</div>';
                            }
                        }
                    }
                }

                // Fecha a conexão
                mysqli_close($conexao);

            ?>

            <p>Você já está autenticado e pronto para solicitar o desbloqueio do carrinho de notebooks.</p>
            <b>Como liberar:</b>
            <ol>
                <li><b>Acesso:</b> Acesse a página destinada ao desbloqueio do carrinho.</li>
                <li><b>Desbloqueio:</b> O carrinho selecionado pelo sistema será aberto ao pressionar o botão correspondente.</li>
                <li><b>Retirada:</b> Preencha o formulário de retirada para registrar no sistema a operação realizada.</li>
                <li><b>Abertura e Fechamento:</b> Após preencher o formulário de retirada, você terá a opção de abrir e fechar o carrinho conforme necessário.</li>
                <li><b>Devolução:</b> Ao devolver o carrinho, lembre-se de preencher o formulário de devolução para finalizar o processo.</li>
                <li><b>Formulários:</b> O preenchimento dos formulários é obrigatório para a validação das operações.</li>

            </ol>
            <p><b>Dúvidas ou Suporte:</b> Para qualquer assistência, entre em contato com a coordenação da escola.</p>
            <p>Agradecemos por utilizar este recurso de forma eficiente e responsável!</p>

            <div class="homeProf">
                <a href="senha.php" class="img_notebook"><div class="navegacao"><img class='icone' src="imagens/notebook.png" alt=""><b>Retirar Carrinho</b></div></a>
                <a href="retiradasProf.php" class="img_tabela"><div class="navegacao"><img class='icone' src="imagens/formulario.png" alt=""><b>Últimas retiradas</b></div></a>
            </div>
            <div class="homeProf">
                <a href="agendar.php" class="img_calendario"><div class="navegacao"><img class='icone' src="imagens/calendario.png" alt=""><b>Agendar</b></div></a>
                <a href="https://heyzine.com/flip-book/44d388fcf2.html" target="_blank" class="img_manual"><div class="navegacao"><img class='icone' src="imagens/manual.png" alt=""><b>Manual do Usuário</b></div></a>
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