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
    <title>Usuários Cadastrados - SENAI SmartLock Pro</title>

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
                    if ($tipos && !empty($parametros)) {
                        $stmt->bind_param($tipos, ...$parametros);
                    }
                    $stmt->execute();
                    return $stmt;
                }
                
                // Exibir dados da tabela
                $conexao = conectarBanco();
                
                $conta = $_SESSION["login"]; // Certifique-se de que $_SESSION está iniciado
                
                // Prepare a consulta
                $sql = "SELECT * FROM usuarios WHERE id = ?";
                $parametros = [$conta];
                $tipos = "s"; // Se 'id' for um string; use "i" se for um inteiro
                
                $stmt = executarConsulta($conexao, $sql, $parametros, $tipos);
                
                if ($stmt) {
                    $resultado_tabela = $stmt->get_result();
                    while ($linha_registro = $resultado_tabela->fetch_assoc()) {
                        if (empty(trim($linha_registro['imagem']))) {
                            echo "<a href='perfilAdm.php'><img src='imagens/icon_perfil.png' alt='' class='icon'></a>";
                        } else {
                            echo "<a href='perfilAdm.php'><img src='".htmlspecialchars($linha_registro['imagem'])."' alt='' class='icon'></a>";
                        }
                    }
                    $stmt->close(); // Fecha o statement
                }
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
                        <a href="carrinhos.php">Carrinhos</a>
                    </li>

                    <li>
                        <a href="usuarios.php" class="destaque">Usuários</a>
                    </li>
                </ul>
            </nav>
            <hr>

            <a href="cadastro.php" class="cadastroNovo">Cadastrar novo usuário</a>

            <h2>Usuários Cadastrados</h2>
            
            <div class="pesquisa">
                <form method="POST">
                <input type="text" placeholder="Busca pelo Nome" name="buscaTermo" value="<?php echo isset($_POST['buscaTermo']) ? htmlspecialchars($_POST['buscaTermo']) : ''; ?>">
                    <select name="tipo">
                        <option value="" <?php echo !isset($_POST['tipo']) || $_POST['tipo'] === '' ? 'selected' : ''; ?>>Tipo de Conta</option>
                        <option value="Administrador" <?php echo isset($_POST['tipo']) && $_POST['tipo'] === 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="Professor" <?php echo isset($_POST['tipo']) && $_POST['tipo'] === 'Professor' ? 'selected' : ''; ?>>Professor</option>
                    </select>
                    <button type="submit" name="acao" value="executar_funcao" class='margem-lupa'><img class="lupa" src="imagens/lupa.png" alt=""></button>
                    <button type="button" name="acao" onclick="location.href = 'usuarios.php';"><img class="lupa" src="imagens/atualizar.png" alt=""></button>
                </form>
            </div>

            <table id="tabela">
                <thead>
                    <tr class="linha1">
                        <th>Nome Completo</th>
                        <th>E-mail</th>
                        <th>Tipo de conta</th>
                        <th>Acesso</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php


                        // Conectar ao banco de dados
                        $conexao = conectarBanco();

                        // Verificar se o formulário foi enviado
                        if (isset($_POST['acao']) && $_POST['acao'] === 'executar_funcao') {
                            // Receber os parâmetros de busca do formulário
                            $buscaTermo = isset($_POST['buscaTermo']) ? $_POST['buscaTermo'] : '';
                            $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';

                            // Construir a consulta SQL dinamicamente com base nos filtros
                            $sql = "SELECT * FROM usuarios WHERE 1=1"; // `1=1` é um truque para simplificar a adição de condições
                            $parametros = [];
                            $tipos = '';

                            if (!empty($buscaTermo)) {
                                $sql .= " AND nome LIKE ?";
                                $parametros[] = "%$buscaTermo%";
                                $tipos .= 's';
                            }

                            if (!empty($tipo)) {
                                $sql .= " AND tipo_conta = ?";
                                $parametros[] = $tipo;
                                $tipos .= 's';
                            }
                        } else {
                            // Se o formulário não foi enviado, mostrar todos os usuários
                            $sql = "SELECT * FROM usuarios";
                            $parametros = [];
                            $tipos = '';
                        }

                        $stmt = executarConsulta($conexao, $sql, $parametros, $tipos);
                        $resultado_tabela = $stmt->get_result();

                        // Exibir dados da tabela
                        if ($resultado_tabela) {
                            while ($linha_registro = $resultado_tabela->fetch_assoc()) {
                                echo "<tr>";
                                if (empty(trim($linha_registro['imagem']))) {
                                    echo "<td class='nomeCompleto'><div><img src='imagens/icon_perfil.png' alt='' class='iconTabela'>" . htmlspecialchars($linha_registro['nome']) . "</div></td>";
                                } else {
                                    echo "<td class='nomeCompleto'><div><img class='fotoPerfilTabela'src='".htmlspecialchars($linha_registro['imagem'])."' alt=''>" . htmlspecialchars($linha_registro['nome']) . "</div></td>";
                                }
                                echo "<td data-label='E-mail'>" . htmlspecialchars($linha_registro['email']) . "</td>";
                                echo "<td data-label='Conta'>" . htmlspecialchars($linha_registro['tipo_conta']) . "</td>";
                                if($linha_registro['bloqueio'] == 0){
                                    echo "<td data-label='Acesso'><label class='switch'><input type='checkbox' checked disabled><span class='slider round'></span></label></td>";
                                }else if($linha_registro['bloqueio'] == 1){
                                    echo "<td data-label='Acesso'><label class='switch'><input type='checkbox' disabled><span class='slider round'></span></label></td>";
                                }
                                echo "<td data-label='Ações'><div class='funcoes'><a href='?funcao=1&id=" . urlencode($linha_registro['id']) . "' class='editar'><img src='imagens/icon_lapis.png' alt=''></a> <a href='?funcao=2&id=" . urlencode($linha_registro['id']) . "' class='deletar'><img src='imagens/icon_lixeira.png' alt=''></a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>Nenhum usuário encontrado.</td></tr>";
                        }

                        // Fecha a conexão
                        mysqli_close($conexao);

                    ?>
                </tbody>
            </table>
            <?php

                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);

                // Processar ações
                if (isset($_GET['funcao']) && isset($_GET['id'])) {
                    $funcao = (int) $_GET['funcao'];
                    $id = $_GET['id'];
                    
                    switch ($funcao) {
                        case 1:
                            editar($id);
                            break;
                        case 2:
                            apagar($id);
                            break;
                    }
                }

                function editar($id) {
                    $conexao = conectarBanco();

                    $sql = "SELECT * FROM usuarios WHERE id = ?";
                    $stmt = executarConsulta($conexao, $sql, [$id], "s");
                    $resultado_tabela = $stmt->get_result();

                    if ($linha_registro = $resultado_tabela->fetch_assoc()) {
                        echo "<div class='fundoOpaco2'>";
                        echo "<div class='editTabela'>";
                        echo "<form method='post'>";
                        echo "<h1>Editar:</h1>";
                        echo "<h3>Nome:</h3>";
                        echo "<input type='text' autocomplete='off' name='campo1' value='" . htmlspecialchars($linha_registro['nome']) . "'>";
                        echo "<h3>E-mail:</h3>";
                        echo "<input type='email' autocomplete='off' name='campo2' value='" . htmlspecialchars($linha_registro['email']) . "'>";
                        echo "<h3>Acesso:</h3>";
                        if($linha_registro['bloqueio'] == 0){
                            echo "<td><label class='switch'><input type='checkbox' checked id='meuCheckbox' name='meuCheckbox' value='0'><span class='slider round'></span></label></td>";
                        }else if($linha_registro['bloqueio'] == 1){
                            echo "<td><label class='switch'><input type='checkbox' id='meuCheckbox' name='meuCheckbox' value='0'><span class='slider round'></span></label></td>";
                        }
                        echo "<input class='submit' type='submit' name='update' value='Editar'>";
                        echo "</form>";
                        echo "</div></div>";

                        if (isset($_POST['update'])) {
                            $campo1 = $_POST['campo1'];
                            $campo2 = $_POST['campo2'];
                    
                            // Captura o valor do checkbox
                            // Se o checkbox estiver marcado, bloqueio será 0, caso contrário será 1
                            $campo4 = isset($_POST['meuCheckbox']) ? '0' : '1';
                    
                            // Atualiza o banco de dados com os novos valores
                            $sql_update = "UPDATE usuarios SET nome = ?, email = ?, bloqueio = ? WHERE id = ?";
                            $stmt = executarConsulta($conexao, $sql_update, [$campo1, $campo2, $campo4, $id], "ssss");
                    
                            if ($stmt->affected_rows > 0) {
                                // Se a atualização foi bem-sucedida, redireciona para outra página ou exibe uma mensagem
                                echo "<p class='sucesso'>Registro atualizado com sucesso!</p>";
                                // Para redirecionar sem problemas de saída:
                                echo "<script type='text/javascript'>window.location.href = 'usuarios.php';</script>";
                                exit();
                            } else {
                                echo "<script type='text/javascript'>window.location.href = 'usuarios.php';</script>";
                            }
                        }
                    }
                    mysqli_close($conexao);
                }

                function apagar($id) {
                    $conexao = conectarBanco();

                    $sql = "SELECT * FROM usuarios WHERE id = ?";
                    $stmt = executarConsulta($conexao, $sql, [$id], "s");
                    $resultado_tabela = $stmt->get_result();

                    if ($linha_registro = $resultado_tabela->fetch_assoc()) {
                        //echo "<script>disableScroll();</script>";

                        echo "<div class='fundoOpaco2'>";
                        echo "<div class='editTabela'>";
                        echo "<form method='post'>";
                        echo "<h2>Você deseja apagar o cadastro de " . htmlspecialchars($linha_registro['nome'])."?</h2>";
                        echo "<input class='submit deleteProf1' type='submit' name='no' value='Cancelar'>";
                        echo "<input class='submit deleteProf2' type='submit' name='delete' value='Apagar'>";
                        echo "</form>";
                        echo "</div></div>";

                        if (isset($_POST['delete'])) {
                            $sql_delete = "DELETE FROM usuarios WHERE id = ?";
                            $stmt = executarConsulta($conexao, $sql_delete, [$linha_registro['id']], "s");

                            if ($stmt->affected_rows > 0) {
                                echo '<script>window.location.href="usuarios.php";</script>';
                                exit();
                            } else {
                                echo "<p class='aviso'>Erro ao excluir registro!</p>";
                            }
                        }

                        if (isset($_POST['no'])) {
                            echo '<script>window.location.href="usuarios.php";</script>';
                            exit();
                        }
                    }
                    mysqli_close($conexao);
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
                <p>Feito por: Lucas Siqueira, Maria Fernanda, Stela Amorim e Ulisses Almeida</p>
            </div>
        </div>
    </footer>
</body>
</html> 