<?php
session_start();

// Registrar log de logout se estiver logado
if (isset($_SESSION['user_id'])) {
    require_once 'models/User.php';
    
    $userModel = new User();
    $userModel->logAction($_SESSION['user_id'], 'Logout realizado');
}

// Destruir todas as variáveis de sessão
session_unset();

// Destruir a sessão
session_destroy();

// Redirecionar para login com mensagem de sucesso
header('Location: login.php?logout=1');
exit();
?>