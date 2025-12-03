<?php
session_start();
require_once 'conexao.php';

// 1. Verifica se é Aluno
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'aluno') {
    header("Location: index.php");
    exit();
}

$aluno_id = $_SESSION['usuario_id'];

// 2. Buscar Dados do Aluno
$sql_aluno = "SELECT u.nome, u.email, pa.telefone, pa.data_nascimento, pa.genero, 
                     pa.altura, pa.peso_atual, pa.objetivo, pa.observacoes_medicas
              FROM usuarios u 
              JOIN perfil_aluno pa ON u.id = pa.usuario_id 
              WHERE u.id = ?";

$stmt = $conn->prepare($sql_aluno);
$stmt->bind_param("i", $aluno_id);
$stmt->execute();
$result = $stmt->get_result();
$dados = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Meus Dados</title>

    <style>
        :root {
            --primary-green: #8BBF56;
            --primary-blue: #030A8C;
            --primary-blue-dark: #02065a;
            --light-gray: #f4f4f4;
            --dark-text: #333;
            --white: #ffffff;
            --danger-red: #ff4757;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding-bottom: 100px;
            min-height: 100vh;
            color: var(--dark-text);
            overflow-x: hidden;
        }

        /* Header */
        .app-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .app-header .logo { width: 110px; transition: transform 0.3s ease; }
        .app-header .logo:hover { transform: scale(1.05); }

        .profile-menu {
            position: absolute; right: 2rem; top: 0; height: 100%;
            display: flex; align-items: center; cursor: pointer;
        }

        .app-header .profile-icon {
            width: 42px; height: 42px;
            background: var(--primary-blue);
            border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            color: white; font-weight: bold; font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(3, 10, 140, 0.2);
            position: relative; z-index: 102;
        }

        .dropdown-content {
            display: none; position: absolute; right: 0; top: 80%; padding-top: 15px; z-index: 101;
        }

        .dropdown-inner {
            background: var(--white); min-width: 180px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 10px; overflow: hidden; border: 1px solid #eee;
        }

        .dropdown-content a {
            color: var(--dark-text); padding: 12px 20px; text-decoration: none;
            display: block; font-size: 0.9rem; border-bottom: 1px solid #f9f9f9;
            transition: background 0.2s;
        }
        .dropdown-content a:hover { background: #f0f4ff; color: var(--primary-blue); }
        .profile-menu:hover .dropdown-content { display: block; }

        /* Main Layout */
        main {
            max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .page-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;
        }

        .page-title { font-size: 1.8rem; font-weight: 700; color: var(--primary-blue); }
        
        .back-btn {
            text-decoration: none; color: #777; font-weight: 600;
            display: flex; align-items: center; gap: 5px; transition: color 0.3s;
        }
        .back-btn:hover { color: var(--primary-blue); }

        /* Form Container */
        .edit-container {
            background: var(--white); border-radius: 20px; padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;
        }

        /* Sections */
        .form-section { margin-bottom: 3rem; padding-bottom: 2rem; border-bottom: 1px solid #eee; }
        .form-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

        .section-header {
            font-size: 1.2rem; font-weight: 700; color: var(--primary-blue);
            margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .full-width { grid-column: span 2; }

        .input-group { margin-bottom: 1rem; }
        .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-text); font-size: 0.95rem; }
        
        .input-group input, .input-group textarea, .input-group select {
            width: 100%; padding: 0.8rem 1rem; border: 2px solid #eee;
            border-radius: 10px; font-size: 1rem; transition: all 0.3s;
            background: #fafafa; font-family: inherit;
        }
        .input-group input:focus, .input-group textarea:focus, .input-group select:focus {
            outline: none; border-color: var(--primary-blue); background: var(--white);
            box-shadow: 0 0 0 3px rgba(3, 10, 140, 0.05);
        }

        /* Upload Foto */
        .photo-upload { display: flex; align-items: center; gap: 20px; margin-bottom: 1.5rem; }
        .current-photo {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), #02065a);
            display: flex; justify-content: center; align-items: center;
            color: var(--white); font-weight: 700; font-size: 1.5rem;
            background-size: cover; background-position: center; overflow: hidden;
        }
        .upload-btn {
            background: var(--white); border: 1px dashed #ccc; padding: 0.8rem 1.5rem;
            border-radius: 10px; cursor: pointer; font-size: 0.9rem; color: #666; transition: all 0.3s;
        }
        .upload-btn:hover { border-color: var(--primary-blue); color: var(--primary-blue); background: #f0f4ff; }

        /* Ações */
        .action-bar { margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem; }
        .btn { padding: 0.8rem 2rem; border-radius: 50px; font-weight: 700; cursor: pointer; border: none; transition: all 0.3s; font-size: 1rem; }
        .btn-cancel { background: transparent; color: #777; border: 1px solid #eee; text-decoration: none; display: flex; align-items: center; }
        .btn-cancel:hover { background: #f9f9f9; color: var(--dark-text); }
        .btn-save { background: linear-gradient(135deg, var(--primary-green) 0%, #7aad47 100%); color: var(--white); box-shadow: 0 4px 15px rgba(139, 191, 86, 0.3); }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(139, 191, 86, 0.4); }

        /* Floating Chat */
        .floating-chat-bar { position: fixed; bottom: 30px; right: 30px; z-index: 200; }
        .floating-chat-btn {
            background: var(--white); color: var(--primary-blue); border: 1px solid #eee;
            padding: 12px 24px; border-radius: 50px; font-weight: 600; font-size: 0.95rem;
            cursor: pointer; box-shadow: 0 5px 20px rgba(0,0,0,0.08); text-decoration: none;
            display: flex; align-items: center; gap: 8px; transition: all 0.3s;
        }
        .floating-chat-btn:hover { background: var(--primary-blue); color: var(--white); transform: translateY(-3px); }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>

    <header class="app-header">
        <a href="inicio_aluno.php">
            <img src="logo.png" alt="Logo BeFit" class="logo">
        </a>
        <div class="profile-menu">
            <div class="profile-icon" title="Meu Perfil">
                <?php echo strtoupper(substr($dados['nome'], 0, 1)); ?>
            </div>
            <div class="dropdown-content">
                <div class="dropdown-inner">
                    <a href="gerenciar_perfil_aluno.php">Meus Dados</a>
                    <a href="logout.php" style="color: var(--danger-red);">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="page-header">
            <h1 class="page-title">Meus Dados</h1>
            <a href="inicio_aluno.php" class="back-btn">
                <span>&#8592;</span> Voltar ao Início
            </a>
        </div>

        <form class="edit-container" action="salvar_perfil_aluno.php" method="POST" enctype="multipart/form-data">
            
            <!-- Seção 1: Informações Pessoais -->
            <div class="form-section">
                <div class="section-header">Informações Pessoais</div>
                
                <div class="photo-upload">
                    <div class="current-photo" id="preview-container">
                        <?php echo strtoupper(substr($dados['nome'], 0, 1)); ?>
                    </div>
                    <label class="upload-btn">
                        Alterar Foto
                        <input type="file" id="photo-input" name="foto_perfil" accept="image/*" style="display: none;">
                    </label>
                </div>

                <div class="form-grid">
                    <div class="full-width input-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($dados['nome']); ?>">
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($dados['email']); ?>" readonly style="background: #f0f0f0; cursor: not-allowed;">
                    </div>
                    <div class="input-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($dados['telefone']); ?>">
                    </div>
                    <div class="input-group">
                        <label for="nascimento">Data de Nascimento</label>
                        <input type="date" id="nascimento" name="nascimento" value="<?php echo htmlspecialchars($dados['data_nascimento']); ?>">
                    </div>
                    <div class="input-group">
                        <label for="genero">Gênero</label>
                        <select id="genero" name="genero">
                            <option value="masculino" <?php echo ($dados['genero'] == 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                            <option value="feminino" <?php echo ($dados['genero'] == 'feminino') ? 'selected' : ''; ?>>Feminino</option>
                            <option value="outro" <?php echo ($dados['genero'] == 'outro') ? 'selected' : ''; ?>>Outro</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Seção 2: Perfil Fitness -->
            <div class="form-section">
                <div class="section-header">Perfil Fitness</div>
                <div class="form-grid">
                    <div class="full-width input-group">
                        <label for="objetivo">Objetivo Principal</label>
                        <input type="text" id="objetivo" name="objetivo" value="<?php echo htmlspecialchars($dados['objetivo']); ?>">
                    </div>
                    <div class="input-group">
                        <label for="peso">Peso Atual (kg)</label>
                        <input type="number" id="peso" name="peso" value="<?php echo htmlspecialchars($dados['peso_atual']); ?>" step="0.1">
                    </div>
                    <div class="input-group">
                        <label for="altura">Altura (m)</label>
                        <input type="number" id="altura" name="altura" value="<?php echo htmlspecialchars($dados['altura']); ?>" step="0.01">
                    </div>
                    <div class="full-width input-group">
                        <label for="observacoes">Observações Médicas / Lesões</label>
                        <textarea id="observacoes" name="observacoes" rows="3"><?php echo htmlspecialchars($dados['observacoes_medicas']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="action-bar">
                <a href="inicio_aluno.php" class="btn btn-cancel">Cancelar</a>
                <button type="submit" class="btn btn-save">Salvar Alterações</button>
            </div>

        </form>
    </main>

    <div class="floating-chat-bar">
        <a href="chat.php" class="floating-chat-btn">Suporte</a>
    </div>

    <script>
        // Upload de Foto Funcional (Preview)
        document.getElementById('photo-input').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview-container');
                    preview.style.backgroundImage = `url('${e.target.result}')`;
                    preview.style.backgroundSize = 'cover';
                    preview.style.backgroundPosition = 'center';
                    preview.textContent = ''; // Remove as iniciais
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

</body>
</html>