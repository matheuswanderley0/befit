<?php
session_start(); // Inicia a sessão para o login automático
require_once 'conexao.php';

$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Define variáveis iniciais
    $tipo = '';
    $nome = '';
    $email = '';
    $senha = '';
    $genero = '';
    
    // Verifica qual formulário foi enviado e captura os dados
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'aluno') {
        $nome = $_POST['nome-aluno'];
        $email = $_POST['email-aluno'];
        $senha = $_POST['senha-aluno']; 
        $genero = $_POST['genero-aluno'];
        $tipo = 'aluno';
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] == 'personal') {
        $nome = $_POST['nome-prof'];
        $email = $_POST['email-prof'];
        $senha = $_POST['senha-prof'];
        $cref = $_POST['cref-prof'];
        $genero = $_POST['genero-prof'];
        $exp_anos = $_POST['exp-anos'];
        $tipo = 'personal';
        $especialidades = isset($_POST['especialidade']) ? $_POST['especialidade'] : [];
    }

    if (!empty($email) && !empty($senha) && !empty($nome)) {
        $conn->begin_transaction();

        try {
            // 1. Inserir Usuário na tabela principal
            $sql_user = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_user);
            $stmt->bind_param("ssss", $nome, $email, $senha, $tipo);
            $stmt->execute();
            $user_id = $conn->insert_id;
            $stmt->close();

            // 2. Inserir Perfil Específico
            if ($tipo == 'aluno') {
                $sql_aluno = "INSERT INTO perfil_aluno (usuario_id, genero) VALUES (?, ?)";
                $stmt = $conn->prepare($sql_aluno);
                $stmt->bind_param("is", $user_id, $genero);
                $stmt->execute();
                $stmt->close();
            } elseif ($tipo == 'personal') {
                $sql_personal = "INSERT INTO perfil_personal (usuario_id, cref, experiencia_anos) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql_personal);
                $stmt->bind_param("isi", $user_id, $cref, $exp_anos);
                $stmt->execute();
                $stmt->close();

                // 3. Inserir Especialidades (apenas para Personal)
                if (!empty($especialidades)) {
                    $sql_spec = "INSERT INTO personal_especialidades (personal_id, especialidade_id) VALUES (?, ?)";
                    $stmt_spec = $conn->prepare($sql_spec);
                    
                    // Mapeamento de nomes para IDs (confirme se os values no HTML batem com isso ou se são IDs diretos)
                    $mapa_specs = [
                        'hipertrofia' => 1, 'emagrecimento' => 2, 'funcional' => 3, 
                        'yoga' => 4, 'crossfit' => 5
                    ];

                    foreach ($especialidades as $spec_val) {
                        // Se o valor vier como texto (ex: 'hipertrofia'), converte. Se vier ID, usa direto.
                        $spec_id = isset($mapa_specs[$spec_val]) ? $mapa_specs[$spec_val] : intval($spec_val);
                        
                        if ($spec_id > 0) {
                            $stmt_spec->bind_param("ii", $user_id, $spec_id);
                            $stmt_spec->execute();
                        }
                    }
                    $stmt_spec->close();
                }
            }

            // Commit da transação
            $conn->commit();

            // --- LOGIN AUTOMÁTICO ---
            $_SESSION['usuario_id'] = $user_id;
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_tipo'] = $tipo;

            // --- REDIRECIONAMENTO ---
            if ($tipo == 'aluno') {
                header("Location: inicio_aluno.php");
            } else {
                header("Location: inicio_personal.php");
            }
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $erro = "Erro ao cadastrar: " . $e->getMessage();
            if ($conn->errno == 1062) { // Código de erro para duplicidade (email já existe)
                 $erro = "Este e-mail já está cadastrado.";
            }
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Cadastro</title>

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
            overflow-y: auto;
            padding: 2rem 0;
        }

        .register-container {
            background-color: var(--white);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            text-align: center;
            max-width: 450px;
            width: 90%;
            transform: translateY(0);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: containerAppear 0.8s ease-out;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-green), var(--primary-blue));
            animation: shimmer 3s infinite;
        }

        @keyframes containerAppear {
            0% { opacity: 0; transform: translateY(30px) scale(0.95); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
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
            transform: translateX(0);
            transition: all 0.3s ease;
        }

        .input-group input[type="text"],
        .input-group input[type="email"],
        .input-group input[type="password"],
        .input-group input[type="number"],
        .input-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        .input-group select {
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%23333%22%3E%3Cpath%20d%3D%22M5.293%207.293a1%201%200%20011.414%200L10%2010.586l3.293-3.293a1%201%200%20111.414%201.414l-4%204a1%201%200%2001-1.414%200l-4-4a1%201%200%20010-1.414z%22%20%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 0.7rem top 50%;
            background-size: 1.25rem;
            padding-right: 2.5rem;
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 
                0 0 0 3px rgba(139, 191, 86, 0.2),
                0 5px 15px rgba(139, 191, 86, 0.1);
            transform: translateY(-2px);
        }

        .input-group input:valid,
        .input-group select:valid {
            border-color: var(--primary-blue);
        }

        .input-group input:not([type="checkbox"]):focus + label,
        .input-group input:not([type="checkbox"]):valid + label,
        .input-group select:focus + label,
        .input-group select:valid + label {
            transform: translateX(10px);
            color: var(--primary-blue);
        }
        
        .experience-inputs {
            display: flex;
            gap: 10px;
        }
        
        .experience-inputs input {
            width: 50%;
        }

        /* Especialidades (Tags Marcáveis) */
        .specialty-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-start;
        }
        
        .hidden-checkbox {
            display: none;
        }
        
        .specialty-box {
            display: inline-block;
            padding: 8px 20px;
            border: 1px solid #e0e0e0;
            border-radius: 50px;
            background: #f8f9fa;
            color: #666;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }
        
        .specialty-box:hover {
            background: #eef2ff;
            border-color: var(--primary-blue);
        }

        .hidden-checkbox:checked + .specialty-box {
            background-color: var(--primary-blue);
            color: var(--white);
            border-color: var(--primary-blue);
            box-shadow: 0 4px 10px rgba(3, 10, 140, 0.3);
            transform: translateY(-2px);
        }

        /* Ícone de Check */
        .hidden-checkbox:checked + .specialty-box::after {
            content: '✔';
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 0.7rem;
            color: var(--primary-green);
        }

        .register-button {
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
            margin-top: 2rem;
            position: relative;
            overflow: hidden;
        }

        .register-button:hover {
            background: linear-gradient(135deg, #7aad47 0%, #6a9a3e 100%);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(139, 191, 86, 0.4);
        }

        .login-link {
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #555;
        }

        .login-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: var(--primary-green);
        }

        .register-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            background: var(--light-gray);
            padding: 5px;
        }
        
        .tab-button {
            flex: 1;
            padding: 0.5rem;
            border: none;
            background: transparent;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #777;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            background: var(--white);
            color: var(--primary-green);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        #form-profissional { display: none; }

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

    <div class="register-container">
        
        <img src="logo.png" alt="Logo BeFit" class="logo">

        <h2>Cadastro</h2>

        <div class="error-message">
            <?php echo $erro; ?>
        </div>

        <div class="register-tabs">
            <button type="button" class="tab-button active" id="tab-aluno">Sou Aluno</button>
            <button type="button" class="tab-button" id="tab-profissional">Sou Profissional</button>
        </div>
        
        <form id="form-aluno" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="form_type" value="aluno">
            
            <div class="input-group">
                <label for="nome-aluno">Nome Completo</label>
                <input type="text" id="nome-aluno" name="nome-aluno" required>
            </div>
            <div class="input-group">
                <label for="email-aluno">Email</label>
                <input type="email" id="email-aluno" name="email-aluno" required>
            </div>
            <div class="input-group">
                <label for="senha-aluno">Senha</label>
                <input type="password" id="senha-aluno" name="senha-aluno" required>
            </div>
            <div class="input-group">
                <label for="genero-aluno">Gênero</label>
                <select id="genero-aluno" name="genero-aluno">
                    <option value="masculino">Masculino</option>
                    <option value="feminino">Feminino</option>
                    <option value="outro">Outro</option>
                </select>
            </div>
            <button type="submit" class="register-button">Cadastrar Aluno</button>
        </form>
        
        <form id="form-profissional" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="form_type" value="personal">

            <div class="input-group">
                <label for="nome-prof">Nome Completo</label>
                <input type="text" id="nome-prof" name="nome-prof" required>
            </div>
            <div class="input-group">
                <label for="email-prof">Email</label>
                <input type="email" id="email-prof" name="email-prof" required>
            </div>
            <div class="input-group">
                <label for="senha-prof">Senha</label>
                <input type="password" id="senha-prof" name="senha-prof" required>
            </div>
            <div class="input-group">
                <label>Tempo de Experiência</label>
                <div class="experience-inputs">
                    <input type="number" id="exp-anos" name="exp-anos" placeholder="Anos" min="0" value="0">
                </div>
            </div>
            <div class="input-group">
                <label for="cref-prof">CREF</label>
                <input type="text" id="cref-prof" name="cref-prof" required>
            </div>
            <div class="input-group">
                <label for="genero-prof">Gênero</label>
                <select id="genero-prof" name="genero-prof">
                    <option value="masculino">Masculino</option>
                    <option value="feminino">Feminino</option>
                    <option value="outro">Outro</option>
                </select>
            </div>

            <!-- Especialidades (Pills/Tags) -->
            <div class="input-group">
                <label>Especialidades</label>
                <div class="specialty-container">
                    
                    <input type="checkbox" id="spec-hipertrofia" name="especialidade[]" value="hipertrofia" class="hidden-checkbox">
                    <label for="spec-hipertrofia" class="specialty-box">Hipertrofia</label>

                    <input type="checkbox" id="spec-emagrecimento" name="especialidade[]" value="emagrecimento" class="hidden-checkbox">
                    <label for="spec-emagrecimento" class="specialty-box">Emagrecimento</label>

                    <input type="checkbox" id="spec-funcional" name="especialidade[]" value="funcional" class="hidden-checkbox">
                    <label for="spec-funcional" class="specialty-box">Funcional</label>

                    <input type="checkbox" id="spec-yoga" name="especialidade[]" value="yoga" class="hidden-checkbox">
                    <label for="spec-yoga" class="specialty-box">Yoga</label>

                    <input type="checkbox" id="spec-crossfit" name="especialidade[]" value="crossfit" class="hidden-checkbox">
                    <label for="spec-crossfit" class="specialty-box">Crossfit</label>
                    
                </div>
            </div>

            <button type="submit" class="register-button">Cadastrar Personal</button>
        </form>

        <p class="login-link">
            Já tem conta? <a href="index.php">Faça o login</a>
        </p>
            
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabAluno = document.getElementById('tab-aluno');
            const tabProf = document.getElementById('tab-profissional');
            const formAluno = document.getElementById('form-aluno');
            const formProf = document.getElementById('form-profissional');

            function switchTab(tab) {
                if (tab === 'aluno') {
                    tabAluno.classList.add('active');
                    tabProf.classList.remove('active');
                    formAluno.style.display = 'block';
                    formProf.style.display = 'none';
                } else {
                    tabProf.classList.add('active');
                    tabAluno.classList.remove('active');
                    formProf.style.display = 'block';
                    formAluno.style.display = 'none';
                }
            }

            tabAluno.addEventListener('click', () => switchTab('aluno'));
            tabProf.addEventListener('click', () => switchTab('personal'));
        });
    </script>
</body>
</html>