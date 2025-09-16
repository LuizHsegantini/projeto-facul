<?php
// includes/auth.php
session_start();

require_once 'config/database.php';

// Verificar se o usuário está logado
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Verificar se o usuário tem permissão específica
function hasPermission($required_profile) {
    if (!isset($_SESSION['user_profile'])) {
        return false;
    }
    
    $user_profile = $_SESSION['user_profile'];
    
    // Administrador tem acesso a tudo
    if ($user_profile === 'administrador') {
        return true;
    }
    
    // Gerente tem acesso a funcionalidades de gerente e colaborador
    if ($user_profile === 'gerente' && in_array($required_profile, ['gerente', 'colaborador'])) {
        return true;
    }
    
    // Colaborador só tem acesso às suas próprias funcionalidades
    if ($user_profile === 'colaborador' && $required_profile === 'colaborador') {
        return true;
    }
    
    return false;
}

// Obter dados do usuário atual
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

// Função para registrar ações no sistema (logs)
function logSystemAction($user_id, $action, $table = null, $record_id = null, $old_data = null, $new_data = null) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Obter IP do usuário
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip_address = $_SERVER['HTTP_X_REAL_IP'];
        }
        
        // Converter arrays para JSON se necessário
        $old_data_json = null;
        $new_data_json = null;
        
        if ($old_data !== null) {
            $old_data_json = is_array($old_data) ? json_encode($old_data, JSON_UNESCAPED_UNICODE) : $old_data;
        }
        
        if ($new_data !== null) {
            $new_data_json = is_array($new_data) ? json_encode($new_data, JSON_UNESCAPED_UNICODE) : $new_data;
        }
        
        $query = "INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos, ip_address) 
                  VALUES (:user_id, :action, :table, :record_id, :old_data, :new_data, :ip_address)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':table', $table);
        $stmt->bindParam(':record_id', $record_id);
        $stmt->bindParam(':old_data', $old_data_json);
        $stmt->bindParam(':new_data', $new_data_json);
        $stmt->bindParam(':ip_address', $ip_address);
        
        return $stmt->execute();
        
    } catch (Exception $e) {
        // Log do erro mas não interromper a execução principal
        error_log("Erro ao registrar log do sistema: " . $e->getMessage());
        return false;
    }
}

// Fazer logout
function logout() {
    if (isset($_SESSION['user_id'])) {
        logSystemAction($_SESSION['user_id'], 'Logout realizado');
    }
    
    session_destroy();
    header('Location: login.php');
    exit();
}

// Processar logout se solicitado
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}
?>