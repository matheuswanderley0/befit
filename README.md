🏋️ BeFit - Plataforma de Personal Trainer & Alunos

Bem-vindo ao BeFit, a solução completa para conectar alunos a personal trainers de alta performance.

📋 Sobre o Projeto

O BeFit é uma plataforma web desenvolvida para facilitar a gestão de treinos e o acompanhamento entre personal trainers e seus alunos.

Para Alunos: Encontre o profissional ideal, receba treinos personalizados, acompanhe sua evolução de peso e tire dúvidas via chat.

Para Personais: Gerencie múltiplos alunos, crie fichas de treino detalhadas, acompanhe o progresso de cada cliente e divulgue seu perfil profissional.

🚀 Funcionalidades Principais

👤 Área do Aluno

Busca Inteligente: Encontre personals por nome ou especialidade (Hipertrofia, Yoga, Crossfit, etc.).

Visualização de Treinos: Acesse suas fichas de treino detalhadas (exercícios, séries, cargas).

Evolução: Acompanhe seu progresso de peso através de gráficos interativos.

Chat: Converse diretamente com seu personal para tirar dúvidas e receber feedback.

Perfil: Gerencie seus dados pessoais e objetivos fitness.

🎓 Área do Personal Trainer

Dashboard de Gestão: Visão geral de todos os alunos ativos e suas estatísticas.

Criação de Treinos: Ferramenta completa para montar fichas de treino personalizadas (Adicionar exercícios, séries, repetições e cargas).

Perfil Profissional: Página pública customizável com foto, bio, especialidades e planos de consultoria.

Gestão de Assinatura: Escolha entre planos (Avançado, Premium, Pro) para aumentar o limite de alunos na plataforma.

🛠️ Tecnologias Utilizadas

Este projeto foi construído utilizando tecnologias web padrão, garantindo compatibilidade e facilidade de manutenção.

Front-end: HTML5, CSS3 (Design Responsivo e Moderno).

Back-end: PHP (Estruturado e Funcional).

Banco de Dados: MySQL (Relacional).

Bibliotecas:

Chart.js (Para gráficos de evolução de peso).

Servidor Local Recomendado: WAMP, XAMPP ou Laragon.

📦 Como Instalar e Rodar

Siga estes passos para rodar o projeto em sua máquina local:

Pré-requisitos
Tenha o WAMP Server (ou similar como XAMPP) instalado para rodar PHP e MySQL.

Configuração dos Arquivos
Baixe ou clone este repositório.

Mova a pasta do projeto para dentro do diretório do servidor:

WAMP: C:\wamp64\www\befit

XAMPP: C:\xampp\htdocs\befit

Configuração do Banco de Dados
Abra o MySQL Workbench ou phpMyAdmin (geralmente em http://localhost/phpmyadmin).

Crie um novo banco de dados chamado befit_system (ou use o nome que preferir e ajuste no arquivo de conexão).

Importe o Banco: Execute o script SQL fornecido (befit_dados_completos.sql) na sua ferramenta de banco de dados. Isso criará todas as tabelas e populará com dados de teste.

Verifique o arquivo conexao.php na raiz do projeto e ajuste as credenciais se necessário (no WAMP, a senha do root geralmente é vazia):

$host = 'localhost'; $usuario = 'root'; $senha = ''; // Sua senha do banco (vazio no WAMP por padrão) $banco = 'befit_system';

Acessar
Abra seu navegador e digite: http://localhost/befit/

📂 Estrutura de Arquivos

index.php - Tela de Login.

cadastro.php - Tela de Cadastro (Aluno/Personal).

conexao.php - Configuração de conexão com o Banco de Dados.

Área do Aluno:

inicio_aluno.php - Dashboard principal.

meus_treinos.php - Visualização das fichas.

buscar_personal.php - Busca de profissionais.

perfil_aluno.php - Edição de dados pessoais.

Área do Personal:

inicio_personal.php - Dashboard de gestão.

perfil_cliente.php - Gerenciamento de um aluno específico.

criar_ficha.php - Ferramenta de criação de treinos.

gerenciar_perfil_personal.php - Edição do perfil profissional.

Comum:

chat.php - Sistema de mensagens.

perfil_personal.php - Visualização pública do perfil do personal.

🎨 Identidade Visual

O projeto segue uma paleta de cores moderna e energética:

🔵 Azul: #030A8C

🟢 Verde: #8BBF56

⚪ Fundo: #F4F4F4 a #FFFFFF

🤝 Equipe

Eduardo Henrique, Matheus Wanderley, Erick Castro, Bianca Campos, Caio Henrique, Arthur Rodrigues
