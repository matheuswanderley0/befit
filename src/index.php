<?php
session_start();
require_once 'conexao.php';

$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['usuario']; // O input name="usuario" na verdade recebe o email
    $senha = $_POST['senha'];

    // Prepara a consulta para evitar SQL Injection
    $sql = "SELECT id, nome, tipo, senha FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verifica a senha (simples comparação por enquanto, ideal usar password_verify com hash)
        if ($senha === $row['senha']) {
            // Login bem sucedido
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario_nome'] = $row['nome'];
            $_SESSION['usuario_tipo'] = $row['tipo'];

            if ($row['tipo'] == 'aluno') {
                header("Location: inicio_aluno.php");
            } else {
                header("Location: inicio_personal.php"); // Redireciona para o dashboard do personal
            }
            exit();
        } else {
            $erro = "Senha incorreta.";
        }
    } else {
        $erro = "Usuário não encontrado.";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Login</title>

    <style>
        :root {
            --primary-green: #8BBF56;
            --primary-blue: #030A8C; 
            --light-gray: #f4f4f4;
            --dark-text: #333;
            --white: #ffffff;
            --error-red: #ff4757;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('fundo.png'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-color: rgba(0, 0, 0, 0.3);
            background-blend-mode: overlay;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        .login-container {
            background-color: var(--white);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
            position: relative;
            overflow: hidden;
            z-index: 1; 
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-green), var(--primary-blue));
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            50% { left: 100%; }
            100% { left: 100%; }
        }

        .logo {
            width: 150px;
            margin-bottom: 1.5rem;
            transition: all 0.5s ease;
            animation: logoFloat 4s ease-in-out infinite;
            filter: drop-shadow(0 5px 15px rgba(139, 191, 86, 0.3));
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(1deg); }
        }

        h2 {
            color: var(--dark-text);
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--dark-text), var(--primary-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .input-group {
            margin-bottom: 1.5rem;
            text-align: left;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-text);
            transition: all 0.3s ease;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(139, 191, 86, 0.2);
        }

        .login-button {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary-green) 0%, #7aad47 100%);
            color: var(--white);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }

        .login-button:hover {
            background: linear-gradient(135deg, #7aad47 0%, #6a9a3e 100%);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(139, 191, 86, 0.4);
        }

        .register-link {
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #555;
        }

        .register-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            color: var(--primary-green);
        }
        
        .error-message {
            color: var(--error-red);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding: 10px;
            background: rgba(255, 71, 87, 0.1);
            border-radius: 5px;
            border: 1px solid rgba(255, 71, 87, 0.2);
            display: <?php echo $erro ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>

    <div class="login-container">
        
        <img src="logo.png" alt="Logo BeFit" class="logo">

        <h2>Login</h2>

        <div class="error-message">
            <?php echo $erro; ?>
        </div>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="input-group">
                <label for="usuario">Email</label>
                <input type="text" id="usuario" name="usuario" required placeholder="seu@email.com">
            </div>

            <div class="input-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required placeholder="******">
            </div>

            <button type="submit" class="login-button">Entrar</button>
        </form>

        <p class="register-link">
            Não tem conta? <a href="cadastro.php">Faça o cadastro</a>
        </p>
    </div>

</body>
</html>