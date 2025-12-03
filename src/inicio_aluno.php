<?php
session_start();
require_once 'conexao.php';

// 1. Verifica login
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'aluno') {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nome_aluno = $_SESSION['usuario_nome'];

// --- PROCESSAR RESPOSTA AO CONVITE ---
if (isset($_POST['acao_convite']) && isset($_POST['convite_id'])) {
    $acao = $_POST['acao_convite'];
    $convite_id = intval($_POST['convite_id']);
    $personal_convite_id = intval($_POST['personal_id']);

    if ($acao === 'aceitar') {
        // 1. Atualiza status do convite
        $conn->query("UPDATE convites SET status = 'aceito' WHERE id = $convite_id");
        
        // 2. Vincula aluno ao personal
        $conn->query("UPDATE perfil_aluno SET personal_id = $personal_convite_id WHERE usuario_id = $usuario_id");
        
        echo "<script>alert('Parabéns! Você agora é aluno desse personal.'); window.location.href='inicio_aluno.php';</script>";
    } elseif ($acao === 'recusar') {
        $conn->query("UPDATE convites SET status = 'recusado' WHERE id = $convite_id");
    }
    // Evita reenvio do form
    header("Location: inicio_aluno.php");
    exit();
}

// --- LÓGICA DE EXIBIÇÃO ---

// Variáveis de controle
$tem_personal = false;
$nome_personal = "";
$lista_personals = [];
$meus_treinos = [];
$convite_pendente = null;

// A. Verifica se tem personal vinculado
$sql_verifica = "SELECT u.nome 
                 FROM perfil_aluno pa
                 JOIN usuarios u ON pa.personal_id = u.id
                 WHERE pa.usuario_id = ?";
$stmt = $conn->prepare($sql_verifica);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // TEM PERSONAL
    $tem_personal = true;
    $row = $result->fetch_assoc();
    $nome_personal = $row['nome'];

    // Busca os treinos do aluno
    $sql_treinos = "SELECT id, nome_ficha, descricao, DATE_FORMAT(data_criacao, '%d/%m/%Y') as data_fmt 
                    FROM fichas_treino 
                    WHERE aluno_id = ? 
                    ORDER BY id DESC LIMIT 3";
    $stmt_treinos = $conn->prepare($sql_treinos);
    $stmt_treinos->bind_param("i", $usuario_id);
    $stmt_treinos->execute();
    $res_treinos = $stmt_treinos->get_result();
    while($t = $res_treinos->fetch_assoc()) {
        $meus_treinos[] = $t;
    }
    $stmt_treinos->close();

} else {
    // NÃO TEM PERSONAL -> Verifica se tem CONVITE PENDENTE
    $sql_convite = "SELECT c.id, c.personal_id, u.nome as nome_personal 
                    FROM convites c
                    JOIN usuarios u ON c.personal_id = u.id
                    WHERE c.aluno_id = ? AND c.status = 'pendente' 
                    ORDER BY c.id DESC LIMIT 1";
    $stmt_c = $conn->prepare($sql_convite);
    $stmt_c->bind_param("i", $usuario_id);
    $stmt_c->execute();
    $res_convite = $stmt_c->get_result();
    
    if ($res_convite->num_rows > 0) {
        $convite_pendente = $res_convite->fetch_assoc();
    }
    $stmt_c->close();

    // Busca sugestões de personals
    $sql_personals = "SELECT u.id, u.nome, pp.cref 
                      FROM usuarios u
                      JOIN perfil_personal pp ON u.id = pp.usuario_id
                      WHERE u.tipo = 'personal'
                      ORDER BY RAND() LIMIT 4";
    $res_personals = $conn->query($sql_personals);
    if ($res_personals) {
        while($p = $res_personals->fetch_assoc()) {
            $lista_personals[] = $p;
        }
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Área do Aluno</title>

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
            background: linear-gradient(-45deg, #f8f9fa, #e9ecef, #f0f4f8, #e6eaf0);
            background-size: 400% 400%;
            animation: gradientFlow 8s ease infinite;
            padding-bottom: 80px;
            min-height: 100vh;
            color: var(--dark-text);
            overflow-x: hidden;
        }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Header */
        .app-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255,255,255,0.8);
        }

        .app-header .logo { width: 110px; transition: transform 0.3s ease; }
        .app-header .logo:hover { transform: scale(1.05); }

        /* Menu Perfil */
        .profile-menu {
            position: absolute;
            right: 2rem;
            top: 0; height: 100%;
            display: flex; align-items: center; cursor: pointer;
        }

        .app-header .profile-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            color: white; font-weight: bold; font-size: 1.2rem;
            box-shadow: 0 6px 15px rgba(3, 10, 140, 0.3);
            position: relative; z-index: 102;
        }

        .dropdown-content {
            display: none;
            position: absolute; right: 0; top: 80%; padding-top: 15px; z-index: 101;
        }

        .dropdown-inner {
            background: var(--white); min-width: 180px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.3);
        }

        .dropdown-content a {
            color: var(--dark-text); padding: 14px 20px; text-decoration: none;
            display: block; font-size: 0.9rem; border-bottom: 1px solid #f9f9f9;
            transition: background 0.2s;
        }

        .dropdown-content a:hover { background: #f0f4ff; color: var(--primary-blue); }
        .profile-menu:hover .dropdown-content { display: block; }

        /* Layout */
        main {
            max-width: 1000px; margin: 0 auto; padding: 4rem 1.5rem;
            display: flex; flex-direction: column; align-items: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .welcome-text { text-align: center; margin-bottom: 3rem; }
        .welcome-text h1 { font-size: 2.2rem; color: var(--primary-blue); margin-bottom: 0.5rem; font-weight: 800; }
        .welcome-text p { color: #666; font-size: 1.1rem; }

        /* --- CARD DE CONVITE --- */
        .invite-card {
            background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
            width: 100%; max-width: 800px; border-radius: 20px; padding: 2rem;
            border: 2px solid var(--primary-green); box-shadow: 0 10px 30px rgba(139, 191, 86, 0.2);
            display: flex; flex-direction: column; align-items: center; text-align: center;
            margin-bottom: 3rem; animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:translateY(0); } }

        .invite-title { font-size: 1.4rem; font-weight: 700; color: var(--primary-green); margin-bottom: 0.5rem; }
        .invite-text { font-size: 1rem; color: #555; margin-bottom: 1.5rem; }
        .invite-actions { display: flex; gap: 15px; }
        
        .btn-accept, .btn-reject {
            padding: 10px 25px; border-radius: 50px; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s;
        }
        .btn-accept { background: var(--primary-green); color: white; box-shadow: 0 4px 15px rgba(139, 191, 86, 0.3); }
        .btn-accept:hover { transform: scale(1.05); }
        .btn-reject { background: #fff; border: 1px solid #ccc; color: #666; }
        .btn-reject:hover { background: #f5f5f5; color: var(--danger-red); border-color: var(--danger-red); }


        /* --- MODO COM PERSONAL (Treinos) --- */
        .workout-section { width: 100%; max-width: 800px; text-align: center; }
        
        .personal-badge {
            background: #eef2ff; border: 1px solid #ccdcfc;
            color: var(--primary-blue); padding: 1rem 2rem;
            border-radius: 15px; font-weight: 600; text-align: center;
            margin-bottom: 2rem; display: inline-block;
        }

        .btn-workouts-large {
            display: inline-flex; justify-content: center; align-items: center;
            background: linear-gradient(135deg, var(--primary-green) 0%, #7aad47 100%);
            color: var(--white); border-radius: 50px; padding: 1.5rem 4rem;
            font-size: 1.3rem; font-weight: 700; text-decoration: none;
            box-shadow: 0 10px 30px rgba(139, 191, 86, 0.4);
            transition: all 0.3s;
            gap: 15px;
        }

        .btn-workouts-large:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 40px rgba(139, 191, 86, 0.6);
        }

        .workout-card-link {
            display: flex; justify-content: space-between; align-items: center;
            background: var(--white); border-radius: 20px; padding: 1.5rem;
            margin-bottom: 1rem; text-decoration: none; color: inherit;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid #eee;
            transition: all 0.3s; text-align: left;
        }
        .workout-card-link:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-color: var(--primary-green); }

        /* --- MODO SEM PERSONAL (Busca) --- */
        .status-card {
            background: var(--white); width: 100%; max-width: 800px;
            border-radius: 25px; padding: 3rem;
            box-shadow: 0 15px 40px rgba(3, 10, 140, 0.08);
            text-align: center; position: relative; overflow: hidden;
        }

        .status-card.secondary {
            background: transparent; box-shadow: none; border: 1px dashed #ccc;
            padding: 2rem; margin-top: 3rem;
        }
        .status-card.secondary .status-title { font-size: 1.1rem; color: #888; margin-bottom: 1rem; }
        .status-card.secondary::before { display: none; }
        .status-card.secondary .empty-state-icon { display: none; }

        .status-card::before {
            content: ''; position: absolute; top: 0; left: 0;
            width: 100%; height: 6px;
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-green));
        }

        .empty-state-icon { font-size: 4rem; margin-bottom: 1.5rem; display: block; opacity: 0.8; }
        
        .search-container { position: relative; max-width: 500px; margin: 0 auto 2rem auto; }
        
        .search-input {
            width: 100%; padding: 1.2rem 1.5rem 1.2rem 3.5rem;
            border: 2px solid #eee; border-radius: 50px; font-size: 1rem;
            background-color: #fafafa;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23030A8C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'%3E%3C/circle%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: 20px center;
        }

        .search-btn {
            position: absolute; right: 8px; top: 8px; bottom: 8px;
            background: var(--primary-green); color: var(--white); border: none;
            padding: 0 1.5rem; border-radius: 50px; font-weight: 600; cursor: pointer;
        }

        /* Grid de Sugestões */
        .personals-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px; margin-top: 2rem;
        }

        .personal-card-mini {
            background: #fff; border: 1px solid #eee; border-radius: 15px;
            padding: 15px; display: flex; align-items: center; gap: 15px;
            text-decoration: none; color: inherit; transition: all 0.3s; text-align: left;
        }
        .personal-card-mini:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-color: var(--primary-blue); }

        .mini-avatar {
            width: 50px; height: 50px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), #02065a);
            color: white; display: flex; justify-content: center; align-items: center;
            font-weight: bold; flex-shrink: 0;
        }
        .mini-info h4 { margin: 0; font-size: 1rem; color: var(--primary-blue); }
        .mini-info span { font-size: 0.8rem; color: #888; }

        /* Floating Chat */
        .floating-chat-bar { position: fixed; bottom: 30px; right: 30px; z-index: 200; }
        .floating-chat-btn {
            background: var(--white); color: var(--primary-blue); border: 1px solid #eee;
            padding: 12px 24px; border-radius: 50px; font-weight: 600; cursor: pointer;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08); text-decoration: none;
            display: flex; align-items: center; gap: 8px; transition: all 0.3s;
        }
        .floating-chat-btn:hover { background: var(--primary-blue); color: var(--white); transform: translateY(-3px); }
    </style>
</head>
<body>

    <header class="app-header">
        <img src="logo.png" alt="Logo BeFit" class="logo">
        
        <div class="profile-menu">
            <div class="profile-icon" title="Meu Perfil">
                <?php echo strtoupper(substr($nome_aluno, 0, 1)); ?>
            </div>
            <div class="dropdown-content">
                <div class="dropdown-inner">
                    <a href="perfil_aluno.php">Meus Dados</a>
                    <a href="logout.php" style="color: var(--danger-red);">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        
        <div class="welcome-text">
            <h1>Olá, <?php echo htmlspecialchars($nome_aluno); ?>!</h1>
            <p>Vamos começar sua jornada fitness hoje?</p>
        </div>

        <?php if ($convite_pendente): ?>
            
            <!-- CARD DE CONVITE -->
            <div class="invite-card">
                <div class="invite-title">🎉 Convite Recebido!</div>
                <p class="invite-text">
                    O personal <strong><?php echo htmlspecialchars($convite_pendente['nome_personal']); ?></strong> convidou você para ser aluno(a).
                </p>
                <form method="POST" class="invite-actions">
                    <input type="hidden" name="convite_id" value="<?php echo $convite_pendente['id']; ?>">
                    <input type="hidden" name="personal_id" value="<?php echo $convite_pendente['personal_id']; ?>">
                    
                    <button type="submit" name="acao_convite" value="aceitar" class="btn-accept">Aceitar Convite</button>
                    <button type="submit" name="acao_convite" value="recusar" class="btn-reject">Recusar</button>
                </form>
            </div>

        <?php endif; ?>

        <?php if ($tem_personal): ?>
            
            <!-- MODO 2: ALUNO COM PERSONAL (LINK PARA TREINOS) -->
            <section class="workout-section">
                <div class="personal-badge">
                    Seu Personal: <?php echo htmlspecialchars($nome_personal); ?>
                </div>
                
                <!-- Botão Grande de Ação Principal -->
                <div>
                    <a href="meus_treinos.php" class="btn-workouts-large">
                        <span>💪</span> Ver Meus Treinos
                    </a>
                </div>

                <!-- Lista de Treinos Recentes -->
                <?php if(!empty($meus_treinos)): ?>
                    <div style="margin-top: 2rem; text-align: left;">
                        <p style="margin-bottom: 10px; color: #666; font-weight: 600;">Últimas fichas:</p>
                        <?php foreach($meus_treinos as $treino): ?>
                            <a href="meus_treinos.php" class="workout-card-link">
                                <div>
                                    <div class="wc-title"><?php echo htmlspecialchars($treino['nome_ficha']); ?></div>
                                    <div class="wc-desc"><?php echo htmlspecialchars($treino['descricao'] ?: 'Sem descrição'); ?></div>
                                </div>
                                <div class="wc-arrow">➜</div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Opção discreta para trocar de personal -->
                <div class="status-card secondary">
                    <h2 class="status-title">Buscar outro profissional?</h2>
                    <form action="buscar_personal.php" method="GET" class="search-container" style="margin-bottom: 0;">
                        <input type="text" name="busca" class="search-input" placeholder="Pesquisar..." style="padding: 0.8rem 1.5rem 0.8rem 2.5rem; font-size: 0.9rem;">
                        <button type="submit" class="search-btn" style="padding: 0 1rem; font-size: 0.9rem;">Go</button>
                    </form>
                </div>

            </section>

        <?php else: ?>
            
            <!-- MODO 1: ALUNO SEM PERSONAL (BUSCA) -->
            <?php if (!$convite_pendente): // Só mostra busca se não tiver convite pendente cobrindo a tela ?>
                <section class="status-card">
                    <span class="empty-state-icon">🔍</span>
                    
                    <h2 class="status-title">Sem profissional ainda</h2>
                    <p class="status-subtitle">Encontre um personal trainer para montar seu treino ideal.</p>

                    <form action="buscar_personal.php" method="GET" class="search-container">
                        <input type="text" name="busca" class="search-input" placeholder="Buscar personal por nome ou especialidade...">
                        <button type="submit" class="search-btn">Buscar</button>
                    </form>

                    <p style="margin-top: 2rem; margin-bottom: 1rem; font-weight: 600; color: #555; font-size: 0.95rem;">Sugestões de Profissionais:</p>

                    <div class="personals-grid">
                        <?php if(!empty($lista_personals)): ?>
                            <?php foreach($lista_personals as $personal): ?>
                                <a href="perfil_personal.php?id=<?php echo $personal['id']; ?>" class="personal-card-mini">
                                    <div class="mini-avatar">
                                        <?php echo strtoupper(substr($personal['nome'], 0, 1)); ?>
                                    </div>
                                    <div class="mini-info">
                                        <h4><?php echo htmlspecialchars($personal['nome']); ?></h4>
                                        <span>CREF: <?php echo htmlspecialchars($personal['cref']); ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #999; font-size: 0.9rem;">Nenhum personal encontrado.</p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

        <?php endif; ?>

    </main>

    <div class="floating-chat-bar">
        <a href="chat.php" class="floating-chat-btn">
            <span>💬</span> Suporte
        </a>
    </div>

</body>
</html>