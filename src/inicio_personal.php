<?php
session_start();
require_once 'conexao.php';

// 1. Verifica se é Personal
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'personal') {
    header("Location: index.php");
    exit();
}

$personal_id = $_SESSION['usuario_id'];
$nome_personal = $_SESSION['usuario_nome'];

// --- LÓGICA DE ENVIO DE CONVITE (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email_aluno'])) {
    $email_convite = $conn->real_escape_string($_POST['email_aluno']);
    
    // Verifica se o usuário existe
    $sql_busca_user = "SELECT id, nome FROM usuarios WHERE email = '$email_convite' AND tipo = 'aluno'";
    $res_user = $conn->query($sql_busca_user);

    if ($res_user && $res_user->num_rows > 0) {
        $aluno = $res_user->fetch_assoc();
        $aluno_id = $aluno['id'];

        // 1. Verifica se já existe convite pendente
        $check_convite = $conn->query("SELECT id FROM convites WHERE personal_id = $personal_id AND aluno_id = $aluno_id AND status = 'pendente'");
        
        if ($check_convite->num_rows == 0) {
            // 2. Cria o convite oficial
            $sql_invite = "INSERT INTO convites (personal_id, aluno_id) VALUES (?, ?)";
            $stmt_inv = $conn->prepare($sql_invite);
            $stmt_inv->bind_param("ii", $personal_id, $aluno_id);
            $stmt_inv->execute();
            $stmt_inv->close();

            // 3. Envia mensagem de aviso no chat
            $msg_convite = "Olá " . $aluno['nome'] . "! Enviei um convite oficial para você ser meu aluno. Aceite no seu painel inicial para começarmos!";
            $sql_msg = "INSERT INTO mensagens (remetente_id, destinatario_id, mensagem, data_envio) VALUES (?, ?, ?, NOW())";
            $stmt_msg = $conn->prepare($sql_msg);
            $stmt_msg->bind_param("iis", $personal_id, $aluno_id, $msg_convite);
            $stmt_msg->execute();
            $stmt_msg->close();

            echo "<script>alert('Convite enviado com sucesso para " . $aluno['nome'] . "!'); window.location.href='inicio_personal.php';</script>";
        } else {
            echo "<script>alert('Já existe um convite pendente para este aluno.'); window.location.href='inicio_personal.php';</script>";
        }
    } else {
        echo "<script>alert('E-mail não encontrado no sistema. Peça para o aluno se cadastrar primeiro.'); window.location.href='inicio_personal.php';</script>";
    }
    exit();
}

// 2. Buscar Alunos do Personal
$sql_alunos = "SELECT u.id, u.nome, pa.objetivo, pa.peso_atual 
               FROM usuarios u 
               JOIN perfil_aluno pa ON u.id = pa.usuario_id 
               WHERE pa.personal_id = ?";

$stmt = $conn->prepare($sql_alunos);
$stmt->bind_param("i", $personal_id);
$stmt->execute();
$result = $stmt->get_result();

$alunos = [];
while($row = $result->fetch_assoc()) {
    // Conta quantas fichas esse aluno tem
    $sql_count = "SELECT COUNT(*) as total FROM fichas_treino WHERE aluno_id = " . $row['id'];
    $res_count = $conn->query($sql_count);
    $row['total_fichas'] = $res_count->fetch_assoc()['total'];
    
    $alunos[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Painel do Personal</title>

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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding-bottom: 80px;
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

        .app-header .logo {
            width: 110px;
            transition: transform 0.3s ease;
        }

        .app-header .logo:hover {
            transform: scale(1.05);
        }

        .profile-menu {
            position: absolute;
            right: 2rem;
            top: 0;
            height: 100%;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .app-header .profile-icon {
            width: 42px;
            height: 42px;
            background: var(--primary-blue);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(3, 10, 140, 0.2);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            position: relative;
            z-index: 102;
        }

        .app-header .profile-icon:hover {
            transform: scale(1.05);
            background: var(--primary-blue-dark);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 80%;
            padding-top: 15px;
            z-index: 101;
        }

        .dropdown-inner {
            background: var(--white);
            min-width: 180px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #eee;
        }

        .dropdown-content a {
            color: var(--dark-text);
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            font-size: 0.9rem;
            border-bottom: 1px solid #f9f9f9;
            transition: background 0.2s;
        }

        .dropdown-content a:hover {
            background: #f0f4ff;
            color: var(--primary-blue);
        }

        .profile-menu:hover .dropdown-content {
            display: block;
        }

        /* Main Layout */
        main {
            max-width: 1000px;
            margin: 0 auto;
            padding: 4rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 3rem;
        }

        .welcome-text h1 {
            font-size: 2.2rem;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
            font-weight: 800;
        }

        .welcome-text p {
            color: #666;
            font-size: 1.1rem;
        }

        /* Dashboard Stats */
        .stats-row {
            display: flex;
            gap: 20px;
            width: 100%;
            max-width: 800px;
            margin-bottom: 3rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            text-align: center;
            flex: 1;
            min-width: 150px;
            border: 1px solid #f0f0f0;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-blue);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-green);
            display: block;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 600;
        }

        /* Clients List Section */
        .clients-section {
            width: 100%;
            max-width: 800px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-text);
        }

        .clients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .client-card {
            background: var(--white);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            border: 1px solid #eee;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .client-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary-blue);
            transition: height 0.3s;
        }

        .client-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }

        .client-card:hover::before {
            height: 8px;
            background: var(--primary-green);
        }

        .client-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), #02065a);
            color: var(--white);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            box-shadow: 0 5px 15px rgba(3, 10, 140, 0.2);
        }

        .client-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .client-meta {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 1rem;
        }

        .client-stats {
            display: flex;
            gap: 10px;
            font-size: 0.8rem;
            color: #666;
            background: #f9f9f9;
            padding: 5px 15px;
            border-radius: 20px;
        }

        .btn-add-client {
            background: var(--primary-green);
            color: var(--white);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(139, 191, 86, 0.3);
        }

        .btn-add-client:hover {
            background: #7aad47;
            transform: translateY(-2px);
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            background: #fff;
            border-radius: 20px;
            border: 1px dashed #ccc;
            color: #888;
        }

        /* Modal de Convite */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
            animation: fadeInModal 0.3s ease-out;
        }

        .modal {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 20px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            position: relative;
            transform: translateY(20px);
            animation: slideUpModal 0.3s ease-out forwards;
        }

        @keyframes fadeInModal { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUpModal { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            transition: color 0.2s;
        }
        .modal-close:hover { color: var(--danger-red); }

        .modal h2 {
            color: var(--primary-blue);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .modal p {
            color: #666;
            text-align: center;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .modal-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            transition: border-color 0.3s;
        }
        .modal-input:focus { outline: none; border-color: var(--primary-blue); }

        .btn-send-invite {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-blue) 0%, #02065a 100%);
            color: var(--white);
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-send-invite:hover { transform: scale(1.02); }

        /* Floating Chat */
        .floating-chat-bar {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 200;
        }

        .floating-chat-btn {
            background: var(--white);
            color: var(--primary-blue);
            border: 1px solid #eee;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .floating-chat-btn:hover {
            background: var(--primary-blue);
            color: var(--white);
            transform: translateY(-3px);
        }

        @media (max-width: 600px) {
            .stats-row {
                flex-direction: column;
            }
            .clients-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <header class="app-header">
        <img src="logo.png" alt="Logo BeFit" class="logo">
        
        <div class="profile-menu">
            <div class="profile-icon" title="Meu Perfil">
                <?php echo strtoupper(substr($nome_personal, 0, 1)); ?>
            </div>
            <div class="dropdown-content">
                <div class="dropdown-inner">
                    <a href="gerenciar_perfil_personal.php">Gerenciar Perfil</a>
                    <a href="logout.php" style="color: var(--danger-red);">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        
        <div class="welcome-text">
            <h1>Olá, <?php echo htmlspecialchars($nome_personal); ?>!</h1>
            <p>Gerencie seus alunos e treinos de forma simples.</p>
        </div>

        <!-- Dashboard Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <span class="stat-number"><?php echo count($alunos); ?></span>
                <span class="stat-label">Alunos Ativos</span>
            </div>
            <!-- Exemplo de dados futuros -->
            <div class="stat-card">
                <span class="stat-number" style="color: var(--primary-blue);">5</span>
                <span class="stat-label">Fichas Criadas</span>
            </div>
            <div class="stat-card">
                <span class="stat-number" style="color: #ffc107;">4.9</span>
                <span class="stat-label">Avaliação Média</span>
            </div>
        </div>

        <section class="clients-section">
            <div class="section-header">
                <h2 class="section-title">Meus Alunos</h2>
                <a href="#" class="btn-add-client" onclick="openInviteModal()">
                    <span>+</span> Convidar Aluno
                </a>
            </div>

            <div class="clients-grid">
                <?php if (!empty($alunos)): ?>
                    <?php foreach($alunos as $aluno): ?>
                        <!-- Card Clicável -> Vai para Gerenciar Aluno -->
                        <a href="perfil_cliente.php?id=<?php echo $aluno['id']; ?>" class="client-card">
                            <div class="client-avatar">
                                <?php echo strtoupper(substr($aluno['nome'], 0, 1)); ?>
                            </div>
                            <div class="client-name"><?php echo htmlspecialchars($aluno['nome']); ?></div>
                            <div class="client-meta">
                                <?php echo htmlspecialchars($aluno['objetivo'] ?: 'Sem objetivo definido'); ?>
                            </div>
                            <div class="client-stats">
                                <span>📄 <?php echo $aluno['total_fichas']; ?> Fichas</span>
                                <span>⚖️ <?php echo $aluno['peso_atual'] ? $aluno['peso_atual'] . 'kg' : '--'; ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>Você ainda não tem alunos vinculados.</h3>
                        <p>Peça para seus alunos buscarem seu perfil na área "Encontrar Profissional" e clicarem em "Chamar", ou envie um convite.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <div class="floating-chat-bar">
        <a href="chat.php" class="floating-chat-btn">
            <span>💬</span> Minhas Conversas
        </a>
    </div>

    <!-- Modal de Convite -->
    <div class="modal-overlay" id="inviteModal">
        <div class="modal">
            <button class="modal-close" onclick="closeInviteModal()">&times;</button>
            <h2>Convidar Novo Aluno</h2>
            <p>Digite o e-mail do aluno para enviar o convite. Se ele já tiver cadastro, receberá um convite no sistema.</p>
            
            <!-- Formulário enviando para a mesma página com método POST -->
            <form method="POST" action="">
                <input type="email" name="email_aluno" class="modal-input" placeholder="E-mail do aluno" required>
                <button type="submit" class="btn-send-invite">Enviar Convite</button>
            </form>
        </div>
    </div>

    <script>
        function openInviteModal() {
            document.getElementById('inviteModal').style.display = 'flex';
        }

        function closeInviteModal() {
            document.getElementById('inviteModal').style.display = 'none';
        }

        // Fechar ao clicar fora do modal
        window.onclick = function(event) {
            const modal = document.getElementById('inviteModal');
            if (event.target == modal) {
                closeInviteModal();
            }
        }
    </script>

</body>
</html>