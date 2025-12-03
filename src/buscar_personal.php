<?php
session_start();
require_once 'conexao.php';

// Verifica login (Opcional: busca pode ser pública ou restrita)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Termo de Busca
$termo = isset($_GET['busca']) ? $conn->real_escape_string($_GET['busca']) : '';
$filtro_ativo = $termo; // Para marcar a tag ativa

// Construção da Query de Busca
$sql_busca = "SELECT u.id, u.nome, pp.cref, pp.biografia, pp.cidade 
              FROM usuarios u 
              JOIN perfil_personal pp ON u.id = pp.usuario_id 
              WHERE u.tipo = 'personal'";

if (!empty($termo)) {
    // Busca por Nome OU Especialidade (Subquery)
    $sql_busca .= " AND (
        u.nome LIKE '%$termo%' OR 
        pp.biografia LIKE '%$termo%' OR 
        u.id IN (
            SELECT pe.personal_id 
            FROM personal_especialidades pe 
            JOIN especialidades e ON pe.especialidade_id = e.id 
            WHERE e.nome LIKE '%$termo%'
        )
    )";
}

$result = $conn->query($sql_busca);
$personals = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Busca as especialidades deste personal
        $pid = $row['id'];
        $sql_specs = "SELECT e.nome FROM especialidades e 
                      JOIN personal_especialidades pe ON e.id = pe.especialidade_id 
                      WHERE pe.personal_id = $pid LIMIT 3";
        $res_specs = $conn->query($sql_specs);
        $specs = [];
        while($s = $res_specs->fetch_assoc()) {
            $specs[] = $s['nome'];
        }
        $row['especialidades'] = $specs;
        
        // Gera uma nota aleatória para simulação (entre 4.5 e 5.0)
        $row['rating'] = number_format((rand(45, 50) / 10), 1);
        $row['alunos_count'] = rand(10, 50); // Simulação
        
        $personals[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Encontrar Personal</title>

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

        .app-header .logo { width: 110px; transition: transform 0.3s ease; }
        .app-header .logo:hover { transform: scale(1.05); }

        .profile-menu {
            position: absolute;
            right: 2rem;
            top: 0; height: 100%;
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
            display: none;
            position: absolute; right: 0; top: 80%; padding-top: 15px; z-index: 101;
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

        /* Main Content */
        main {
            max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Search Section */
        .search-header {
            margin-bottom: 2rem;
            display: flex; flex-direction: column; align-items: center; gap: 1.5rem;
        }

        .search-bar-container { width: 100%; max-width: 600px; position: relative; }

        .search-input {
            width: 100%; padding: 1rem 1.5rem 1rem 3.5rem;
            border: 2px solid #fff; border-radius: 50px; font-size: 1rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); transition: all 0.3s;
            background-color: var(--white);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%23030A8C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'%3E%3C/circle%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: 20px center;
        }

        .search-input:focus { outline: none; border-color: var(--primary-blue); box-shadow: 0 5px 25px rgba(3, 10, 140, 0.15); }

        .filters { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }

        .filter-tag {
            padding: 8px 16px; background: var(--white); border: 1px solid #eee;
            border-radius: 20px; font-size: 0.9rem; color: #666;
            cursor: pointer; transition: all 0.2s; text-decoration: none;
        }

        .filter-tag:hover, .filter-tag.active {
            background: var(--primary-blue); color: var(--white); border-color: var(--primary-blue);
        }

        /* Results Grid */
        .results-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;
        }

        /* Trainer Card */
        .trainer-card {
            background: var(--white); border-radius: 20px; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #f0f0f0; display: flex; flex-direction: column;
        }

        .trainer-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); }

        .card-header {
            padding: 1.5rem; display: flex; align-items: center; gap: 15px;
            border-bottom: 1px solid #f9f9f9;
        }

        .trainer-avatar {
            width: 65px; height: 65px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), #02065a);
            color: var(--white); display: flex; justify-content: center; align-items: center;
            font-weight: 700; font-size: 1.2rem; box-shadow: 0 4px 10px rgba(3, 10, 140, 0.2);
        }

        .trainer-info h3 { margin: 0; font-size: 1.1rem; color: var(--dark-text); }
        .trainer-info .cref { font-size: 0.85rem; color: #888; margin-top: 3px; display: block; }
        .trainer-info .rating { color: #ffc107; font-size: 0.9rem; margin-top: 3px; }

        .card-body { padding: 1.5rem; flex: 1; }

        .specialties { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1rem; }
        .spec-badge {
            background: #f0f9eb; color: var(--primary-green); font-size: 0.8rem;
            padding: 4px 10px; border-radius: 12px; font-weight: 600;
        }

        .bio {
            font-size: 0.9rem; color: #666; line-height: 1.5;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            padding: 1.5rem; background: #fcfcfc; border-top: 1px solid #f0f0f0;
            display: flex; gap: 10px;
        }

        .btn-card {
            flex: 1; padding: 0.8rem; border-radius: 10px; font-size: 0.9rem;
            font-weight: 600; text-align: center; cursor: pointer; transition: all 0.3s;
            text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 5px;
        }

        .btn-outline {
            background: var(--white); border: 1px solid var(--primary-blue); color: var(--primary-blue);
        }
        .btn-outline:hover { background: #f0f4ff; }

        .btn-primary {
            background: var(--primary-green); border: 1px solid var(--primary-green); color: var(--white);
        }
        .btn-primary:hover {
            background: #7aad47; box-shadow: 0 4px 10px rgba(139, 191, 86, 0.3);
        }

        .no-results {
            grid-column: 1 / -1; text-align: center; padding: 4rem 0; color: #888;
        }

        /* Floating Chat */
        .floating-chat-bar { position: fixed; bottom: 30px; right: 30px; z-index: 200; }
        .floating-chat-btn {
            background: var(--white); color: var(--primary-blue); border: 1px solid #eee;
            padding: 12px 24px; border-radius: 50px; font-weight: 600; font-size: 0.95rem;
            cursor: pointer; box-shadow: 0 5px 20px rgba(0,0,0,0.08); text-decoration: none;
            display: flex; align-items: center; gap: 8px; transition: all 0.3s;
        }
        .floating-chat-btn:hover { background: var(--primary-blue); color: var(--white); transform: translateY(-3px); }

        @media (max-width: 600px) { .results-grid { grid-template-columns: 1fr; } }
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
                <div class="dropdown-inner">
                    <a href="perfil_aluno.php">Meus Dados</a>
                    <a href="logout.php" style="color: var(--danger-red);">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        
        <section class="search-header">
            <div class="search-bar-container">
                <form action="" method="GET" style="width: 100%;">
                    <input type="text" name="busca" class="search-input" 
                           placeholder="Busque por nome ou especialidade..." 
                           value="<?php echo htmlspecialchars($termo); ?>">
                </form>
            </div>
            
            <div class="filters">
                <?php 
                $tags = ['Todos', 'Hipertrofia', 'Emagrecimento', 'Funcional', 'Crossfit', 'Yoga'];
                foreach ($tags as $tag) {
                    $activeClass = (empty($termo) && $tag === 'Todos') || ($termo === $tag) ? 'active' : '';
                    $link = $tag === 'Todos' ? 'buscar_personal.php' : 'buscar_personal.php?busca=' . urlencode($tag);
                    echo "<a href='$link' class='filter-tag $activeClass'>$tag</a>";
                }
                ?>
            </div>
        </section>

        <section class="results-grid" id="resultsContainer">
            
            <?php if (!empty($personals)): ?>
                <?php foreach ($personals as $personal): ?>
                    <div class="trainer-card">
                        <div class="card-header">
                            <div class="trainer-avatar">
                                <?php echo strtoupper(substr($personal['nome'], 0, 1)); ?>
                            </div>
                            <div class="trainer-info">
                                <h3><?php echo htmlspecialchars($personal['nome']); ?></h3>
                                <span class="cref">CREF: <?php echo htmlspecialchars($personal['cref']); ?></span>
                                <div class="rating">★ <?php echo $personal['rating']; ?> (<?php echo $personal['alunos_count']; ?> alunos)</div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="specialties">
                                <?php foreach ($personal['especialidades'] as $spec): ?>
                                    <span class="spec-badge"><?php echo htmlspecialchars($spec); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <p class="bio">
                                <?php echo htmlspecialchars($personal['biografia'] ?: 'Olá! Sou personal trainer focado em resultados...'); ?>
                            </p>
                        </div>
                        <div class="card-footer">
                            <a href="perfil_personal.php?id=<?php echo $personal['id']; ?>" class="btn-card btn-outline">Ver Perfil</a>
                            <a href="chat.php" class="btn-card btn-primary">Chamar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results" style="display: block;">
                    <p>Nenhum personal encontrado com esses termos.</p>
                    <a href="buscar_personal.php" style="color: var(--primary-blue); text-decoration: none; margin-top: 10px; display: inline-block;">Limpar filtros</a>
                </div>
            <?php endif; ?>

        </section>

    </main>

    <div class="floating-chat-bar">
        <a href="chat.php" class="floating-chat-btn">Suporte</a>
    </div>

</body>
</html>