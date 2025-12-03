<?php
session_start();
require_once 'conexao.php';

// 1. Verifica se o usuário está logado e é um aluno
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'aluno') {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// 2. Buscar as Fichas de Treino do Aluno
$sql_fichas = "SELECT id, nome_ficha, descricao, DATE_FORMAT(data_criacao, '%d/%m/%Y') as data_formatada 
               FROM fichas_treino 
               WHERE aluno_id = ? 
               ORDER BY id DESC";
$stmt = $conn->prepare($sql_fichas);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result_fichas = $stmt->get_result();
$fichas = [];
if ($result_fichas->num_rows > 0) {
    while($row = $result_fichas->fetch_assoc()) {
        $fichas[] = $row;
    }
}
$stmt->close();

// Função auxiliar para buscar exercícios e séries de uma ficha
function getExerciciosComSeries($conn, $ficha_id) {
    $exercicios = [];
    
    // Busca Exercícios
    $sql_ex = "SELECT id, nome_exercicio, observacao FROM exercicios WHERE ficha_id = ? ORDER BY ordem ASC";
    $stmt_ex = $conn->prepare($sql_ex);
    $stmt_ex->bind_param("i", $ficha_id);
    $stmt_ex->execute();
    $result_ex = $stmt_ex->get_result();
    
    while($ex = $result_ex->fetch_assoc()) {
        // Busca Séries para este exercício
        $sql_series = "SELECT numero_serie, carga, repeticoes FROM series_exercicio WHERE exercicio_id = ? ORDER BY numero_serie ASC";
        $stmt_series = $conn->prepare($sql_series);
        $stmt_series->bind_param("i", $ex['id']);
        $stmt_series->execute();
        $result_series = $stmt_series->get_result();
        
        $series = [];
        while($s = $result_series->fetch_assoc()) {
            $series[] = $s;
        }
        $ex['series'] = $series;
        $exercicios[] = $ex;
        $stmt_series->close();
    }
    $stmt_ex->close();
    return $exercicios;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Meus Treinos</title>

    <style>
        :root {
            --primary-green: #8BBF56;
            --primary-blue: #030A8C;
            --primary-blue-dark: #02065a;
            --light-gray: #f4f4f4;
            --dark-text: #333;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .app-header .logo {
            width: 110px;
            transition: transform 0.3s ease;
        }

        .app-header .logo:hover {
            transform: scale(1.05);
        }

        /* Main Layout */
        main {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .back-btn {
            text-decoration: none;
            color: #777;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
            padding: 8px 16px;
            border-radius: 25px;
            background: rgba(255,255,255,0.6);
        }

        .back-btn:hover { 
            color: var(--primary-blue);
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Lista de Treinos (Acordeão) */
        .workout-card {
            background: var(--white);
            border-radius: 20px;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid #f0f0f0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .workout-card:hover {
            box-shadow: 0 15px 40px rgba(0,0,0,0.06);
            transform: translateY(-2px);
        }

        .workout-header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            background: #fff;
            border-bottom: 1px solid transparent;
            transition: background 0.3s;
        }

        .workout-header:hover {
            background: #fcfcfc;
        }

        .workout-title h3 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--primary-blue);
            font-weight: 700;
        }

        .workout-meta {
            font-size: 0.85rem;
            color: #888;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-icon {
            font-size: 1.2rem;
            color: var(--primary-green);
            transition: transform 0.3s;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f0f9eb;
            border-radius: 50%;
        }

        /* Estado Aberto */
        .workout-card.open .workout-header {
            border-bottom-color: #eee;
            background: #f8f9fa;
        }

        .workout-card.open .toggle-icon {
            transform: rotate(180deg);
            background: var(--primary-green);
            color: white;
        }

        .workout-body {
            display: none;
            padding: 1.5rem;
            animation: slideDown 0.3s ease-out;
            background: #fcfcfc;
        }

        .workout-card.open .workout-body {
            display: block;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Exercícios */
        .exercise-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px dashed #eee;
        }

        .exercise-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .exercise-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .exercise-number {
            background: var(--primary-blue);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .exercise-name {
            font-weight: 700;
            color: var(--dark-text);
            font-size: 1.05rem;
        }

        .sets-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
        }

        .set-box {
            background: #fff;
            padding: 10px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #eee;
            position: relative;
            transition: all 0.2s;
        }

        .set-label {
            font-size: 0.7rem;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .set-value {
            font-weight: 700;
            color: var(--primary-blue);
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        /* Checkbox Customizado */
        .check-wrapper {
            display: flex;
            justify-content: center;
        }
        
        .check-label {
            cursor: pointer;
            display: block;
        }
        
        .check-circle {
            width: 28px;
            height: 28px;
            border: 2px solid #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.2s;
            background: #f9f9f9;
        }

        input[type="checkbox"]:checked + .check-circle {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(139, 191, 86, 0.3);
        }
        
        input[type="checkbox"] { display: none; }
        
        /* Estado Concluído do Card */
        .set-box.completed {
            border-color: var(--primary-green);
            background: #f0f9eb;
        }

        /* Mensagem de Vazio */
        .no-workouts {
            text-align: center;
            padding: 3rem;
            color: #888;
            font-size: 1.1rem;
            border: 1px dashed #ccc;
            border-radius: 20px;
            background: #fff;
        }

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
    </style>
</head>
<body>

    <header class="app-header">
        <a href="inicio_aluno.php">
            <img src="logo.png" alt="Logo BeFit" class="logo">
        </a>
    </header>

    <main>
        <div class="page-header">
            <h1 class="page-title">Meus Treinos</h1>
            <a href="inicio_aluno.php" class="back-btn">
                <span>&#8592;</span> Voltar
            </a>
        </div>

        <div class="workouts-container">

            <?php if (!empty($fichas)): ?>
                <?php foreach ($fichas as $ficha): 
                    $exercicios = getExerciciosComSeries($conn, $ficha['id']);
                    $qtd_exercicios = count($exercicios);
                ?>
                    <!-- Card de Ficha (Dinâmico) -->
                    <div class="workout-card" onclick="toggleWorkout(this)">
                        <div class="workout-header">
                            <div class="workout-title">
                                <h3><?php echo htmlspecialchars($ficha['nome_ficha']); ?></h3>
                                <div class="workout-meta">
                                    <span><?php echo $qtd_exercicios; ?> Exercícios</span>
                                    <span style="margin-left: 10px;">📅 <?php echo $ficha['data_formatada']; ?></span>
                                </div>
                            </div>
                            <div class="toggle-icon">▼</div>
                        </div>
                        <div class="workout-body" onclick="event.stopPropagation()">
                            
                            <?php if (!empty($exercicios)): ?>
                                <?php foreach ($exercicios as $index => $exercicio): ?>
                                    <div class="exercise-item">
                                        <div class="exercise-header">
                                            <div class="exercise-number"><?php echo $index + 1; ?></div>
                                            <div class="exercise-name"><?php echo htmlspecialchars($exercicio['nome_exercicio']); ?></div>
                                        </div>
                                        
                                        <?php if (!empty($exercicio['observacao'])): ?>
                                            <p style="color: #666; font-size: 0.9rem; margin-bottom: 10px; padding-left: 34px;">
                                                <i>Nota: <?php echo htmlspecialchars($exercicio['observacao']); ?></i>
                                            </p>
                                        <?php endif; ?>

                                        <div class="sets-container">
                                            <?php foreach ($exercicio['series'] as $serie): ?>
                                                <div class="set-box">
                                                    <div class="set-label">Série <?php echo $serie['numero_serie']; ?></div>
                                                    <div class="set-value">
                                                        <?php echo $serie['repeticoes']; ?> reps / <?php echo floatval($serie['carga']); ?>kg
                                                    </div>
                                                    <div class="check-wrapper">
                                                        <label class="check-label">
                                                            <input type="checkbox" onchange="toggleSet(this)">
                                                            <div class="check-circle">✓</div>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="text-align:center; color:#999;">Nenhum exercício cadastrado nesta ficha.</p>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-workouts">
                    <h3>Você ainda não possui fichas de treino cadastradas.</h3>
                    <p>Aguarde seu personal criar seu plano de treino.</p>
                </div>
            <?php endif; ?>

        </div>

    </main>

    <div class="floating-chat-bar">
        <a href="chat.php" class="floating-chat-btn">💬 Suporte Personal</a>
    </div>

    <script>
        function toggleWorkout(card) {
            // Fecha outros se quiser efeito acordeão único (opcional)
            // document.querySelectorAll('.workout-card').forEach(c => {
            //     if(c !== card) c.classList.remove('open');
            // });
            card.classList.toggle('open');
        }

        function toggleSet(checkbox) {
            const box = checkbox.closest('.set-box');
            if (checkbox.checked) {
                box.classList.add('completed');
            } else {
                box.classList.remove('completed');
            }
        }
    </script>

</body>
</html>