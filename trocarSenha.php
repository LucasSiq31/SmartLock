<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Troca de Senha - SENAI SmartLock Pro</title>
    <link rel="stylesheet" href="css/styleVerm.css">
    <link rel="shortcut icon" href="imagens/faviconV.png" type="image/x-icon">

    <script>
        // Função para gerar uma senha forte de acordo com os requisitos
        function gerarSenhaForte() {
            const maiusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const minusculas = 'abcdefghijklmnopqrstuvwxyz';
            const digitos = '0123456789';
            const simbolos = '!@#$%^&*()-_=+[{]}\\|;:\'",<.>/?';
           
            // Garantir que a senha tenha pelo menos um caractere de cada tipo
            let senha = '';
            senha += maiusculas.charAt(Math.floor(Math.random() * maiusculas.length)); // 1 letra maiúscula
            senha += minusculas.charAt(Math.floor(Math.random() * minusculas.length)); // 1 letra minúscula
            senha += digitos.charAt(Math.floor(Math.random() * digitos.length)); // 1 número
            senha += simbolos.charAt(Math.floor(Math.random() * simbolos.length)); // 1 caractere especial


            // Preencher os 4 caracteres restantes com caracteres aleatórios
            const todosCaracteres = maiusculas + minusculas + digitos + simbolos;
            for (let i = 0; i < 4; i++) {
                senha += todosCaracteres.charAt(Math.floor(Math.random() * todosCaracteres.length));
            }


            // Embaralhar a senha para garantir aleatoriedade na posição dos caracteres
            senha = senha.split('').sort(() => Math.random() - 0.5).join('');


            return senha;
        }


        // Função para preencher o campo da senha com a senha sugerida
        function sugerirSenha() {
            const senhaGerada = gerarSenhaForte();
            document.getElementsByName("novaSenha")[0].value = senhaGerada;
        }
    </script>

</head>
<body>
<header>
        <div class="cabecalho">
            <img src="imagens/senai_branco.png" alt="" class="logo">
            <div></div>
            <h1>SmartLock Pro</h1>
        </div>
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

                
            ?>
    </header>
    <div class="linha"></div>

    <main>
        <div class="container">
            <form method="post">
                <h1>Trocar Senha</h1>
                <?php

                    $conta = $_SESSION["login"];

                    $comando_banco = "SELECT * FROM usuarios WHERE id = ?";
                    $stmt = $conexao->prepare($comando_banco);
                    $stmt->bind_param("i", $conta);
                    $stmt->execute();
                    $resultado_tabela = $stmt->get_result();
                    
                    if ($resultado_tabela->num_rows > 0) {
                        while ($linha_registro = $resultado_tabela->fetch_assoc()) {
                            echo "<h3>Trocar senha de ". htmlspecialchars($linha_registro['nome']) ."</h3>";
                            echo "<p class='acesso'>Não é você? <a href='index.php'>Trocar conta!</a></p>";
                        }
                    }
                ?>

                <b>Sua nova senha precisa ter:</b>

                <ul>
                    <li>8 dígitos</li>
                    <li>1 letra maiúscula</li>
                    <li>1 letra minúscula</li>
                    <li>1 número</li>
                    <li>1 caractére especial</li>
                </ul>
                
                <p>Sem ideias de senha? <a href="#" onclick="sugerirSenha()">Sugerir Senha Forte</a></p>

                <h3 for="novaSenha">Digite sua senha nova:</h3>
                <input type="text" autocomplete="off" name="novaSenha">
                
                <h3 for="senha2">Confirme a senha:</h3>
                <input type="password" autocomplete="off" name="senha2">

                <input class="submit" type="submit" value="Trocar Senha" name="trocarSenha">
            </form>

            <?php
                if (isset($_POST['trocarSenha'])) {
                    $senha = $_POST['novaSenha'];
                    $senha2 = $_POST['senha2'];
                
                    function validarSenha($senha) {
                        // Verifica se a senha tem pelo menos 8 caracteres
                        if (strlen($senha) < 8) {
                            return false;
                        }
                
                        // Expressão regular para verificar todos os requisitos
                        $padrao = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
                
                        // Verifica se a senha corresponde ao padrão
                        return preg_match($padrao, $senha) === 1;
                    }
                
                    $validacao = false;
                
                    // Verifica se a nova senha é válida
                    if (validarSenha($senha)) {
                        $validacao = true;
                    } else {
                        $validacao = false;
                    }
                
                    // Verifica se as senhas combinam e se a validação é verdadeira
                    if ($senha === $senha2 && $validacao) {
                        // Hash da senha
                        $hash = password_hash($senha, PASSWORD_DEFAULT);
                
                        // Atualiza a senha no banco de dados
                        $sql_update = "UPDATE usuarios SET senha = ? WHERE id = ?";
                        $stmt = $conexao->prepare($sql_update);
                
                        // Checa se a preparação da consulta foi bem-sucedida
                        if ($stmt) {
                            // 's' para string (senha) e 'i' para inteiro (id)
                            $stmt->bind_param("si", $hash, $conta);
                            $stmt->execute();
                
                            if ($stmt->affected_rows > 0) {
                                echo "<p class='aviso'>Senha atualizada com sucesso!</p>";
                
                                sleep(2); // Opcional: para demonstrar a mensagem
                
                                // Redireciona para a página apropriada
                                if ($_SESSION["tipo"] === "Administrador") {
                                    header("Location: retiradas.php");
                                } elseif ($_SESSION["tipo"] === "Professor") {
                                    header("Location: home.php");
                                } else {
                                    header("Location: index.php");
                                }
                                exit(); // Adiciona exit() para garantir que o script pare após redirecionar
                            } else {
                                echo "<p class='aviso'>Nenhuma alteração feita ou erro ao atualizar registro!</p>";
                            }
                
                            $stmt->close();
                        } else {
                            echo "<p class='aviso'>Erro ao preparar a consulta!</p>";
                        }
                    } else {
                        echo "<p class='aviso'>As senhas não se combinam ou a validação falhou!</p>";
                    }
                }
                
                $conexao->close();
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
                <p>Feito por: Lucas Siqueira, Maria Fernanda, Ulisses Almeida e Stela Amorim</p>
            </div>
        </div>
    </footer>
    
</body>
</html>