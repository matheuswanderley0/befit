<?php
session_start();
require_once 'conexao.php';

// Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$meu_id = $_SESSION['usuario_id'];
$meu_tipo = $_SESSION['usuario_tipo'];
$contato_id = isset($_GET['contato']) ? intval($_GET['contato']) : 0;

// --- API: RECEBER NOVAS MENSAGENS (AJAX) ---
if (isset($_GET['ajax_get_msgs']) && $contato_id > 0) {
    $sql_msgs = "SELECT mensagem, remetente_id, DATE_FORMAT(data_envio, '%H:%i') as hora 
                 FROM mensagens 
                 WHERE (remetente_id = ? AND destinatario_id = ?) 
                    OR (remetente_id = ? AND destinatario_id = ?) 
                 ORDER BY data_envio ASC";
    $stmt = $conn->prepare($sql_msgs);
    $stmt->bind_param("iiii", $meu_id, $contato_id, $contato_id, $meu_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Retorna apenas o HTML das mensagens
    if ($result->num_rows > 0) {
        while($m = $result->fetch_assoc()) {
            $classe = ($m['remetente_id'] == $meu_id) ? 'sent' : 'received';
            echo '<div class="message ' . $classe . '">';
            echo nl2br(htmlspecialchars($m['mensagem']));
            echo '<span class="msg-time">' . $m['hora'] . '</span>';
            echo '</div>';
        }
    } else {
        echo '<p style="text-align:center; color:#999; margin-top: 20px;">Nenhuma mensagem ainda. Diga Olá!</p>';
    }
    $stmt->close();
    exit(); // Para a execução aqui para não carregar o resto da página
}

// --- API: ENVIAR MENSAGEM (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_send']) && $contato_id > 0) {
    $msg = trim($_POST['mensagem']);
    if (!empty($msg)) {
        $sql_envio = "INSERT INTO mensagens (remetente_id, destinatario_id, mensagem, data_envio) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql_envio);
        $stmt->bind_param("iis", $meu_id, $contato_id, $msg);
        if($stmt->execute()) {
            echo "sucesso";
        } else {
            echo "erro";
        }
        $stmt->close();
    }
    exit();
}

// --- CARREGAMENTO NORMAL DA PÁGINA ---

// Busca nome do contato
$contato_nome = "";
if ($contato_id > 0) {
    $sql_nome = "SELECT nome FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql_nome);
    $stmt->bind_param("i", $contato_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $contato_nome = $res->fetch_assoc()['nome'];
    }
}

// Busca Lista de Contatos (Sidebar)
$lista_contatos = [];
if ($meu_tipo == 'aluno') {
    $sql_contatos = "SELECT u.id, u.nome, 
                        (SELECT mensagem FROM mensagens WHERE (remetente_id = u.id AND destinatario_id = ?) OR (remetente_id = ? AND destinatario_id = u.id) ORDER BY data_envio DESC LIMIT 1) as ultima_msg
                     FROM usuarios u 
                     JOIN perfil_aluno pa ON u.id = pa.personal_id 
                     WHERE pa.usuario_id = ?";
    $stmt = $conn->prepare($sql_contatos);
    $stmt->bind_param("iii", $meu_id, $meu_id, $meu_id);
} else {
    $sql_contatos = "SELECT u.id, u.nome,
                        (SELECT mensagem FROM mensagens WHERE (remetente_id = u.id AND destinatario_id = ?) OR (remetente_id = ? AND destinatario_id = u.id) ORDER BY data_envio DESC LIMIT 1) as ultima_msg
                     FROM usuarios u 
                     JOIN perfil_aluno pa ON u.id = pa.usuario_id 
                     WHERE pa.personal_id = ?";
    $stmt = $conn->prepare($sql_contatos);
    $stmt->bind_param("iii", $meu_id, $meu_id, $meu_id);
}
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()) {
    $lista_contatos[] = $row;
    if ($contato_id == 0) { $contato_id = $row['id']; $contato_nome = $row['nome']; } // Default
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Chat</title>

    <style>
        :root {
            --primary-green: #8BBF56;
            --primary-blue: #030A8C;
            --primary-blue-dark: #02065a;
            --light-gray: #f4f4f4;
            --dark-text: #333;
            --white: #ffffff;
            --chat-bg: #eef1f5;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            height: 100vh;
            color: var(--dark-text);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Header */
        .app-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            z-index: 100;
            border-bottom: 1px solid #eee;
        }

        .app-header .logo { width: 100px; transition: transform 0.3s ease; }
        .app-header .logo:hover { transform: scale(1.05); }

        /* Layout do Chat */
        .chat-wrapper {
            flex: 1;
            display: flex;
            max-width: 1200px;
            margin: 2rem auto;
            width: 95%;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            overflow: hidden;
            height: calc(100% - 100px);
            border: 1px solid #e0e0e0;
        }

        /* Sidebar */
        .chat-sidebar {
            width: 350px;
            background: var(--white);
            border-right: 1px solid #f0f0f0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid #f0f0f0; }
        .search-input {
            width: 100%; padding: 0.8rem 1rem; border: 2px solid #f5f5f5;
            border-radius: 30px; font-size: 0.9rem; background-color: #fafafa;
        }

        .conversation-list { flex: 1; overflow-y: auto; padding: 0.5rem; }
        .conversation-item {
            display: flex; align-items: center; gap: 15px;
            padding: 1rem; border-radius: 12px; cursor: pointer;
            transition: all 0.2s; margin-bottom: 0.3rem;
            text-decoration: none; color: inherit;
        }
        .conversation-item:hover { background-color: #f9f9f9; }
        .conversation-item.active { background-color: #f0f4ff; border-left: 4px solid var(--primary-blue); }

        .avatar {
            width: 45px; height: 45px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue) 0%, #02065a 100%);
            color: var(--white); display: flex; justify-content: center; align-items: center;
            font-weight: 700; font-size: 1rem; flex-shrink: 0;
        }

        .conversation-info { flex: 1; overflow: hidden; }
        .contact-name { font-weight: 600; color: var(--dark-text); margin-bottom: 4px; }
        .last-msg { font-size: 0.85rem; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Área Principal */
        .chat-main {
            flex: 1; display: flex; flex-direction: column; background-color: var(--chat-bg);
        }

        .chat-header-main {
            padding: 1rem 2rem; background: var(--white);
            border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between;
        }

        .chat-contact-info { display: flex; align-items: center; gap: 15px; }
        .contact-status { font-size: 0.85rem; color: var(--primary-green); margin-top: 2px; }

        .back-to-dash {
            text-decoration: none; color: #777; font-size: 0.9rem;
            font-weight: 600; display: flex; align-items: center; gap: 5px; transition: color 0.2s;
        }
        .back-to-dash:hover { color: var(--primary-blue); }

        /* Mensagens */
        .messages-area {
            flex: 1; padding: 2rem; overflow-y: auto;
            display: flex; flex-direction: column; gap: 1rem;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 20px 20px; background-color: #f8f9fa;
        }

        .message {
            max-width: 70%; padding: 0.8rem 1.2rem; border-radius: 15px;
            font-size: 0.95rem; line-height: 1.5; position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); word-wrap: break-word;
        }

        .message.received {
            background: var(--white); color: var(--dark-text);
            align-self: flex-start; border-top-left-radius: 0;
        }

        .message.sent {
            background: var(--primary-green); color: var(--white);
            align-self: flex-end; border-top-right-radius: 0;
            box-shadow: 0 2px 8px rgba(139, 191, 86, 0.3);
        }

        .msg-time {
            display: block; font-size: 0.7rem; margin-top: 5px; opacity: 0.7; text-align: right;
        }

        /* Input Area */
        .chat-input-area {
            padding: 1.5rem; background: var(--white);
            border-top: 1px solid #eee;
        }
        
        .input-form {
            display: flex; align-items: center; gap: 15px; width: 100%;
        }

        .msg-input {
            flex: 1; padding: 1rem 1.5rem; border: 2px solid #f0f0f0;
            border-radius: 30px; font-size: 0.95rem; background: #fafafa; transition: all 0.3s;
        }
        .msg-input:focus { outline: none; border-color: var(--primary-blue); background: var(--white); }

        .btn-send {
            background: var(--primary-blue); color: var(--white); border: none;
            width: 50px; height: 50px; border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
            transition: all 0.3s; box-shadow: 0 4px 10px rgba(3, 10, 140, 0.2);
        }
        .btn-send:hover { transform: scale(1.1); background: var(--primary-blue-dark); }

        @media (max-width: 768px) {
            .chat-sidebar { display: <?php echo $contato_id ? 'none' : 'flex'; ?>; width: 100%; border-right: none; }
            .chat-main { display: <?php echo $contato_id ? 'flex' : 'none'; ?>; }
            .chat-wrapper { height: calc(100% - 80px); margin: 0.5rem; width: calc(100% - 1rem); border-radius: 15px; }
            .back-mobile { display: block; margin-right: 10px; color: #333; font-size: 1.2rem; text-decoration: none; }
        }
        
        .back-mobile { display: none; }
    </style>
</head>
<body>

    <header class="app-header">
        <a href="<?php echo $meu_tipo == 'aluno' ? 'inicio_aluno.php' : 'inicio_personal.php'; ?>">
            <img src="logo.png" alt="Logo BeFit" class="logo">
        </a>
    </header>

    <div class="chat-wrapper">
        
        <!-- Sidebar -->
        <aside class="chat-sidebar">
            <div class="sidebar-header">
                <input type="text" class="search-input" placeholder="Buscar conversa...">
            </div>
            
            <div class="conversation-list">
                <?php if (!empty($lista_contatos)): ?>
                    <?php foreach($lista_contatos as $contato): ?>
                        <a href="chat.php?contato=<?php echo $contato['id']; ?>" class="conversation-item <?php echo ($contato_id == $contato['id']) ? 'active' : ''; ?>">
                            <div class="avatar"><?php echo strtoupper(substr($contato['nome'], 0, 2)); ?></div>
                            <div class="conversation-info">
                                <div class="contact-name">
                                    <?php echo htmlspecialchars($contato['nome']); ?>
                                </div>
                                <div class="last-msg">
                                    <?php echo htmlspecialchars($contato['ultima_msg'] ?: 'Iniciar conversa...'); ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; padding: 20px; color: #999;">Nenhum contato encontrado.</p>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Chat Principal -->
        <section class="chat-main">
            <?php if ($contato_id > 0): ?>
                <div class="chat-header-main">
                    <div class="chat-contact-info">
                        <a href="chat.php" class="back-mobile">←</a>
                        <div class="avatar"><?php echo strtoupper(substr($contato_nome, 0, 2)); ?></div>
                        <div>
                            <h3 style="font-size: 1rem; margin: 0; color: var(--dark-text);"><?php echo htmlspecialchars($contato_nome); ?></h3>
                            <div class="contact-status">Online</div>
                        </div>
                    </div>
                    <a href="<?php echo $meu_tipo == 'aluno' ? 'inicio_aluno.php' : 'inicio_personal.php'; ?>" class="back-to-dash">Sair do Chat</a>
                </div>

                <div class="messages-area" id="msgArea">
                    <!-- Mensagens carregadas via AJAX aqui -->
                </div>

                <div class="chat-input-area">
                    <form id="chatForm" class="input-form">
                        <input type="hidden" name="ajax_send" value="1">
                        <input type="hidden" name="contato_id" value="<?php echo $contato_id; ?>">
                        <input type="text" id="msgInput" name="mensagem" class="msg-input" placeholder="Digite sua mensagem..." required autocomplete="off" autofocus>
                        <button type="submit" class="btn-send">➤</button>
                    </form>
                </div>
            <?php else: ?>
                <div style="display:flex; justify-content:center; align-items:center; height:100%; color:#999; flex-direction: column;">
                    <span style="font-size: 3rem; opacity: 0.2;">💬</span>
                    <p>Selecione uma conversa para continuar.</p>
                </div>
            <?php endif; ?>
        </section>

    </div>
    
    <script>
        const contatoId = <?php echo $contato_id; ?>;
        const msgArea = document.getElementById('msgArea');

        // Função para carregar mensagens sem recarregar página
        function carregarMensagens() {
            if (contatoId === 0) return;

            fetch(`chat.php?ajax_get_msgs=1&contato=${contatoId}`)
                .then(response => response.text())
                .then(data => {
                    msgArea.innerHTML = data;
                    // Só rola pra baixo se não estiver lendo histórico (lógica simples)
                    // msgArea.scrollTop = msgArea.scrollHeight; 
                });
        }

        // Enviar mensagem via AJAX
        const form = document.getElementById('chatForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const input = document.getElementById('msgInput');
                const msg = input.value;
                
                const formData = new FormData();
                formData.append('ajax_send', '1');
                formData.append('mensagem', msg);

                fetch(`chat.php?contato=${contatoId}`, {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    input.value = ''; // Limpa input
                    carregarMensagens(); // Atualiza na hora
                    setTimeout(() => { msgArea.scrollTop = msgArea.scrollHeight; }, 100); // Rola pra baixo
                });
            });
        }

        // Atualiza a cada 3 segundos
        if (contatoId > 0) {
            carregarMensagens(); // Carrega ao abrir
            setInterval(carregarMensagens, 3000); // Loop
        }
    </script>

</body>
</html>