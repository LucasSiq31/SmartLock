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
    <title>Retiradas de carrinhos - SENAI SmartLock Pro</title>

    <link rel="stylesheet" href="css/styleAzul.css">
    <link rel="stylesheet" href="css/responsividadeTabela.css">
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
                        <a href="retiradas.php" class="destaque">Retiradas</a>
                    </li>

                    <li>
                        <a href="agendamentos.php">Agendamentos</a>
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

            <h2>Últimas Retiradas</h2>
            
            <div class="pesquisa">
                <form method="POST">
                    <input type="text" placeholder="Busca por Nome" name="buscaNome" value="">
                    <input type="number" placeholder="Busca por Carrinho" name="buscaCarrinho" value="">
                    <input type="date" placeholder="Busca por Data" name="buscaData" value="">
                    <select name="mostrar">
                        <option value="10">Últimas 10</option>
                        <option value="30">Últimas 30</option>
                        <option value="50">Últimas 50</option>
                    </select>
                    <button type="submit" name="acao" value="executar_funcao" class='margem-lupa'><img class="lupa" src="imagens/lupa.png" alt=""></button>
                    <button type="button" name="acao" onclick="location.href = 'retiradas.php';"><img class="lupa" src="imagens/atualizar.png" alt=""></button>
                </form>
            </div>
            
            <table id="tabela">
                <thead>
                    <tr class="linha1">
                        <th>Professor</th>
                        <th>Carrinho</th>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Notebooks</th>
                        <th>Mouses</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Conectar ao banco de dados
                        $conectar_banco = mysqli_connect("localhost", "root", "", "DBTrava");

                        // Verificar a conexão
                        if (!$conectar_banco) {
                            die("Falha na conexão: " . mysqli_connect_error());
                        }

                        // Receber os dados do formulário
                        $nome = isset($_POST['buscaNome']) ? mysqli_real_escape_string($conectar_banco, $_POST['buscaNome']) : '';
                        $carrinho = isset($_POST['buscaCarrinho']) ? mysqli_real_escape_string($conectar_banco, $_POST['buscaCarrinho']) : '';
                        $data = isset($_POST['buscaData']) ? mysqli_real_escape_string($conectar_banco, $_POST['buscaData']) : '';
                        $mostrar = isset($_POST['mostrar']) ? intval($_POST['mostrar']) : 10;

                        // Criar a consulta SQL com base nos filtros
                        $comando_banco = "SELECT r.idProfessor, r.idCarrinho, 
                            DATE_FORMAT(r.dia, '%d/%m') AS data_formatada, 
                            TIME_FORMAT(r.hora, '%H:%i') AS hora_formatada, 
                            r.notebook, r.mouse, r.observacao, r.situacao, 
                            p.nome AS professor_nome 
                            FROM retiradas r
                            JOIN usuarios p ON r.idProfessor = p.id
                            WHERE 1=1";

                        // Adicionar filtros à consulta
                        if ($nome) {
                            $comando_banco .= " AND p.nome LIKE '%$nome%'";
                        }
                        if ($carrinho) {
                            $comando_banco .= " AND r.idCarrinho = '$carrinho'";
                        }
                        if ($data) {
                            $comando_banco .= " AND DATE(r.dia) = '$data'";
                        }

                        // Ordenar e limitar os resultados
                        $comando_banco .= " ORDER BY r.dia DESC, r.hora DESC LIMIT $mostrar";

                        // Executar a consulta
                        $retiradas = mysqli_query($conectar_banco, $comando_banco);

                        if (!$retiradas) {
                            echo "Erro na consulta SQL: " . mysqli_error($conectar_banco);
                        } else {
                            // Preencher a tabela com os dados das retiradas
                            while ($linha = mysqli_fetch_assoc($retiradas)) {
                                echo "<tr onclick='showObservacao(\"" . htmlspecialchars($linha['observacao']) . "\")'>";
                                echo "<td class='nomeCompleto'>" . htmlspecialchars($linha['professor_nome']) . "</td>";
                                echo "<td data-label='Carrinho'>" . htmlspecialchars($linha['idCarrinho']) . "</td>";
                                echo "<td data-label='Data'>" . htmlspecialchars($linha['data_formatada']) . "</td>";
                                echo "<td data-label='Hora'>" . htmlspecialchars($linha['hora_formatada']) . "</td>";

                                if($linha['notebook'] == -1){
                                    echo "<td data-label='Notebooks'>Nulo</td>";
                                }else{
                                    echo "<td data-label='Notebooks'>" . htmlspecialchars($linha['notebook']) . "</td>";
                                }

                                if($linha['mouse'] == -1){
                                    echo "<td data-label='Mouses'>Nulo</td>";
                                }else{
                                    echo "<td data-label='Mouses'>" . htmlspecialchars($linha['mouse']) . "</td>";
                                }
                                
                                if($linha['situacao'] == 'Emprestado'){
                                    echo "<td data-label='Status'><span style='color: red'>" . htmlspecialchars($linha['situacao']) . "</span></td>";
                                } else if($linha['situacao'] == 'Devolvido'){
                                    echo "<td data-label='Status'><span style='color: #27cc1f'>" . htmlspecialchars($linha['situacao']) . "</span></td>";
                                } else if($linha['situacao'] == 'Aberto'){
                                    echo "<td data-label='Status'><span style='color: #e3a600'>" . htmlspecialchars($linha['situacao']) . "</span></td>";
                                }
                                echo "</tr>";
                            }
                        }

                        // Fechar a conexão com o banco de dados
                        mysqli_close($conectar_banco);
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal -->
    <div id="myModal" class="fundoOpaco">
        <div class="editTabela">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Observação</h3><hr>
            <p id="observacaoText"></p>
        </div>
    </div>

    <script>
        function showObservacao(observacao) {
            if(observacao == ""){
                document.getElementById('observacaoText').textContent = "Não há observações registradas pelo usuário!";
            }else{
                document.getElementById('observacaoText').textContent = observacao;
            }
            document.getElementById('myModal').style.display = "flex";
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