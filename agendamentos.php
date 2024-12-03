<?php
    //inicisnd sessão 
    session_start();

    if($_SESSION['tipo'] != 'Administrador'){
        header('Location: index.php');
    }

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - SENAI SmartLock Pro</title>

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
                        <a href="agendamentos.php" class="destaque">Agendamentos</a>
                    </li>

                    <li>
                        <a href="carrinhos.php">Carrinhos</a>
                    </li>

                    <li>
                        <a href="usuarios.php">Usuários</a>
                    </li>
                </ul>
            </nav>
            <hr>

            <h1>Agendamentos</h1>

            <p>Clique no dia para visualizar os agendamentos!</p>

            <div class="agendamento">
                <div class="tabelaCalendario">
                    <div id="calendar"></div>
                </div>

                <div class="horariosMarcados">
                    <h3 id='diaMesAno'>Selecione uma data</h3>
                    <hr>

                    <div id="horariosMarcados2">

                    </div>
                    
                    <input type="date" name="campoData" id="campoData2" style='display: none'>
                </div>
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
                <p>Feito por: Lucas Siqueira, Maria Fernanda, Ulisses Almeida e Stela Amorim</p>
            </div>
        </div>
    </footer>

    <script src="script/calendario.js"></script>

    <?php
        // Inicializa o array para os agendamentos
        // Inicializa o array para os agendamentos
        $linha = [];

        // Verifica se as variáveis foram passadas na URL
        if (!isset($_GET['dia']) || !isset($_GET['mes']) || !isset($_GET['ano'])) {
            exit;
        }

        $dia = intval($_GET['dia']);
        $mes = intval($_GET['mes']);
        $ano = intval($_GET['ano']);

        // Formata a data de dd/mm/yyyy para yyyy-mm-dd
        $data_agendada = DateTime::createFromFormat('d/m/Y', sprintf('%02d/%02d/%04d', $dia, $mes, $ano));
        if ($data_agendada === false) {
            exit;
        }

        $data_agendada_sql = $data_agendada->format('Y-m-d'); // Formato para SQL

        // Conectar ao banco de dados
        $conexao = conectarBanco();

        // Consulta para recuperar os agendamentos no dia
        $comando_banco = "
            SELECT 
                r.carrinho_id, 
                r.usuario_id, 
                r.horario, 
                r.status,
                usuarios.nome AS Nome
            FROM 
                agendamentos r 
            JOIN 
                usuarios ON r.usuario_id = usuarios.id 
            WHERE 
                r.data_agendada = '$data_agendada_sql'
            ORDER BY 
                r.horario ASC;
        ";

        $resultado_tabela = mysqli_query($conexao, $comando_banco);

        if (!$resultado_tabela) {
            echo "<p>Erro na consulta: " . mysqli_error($conexao) . "</p>";
            exit;
        }

        // Inicializa o array de horários já agendados
        $horarios_desabilitados = [];

        // Consulta para verificar se já existe um agendamento para o dia
        $comando_verificacao = "SELECT horario FROM agendamentos WHERE data_agendada = '$data_agendada_sql' AND status != 'Cancelado'";
        $resultado = mysqli_query($conexao, $comando_verificacao);

        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                // Armazena os horários já ocupados
                $horarios_desabilitados[] = $row['horario'];
            }
        }

        // Recupera os dados dos agendamentos
        while ($linha_registro = mysqli_fetch_assoc($resultado_tabela)) {
            $linha[] = [
                "horario" => $linha_registro["horario"],
                "usuario_id" => $linha_registro["usuario_id"],
                "usuario" => $linha_registro["Nome"],
                "status" => $linha_registro["status"],
            ];
        }

        // Fecha a conexão
        mysqli_close($conexao);

        // Gera um JSON a partir do array
        $jsonDados = json_encode($linha) ?: '[]';

        // Exibe o script com as variáveis PHP para o JavaScript
        echo "<script>
                // Quando o DOM estiver pronto
                document.addEventListener('DOMContentLoaded', function() {

                    // Verifica se a variável $dia existe e é válida
                    if (typeof $dia === 'number' && !isNaN($dia)) {
                        var div = document.getElementById('diaMesAno');
                        div.innerHTML = '$dia/$mes/$ano';
                        var dia = document.getElementById('dia$dia-$mes');
                        if (dia) {
                            dia.style.backgroundColor = '#adffb4'; // Marca o dia selecionado
                        }

                        // Chama a função de agendamento
                        janelaAgendamento($dia, $mes, $ano);

                        // Exibe o botão para agendar
                        var botao = document.getElementById('btnAgendar');
                        botao.style.display = 'block';
                    } else {
                        // Se os parâmetros da data não forem válidos, oculta o botão
                        var botao = document.getElementById('btnAgendar');
                        botao.style.display = 'none';
                    }
                }
            );
        </script>";
    ?> 
    
    <script>
        // Verifique se o PHP está gerando um JSON válido
        console.log("JSON gerado:", <?php echo $jsonDados; ?>);

        const agendamentos = <?php echo $jsonDados; ?>;
        const titulo = document.getElementById('horariosMarcados2');

        // Limpa a div antes de exibir os dados
        titulo.innerHTML = "";

        // Exibir agendamentos
        if (agendamentos.length === 0) {
            titulo.innerHTML = "<p>Não há agendamentos para este dia!</p>"; // Mantém vazio se não houver agendamentos
        } else {
            agendamentos.forEach(agendamento => {
                if(agendamento.status != 'Cancelado'){
                    titulo.innerHTML += "<p>" + agendamento.horario + " - " + agendamento.usuario +  "</p>";
                }
            });
        }

       
    </script>

</body>
</html>