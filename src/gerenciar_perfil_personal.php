<?php
session_start();
require_once 'conexao.php';

// 1. Verifica se é Personal
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'personal') {
    header("Location: index.php");
    exit();
}

$personal_id = $_SESSION['usuario_id'];

// 2. Buscar Dados do Personal
$sql_personal = "SELECT u.nome, u.email, pp.cref, pp.biografia, pp.cidade, pp.experiencia_anos, pp.plano_assinatura 
                 FROM usuarios u 
                 JOIN perfil_personal pp ON u.id = pp.usuario_id 
                 WHERE u.id = ?";
$stmt = $conn->prepare($sql_personal);
$stmt->bind_param("i", $personal_id);
$stmt->execute();
$result = $stmt->get_result();
$dados = $result->fetch_assoc();
$stmt->close();

// 3. Buscar Especialidades do Personal
$sql_specs = "SELECT especialidade_id FROM personal_especialidades WHERE personal_id = ?";
$stmt_specs = $conn->prepare($sql_specs);
$stmt_specs->bind_param("i", $personal_id);
$stmt_specs->execute();
$res_specs = $stmt_specs->get_result();
$minhas_specs = [];
while($row = $res_specs->fetch_assoc()) {
    $minhas_specs[] = $row['especialidade_id'];
}
$stmt_specs->close();

// Lista de todas as especialidades (para gerar os checkboxes)
$todas_specs = [
    1 => 'Hipertrofia',
    2 => 'Emagrecimento',
    3 => 'Funcional',
    4 => 'Yoga',
    5 => 'Crossfit'
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Gerenciar Perfil</title>

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

        /* Especialidades */
        .specialty-container { display: flex; flex-wrap: wrap; gap: 10px; }
        .hidden-checkbox { display: none; }
        .specialty-box {
            display: inline-block; padding: 8px 20px; border: 1px solid #e0e0e0;
            border-radius: 50px; background: #f8f9fa; color: #666; font-size: 0.9rem;
            font-weight: 600; cursor: pointer; transition: all 0.2s ease; user-select: none;
        }
        .specialty-box:hover { background: #eef2ff; border-color: var(--primary-blue); color: var(--primary-blue); }
        .hidden-checkbox:checked + .specialty-box {
            background-color: var(--primary-blue); color: var(--white);
            border-color: var(--primary-blue); box-shadow: 0 4px 10px rgba(3, 10, 140, 0.3);
            transform: translateY(-2px);
        }

        /* Assinatura */
        .subscription-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem; margin-top: 1rem;
        }
        .hidden-radio { display: none; }
        .plan-card {
            border: 2px solid #eee; border-radius: 15px; padding: 2rem 1.5rem;
            text-align: center; cursor: pointer; transition: all 0.3s ease;
            background: #fff; position: relative; overflow: hidden; display: flex;
            flex-direction: column; justify-content: space-between; height: 100%;
        }
        .plan-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-color: #e0e0e0; }
        
        /* Plano Ativo (Lógica Visual) */
        .plan-card.active-plan {
            border-color: var(--primary-green); background-color: #f9fff5;
            box-shadow: 0 10px 30px rgba(139, 191, 86, 0.15);
        }
        .plan-card.active-plan::before {
            content: 'Seu Plano Atual'; position: absolute; top: 0; left: 50%;
            transform: translateX(-50%); background: var(--primary-green); color: white;
            padding: 4px 15px; border-radius: 0 0 10px 10px; font-size: 0.75rem; font-weight: 700; width: 100%;
        }

        .plan-name { font-size: 1.4rem; font-weight: 800; color: var(--primary-blue); margin-bottom: 0.5rem; margin-top: 1rem; }
        .plan-limit { font-size: 1.1rem; font-weight: 600; color: var(--dark-text); margin-bottom: 1.5rem; background: #f0f4ff; padding: 5px 10px; border-radius: 20px; display: inline-block; }
        .plan-price { font-size: 2rem; color: #444; font-weight: 700; margin-bottom: 1.5rem; }
        .plan-price span { font-size: 1rem; font-weight: 400; color: #888; }

        .btn-subscribe {
            display: block; width: 100%; padding: 0.8rem; border-radius: 50px;
            text-decoration: none; font-weight: 700; transition: all 0.3s;
            border: 2px solid var(--primary-green); background: transparent; color: var(--primary-green);
        }
        .btn-subscribe:hover { background: var(--primary-green); color: var(--white); }
        
        .plan-card.active-plan .btn-subscribe {
            border-color: #ccc; color: #999; cursor: default; background: #eee;
        }

        .cancel-link {
            display: block; margin-top: 15px; color: var(--danger-red);
            font-size: 0.85rem; text-decoration: underline; cursor: pointer;
            font-weight: 500; transition: color 0.3s;
        }
        .cancel-link:hover { color: #d32f2f; }

        /* Ações */
        .action-bar { margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem; }
        .btn { padding: 0.8rem 2rem; border-radius: 50px; font-weight: 700; cursor: pointer; border: none; transition: all 0.3s; font-size: 1rem; }
        .btn-cancel { background: transparent; color: #777; border: 1px solid #eee; text-decoration: none; display: flex; align-items: center; }
        .btn-cancel:hover { background: #f9f9f9; color: var(--dark-text); }
        .btn-save { background: linear-gradient(135deg, var(--primary-green) 0%, #7aad47 100%); color: var(--white); box-shadow: 0 4px 15px rgba(139, 191, 86, 0.3); }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(139, 191, 86, 0.4); }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .subscription-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <header class="app-header">
        <a href="inicio_personal.php">
            <img src="logo.png" alt="Logo BeFit" class="logo">
        </a>
        <div class="profile-menu">
            <div class="profile-icon" title="Meu Perfil">
                <?php echo strtoupper(substr($dados['nome'], 0, 1)); ?>
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
        <div class="page-header">
            <h1 class="page-title">Gerenciar Perfil</h1>
            <a href="inicio_personal.php" class="back-btn">
                <span>&#8592;</span> Voltar ao Dashboard
            </a>
        </div>

        <form class="edit-container" action="salvar_perfil.php" method="POST" enctype="multipart/form-data">
            
            <!-- Seção 1: Informações Básicas -->
            <div class="form-section">
                <div class="section-header">Informações Básicas</div>
                
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
                    <div class="input-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($dados['nome']); ?>">
                    </div>
                    <div class="input-group">
                        <label for="cref">CREF</label>
                        <input type="text" id="cref" name="cref" value="<?php echo htmlspecialchars($dados['cref']); ?>">
                    </div>
                    <div class="input-group">
                        <label for="cidade">Cidade/Estado</label>
                        <input type="text" id="cidade" name="cidade" value="<?php echo htmlspecialchars($dados['cidade']); ?>">
                    </div>
                    <div class="input-group">
                        <label for="experiencia">Anos de Experiência</label>
                        <input type="number" id="experiencia" name="experiencia" value="<?php echo htmlspecialchars($dados['experiencia_anos']); ?>">
                    </div>
                </div>
            </div>

            <!-- Seção 2: Sobre Mim -->
            <div class="form-section">
                <div class="section-header">Sobre Mim</div>
                <div class="input-group">
                    <label for="bio">Biografia Profissional</label>
                    <textarea id="bio" name="bio" rows="5"><?php echo htmlspecialchars($dados['biografia']); ?></textarea>
                </div>
            </div>

            <!-- Seção 3: Especialidades (Dinâmico) -->
            <div class="form-section">
                <div class="section-header">Minhas Especialidades</div>
                <div class="specialty-container">
                    <?php foreach($todas_specs as $id => $nome): ?>
                        <input type="checkbox" id="spec-<?php echo $id; ?>" name="especialidades[]" value="<?php echo $id; ?>" class="hidden-checkbox" <?php echo in_array($id, $minhas_specs) ? 'checked' : ''; ?>>
                        <label for="spec-<?php echo $id; ?>" class="specialty-box"><?php echo $nome; ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Seção 4: Minha Assinatura BeFit -->
            <div class="form-section">
                <div class="section-header">Minha Assinatura BeFit</div>
                <p style="color:#666; font-size:0.95rem; margin-bottom:1.5rem;">
                    Escolha o plano ideal para aumentar seu limite de alunos e recursos na plataforma.
                </p>
                
                <div class="subscription-grid">
                    <?php 
                    $plano_atual = $dados['plano_assinatura'] ?? 'avancado'; 
                    ?>

                    <!-- Plano Avançado -->
                    <div class="plan-card <?php echo ($plano_atual == 'avancado') ? 'active-plan' : ''; ?>">
                        <div class="plan-name">Avançado</div>
                        <div class="plan-limit">Até 10 alunos</div>
                        <div class="plan-price">R$ 49,90 <span>/mês</span></div>
                        <?php if($plano_atual == 'avancado'): ?>
                            <span class="btn-subscribe">Seu Plano Atual</span>
                            <a onclick="confirmCancellation()" class="cancel-link">Cancelar Assinatura</a>
                        <?php else: ?>
                            <a href="pagamento.php?plano=avancado" class="btn-subscribe">Assinar Agora</a>
                        <?php endif; ?>
                    </div>

                    <!-- Plano Premium -->
                    <div class="plan-card <?php echo ($plano_atual == 'premium') ? 'active-plan' : ''; ?>">
                        <div class="plan-name">Premium</div>
                        <div class="plan-limit">Até 20 alunos</div>
                        <div class="plan-price">R$ 89,90 <span>/mês</span></div>
                        <?php if($plano_atual == 'premium'): ?>
                            <span class="btn-subscribe">Seu Plano Atual</span>
                            <a onclick="confirmCancellation()" class="cancel-link">Cancelar Assinatura</a>
                        <?php else: ?>
                            <a href="pagamento.php?plano=premium" class="btn-subscribe">Assinar Agora</a>
                        <?php endif; ?>
                    </div>

                    <!-- Plano Pro -->
                    <div class="plan-card <?php echo ($plano_atual == 'pro') ? 'active-plan' : ''; ?>">
                        <div class="plan-name">Pro</div>
                        <div class="plan-limit">Alunos Ilimitados</div>
                        <div class="plan-price">R$ 129,90 <span>/mês</span></div>
                        <?php if($plano_atual == 'pro'): ?>
                            <span class="btn-subscribe">Seu Plano Atual</span>
                            <a onclick="confirmCancellation()" class="cancel-link">Cancelar Assinatura</a>
                        <?php else: ?>
                            <a href="pagamento.php?plano=pro" class="btn-subscribe">Assinar Agora</a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="action-bar">
                <a href="inicio_personal.php" class="btn btn-cancel">Cancelar</a>
                <button type="submit" class="btn btn-save">Salvar Alterações</button>
            </div>

        </form>
    </main>

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

        // Script de confirmação de cancelamento
        function confirmCancellation() {
            if (confirm('Tem certeza que deseja cancelar sua assinatura? Seu perfil deixará de aparecer nas buscas e o limite de alunos será reduzido.')) {
                alert('Solicitação de cancelamento enviada. (Simulação)');
            }
        }
    </script>

</body>
</html>