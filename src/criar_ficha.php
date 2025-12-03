<?php
session_start();
require_once 'conexao.php';

// 1. Verifica se é Personal
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'personal') {
    header("Location: index.php");
    exit();
}

// 2. Verifica se tem o ID do aluno
if (!isset($_GET['aluno_id'])) {
    die("Aluno não especificado.");
}

$personal_id = $_SESSION['usuario_id'];
$aluno_id = intval($_GET['aluno_id']);

// 3. Processar o Formulário (Salvar no Banco)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_ficha = $_POST['nome_ficha'];
    $descricao = $_POST['descricao'];
    
    // Inicia transação para garantir que salva tudo ou nada
    $conn->begin_transaction();

    try {
        // A. Inserir a Ficha
        $sql_ficha = "INSERT INTO fichas_treino (aluno_id, personal_id, nome_ficha, descricao, data_criacao) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql_ficha);
        $stmt->bind_param("iiss", $aluno_id, $personal_id, $nome_ficha, $descricao);
        $stmt->execute();
        $ficha_id = $conn->insert_id;
        $stmt->close();

        // B. Inserir Exercícios e Séries
        if (isset($_POST['exercicios']) && is_array($_POST['exercicios'])) {
            foreach ($_POST['exercicios'] as $index => $ex) {
                $nome_exercicio = $ex['nome'];
                $ordem = $index + 1; // Ordem sequencial
                
                // Insere Exercício
                $sql_ex = "INSERT INTO exercicios (ficha_id, nome_exercicio, ordem) VALUES (?, ?, ?)";
                $stmt_ex = $conn->prepare($sql_ex);
                $stmt_ex->bind_param("isi", $ficha_id, $nome_exercicio, $ordem);
                $stmt_ex->execute();
                $exercicio_id = $conn->insert_id;
                $stmt_ex->close();

                // Insere Séries deste Exercício
                if (isset($ex['series']) && is_array($ex['series'])) {
                    $sql_serie = "INSERT INTO series_exercicio (exercicio_id, numero_serie, carga, repeticoes) VALUES (?, ?, ?, ?)";
                    $stmt_serie = $conn->prepare($sql_serie);
                    
                    foreach ($ex['series'] as $idx_serie => $serie) {
                        $num_serie = $idx_serie + 1;
                        $carga = floatval($serie['carga']);
                        $reps = intval($serie['reps']);
                        
                        $stmt_serie->bind_param("iidi", $exercicio_id, $num_serie, $carga, $reps);
                        $stmt_serie->execute();
                    }
                    $stmt_serie->close();
                }
            }
        }

        $conn->commit();
        // Redireciona de volta para o perfil do aluno
        header("Location: perfil_cliente.php?id=" . $aluno_id);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro ao salvar treino: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Criar Treino</title>

    <style>
        :root {
            --primary-green: #8BBF56;
            --primary-blue: #030A8C;
            --light-gray: #f4f4f4;
            --dark-text: #333;
            --white: #ffffff;
            --danger-red: #ff4757;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7fa; padding-bottom: 100px; min-height: 100vh;
            color: var(--dark-text);
        }

        /* Header Simplificado */
        .app-header {
            background: var(--white); padding: 1rem 2rem; display: flex;
            justify-content: center; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky; top: 0; z-index: 100;
        }
        .app-header .logo { width: 100px; }

        /* Main */
        main { max-width: 800px; margin: 0 auto; padding: 2rem 1rem; }

        /* Cabeçalho da Ficha */
        .workout-header {
            background: var(--white); padding: 2rem; border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); margin-bottom: 2rem;
        }

        .header-input {
            width: 100%; padding: 0.8rem; border: 1px solid #e0e0e0;
            border-radius: 8px; font-size: 1.2rem; font-weight: 700;
            margin-bottom: 1rem; color: var(--primary-blue);
        }

        .header-textarea {
            width: 100%; padding: 0.8rem; border: 1px solid #e0e0e0;
            border-radius: 8px; font-size: 0.95rem; resize: vertical; min-height: 80px;
        }

        /* Lista de Exercícios */
        .exercise-list { display: flex; flex-direction: column; gap: 1.5rem; }

        .exercise-card {
            background: var(--white); border-radius: 15px; padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); border-left: 5px solid var(--primary-green);
            position: relative; animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .exercise-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;
        }

        .exercise-title-input {
            font-size: 1.1rem; font-weight: 600; border: none;
            border-bottom: 1px dashed #ccc; width: 70%; padding: 5px; color: var(--dark-text);
        }

        .remove-exercise-btn {
            color: #999; background: none; border: none; cursor: pointer;
            font-size: 1.5rem; line-height: 1;
        }
        .remove-exercise-btn:hover { color: var(--danger-red); }

        /* Tabela de Séries */
        .sets-table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .sets-table th {
            text-align: center; font-size: 0.8rem; color: #888; text-transform: uppercase; padding-bottom: 10px;
        }
        .set-row td { padding: 5px; text-align: center; }
        .set-number { font-weight: 700; color: #aaa; width: 30px; }

        .set-input {
            width: 100%; padding: 8px; border: 1px solid #eee; border-radius: 8px;
            text-align: center; font-size: 1rem; background: #fafafa;
        }
        .set-input:focus { background: var(--white); border-color: var(--primary-blue); outline: none; }

        .delete-set-btn { background: none; border: none; color: #ddd; cursor: pointer; font-size: 1.2rem; }
        .delete-set-btn:hover { color: var(--danger-red); }

        .add-set-btn {
            width: 100%; padding: 8px; background: #f0f4ff; color: var(--primary-blue);
            border: none; border-radius: 8px; font-weight: 600; cursor: pointer;
            font-size: 0.9rem; transition: background 0.2s;
        }
        .add-set-btn:hover { background: #e0eaff; }

        /* Botão Adicionar Exercício */
        .add-exercise-block-btn {
            width: 100%; padding: 1.2rem; background: transparent;
            border: 2px dashed var(--primary-blue); color: var(--primary-blue);
            border-radius: 15px; font-weight: 700; font-size: 1rem; cursor: pointer;
            margin-top: 2rem; transition: all 0.3s;
        }
        .add-exercise-block-btn:hover { background: #f0f4ff; transform: translateY(-2px); }

        /* Footer Actions */
        .action-footer {
            position: fixed; bottom: 0; left: 0; width: 100%; background: var(--white);
            padding: 1rem 2rem; box-shadow: 0 -5px 20px rgba(0,0,0,0.05);
            display: flex; justify-content: flex-end; gap: 1rem; z-index: 100;
        }

        .btn {
            padding: 0.8rem 2rem; border-radius: 50px; font-weight: 700; cursor: pointer;
            border: none; font-size: 1rem; text-decoration: none;
        }
        .btn-cancel { background: transparent; color: #777; border: 1px solid #eee; }
        .btn-save { background: linear-gradient(135deg, var(--primary-green) 0%, #7aad47 100%); color: var(--white); box-shadow: 0 4px 15px rgba(139, 191, 86, 0.3); }

        @media (max-width: 600px) {
            .header-input { font-size: 1.1rem; }
        }
    </style>
</head>
<body>

    <header class="app-header">
        <img src="logo.png" alt="Logo BeFit" class="logo">
    </header>

    <main>
        <!-- O formulário envolve tudo para enviar os dados -->
        <form id="workoutForm" method="POST" action="criar_ficha.php?aluno_id=<?php echo $aluno_id; ?>">
            
            <!-- Dados Gerais do Treino -->
            <section class="workout-header">
                <input type="text" name="nome_ficha" class="header-input" placeholder="Nome do Treino (ex: Treino A - Peito)" required>
                <textarea name="descricao" class="header-textarea" placeholder="Notas do treino (opcional)..."></textarea>
            </section>

            <!-- Lista de Exercícios -->
            <div id="exercise-container" class="exercise-list">
                <!-- Os exercícios serão adicionados aqui via JS -->
            </div>

            <button type="button" class="add-exercise-block-btn" onclick="addExerciseBlock()">
                + Adicionar Exercício
            </button>

        </form>
    </main>

    <footer class="action-footer">
        <a href="perfil_cliente.php?id=<?php echo $aluno_id; ?>" class="btn btn-cancel">Cancelar</a>
        <!-- Mudança aqui: Onclick chama submitForm() -->
        <button type="button" onclick="submitForm()" class="btn btn-save">Salvar Treino</button>
    </footer>

    <script>
        let exerciseCount = 0;

        // Função para submeter o formulário manualmente via JS
        function submitForm() {
            // Validação simples
            const nomeFicha = document.querySelector('input[name="nome_ficha"]').value;
            if (!nomeFicha) {
                alert("Por favor, dê um nome ao treino.");
                return;
            }
            
            // Submete o formulário
            document.getElementById('workoutForm').submit();
        }

        // Template para uma nova linha de série
        function createSetRow(exerciseIndex, setNum, setIndex) {
            return `
                <tr class="set-row">
                    <td class="set-number">${setNum}</td>
                    <td><input type="number" name="exercicios[${exerciseIndex}][series][${setIndex}][carga]" class="set-input" placeholder="kg" step="0.5"></td>
                    <td><input type="number" name="exercicios[${exerciseIndex}][series][${setIndex}][reps]" class="set-input" placeholder="reps"></td>
                    <td><button type="button" class="delete-set-btn" onclick="removeSet(this)">&times;</button></td>
                </tr>
            `;
        }

        function addExerciseBlock() {
            const container = document.getElementById('exercise-container');
            const exerciseIndex = exerciseCount; // Usa contador atual como índice
            
            const card = document.createElement('div');
            card.className = 'exercise-card';
            card.id = `exercise-${exerciseIndex}`;
            
            // Gera HTML do card com inputs name corretos
            card.innerHTML = `
                <div class="exercise-header">
                    <input type="text" name="exercicios[${exerciseIndex}][nome]" class="exercise-title-input" placeholder="Nome do Exercício (ex: Supino Reto)" required>
                    <button type="button" class="remove-exercise-btn" onclick="removeExercise(${exerciseIndex})">&times;</button>
                </div>
                
                <table class="sets-table">
                    <thead>
                        <tr>
                            <th>SET</th>
                            <th>KG</th>
                            <th>REPS</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="sets-body-${exerciseIndex}">
                        ${createSetRow(exerciseIndex, 1, 0)}
                        ${createSetRow(exerciseIndex, 2, 1)}
                        ${createSetRow(exerciseIndex, 3, 2)}
                    </tbody>
                </table>
                
                <button type="button" class="add-set-btn" onclick="addSet(${exerciseIndex})">+ Adicionar Série</button>
            `;
            
            container.appendChild(card);
            exerciseCount++;
            
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function addSet(exerciseIndex) {
            const tbody = document.getElementById(`sets-body-${exerciseIndex}`);
            const currentSets = tbody.children.length;
            const setNum = currentSets + 1;
            
            const newRow = document.createElement('tr');
            newRow.className = 'set-row';
            newRow.innerHTML = `
                <td class="set-number">${setNum}</td>
                <td><input type="number" name="exercicios[${exerciseIndex}][series][${currentSets}][carga]" class="set-input" placeholder="kg" step="0.5"></td>
                <td><input type="number" name="exercicios[${exerciseIndex}][series][${currentSets}][reps]" class="set-input" placeholder="reps"></td>
                <td><button type="button" class="delete-set-btn" onclick="removeSet(this)">&times;</button></td>
            `;
            tbody.appendChild(newRow);
        }

        function removeSet(btn) {
            const row = btn.closest('tr');
            const tbody = row.parentElement;
            
            // Se for a última série, apenas remove.
            row.remove();
            
            // Reordenar números das séries visualmente
            Array.from(tbody.children).forEach((row, index) => {
                row.querySelector('.set-number').textContent = index + 1;
            });
        }

        function removeExercise(id) {
            if(confirm("Remover este exercício?")) {
                const card = document.getElementById(`exercise-${id}`);
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            }
        }

        // Iniciar com um exercício vazio
        window.onload = function() {
            addExerciseBlock();
        };
    </script>

</body>
</html>     