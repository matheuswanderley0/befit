<?php
// 1. Inicia a sessão
session_start();

// 2. Remove todas as variáveis de sessão
$_SESSION = array();

// 3. Se desejar destruir o cookie da sessão (limpeza completa)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destrói a sessão
session_destroy();

// 5. Redireciona para a página de login
header("Location: index.php");
exit();
?>