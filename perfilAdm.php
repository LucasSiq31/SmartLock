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
    <title>Perfil - SENAI SmartLock Pro</title>
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
        <div>
            <a href="retiradas.php" ><img src="imagens/icon_home.png" alt="" class="home"></a>
        </div>
    </header>
    <div class="linha"></div>
 
    <main>
        <div class="container">

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

                        echo "<script>document.title = '". htmlspecialchars($linha_registro['nome']) ." - SENAI SmartLock Pro'</script>";

                        echo "<div class='perfil'>";
                        if($linha_registro['imagem'] == ''){
                            echo "<div class='fundoPerfil' onclick='trocarImg()'><img src='imagens/icon_perfil.png' alt='' class='fotoPerfil'> <div class='hoverPerfil'><img src='imagens/icon_lapis.png' alt=''></div> </div>";
                        }else{
                            echo "<div class='fundoPerfil' onclick='trocarImg()'><img src='".$linha_registro['imagem']."' alt='' class='fotoPerfil'> <div class='hoverPerfil'><img src='imagens/icon_lapis.png' alt=''></div> </div>";
                        }
                        echo "<div>";
                        echo "<p class='nomeComp'>". htmlspecialchars($linha_registro['nome']) ."</p>";
                        echo "<p class='email'>". htmlspecialchars($linha_registro['email']) ."</p>";
                        echo "</div>";
                        echo "</div>";

                        echo "<div class='espBotao'>";
                        echo "<button onclick='window.location.replace(`index.php`)'><img class='icones' src='imagens/icon_sair.png' alt=''>Sair</button>";
                        echo "<button onclick='window.location.replace(`trocarSenha.php`)'><img class='icones' src='imagens/icon_cadeado.png' alt=''>Trocar Senha</button>";
                        echo "</div>";

                        // Formulário para edição
                        echo "<div><form method='post'>";
                        echo   "<h3>Dados Pessoais:</h3>";
                        echo   "<div class='dadosPessoais'>";
                        echo     "<div>";
                        echo       "<label for='nome'><b>Nome:</b></label>";
                        echo       "<input type='text' id='nome' disabled='true' name='nome' value='". htmlspecialchars($linha_registro['nome']) ."'>";
                        echo     "</div>";
                        echo     "<div>";
                        echo       "<label for='email'><b>Email:</b></label>";
                        echo       "<input type='text' id='email' disabled='true' name='email' value='". htmlspecialchars($linha_registro['email']) ."'>";
                        echo     "</div>";
                        echo   "</div>";

                        // Botão Editar e Salvar
                        echo   "<div class='espBotao' id='buttonEditar'>";
                        echo     "<button type='button' class='editInfo' onclick='editar()'><img class='icones' src='imagens/icon_lapis.png' alt=''>Editar Informações</button>";
                        echo   "</div>";

                        echo   "<div class='espBotao' id='buttonSalvar' style='display:none;'>";
                        echo     "<button type='submit' class='editInfo' name='dados'><img class='icones' src='imagens/icon_salvar.png' alt=''>Salvar Informações</button>";
                        echo   "</div>";

                        echo "</form></div>";

                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            if (isset($_POST['dados'])) {
                                if (isset($_POST['nome']) && isset($_POST['email'])) {
                                    $nome = $_POST['nome'];
                                    $email = $_POST['email'];
                    
                                    // Atualiza os dados do usuário no banco de dados
                                    $sql_update = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
                                    $stmt = executarConsulta($conexao, $sql_update, [$nome, $email, $conta], "sss");
                    
                                    if ($stmt->affected_rows > 0) {
                                        echo "<p class='sucesso'>Informações atualizadas com sucesso!</p>";
                                        header("Location: perfilAdm.php");
                                    } else {
                                        echo "<p class='aviso'>Nenhuma alteração foi feita.</p>";
                                    }
                                } else {
                                    echo "<p class='aviso'>Por favor, preencha todos os campos.</p>";
                                }
                            }
                        }   
                        
                    }
                } else {
                    echo "<p>Erro ao recuperar dados.</p>";
                }
                
            ?>

            <a href="https://heyzine.com/flip-book/44d388fcf2.html" target="_blank"><div class='manualDoUsuario'><img src="imagens/manualA.png" alt=""><b>Manual do Usuário</b></div></a>
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

    <script>
        function editar() {

            // Habilitar os campos
            document.getElementById('nome').disabled = false;
            document.getElementById('email').disabled = false;


            // Exibir o botão "Salvar" e ocultar o botão "Editar"
            document.getElementById('buttonEditar').style.display = 'none';
            document.getElementById('buttonSalvar').style.display = 'block';
        }
    </script>

    <div class="fundoOpaco" id='modal'>
        <div class="editTabela">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h1>Trocar Imagem</h1>
            <hr>
            <p>Clique na imagem para trocar!</p>
            <form method='post' enctype='multipart/form-data'>
                <label for="image-field" class='modal-Perfil'>
                    <?php
                        $comando_banco = "SELECT * FROM usuarios WHERE id = '$conta'";
                        $resultado_tabela2 = mysqli_query($conexao, $comando_banco);
                
                        if ($resultado_tabela2) {  // Corrigido aqui
                            while ($usuario = mysqli_fetch_assoc($resultado_tabela2)){
                                echo "<img id='image-preview' src='". htmlspecialchars($usuario['imagem'] ?: 'imagens/icon_perfil.png') ."' alt='Foto de perfil' class='fotoPerfil'>";
                            }
                        }
                    ?>
                </label>
                <input id="image-field" type="file" name="image-field" required>
                <button type='submit' name='image' id="modal-confirm">Salvar Imagem</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('image-field').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileReader = new FileReader();
                fileReader.onload = function() {
                    document.getElementById('image-preview').src = fileReader.result;
                };
                fileReader.readAsDataURL(file);
            }
        });

        function trocarImg(){
            document.getElementById('modal').style.display = 'flex';
        }

        function fecharModal(){
            document.getElementById('modal').style.display = 'none';
        }

    </script>

    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            var_dump($_POST); // Debugging
            var_dump($_FILES); // Debugging
            
            if (isset($_FILES['image-field'])) {
                if (isset($_POST['image'])) {
                    $file = $_FILES['image-field'];
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = 'uploads/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $uploadFileName = basename($file['name']);
                        $uploadFile = $uploadDir . $uploadFileName;

                        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                            // Atualiza o caminho da imagem no banco de dados
                            $stmt = $conexao->prepare("UPDATE usuarios SET imagem = ? WHERE id = ?");
                            if ($stmt) {
                                $stmt->bind_param("si", $uploadFile, $conta);
                                if ($stmt->execute()) {
                                    echo '<script>window.location.replace(`perfilAdm.php`)</script>';
                                } else {
                                    echo "Erro ao atualizar a imagem: " . $stmt->error;
                                }
                            } else {
                                echo "Erro na preparação da consulta: " . $conexao->error;
                            }
                        } else {
                            echo "Falha ao mover o arquivo.";
                        }
                    } else {
                        echo "Erro no upload do arquivo: " . $file['error'];
                    }
                }
            }
        }
    ?>

</body>
</html>