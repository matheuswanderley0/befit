<?php
session_start();
require_once 'conexao.php';

// Verifica se o ID do personal foi passado na URL
if (!isset($_GET['id'])) {
    // Se não tiver ID, redireciona para a busca ou mostra erro
    header("Location: buscar_personal.php");
    exit();
}

$personal_id = intval($_GET['id']);

// 1. Buscar Dados do Personal
$sql_personal = "SELECT u.nome, pp.cref, pp.biografia, pp.cidade, pp.experiencia_anos 
                 FROM usuarios u 
                 JOIN perfil_personal pp ON u.id = pp.usuario_id 
                 WHERE u.id = ? AND u.tipo = 'personal'";

$stmt = $conn->prepare($sql_personal);
$stmt->bind_param("i", $personal_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Personal não encontrado.";
    exit();
}

$personal = $result->fetch_assoc();
$stmt->close();

// 2. Buscar Especialidades
$sql_specs = "SELECT e.nome 
              FROM especialidades e 
              JOIN personal_especialidades pe ON e.id = pe.especialidade_id 
              WHERE pe.personal_id = ?";
$stmt_specs = $conn->prepare($sql_specs);
$stmt_specs->bind_param("i", $personal_id);
$stmt_specs->execute();
$res_specs = $stmt_specs->get_result();
$especialidades = [];
while($s = $res_specs->fetch_assoc()) {
    $especialidades[] = $s['nome'];
}
$stmt_specs->close();
$conn->close();

// Dados simulados para compor a tela (enquanto não temos tabelas específicas para isso)
$rating = "4.9"; // Poderia vir de uma tabela de avaliações
$avaliacoes_count = 24;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($personal['nome']); ?> - BeFit</title>

    <style>
        :root {
            --primary-green: #8BBF56;
            --primary-blue: #030A8C;
            --primary-blue-dark: #02065a;
            --light-gray: #f4f4f4;
            --dark-text: #333;
            --white: #ffffff;
            --danger-red: #ff4757;
            --star-yellow: #ffc107;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            /* Mantive o background animado que você gostou para a visualização do perfil */
            background: linear-gradient(-45deg, #f8f9fa, #e9ecef, #f0f4f8, #e6eaf0);
            background-size: 400% 400%;
            animation: gradientFlow 15s ease infinite;
            padding-bottom: 100px;
            min-height: 100vh;
            color: var(--dark-text);
            overflow-x: hidden;
        }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Header Clean (Estilo consistente com o Dashboard) */
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
            top: 50%;
            transform: translateY(-50%);
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
        }

        .app-header .profile-icon:hover {
            transform: scale(1.05);
            background: var(--primary-blue-dark);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 55px;
            background: var(--white);
            min-width: 180px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 10px;
            z-index: 101;
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
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .back-link-container {
            margin-bottom: 1.5rem;
        }

        .back-btn {
            text-decoration: none;
            color: #777;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 25px;
            background: rgba(255,255,255,0.6);
            width: fit-content;
        }

        .back-btn:hover {
            color: var(--primary-blue);
            background: var(--white);
            transform: translateX(-5px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Profile Card Container */
        .profile-container {
            background: var(--white);
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
            position: relative;
        }

        /* Cover & Avatar Area */
        .profile-header {
            background: linear-gradient(135deg, var(--primary-blue), #02065a);
            height: 150px;
            position: relative;
            overflow: hidden;
        }

        .profile-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmerHeader 3s infinite;
        }

        @keyframes shimmerHeader {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .profile-content {
            padding: 0 2.5rem 2.5rem 2.5rem;
            position: relative;
        }

        .avatar-wrapper {
            position: absolute;
            top: -75px;
            left: 2.5rem;
            border: 5px solid var(--white);
            border-radius: 50%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: var(--white);
        }

        .avatar-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue) 0%, #02065a 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 3rem;
            font-weight: 700;
            color: var(--white);
            /* background-image: url('...');  // Se tivesse foto real */
            background-size: cover;
            background-position: center;
        }

        .header-info {
            padding-left: 170px; /* Espaço para o avatar */
            padding-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
        }

        .name-area h1 {
            font-size: 1.8rem;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .cref-badge {
            background: #f0f4ff;
            color: var(--primary-blue);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .rating-area {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 1rem;
            color: #666;
        }

        .stars {
            color: var(--star-yellow);
        }

        /* Botão de Chat no Header */
        .chat-header-btn {
            background: var(--primary-green);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(139, 191, 86, 0.3);
        }

        .chat-header-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(139, 191, 86, 0.4);
            background: #7aad47;
        }

        /* Body Content */
        .profile-body {
            margin-top: 4rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2.5rem;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-green);
            padding-left: 10px;
        }

        .bio-text {
            color: #555;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .tags-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 2rem;
        }

        .tag-pill {
            background: #f8f9fa;
            border: 1px solid #eee;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #555;
            font-weight: 500;
        }

        /* Plans Cards (Consistente com o Gerenciar) */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 2.5rem;
        }

        .plan-card {
            border: 1px solid #eee;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s;
            background: #fafafa;
            position: relative;
            overflow: hidden;
        }

        .plan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--primary-green));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            background: var(--white);
        }

        .plan-card:hover::before {
            opacity: 1;
        }

        .plan-name {
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .plan-price {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary-blue);
            margin-bottom: 10px;
        }

        .plan-desc {
            font-size: 0.85rem;
            color: #777;
            line-height: 1.4;
        }

        /* Reviews */
        .review-item {
            background: #fbfbfb;
            padding: 1.2rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 1px solid #f0f0f0;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .review-text {
            font-size: 0.9rem;
            color: #666;
            font-style: italic;
        }

        /* Side Info */
        .info-box {
            background: #fcfcfc;
            padding: 1.5rem;
            border-radius: 15px;
            border: 1px solid #f0f0f0;
            margin-bottom: 1.5rem;
        }

        .info-item {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f5f5f5;
        }

        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-label {
            font-size: 0.85rem;
            color: #999;
            display: block;
            margin-bottom: 2px;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark-text);
        }

        /* Floating Action Button (Mobile style but visible on desktop too) */
        .cta-container {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(255,255,255,0.95);
            padding: 1rem 2rem;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.05);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99;
            backdrop-filter: blur(5px);
        }

        .btn-chat-large {
            background: linear-gradient(135deg, var(--primary-green) 0%, #7aad47 100%);
            color: var(--white);
            text-decoration: none;
            padding: 1rem 4rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            box-shadow: 0 8px 25px rgba(139, 191, 86, 0.4);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
            cursor: pointer;
        }

        .btn-chat-large:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(139, 191, 86, 0.5);
        }

        @media (max-width: 768px) {
            .profile-body {
                grid-template-columns: 1fr;
            }
            .header-info {
                padding-left: 0;
                padding-top: 80px; /* Space for avatar */
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .avatar-wrapper {
                left: 50%;
                transform: translateX(-50%);
            }
            .plans-grid {
                grid-template-columns: 1fr;
            }
            .chat-header-btn {
                display: none; /* Esconde no topo em mobile, pois tem o flutuante */
            }
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
                <?php echo isset($_SESSION['usuario_nome']) ? strtoupper(substr($_SESSION['usuario_nome'], 0, 1)) : 'A'; ?>
            </div>
            <div class="dropdown-content">
                <a href="perfil_aluno.php">Meus Dados</a>
                <a href="logout.php" style="color: var(--danger-red);">Sair</a>
            </div>
        </div>
    </header>

    <main>
        <div class="back-link-container">
            <a href="buscar_personal.php" class="back-btn">
                <span>&#8592;</span> Voltar para a busca
            </a>
        </div>

        <article class="profile-container">
            
            <!-- Capa e Header -->
            <div class="profile-header"></div>
            
            <div class="profile-content">
                <div class="avatar-wrapper">
                    <div class="avatar-large">
                        <?php echo strtoupper(substr($personal['nome'], 0, 1)); ?>
                    </div>
                </div>

                <div class="header-info">
                    <div class="name-area">
                        <h1><?php echo htmlspecialchars($personal['nome']); ?></h1>
                        <span class="cref-badge">CREF: <?php echo htmlspecialchars($personal['cref']); ?></span>
                    </div>
                    <div class="rating-area">
                        <div class="stars">★★★★★</div>
                        <span><?php echo $rating; ?> (<?php echo $avaliacoes_count; ?> avaliações)</span>
                    </div>
                    
                    <!-- Botão de Chat no Header (Visível em Desktop) -->
                    <form action="chat.php" method="GET">
                        <input type="hidden" name="contato" value="<?php echo $personal_id; ?>">
                        <button type="submit" class="chat-header-btn">
                            <span>💬</span> Enviar Mensagem
                        </button>
                    </form>
                </div>

                <div class="profile-body">
                    
                    <!-- Coluna Esquerda (Principal) -->
                    <div class="main-info">
                        <h3 class="section-title">Sobre mim</h3>
                        <p class="bio-text">
                            <?php echo nl2br(htmlspecialchars($personal['biografia'] ?: 'Sem biografia disponível.')); ?>
                        </p>

                        <h3 class="section-title">Especialidades</h3>
                        <div class="tags-cloud">
                            <?php foreach($especialidades as $spec): ?>
                                <span class="tag-pill"><?php echo htmlspecialchars($spec); ?></span>
                            <?php endforeach; ?>
                        </div>

                        <h3 class="section-title">Planos & Consultorias</h3>
                        <div class="plans-grid">
                            <!-- Cards Estáticos para Exemplo (Futuramente virão do BD) -->
                            <div class="plan-card">
                                <div class="plan-name">Mensal</div>
                                <div class="plan-price">R$ 150</div>
                                <div class="plan-desc">Treino personalizado + Suporte via chat</div>
                            </div>
                            <div class="plan-card">
                                <div class="plan-name">Trimestral</div>
                                <div class="plan-price">R$ 400</div>
                                <div class="plan-desc">Economize R$ 50. Avaliação inclusa.</div>
                            </div>
                            <div class="plan-card">
                                <div class="plan-name">Presencial</div>
                                <div class="plan-price">R$ 80/h</div>
                                <div class="plan-desc">Acompanhamento na academia.</div>
                            </div>
                        </div>

                        <h3 class="section-title">O que dizem os alunos</h3>
                        <div class="reviews-list">
                            <div class="review-item">
                                <div class="review-header">
                                    <span>Ana Paula</span>
                                    <span class="stars">★★★★★</span>
                                </div>
                                <p class="review-text">"Excelente profissional! Muito atencioso."</p>
                            </div>
                            <div class="review-item">
                                <div class="review-header">
                                    <span>João Vitor</span>
                                    <span class="stars">★★★★☆</span>
                                </div>
                                <p class="review-text">"Ótimo acompanhamento, recomendo."</p>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna Direita (Detalhes) -->
                    <aside class="side-info">
                        <div class="info-box">
                            <div class="info-item">
                                <span class="info-label">Experiência</span>
                                <span class="info-value"><?php echo htmlspecialchars($personal['experiencia_anos']); ?> Anos</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Localização</span>
                                <span class="info-value"><?php echo htmlspecialchars($personal['cidade']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Alunos Ativos</span>
                                <span class="info-value"><?php echo $avaliacoes_count; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Atendimento</span>
                                <span class="info-value">Online & Presencial</span>
                            </div>
                        </div>
                    </aside>

                </div>
            </div>
        </article>
    </main>

    <!-- Botão de Ação Flutuante -->
    <div class="cta-container">
        <form action="chat.php" method="GET" style="width: 100%; max-width: 300px;">
            <input type="hidden" name="contato" value="<?php echo $personal_id; ?>">
            <button type="submit" class="btn-chat-large" style="width: 100%; justify-content: center;">
                <span>💬</span> Chamar no Chat
            </button>
        </form>
    </div>

</body>
</html>