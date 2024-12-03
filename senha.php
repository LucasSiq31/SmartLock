<?php
    // Iniciar sessão
    session_start();


    // Verificar se o usuário é um professor
    if ($_SESSION['tipo'] != 'Professor') {
         header('Location: index.php');
         exit();
    }

    $conta = $_SESSION["login"];

    $numCarrinho = '';
    // Conectar ao banco de dados
    $conectar_banco2 = mysqli_connect("localhost", "root", "", "DBTrava");


    // Verificar a conexão
    if (!$conectar_banco2) {
        die("Falha na conexão: " . mysqli_connect_error());
    }


    date_default_timezone_set('America/Sao_Paulo');

    $pegar_agendamentos = "
                SELECT * 
                FROM agendamentos 
                WHERE usuario_id = '$conta'";

    $resultado_busca = mysqli_query($conectar_banco2, $pegar_agendamentos);

    //Variável que verifica se há agendamento
    $agenda = 0;
    $num_agenda = 0;

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
                    $agenda = $linha['carrinho_id'];

                    $num_agenda = $linha['id'];
                }
            }
        }
    }


    $result = mysqli_query($conectar_banco2, "SELECT * FROM carrinhos WHERE status = 'Disponível' LIMIT 1");

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        if($agenda == 0){
            $carrinho = $data['id'];
            $carrinho_aberto = $carrinho;
            $numCarrinho = $data['id'];
        }
    }else if($agenda != 0){
        $carrinho = $agenda;
        $carrinho_aberto = $carrinho;
        $numCarrinho = $agenda;

    }else{
        $carrinho = 0;
    }
        

    // Função para atualizar o arquivo JSON de comandos
    function atualizarJson($carrinhoN, $status, $senhaEnviada) {
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
                $carrinho['senha'] = $senhaEnviada;
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

        // Criar e preparar a consulta SQL
        $comando_banco = "INSERT INTO retiradas (idProfessor, idCarrinho, dia, hora, notebook, mouse, observacao, situacao) VALUES ('" . $_SESSION["login"] . "', '$carrinho', '" . date("Y-m-d") . "', '" . date("H:i") . "', -1, -1, '', 'Aberto')";


        // Executar a consulta e redirecionar em caso de sucesso
        if (mysqli_query($conectar_banco2, $comando_banco)) {
            // Atualizar tabela carrinhos
            $comando_banco2 = "UPDATE carrinhos SET status = 'Aberto', retirada = '".$_SESSION['login']."' WHERE id = '$carrinho'";

            if (mysqli_query($conectar_banco2, $comando_banco2)) {
                // Mudar página inicial
                $comando_banco3 = "UPDATE usuarios SET retirada = 2 WHERE id = '".$_SESSION['login']."'";

                // Executa as consultas SQL
                if (mysqli_query($conectar_banco2, $comando_banco3)) {

                    if($num_agenda != 0){
                        $comando_banco4 = "UPDATE agendamentos SET status = 'Realizado' WHERE id = '".$num_agenda."'";

                        // Executa as consultas SQL
                        if (mysqli_query($conectar_banco2, $comando_banco4)) {

                        }
                    }
                        

                    $_SESSION['comando'] = "aberto";
                    atualizarJson($carrinho_aberto, $_SESSION['comando'], $senhaGerada);

                    header("Location: formulario.php");
                    exit();
                }
            }

        } else {
            echo "<p class='aviso'>Erro ao enviar o formulário: " . mysqli_error($conectar_banco2) . "</p>";
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
    <title>Liberação do Carrinho- SENAI SmartLock Pro</title>


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
           
            // Exibir dados da tabela
            $conexao = conectarBanco();
            $conta = $_SESSION["login"];
            $comando_banco = "SELECT * FROM usuarios WHERE id = '$conta'";
            $resultado_tabela = mysqli_query($conexao, $comando_banco);
           
            if ($resultado_tabela) {
                while ($linha_registro = mysqli_fetch_assoc($resultado_tabela)) {
                    if(empty(trim($linha_registro['imagem']))){
                        echo "<a href='perfilProf.php'><img src='imagens/icon_perfil.png' alt='' class='icon'></a>";
                    } else {
                        echo "<a href='perfilProf.php'><img src='".$linha_registro['imagem']."' alt='' class='icon'></a>";
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
    <div class="container senha">
        <a href="home.php" class="buttonHome">
            <img src="imagens/icon_home.png" alt="" class="icone">
            <p>Voltar ao início</p>
        </a>
        
        <?php
            if($carrinho == 0){
                echo "<h2>Não há carrinhos disponíveis! Tente novamente mais tarde.</h2>";
            }else{
                echo "<h2>O carrinho disponível para retirada é o $carrinho</h2>";
            }

            if($agenda != 0){
                echo '<b>Essa retirada foi agendada!</b><br>';
            }
        ?>
       
        <p>Ao confirmar a retirada do carrinho, você deve estar ciente:</p>
        <ol>
            <li>Todo o processo de retirada do carrinho, é salvo.</li>
            <li>Seu nome vai estar registrado na retirada do carrinho.</li>
            <li>A responsabilidade do carrinho é sua no momento da retirada até o momento da devolução.</li>
        </ol>
        <form method='post'>
            <?php
                if($carrinho != 0){
                    echo "<button>Confirmar</button>";
                }
            ?>
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


<script src="script/temporizador.js"></script>
</body>
</html>
