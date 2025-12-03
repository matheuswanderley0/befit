<?php
session_start();
require_once 'conexao.php';

// 1. Verifica se é Personal
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'personal') {
    header("Location: index.php");
    exit();
}

// 2. Verifica se passou o ID do aluno na URL
if (!isset($_GET['id'])) {
    // Se não tiver ID, redireciona para o início do personal
    header("Location: inicio_personal.php");
    exit();
}

$aluno_id = intval($_GET['id']);
$personal_id = $_SESSION['usuario_id'];

// 3. Processar Atualização de Peso (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['novo_peso'])) {
    $novo_peso = floatval($_POST['novo_peso']);
    if ($novo_peso > 0) {
        // Atualiza histórico
        $sql_hist = "INSERT INTO historico_peso (aluno_id, peso, data_registro) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql_hist);
        $stmt->bind_param("id", $aluno_id, $novo_peso);
        $stmt->execute();
        $stmt->close();

        // Atualiza perfil atual
        $sql_up = "UPDATE perfil_aluno SET peso_atual = ? WHERE usuario_id = ?";
        $stmt = $conn->prepare($sql_up);
        $stmt->bind_param("di", $novo_peso, $aluno_id);
        $stmt->execute();
        $stmt->close();
        
        // Recarrega para evitar reenvio
        header("Location: perfil_cliente.php?id=" . $aluno_id);
        exit();
    }
}

// 4. Buscar Dados do Aluno (Verifica vínculo com o personal logado)
$sql_aluno = "SELECT u.nome, u.email, pa.objetivo, pa.peso_atual, pa.altura, pa.genero 
              FROM usuarios u 
              JOIN perfil_aluno pa ON u.id = pa.usuario_id 
              WHERE u.id = ? AND pa.personal_id = ?";

$stmt = $conn->prepare($sql_aluno);
$stmt->bind_param("ii", $aluno_id, $personal_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Aluno não encontrado ou não pertence a este personal
    echo "<div style='padding:50px; text-align:center;'>
            <h2>Aluno não encontrado</h2>
            <p>Este aluno não existe ou não está vinculado a você.</p>
            <a href='inicio_personal.php'>Voltar ao Início</a>
          </div>";
    exit();
}

$aluno = $result->fetch_assoc();
$stmt->close();

// 5. Buscar Histórico de Peso (Para o Gráfico)
$sql_grafico = "SELECT peso, DATE_FORMAT(data_registro, '%d/%m') as data_fmt 
                FROM historico_peso 
                WHERE aluno_id = ? 
                ORDER BY data_registro ASC LIMIT 10"; // Pega os últimos 10 registros
$stmt = $conn->prepare($sql_grafico);
$stmt->bind_param("i", $aluno_id);
$stmt->execute();
$res_grafico = $stmt->get_result();

$labels_grafico = [];
$dados_grafico = [];

while($row = $res_grafico->fetch_assoc()) {
    $labels_grafico[] = $row['data_fmt'];
    $dados_grafico[] = $row['peso'];
}
$stmt->close();

// 6. Buscar Fichas de Treino
$sql_fichas = "SELECT id, nome_ficha, descricao, DATE_FORMAT(data_criacao, '%d/%m/%Y') as data_fmt 
               FROM fichas_treino 
               WHERE aluno_id = ? 
               ORDER BY id DESC";
$stmt = $conn->prepare($sql_fichas);
$stmt->bind_param("i", $aluno_id);
$stmt->execute();
$res_fichas = $stmt->get_result();
$fichas = [];
while($row = $res_fichas->fetch_assoc()) {
    $fichas[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar <?php echo htmlspecialchars($aluno['nome']); ?> - BeFit</title>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            position: absolute;
            right: 2rem;
            top: 0; height: 100%;
            display: flex; align-items: center;
        }

        .app-header .profile-icon {
            width: 42px; height: 42px;
            background: var(--primary-blue);
            border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            color: white; font-weight: bold;
            cursor: pointer;
        }

        /* Main Layout */
        main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .back-link-container { margin-bottom: 1.5rem; }
        .back-btn {
            text-decoration: none; color: #777; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
            transition: all 0.3s ease; padding: 8px 16px;
            border-radius: 25px; background: rgba(255,255,255,0.6); width: fit-content;
        }
        .back-btn:hover { color: var(--primary-blue); background: var(--white); box-shadow: 0 2px 10px rgba(0,0,0,0.05); }

        /* User Header */
        .user-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 2rem; background: var(--white); padding: 2rem;
            border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
        }

        .user-info { display: flex; align-items: center; gap: 25px; }
        
        .user-avatar {
            width: 90px; height: 90px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), #02065a);
            color: var(--white); display: flex; justify-content: center; align-items: center;
            font-size: 2rem; font-weight: 700; box-shadow: 0 5px 15px rgba(3, 10, 140, 0.2);
        }

        .user-details h1 { font-size: 1.8rem; margin: 0 0 8px 0; color: var(--dark-text); }
        .user-meta { display: flex; gap: 15px; font-size: 0.95rem; color: #666; }
        .user-meta span { display: flex; align-items: center; gap: 5px; }

        .action-btn {
            padding: 0.8rem 1.5rem; border-radius: 50px; text-decoration: none;
            font-size: 0.95rem; font-weight: 600; transition: all 0.2s;
            border: 1px solid var(--primary-blue); background: var(--white);
            color: var(--primary-blue); display: flex; align-items: center; gap: 8px;
        }
        .action-btn:hover { background: var(--primary-blue); color: var(--white); }

        /* Grid */
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }

        .dash-card {
            background: var(--white); border-radius: 20px; padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04); margin-bottom: 2rem;
            border: 1px solid #f0f0f0; position: relative;
        }

        .card-title {
            font-size: 1.3rem; font-weight: 700; color: var(--primary-blue);
            margin-bottom: 1.5rem; display: flex; justify-content: space-between;
            align-items: center; border-bottom: 1px solid #eee; padding-bottom: 1rem;
        }

        .chart-container { position: relative; height: 300px; width: 100%; }

        .update-weight-form {
            display: flex; gap: 10px; margin-top: 1.5rem;
            padding-top: 1.5rem; border-top: 1px solid #f0f0f0; align-items: center;
        }

        .weight-input {
            padding: 0.7rem 1rem; border: 1px solid #ddd; border-radius: 10px;
            width: 140px; font-size: 1rem;
        }

        .weight-btn {
            background: var(--primary-green); color: var(--white); border: none;
            padding: 0.7rem 1.5rem; border-radius: 10px; font-weight: 600;
            cursor: pointer; transition: background 0.3s;
        }
        .weight-btn:hover { background: #7aad47; }

        /* Workout List */
        .workout-list { display: flex; flex-direction: column; gap: 1rem; }

        .workout-item {
            background: #fff; border: 1px solid #e0e0e0; border-radius: 15px;
            padding: 1.2rem 1.5rem; display: flex; justify-content: space-between;
            align-items: center; transition: all 0.3s; position: relative; overflow: hidden;
        }

        .workout-item::before {
            content: ''; position: absolute; left: 0; top: 0;
            height: 100%; width: 5px; background: var(--primary-green);
        }

        .workout-item:hover {
            transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .workout-info h3 { font-size: 1.1rem; margin: 0 0 5px 0; color: var(--dark-text); font-weight: 700; }
        .workout-info p { font-size: 0.85rem; color: #888; margin: 0; }

        .workout-actions { display: flex; gap: 10px; }

        .icon-btn {
            background: #f4f4f4; border: none; cursor: pointer; font-size: 1rem;
            padding: 8px; border-radius: 8px; color: #666; transition: all 0.2s;
            text-decoration: none; display: flex; align-items: center; justify-content: center;
        }
        .icon-btn:hover { background: #e0e0e0; color: var(--dark-text); }
        .icon-btn.delete:hover { background: #ffe5e5; color: var(--danger-red); }

        .add-workout-btn {
            width: 100%; padding: 1.2rem; border: 2px dashed var(--primary-blue);
            border-radius: 15px; background: #f0f4ff; color: var(--primary-blue);
            font-weight: 700; cursor: pointer; transition: all 0.3s; margin-top: 1rem;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            text-decoration: none;
        }
        .add-workout-btn:hover { background: var(--primary-blue); color: var(--white); border-style: solid; }

        /* Side Stats */
        .quick-stats { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        
        .stat-card {
            background: var(--white); padding: 1.5rem; border-radius: 15px;
            text-align: center; border: 1px solid #f0f0f0; box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        }
        .stat-value { font-size: 2rem; font-weight: 800; color: var(--primary-blue); margin-bottom: 5px; }
        .stat-label { font-size: 0.9rem; color: #666; }

        /* Floating Chat */
        .floating-chat-bar { position: fixed; bottom: 30px; right: 30px; z-index: 200; }
        .floating-chat-btn {
            background: var(--white); color: var(--primary-blue); border: 1px solid #eee;
            padding: 12px 24px; border-radius: 50px; font-weight: 600; font-size: 0.95rem;
            cursor: pointer; box-shadow: 0 5px 20px rgba(0,0,0,0.08); text-decoration: none;
            display: flex; align-items: center; gap: 8px; transition: all 0.3s;
        }
        .floating-chat-btn:hover { background: var(--primary-blue); color: var(--white); transform: translateY(-3px); }

        @media (max-width: 900px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            .user-header { flex-direction: column; align-items: center; text-align: center; gap: 20px; }
            .user-info { flex-direction: column; gap: 15px; }
            .user-meta { justify-content: center; }
        }
    </style>
</head>
<body>

    <header class="app-header">
        <a href="inicio_personal.php">
            <img src="logo.png" alt="Logo BeFit" class="logo">
        </a>
        <div class="profile-menu">
            <div class="profile-icon" title="Meu Perfil">P</div>
        </div>
    </header>

    <main>
        
        <div class="back-link-container">
            <a href="inicio_personal.php" class="back-btn">
                <span>&#8592;</span> Voltar para Lista de Alunos
            </a>
        </div>

        <!-- Cabeçalho do Aluno -->
        <div class="user-header">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($aluno['nome'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h1><?php echo htmlspecialchars($aluno['nome']); ?></h1>
                    <div class="user-meta">
                        <span>🎯 <?php echo htmlspecialchars($aluno['objetivo']); ?></span>
                        <span>⚖️ <?php echo htmlspecialchars($aluno['peso_atual']); ?> kg</span>
                    </div>
                </div>
            </div>
            <a href="chat.php?contato=<?php echo $aluno_id; ?>" class="action-btn">
                <span>✉️</span> Enviar Mensagem
            </a>
        </div>

        <div class="dashboard-grid">
            
            <!-- Coluna Principal -->
            <div class="main-col">
                
                <!-- Gráfico de Evolução -->
                <div class="dash-card">
                    <div class="card-title">
                        Progresso do Aluno
                        <span style="font-size:0.8rem; color:#999; font-weight:400; background:#f5f5f5; padding:4px 10px; border-radius:20px;">Editável</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="weightChart"></canvas>
                    </div>
                    
                    <!-- Form de Atualização de Peso -->
                    <form method="POST" class="update-weight-form">
                        <input type="number" name="novo_peso" class="weight-input" placeholder="Novo Peso (kg)" step="0.1" required>
                        <button type="submit" class="weight-btn">Registrar Peso</button>
                    </form>
                </div>

                <!-- Gestão de Fichas -->
                <div class="dash-card">
                    <div class="card-title">Fichas de Treino Ativas</div>
                    
                    <div class="workout-list">
                        <?php if(!empty($fichas)): ?>
                            <?php foreach($fichas as $ficha): ?>
                                <div class="workout-item">
                                    <div class="workout-info">
                                        <h3><?php echo htmlspecialchars($ficha['nome_ficha']); ?></h3>
                                        <p>Criado em: <?php echo $ficha['data_fmt']; ?></p>
                                    </div>
                                    <div class="workout-actions">
                                        <!-- Link para Editar (Futuro: criar_ficha.php?id=X) -->
                                        <a href="#" class="icon-btn hover-green" title="Editar Ficha">✏️</a>
                                        <!-- Botão Remover (Lógica JS ou PHP) -->
                                        <button class="icon-btn delete" onclick="removeWorkout(this)" title="Excluir Ficha">🗑️</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="text-align:center; color:#999;">Nenhuma ficha criada para este aluno.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Link para criar nova ficha passando ID do aluno -->
                    <a href="criar_ficha.php?aluno_id=<?php echo $aluno_id; ?>" class="add-workout-btn">
                        <span>+</span> Criar Nova Ficha de Treino
                    </a>
                </div>

            </div>

            <!-- Coluna Lateral -->
            <aside class="side-col">
                <div class="quick-stats">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo htmlspecialchars($aluno['peso_atual']); ?> kg</div>
                        <div class="stat-label">Peso Atual</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo count($fichas); ?></div>
                        <div class="stat-label">Fichas Ativas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" style="color: var(--primary-green);">Ativo</div>
                        <div class="stat-label">Status da Assinatura</div>
                    </div>
                </div>
            </aside>

        </div>

    </main>

    <div class="floating-chat-bar">
        <a href="chat.php" class="floating-chat-btn">Chat</a>
    </div>

    <!-- Scripts -->
    <script>
        // --- Gráfico Chart.js com dados do PHP ---
        const ctx = document.getElementById('weightChart').getContext('2d');
        
        // Dados vindos do PHP (arrays)
        const labels = <?php echo json_encode($labels_grafico); ?>;
        const data = <?php echo json_encode($dados_grafico); ?>;

        const weightChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Peso (kg)',
                    data: data,
                    borderColor: '#030A8C',
                    backgroundColor: 'rgba(3, 10, 140, 0.05)',
                    borderWidth: 3,
                    pointBackgroundColor: '#8BBF56',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: false, grid: { color: '#f5f5f5' } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });

        function removeWorkout(btn) {
            if(confirm("Tem certeza que deseja EXCLUIR esta ficha do aluno?")) {
                // Aqui você faria uma requisição AJAX ou redirecionaria para excluir_ficha.php
                const item = btn.closest('.workout-item');
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
            }
        }
    </script>

</body>
</html>