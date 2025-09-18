<?php
// controllers/ProfileController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/LogService.php';

class ProfileController
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getUserProfile($userId)
    {
        try {
            $sql = "SELECT 
                        id,
                        nome_completo,
                        cpf,
                        email,
                        cargo,
                        login,
                        perfil,
                        data_criacao,
                        data_atualizacao
                    FROM usuarios 
                    WHERE id = :user_id";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar perfil do usuário: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfile($userId, $data)
    {
        try {
            // Buscar dados atuais para o log
            $currentData = $this->getUserProfile($userId);
            
            $sql = "UPDATE usuarios SET 
                        nome_completo = :nome_completo,
                        email = :email,
                        cargo = :cargo,
                        data_atualizacao = CURRENT_TIMESTAMP
                    WHERE id = :user_id";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nome_completo', $data['nome_completo']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':cargo', $data['cargo']);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Registrar log da alteração
                LogService::recordLog(
                    $userId,
                    'Perfil atualizado',
                    'usuarios',
                    $userId,
                    $currentData,
                    array_merge($data, ['id' => $userId])
                );
                
                return true;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Erro ao atualizar perfil: " . $e->getMessage());
            return false;
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            // Verificar senha atual
            $sql = "SELECT senha FROM usuarios WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || md5($currentPassword) !== $user['senha']) {
                return ['success' => false, 'message' => 'Senha atual incorreta'];
            }
            
            // Atualizar senha
            $sql = "UPDATE usuarios SET 
                        senha = :new_password,
                        data_atualizacao = CURRENT_TIMESTAMP
                    WHERE id = :user_id";
                    
            $stmt = $this->conn->prepare($sql);
            $newPasswordHash = md5($newPassword);
            $stmt->bindParam(':new_password', $newPasswordHash);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Registrar log
                LogService::recordLog(
                    $userId,
                    'Senha alterada',
                    'usuarios',
                    $userId,
                    null,
                    ['senha_alterada' => true]
                );
                
                return ['success' => true, 'message' => 'Senha alterada com sucesso'];
            }
            
            return ['success' => false, 'message' => 'Erro ao alterar senha'];
            
        } catch (PDOException $e) {
            error_log("Erro ao alterar senha: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do sistema'];
        }
    }

    public function getUserStats($userId)
    {
        try {
            $stats = [];
            
            // Total de logs do usuário
            $sql = "SELECT COUNT(*) FROM logs_sistema WHERE usuario_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $stats['total_logs'] = $stmt->fetchColumn();
            
            // Equipes que participa
            $sql = "SELECT COUNT(*) FROM equipe_membros WHERE usuario_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $stats['total_equipes'] = $stmt->fetchColumn();
            
            // Atividades atribuídas
            $sql = "SELECT COUNT(*) FROM tarefas WHERE responsavel_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $stats['total_atividades'] = $stmt->fetchColumn();
            
            // Eventos coordenados (se for coordenador)
            $sql = "SELECT COUNT(*) FROM eventos WHERE coordenador_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $stats['total_eventos_coordenados'] = $stmt->fetchColumn();
            
            // Último login
            $sql = "SELECT data_criacao FROM logs_sistema 
                    WHERE usuario_id = :user_id AND acao = 'Login realizado'
                    ORDER BY data_criacao DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $stats['ultimo_login'] = $stmt->fetchColumn();
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas do usuário: " . $e->getMessage());
            return [];
        }
    }

    public function getUserActivities($userId, $limit = 10)
    {
        try {
            $sql = "SELECT 
                        t.id,
                        t.titulo,
                        t.status,
                        t.data_inicio,
                        t.data_fim_prevista,
                        e.nome as evento_nome
                    FROM tarefas t
                    LEFT JOIN eventos e ON e.id = t.evento_id
                    WHERE t.responsavel_id = :user_id
                    ORDER BY t.data_atualizacao DESC
                    LIMIT :limit";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar atividades do usuário: " . $e->getMessage());
            return [];
        }
    }

    public function getUserTeams($userId)
    {
        try {
            $sql = "SELECT 
                        e.id,
                        e.nome,
                        e.especialidade,
                        em.data_entrada
                    FROM equipe_membros em
                    JOIN equipes e ON e.id = em.equipe_id
                    WHERE em.usuario_id = :user_id
                    ORDER BY em.data_entrada DESC";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar equipes do usuário: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentLogs($userId, $limit = 5)
    {
        try {
            $sql = "SELECT 
                        acao,
                        tabela_afetada,
                        data_criacao
                    FROM logs_sistema 
                    WHERE usuario_id = :user_id
                    ORDER BY data_criacao DESC 
                    LIMIT :limit";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar logs recentes: " . $e->getMessage());
            return [];
        }
    }

    public function validateProfileData($data)
    {
        $errors = [];
        
        // Validar nome
        if (empty(trim($data['nome_completo']))) {
            $errors[] = "Nome completo é obrigatório";
        } elseif (strlen(trim($data['nome_completo'])) < 3) {
            $errors[] = "Nome completo deve ter pelo menos 3 caracteres";
        }
        
        // Validar email
        if (empty(trim($data['email']))) {
            $errors[] = "Email é obrigatório";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email inválido";
        } else {
            // Verificar se email já existe para outro usuário
            $sql = "SELECT id FROM usuarios WHERE email = :email AND id != :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                $errors[] = "Este email já está sendo usado por outro usuário";
            }
        }
        
        // Validar cargo
        if (empty(trim($data['cargo']))) {
            $errors[] = "Cargo é obrigatório";
        }
        
        return $errors;
    }

    public function validatePassword($password, $confirmPassword)
    {
        $errors = [];
        
        if (empty($password)) {
            $errors[] = "Nova senha é obrigatória";
        } elseif (strlen($password) < 6) {
            $errors[] = "Nova senha deve ter pelo menos 6 caracteres";
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = "Confirmação de senha não confere";
        }
        
        return $errors;
    }
}