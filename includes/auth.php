<?php
// includes/auth.php - Versão Simplificada e Robusta

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
            $_SESSION['last_activity'] = time();
            
            // Registrar log de login usando LogService
            try {
                LogService::recordLog(
                    $user['id'], 
                    'Login realizado',
                    null,
                    null,
                    null,
                    null,
                    [
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                        'session_id' => session_id()
                    ]
                );
            } catch (Exception $e) {
                // Log falhou mas login continua
                error_log("Erro ao registrar log de login: " . $e->getMessage());
            }
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Erro na autenticação: " . $e->getMessage());
        return false;
    }
}

// Validar sessão básica
function validateSession() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Verificar timeout de 30 minutos
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive >= 1800) { // 30 minutos
            // Não chamar performLogout aqui para evitar recursão
            session_unset();
            session_destroy();
            return false;
        }
    }
    
    // Atualizar último acesso
    $_SESSION['last_activity'] = time();
    
    return true;
}

// Verificar se o usuário está logado
function requireLogin() {
    if (!validateSession()) {
        header('Location: login.php?error=' . urlencode('Você precisa estar logado'));
        exit();
    }
}

// Função para verificar autenticação com perfil específico
function checkAuth($required_profile = null) {
    if (!validateSession()) {
        header('Location: login.php?error=' . urlencode('Você precisa estar logado para acessar esta página'));
        exit();
    }
    
    if ($required_profile && $_SESSION['user_profile'] !== $required_profile) {
        if ($required_profile === 'administrador' && !in_array($_SESSION['user_profile'], ['administrador'])) {
            header('Location: dashboard_eventos.php?error=' . urlencode('Acesso negado: Permissões insuficientes'));
            exit();
        }
        
        if ($required_profile === 'coordenador' && !in_array($_SESSION['user_profile'], ['administrador', 'coordenador'])) {
            header('Location: dashboard_eventos.php?error=' . urlencode('Acesso negado: Permissões insuficientes'));
            exit();
        }
    }
    
    return true;
}

// Verificar se o usuário tem permissão específica
function hasPermission($permission) {
    if (!validateSession()) {
        return false;
    }
    
    $user_profile = $_SESSION['user_profile'];
    
    switch ($permission) {
        case 'admin':
        case 'administrador':
            return $user_profile === 'administrador';
            
        case 'manage_projects':
            return in_array($user_profile, ['administrador', 'coordenador']);
            
        case 'manage_users':
            return $user_profile === 'administrador';
            
        case 'view_reports':
            return in_array($user_profile, ['administrador', 'coordenador']);
            
        case 'coordenador':
            return in_array($user_profile, ['administrador', 'coordenador']);
            
        case 'animador':
            return in_array($user_profile, ['administrador', 'coordenador', 'animador']);
            
        case 'monitor':
            return in_array($user_profile, ['administrador', 'coordenador', 'animador', 'monitor']);
            
        case 'auxiliar':
        case 'colaborador':
            return in_array($user_profile, ['administrador', 'coordenador', 'animador', 'monitor', 'auxiliar']);
            
        default:
            return false;
    }
}

// Obter informações do usuário atual
function getCurrentUser() {
    if (!validateSession()) {
        return null;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user && isset($user['nome_completo'])) {
            $user['nome'] = $user['nome_completo'];
        }
        
        return $user;
    } catch (Exception $e) {
        error_log("Erro ao obter usuário atual: " . $e->getMessage());
        return null;
    }
}

// Obter informações básicas do usuário da sessão
function getUserInfo() {
    if (!validateSession()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'profile' => $_SESSION['user_profile'] ?? '',
        'login' => $_SESSION['user_login'] ?? '',
        'cargo' => $_SESSION['user_cargo'] ?? ''
    ];
}

// Função para registrar logs usando LogService
function logSystemAction($user_id, $action, $table = null, $record_id = null, $old_data = null, $new_data = null, array $metadata = []) {
    try {
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
        error_log("Erro ao registrar log: " . $e->getMessage());
        return false;
    }
}

// Função para logout simples e direto
function performLogout() {
    // Registrar logout se tiver usuário logado
    if (isset($_SESSION['user_id'])) {
        try {
            LogService::recordLog(
                $_SESSION['user_id'], 
                'Logout realizado',
                null,
                null,
                null,
                null,
                [
                    'method' => 'manual',
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            );
        } catch (Exception $e) {
            // Log falhou mas logout continua
            error_log("Erro ao registrar log de logout: " . $e->getMessage());
        }
    }
    
    // Limpar e destruir sessão
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    return true;
}

// Processar logout via POST direto com suporte aos efeitos visuais
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    
    try {
        performLogout();
        
        // Sempre redirecionar para login com sucesso (sem AJAX problemático)
        header('Location: login.php?logout=visual_success');
        exit();
        
    } catch (Exception $e) {
        error_log("Erro no logout: " . $e->getMessage());
        
        // Em caso de erro, ainda assim redirecionar
        header('Location: login.php?logout=error&msg=' . urlencode($e->getMessage()));
        exit();
    }
}

// Compatibilidade
function destroySession() {
    performLogout();
}
?>