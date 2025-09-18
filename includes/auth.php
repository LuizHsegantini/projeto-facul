<?php
// includes/auth.php - Versão Unificada

// Iniciar sessão apenas se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/LogService.php';

// Função para fazer login
function login($username, $password) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Usar MD5 como no banco de dados original
        $query = "SELECT * FROM usuarios WHERE login = :login AND senha = MD5(:senha)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':login', $username);
        $stmt->bindParam(':senha', $password);
        
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            // Definir variáveis de sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome_completo'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_profile'] = $user['perfil'];
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['user_cargo'] = $user['cargo'] ?? '';
            
            // Registrar log de login
            logSystemAction($user['id'], 'Login realizado');
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Erro na autenticação: " . $e->getMessage());
        return false;
    }
}

// Verificar se o usuário está logado
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Função para verificar autenticação com perfil específico
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

// Verificar se o usuário tem permissão específica
function hasPermission($permission) {
    if (!isset($_SESSION['user_profile'])) {
        return false;
    }
    
    $user_profile = $_SESSION['user_profile'];
    
    // Para compatibilidade com ambos os sistemas
    switch ($permission) {
        case 'admin':
        case 'administrador':
            return $user_profile === 'administrador';
            
        case 'manage_projects':
            return in_array($user_profile, ['administrador', 'gerente']);
            
        case 'manage_users':
            return $user_profile === 'administrador';
            
        case 'view_reports':
            return in_array($user_profile, ['administrador', 'gerente']);
            
        case 'gerente':
            return in_array($user_profile, ['administrador', 'gerente']);
            
        case 'colaborador':
            return in_array($user_profile, ['administrador', 'gerente', 'colaborador']);
            
        default:
            return false;
    }
}

// Obter informações do usuário atual
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    
    $user = $stmt->fetch();
    
    // Mapear campo nome_completo para nome para compatibilidade
    if ($user && isset($user['nome_completo'])) {
        $user['nome'] = $user['nome_completo'];
    }
    
    return $user;
}

// Obter informações básicas do usuário da sessão
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

// Função para registrar ações no sistema (logs)
function logSystemAction($user_id, $action, $table = null, $record_id = null, $old_data = null, $new_data = null, array $metadata = []) {
    try {
        $autoMetadata = [
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'session_user_name' => $_SESSION['user_name'] ?? null,
            'session_user_profile' => $_SESSION['user_profile'] ?? null,
        ];

        $metadata = array_filter(
            array_merge($autoMetadata, $metadata),
            static function ($value) {
                return $value !== null && $value !== '';
            }
        );

        return LogService::recordLog(
            (int) $user_id,
            $action,
            $table,
            $record_id !== null ? (int) $record_id : null,
            $old_data,
            $new_data,
            $metadata
        );
    } catch (Exception $e) {
        error_log("Erro ao registrar log do sistema: " . $e->getMessage());
        return false;
    }
}


// Função para fazer logout simples (sem animação) - APENAS PARA EMERGÊNCIA
function simpleLogout() {
    if (isset($_SESSION['user_id'])) {
        logSystemAction($_SESSION['user_id'], 'Logout simples realizado');
    }
    
    session_destroy();
    header('Location: login.php?logout=1');
    exit();
}

// Processar logout - AGORA REDIRECIONA PARA LOGOUT.PHP COM ANIMAÇÕES
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Em vez de fazer logout direto, redirecionar para logout.php
    header('Location: logout.php');
    exit();
}

// Função auxiliar para logs (pode ser usada pelo logout.php se necessário)
function logLogoutAction($user_id, $action = 'Logout realizado') {
    return logSystemAction($user_id, $action);
}
?>