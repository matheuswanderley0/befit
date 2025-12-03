<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Adicionar Cliente</title>

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
            background: linear-gradient(-45deg, #f8f9fa, #e9ecef, #f0f4f8, #e6eaf0);
            background-size: 400% 400%;
            animation: gradientFlow 8s ease infinite;
            padding-bottom: 80px;
            min-height: 100vh;
            overflow-x: hidden;
            color: var(--dark-text);
        }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

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
            animation: headerSlideDown 0.6s ease-out;
            border-bottom: 1px solid rgba(255,255,255,0.8);
        }

        @keyframes headerSlideDown {
            from {
                opacity: 0;
                transform: translateY(-100%);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .app-header .logo {
            width: 110px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            filter: drop-shadow(0 2px 8px rgba(3, 10, 140, 0.2));
        }

        .app-header .logo:hover {
            transform: scale(1.1) rotate(2deg);
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
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 6px 15px rgba(3, 10, 140, 0.3);
            position: relative;
            overflow: hidden;
        }

        .app-header .profile-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .app-header .profile-icon:hover {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 8px 20px rgba(3, 10, 140, 0.4);
        }

        .app-header .profile-icon:hover::before {
            left: 100%;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 55px;
            background: linear-gradient(135deg, var(--white) 0%, #f9f9f9 100%);
            min-width: 180px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-radius: 12px;
            z-index: 101;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-15px) scale(0.95);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .dropdown-content a {
            color: var(--dark-text);
            padding: 14px 20px;
            text-decoration: none;
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dropdown-content a::before {
            content: '';
            position: absolute;
            left: -100%;
            bottom: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-green), var(--primary-blue));
            transition: left 0.4s ease;
        }

        .dropdown-content a:hover {
            background: linear-gradient(135deg, #f8f8f8 0%, #f0f0f0 100%);
            color: var(--primary-blue);
            padding-left: 25px;
            transform: translateX(5px);
        }

        .dropdown-content a:hover::before {
            left: 0;
        }

        .profile-menu:hover .dropdown-content {
            display: block;
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        main {
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            animation: contentFadeIn 0.8s ease-out 0.2s both;
        }

        @keyframes contentFadeIn {
            from { 
                opacity: 0; 
                transform: translateY(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            animation: slideInLeft 0.6s ease-out 0.3s both;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-blue);
            position: relative;
            background: linear-gradient(45deg, var(--primary-blue), #1a237e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: titlePulse 2s ease-in-out infinite alternate;
        }

        @keyframes titlePulse {
            from { filter: drop-shadow(0 0 5px rgba(3, 10, 140, 0.2)); }
            to { filter: drop-shadow(0 0 15px rgba(3, 10, 140, 0.4)); }
        }
        
        .page-title::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-green), var(--primary-blue));
            margin-top: 8px;
            border-radius: 2px;
            transform-origin: left;
            animation: lineGrow 1s ease-out 0.5s both;
        }

        @keyframes lineGrow {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        .back-btn {
            text-decoration: none;
            color: #777;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.4s ease;
            padding: 8px 16px;
            border-radius: 25px;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.5);
        }

        .back-btn:hover {
            color: var(--primary-blue);
            background: rgba(255,255,255,0.9);
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-container {
            background: linear-gradient(135deg, var(--white) 0%, #f9f9f9 100%);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 
                0 15px 40px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255,255,255,0.8);
            border: 1px solid rgba(255,255,255,0.6);
            animation: formSlideUp 0.6s ease-out 0.4s both;
            position: relative;
            overflow: hidden;
        }

        @keyframes formSlideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(139, 191, 86, 0.03), transparent);
            transform: rotate(45deg);
            animation: shimmer 4s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .full-width {
            grid-column: span 2;
        }

        .input-group {
            margin-bottom: 1rem;
            animation: inputAppear 0.6s ease-out both;
        }

        .input-group:nth-child(1) { animation-delay: 0.5s; }
        .input-group:nth-child(2) { animation-delay: 0.6s; }
        .input-group:nth-child(3) { animation-delay: 0.7s; }
        .input-group:nth-child(4) { animation-delay: 0.8s; }
        .input-group:nth-child(5) { animation-delay: 0.9s; }
        .input-group:nth-child(6) { animation-delay: 1s; }
        .input-group:nth-child(7) { animation-delay: 1.1s; }

        @keyframes inputAppear {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-text);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .input-group input,
        .input-group select,
        .input-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: rgba(255,255,255,0.8);
            font-family: inherit;
            backdrop-filter: blur(10px);
        }

        .input-group input:focus,
        .input-group select:focus,
        .input-group textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 
                0 0 0 4px rgba(3, 10, 140, 0.1),
                0 5px 15px rgba(3, 10, 140, 0.1);
            background: var(--white);
            transform: translateY(-2px);
        }

        .input-group input:hover,
        .input-group select:hover,
        .input-group textarea:hover {
            border-color: #ddd;
            transform: translateY(-1px);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(240,240,240,0.8);
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out 1.2s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn {
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .btn-cancel {
            background: transparent;
            color: #777;
            border: 1px solid #eee;
            backdrop-filter: blur(10px);
        }

        .btn-cancel:hover {
            background: rgba(255,255,255,0.9);
            color: var(--dark-text);
            border-color: #ccc;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-cancel:hover::before {
            left: 100%;
        }

        .btn-save {
            background: linear-gradient(135deg, var(--primary-green) 0%, #7aad47 100%);
            color: var(--white);
            box-shadow: 0 6px 20px rgba(139, 191, 86, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 10px 25px rgba(139, 191, 86, 0.4);
            background: linear-gradient(135deg, #7aad47 0%, #6a9a3e 100%);
        }

        .btn-save:hover::before {
            left: 100%;
        }

        .floating-chat-bar {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 200;
            animation: chatFloat 3s ease-in-out infinite;
        }

        @keyframes chatFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(2deg); }
        }

        .floating-chat-btn {
            background: rgba(255,255,255,0.9);
            color: var(--primary-blue);
            border: 1px solid rgba(255,255,255,0.8);
            padding: 14px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(15px);
            position: relative;
            overflow: hidden;
        }

        .floating-chat-btn::before {
            content: '💬';
            font-size: 1.1rem;
        }

        .floating-chat-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }

        .floating-chat-btn:hover {
            background: var(--primary-blue);
            color: var(--white);
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 30px rgba(3, 10, 140, 0.3);
            border-color: var(--primary-blue);
        }

        .floating-chat-btn:hover::after {
            left: 100%;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>

    <header class="app-header">
        <a href="dashboard.php">
            <img src="logo.png" alt="Logo BeFit" class="logo">
        </a>
        
        <div class="profile-menu">
            <div class="profile-icon" title="Meu Perfil"></div>
            <div class="dropdown-content">
                <a href="perfil.php">Gerenciar Perfil</a>
                <a href="logout.php" style="color: var(--danger-red);">Sair</a>
            </div>
        </div>
    </header>

    <main>
        <div class="page-header">
            <h1 class="page-title">Novo Aluno</h1>
            <a href="dashboard.php" class="back-btn">
                <span>&#8592;</span> Voltar
            </a>
        </div>

        <div class="form-container">
            <form action="salvar_cliente.php" method="POST">
                <div class="form-grid">
                    
                    <div class="full-width input-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" required placeholder="Ex: Ana Silva">
                    </div>

                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="email@exemplo.com">
                    </div>

                    <div class="input-group">
                        <label for="telefone">WhatsApp / Telefone</label>
                        <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000">
                    </div>

                    <div class="input-group">
                        <label for="nascimento">Data de Nascimento</label>
                        <input type="date" id="nascimento" name="nascimento">
                    </div>

                    <div class="input-group">
                        <label for="genero">Gênero</label>
                        <select id="genero" name="genero">
                            <option value="" disabled selected>Selecione...</option>
                            <option value="feminino">Feminino</option>
                            <option value="masculino">Masculino</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>

                    <div class="full-width input-group">
                        <label for="objetivo">Objetivo Principal</label>
                        <input type="text" id="objetivo" name="objetivo" placeholder="Ex: Hipertrofia, Emagrecimento, Condicionamento...">
                    </div>

                    <div class="full-width input-group">
                        <label for="observacoes">Observações / Histórico de Lesões</label>
                        <textarea id="observacoes" name="observacoes" rows="4" placeholder="Alguma restrição médica ou detalhe importante?"></textarea>
                    </div>

                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-cancel">Cancelar</a>
                    <button type="submit" class="btn btn-save">Salvar Aluno</button>
                </div>
            </form>
        </div>
    </main>

    <div class="floating-chat-bar">
        <button class="floating-chat-btn">Chat Rápido</button>
    </div>

</body>
</html>