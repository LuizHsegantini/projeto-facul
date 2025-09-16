<?php
// includes/auth_check.php
session_start();

function checkAuth($required_profile = null) {
    // Verificar se o usuário está logado
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?error=Você precisa estar logado para acessar esta página');
        exit();
    }
    
    // Verificar perfil se especificado
    if ($required_profile && $_SESSION['user_profile'] !== $required_profile) {
        if ($required_profile === 'administrador' && !in_array($_SESSION['user_profile'], ['administrador'])) {
            header('Location: dashboard.php?error=Acesso negado: Permissões insuficientes');
            exit();
        }
        
        if ($required_profile === 'gerente' && !in_array($_SESSION['user_profile'], ['administrador', 'gerente'])) {
            header('Location: dashboard.php?error=Acesso negado: Permissões insuficientes');
            exit();
        }
    }
    
    return true;
}

function hasPermission($permission) {
    $user_profile = $_SESSION['user_profile'] ?? '';
    
    switch ($permission) {
        case 'admin':
            return $user_profile === 'administrador';
            
        case 'manage_projects':
            return in_array($user_profile, ['administrador', 'gerente']);
            
        case 'manage_users':
            return $user_profile === 'administrador';
            
        case 'view_reports':
            return in_array($user_profile, ['administrador', 'gerente']);
            
        default:
            return false;
    }
}

function getUserInfo() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'profile' => $_SESSION['user_profile'] ?? '',
        'login' => $_SESSION['user_login'] ?? '',
        'cargo' => $_SESSION['user_cargo'] ?? ''
    ];
}
?>