<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeFit - Pagamento</title>

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
            padding-bottom: 100px;
            min-height: 100vh;
            color: var(--dark-text);
            overflow-x: hidden;
        }

        /* Header Clean */
        .app-header {
            background: rgba(255, 255, 255, 0.9);
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
            position: relative;
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
            padding: 3rem 1.5rem;
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
            position: relative;
        }
        
        .page-title::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: var(--primary-green);
            margin-top: 5px;
            border-radius: 2px;
        }

        .back-btn {
            text-decoration: none;
            color: #777;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }

        .back-btn:hover {
            color: var(--primary-blue);
        }

        /* Checkout Layout */
        .checkout-container {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
        }

        /* Payment Section */
        .payment-section {
            background: var(--white);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
        }

        .section-header {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }

        /* Summary Section */
        .summary-card {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #02065a 100%);
            border-radius: 20px;
            padding: 2rem;
            color: var(--white);
            box-shadow: 0 15px 30px rgba(3, 10, 140, 0.25);
            height: fit-content;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .summary-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .plan-detail {
            margin-bottom: 2rem;
        }

        .plan-name-display {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .plan-benefits {
            font-size: 0.95rem;
            opacity: 0.8;
            list-style: none;
            padding: 0;
        }

        .plan-benefits li {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .plan-benefits li::before {
            content: '✓';
            color: var(--primary-green);
            font-weight: bold;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 1.5rem;
            margin-top: 1.5rem;
        }

        .total-label {
            font-size: 1.1rem;
        }

        .total-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-green);
        }

        /* Form Elements */
        .input-group {
            margin-bottom: 1.2rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-text);
            font-size: 0.9rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #fafafa;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary-blue);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(3, 10, 140, 0.05);
        }

        .card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .payment-methods {
            display: flex;
            gap: 10px;
            margin-bottom: 1.5rem;
        }

        .method-card {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.2s;
            opacity: 0.6;
            filter: grayscale(100%);
        }

        .method-card.active {
            border-color: var(--primary-blue);
            opacity: 1;
            filter: grayscale(0%);
            background: #f0f4ff;
        }

        .btn-pay {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 50px;
            background: linear-gradient(135deg, var(--primary-green) 0%, #7aad47 100%);
            color: var(--white);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(139, 191, 86, 0.3);
            margin-top: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 191, 86, 0.4);
        }

        .secure-badge {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #999;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
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
        }

        .floating-chat-btn::before {
            content: '💬';
            font-size: 1.1rem;
        }

        .floating-chat-btn:hover {
            background: var(--primary-blue);
            color: var(--white);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(3, 10, 140, 0.25);
            border-color: var(--primary-blue);
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            .summary-card {
                order: -1; /* Summary first on mobile */
            }
        }
    </style>
</head>
<body>

    <header class="app-header">
        <a href="inicio_personal.php">
            <img src="logo.png" alt="Logo BeFit" class="logo">
        </a>
        
        <div class="profile-menu">
            <div class="profile-icon" title="Meu Perfil"></div>
            <div class="dropdown-content">
                <a href="gerenciar_perfil_personal.php">Gerenciar Perfil</a>
                <a href="logout.php" style="color: var(--danger-red);">Sair</a>
            </div>
        </div>
    </header>

    <main>
        <div class="page-header">
            <h1 class="page-title">Finalizar Assinatura</h1>
            <a href="gerenciar_perfil_personal.php" class="back-btn">
                <span>&#8592;</span> Cancelar
            </a>
        </div>

        <div class="checkout-container">
            
            <!-- Formulário de Pagamento -->
            <div class="payment-section">
                <div class="section-header">
                    <span>💳</span> Dados do Pagamento
                </div>

                <div class="payment-methods">
                    <div class="method-card active">Cartão de Crédito</div>
                    <div class="method-card">PIX</div>
                </div>

                <form id="payment-form" onsubmit="processPayment(event)">
                    <div class="input-group">
                        <label>Nome no Cartão</label>
                        <input type="text" placeholder="Como impresso no cartão" required>
                    </div>

                    <div class="input-group">
                        <label>Número do Cartão</label>
                        <input type="text" placeholder="0000 0000 0000 0000" maxlength="19" required>
                    </div>

                    <div class="card-grid">
                        <div class="input-group">
                            <label>Validade</label>
                            <input type="text" placeholder="MM/AA" maxlength="5" required>
                        </div>
                        <div class="input-group">
                            <label>CVV</label>
                            <input type="text" placeholder="123" maxlength="3" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>CPF do Titular</label>
                        <input type="text" placeholder="000.000.000-00" maxlength="14" required>
                    </div>

                    <button type="submit" class="btn-pay">Confirmar Assinatura</button>
                    
                    <div class="secure-badge">
                        🔒 Pagamento 100% Seguro e Criptografado
                    </div>
                </form>
            </div>

            <!-- Resumo do Pedido -->
            <div class="summary-card">
                <div class="summary-title">Resumo do Pedido</div>
                
                <div class="plan-detail">
                    <div class="plan-name-display" id="plan-name">Carregando...</div>
                    <ul class="plan-benefits" id="plan-desc">
                        <!-- Preenchido via JS -->
                    </ul>
                </div>

                <div class="total-row">
                    <div class="total-label">Total Mensal</div>
                    <div class="total-value" id="plan-price">R$ --,--</div>
                </div>
                
                <p style="font-size: 0.8rem; margin-top: 1rem; opacity: 0.7;">
                    Renovação automática mensalmente. Cancele quando quiser.
                </p>
            </div>

        </div>
    </main>

    <div class="floating-chat-bar">
        <button class="floating-chat-btn">Chat Rápido</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Simula dados dos planos (normalmente viria do Backend)
            const plans = {
                'avancado': {
                    name: 'Plano Avançado',
                    price: 'R$ 49,90',
                    benefits: ['Até 10 Alunos', 'Suporte Básico', 'Painel de Controle']
                },
                'premium': {
                    name: 'Plano Premium',
                    price: 'R$ 89,90',
                    benefits: ['Até 20 Alunos', 'Suporte Prioritário', 'Relatórios de Desempenho']
                },
                'pro': {
                    name: 'Plano Pro',
                    price: 'R$ 129,90',
                    benefits: ['Alunos Ilimitados', 'Suporte VIP 24/7', 'Todos Recursos Liberados']
                }
            };

            // Pega o plano da URL (?plano=xyz)
            const urlParams = new URLSearchParams(window.location.search);
            const planKey = urlParams.get('plano') || 'premium'; // Default se não tiver

            const selectedPlan = plans[planKey];

            if (selectedPlan) {
                document.getElementById('plan-name').textContent = selectedPlan.name;
                document.getElementById('plan-price').textContent = selectedPlan.price;
                
                const benefitsList = document.getElementById('plan-desc');
                benefitsList.innerHTML = ''; // Limpa
                selectedPlan.benefits.forEach(benefit => {
                    const li = document.createElement('li');
                    li.textContent = benefit;
                    benefitsList.appendChild(li);
                });
            }
        });

        function processPayment(e) {
            e.preventDefault();
            const btn = document.querySelector('.btn-pay');
            const originalText = btn.innerText;
            
            btn.innerText = 'Processando...';
            btn.style.background = '#ccc';
            btn.disabled = true;

            setTimeout(() => {
                btn.innerText = 'Sucesso! Redirecionando...';
                btn.style.background = 'var(--primary-green)';
                
                setTimeout(() => {
                    alert('Assinatura realizada com sucesso! Bem-vindo ao time.');
                    window.location.href = 'gerenciar_perfil_personal.php'; // Volta pro perfil
                }, 1000);
            }, 2000);
        }
    </script>

</body>
</html>