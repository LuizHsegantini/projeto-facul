<?php
// controllers/ProfileController.php - CORRIGIDO
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/LogService.php';

class ProfileController
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Verificar se a conexão foi estabelecida
        if (!$this->conn) {
            error_log("Erro: Não foi possível conectar ao banco de dados");
            throw new Exception("Falha na conexão com o banco de dados");
        }
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
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("Usuário não encontrado: ID = $userId");
                return false;
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar perfil do usuário: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfile($userId, $data)
    {
        try {
            // VERIFICAÇÃO CRÍTICA: Verificar se os dados estão sendo recebidos
            error_log("ProfileController::updateProfile - Dados recebidos: " . print_r($data, true));
            error_log("ProfileController::updateProfile - UserID: " . $userId);
            
            // Buscar dados atuais para o log e verificação
            $currentData = $this->getUserProfile($userId);
            if (!$currentData) {
                error_log("Erro: Usuário não encontrado para atualização: ID = $userId");
                return false;
            }
            
            error_log("ProfileController::updateProfile - Dados atuais: " . print_r($currentData, true));
            
            // VALIDAÇÃO: Verificar se os campos necessários estão presentes
            if (empty($data['nome_completo']) || empty($data['email']) || empty($data['cargo'])) {
                error_log("Erro: Campos obrigatórios não preenchidos");
                return false;
            }
            
            // Iniciar transação para garantir consistência
            $this->conn->beginTransaction();
            
            $sql = "UPDATE usuarios SET 
                        nome_completo = :nome_completo,
                        email = :email,
                        cargo = :cargo,
                        data_atualizacao = NOW()
                    WHERE id = :user_id";
                    
            $stmt = $this->conn->prepare($sql);
            
            // Binding com verificação
            $nome = trim($data['nome_completo']);
            $email = trim($data['email']);
            $cargo = trim($data['cargo']);
            
            $stmt->bindParam(':nome_completo', $nome, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':cargo', $cargo, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            // Log antes da execução
            error_log("ProfileController::updateProfile - SQL: " . $sql);
            error_log("ProfileController::updateProfile - Parâmetros: nome=$nome, email=$email, cargo=$cargo, id=$userId");
            
            $success = $stmt->execute();
            
            if ($success) {
                $rowsAffected = $stmt->rowCount();
                error_log("ProfileController::updateProfile - Linhas afetadas: " . $rowsAffected);
                
                if ($rowsAffected > 0) {
                    // Commit da transação
                    $this->conn->commit();
                    
                    // Registrar log da alteração
                    LogService::recordLog(
                        $userId,
                        'Perfil atualizado',
                        'usuarios',
                        $userId,
                        $currentData,
                        array_merge($data, ['id' => $userId])
                    );
                    
                    // Atualizar sessão se necessário
                    $this->updateUserSession($userId);
                    
                    error_log("ProfileController::updateProfile - Sucesso!");
                    return true;
                } else {
                    error_log("ProfileController::updateProfile - Nenhuma linha foi afetada");
                    $this->conn->rollback();
                    return false;
                }
            } else {
                error_log("ProfileController::updateProfile - Falha na execução do SQL");
                $this->conn->rollback();
                return false;
            }
            
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollback();
            }
            error_log("Erro ao atualizar perfil: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollback();
            }
            error_log("Erro geral ao atualizar perfil: " . $e->getMessage());
            return false;
        }
    }

    private function updateUserSession($userId)
    {
        try {
            // Buscar dados atualizados
            $updatedData = $this->getUserProfile($userId);
            if ($updatedData) {
                // Atualizar dados na sessão
                $_SESSION['user'] = $updatedData;
                error_log("Sessão do usuário atualizada");
            }
        } catch (Exception $e) {
            error_log("Erro ao atualizar sessão: " . $e->getMessage());
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            error_log("ProfileController::changePassword - Iniciando alteração de senha para usuário: $userId");
            
            // Verificar senha atual
            $sql = "SELECT senha FROM usuarios WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("Usuário não encontrado para alteração de senha: ID = $userId");
                return ['success' => false, 'message' => 'Usuário não encontrado'];
            }
            
            // Verificar senha atual (assumindo que está usando MD5 - considere migrar para password_hash())
            $currentPasswordHash = md5($currentPassword);
            if ($currentPasswordHash !== $user['senha']) {
                error_log("Senha atual incorreta para usuário: ID = $userId");
                return ['success' => false, 'message' => 'Senha atual incorreta'];
            }
            
            // Iniciar transação
            $this->conn->beginTransaction();
            
            // Atualizar senha
            $sql = "UPDATE usuarios SET 
                        senha = :new_password,
                        data_atualizacao = NOW()
                    WHERE id = :user_id";
                    
            $stmt = $this->conn->prepare($sql);
            $newPasswordHash = md5($newPassword);
            $stmt->bindParam(':new_password', $newPasswordHash, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                $this->conn->commit();
                
                // Registrar log
                LogService::recordLog(
                    $userId,
                    'Senha alterada',
                    'usuarios',
                    $userId,
                    null,
                    ['senha_alterada' => true, 'data_alteracao' => date('Y-m-d H:i:s')]
                );
                
                error_log("Senha alterada com sucesso para usuário: ID = $userId");
                return ['success' => true, 'message' => 'Senha alterada com sucesso'];
            } else {
                $this->conn->rollback();
                error_log("Falha ao alterar senha para usuário: ID = $userId");
                return ['success' => false, 'message' => 'Erro ao alterar senha'];
            }
            
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollback();
            }
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
            $stats['total_logs'] = $stmt->fetchColumn() ?: 0;
            
            // Equipes que participa
            $sql = "SELECT COUNT(*) FROM equipe_membros WHERE usuario_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $stats['total_equipes'] = $stmt->fetchColumn() ?: 0;
            
            // Atividades atribuídas
            $sql = "SELECT COUNT(*) FROM tarefas WHERE responsavel_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $stats['total_atividades'] = $stmt->fetchColumn() ?: 0;
            
            // Eventos coordenados (se for coordenador)
            $sql = "SELECT COUNT(*) FROM eventos WHERE coordenador_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $stats['total_eventos_coordenados'] = $stmt->fetchColumn() ?: 0;
            
            // Último login
            $sql = "SELECT data_criacao FROM logs_sistema 
                    WHERE usuario_id = :user_id AND acao LIKE '%login%'
                    ORDER BY data_criacao DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $stats['ultimo_login'] = $stmt->fetchColumn();
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas do usuário: " . $e->getMessage());
            return [
                'total_logs' => 0,
                'total_equipes' => 0,
                'total_atividades' => 0,
                'total_eventos_coordenados' => 0,
                'ultimo_login' => null
            ];
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
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
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
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
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
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar logs recentes: " . $e->getMessage());
            return [];
        }
    }

    public function validateProfileData($data)
    {
        $errors = [];
        
        // Log dos dados recebidos para validação
        error_log("ProfileController::validateProfileData - Dados: " . print_r($data, true));
        
        // Validar nome
        if (empty(trim($data['nome_completo'] ?? ''))) {
            $errors[] = "Nome completo é obrigatório";
        } elseif (strlen(trim($data['nome_completo'])) < 3) {
            $errors[] = "Nome completo deve ter pelo menos 3 caracteres";
        }
        
        // Validar email
        if (empty(trim($data['email'] ?? ''))) {
            $errors[] = "Email é obrigatório";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email inválido";
        } else {
            // Verificar se email já existe para outro usuário
            try {
                $sql = "SELECT id FROM usuarios WHERE email = :email AND id != :user_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':email', $data['email']);
                $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                
                if ($stmt->fetch()) {
                    $errors[] = "Este email já está sendo usado por outro usuário";
                }
            } catch (PDOException $e) {
                error_log("Erro ao verificar email duplicado: " . $e->getMessage());
            }
        }
        
        // Validar cargo
        if (empty(trim($data['cargo'] ?? ''))) {
            $errors[] = "Cargo é obrigatório";
        }
        
        if (!empty($errors)) {
            error_log("ProfileController::validateProfileData - Erros encontrados: " . implode(', ', $errors));
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

    // Método de debug para verificar se os dados estão chegando
    public function debugFormData($postData)
    {
        error_log("=== DEBUG FORM DATA ===");
        error_log("POST Data: " . print_r($postData, true));
        error_log("Session Data: " . print_r($_SESSION ?? [], true));
        error_log("=======================");
    }
}