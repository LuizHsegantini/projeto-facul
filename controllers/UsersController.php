<?php
// controllers/UsersController.php
require_once 'config/database.php';
require_once 'includes/auth.php';

class UsersController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function index($search = '', $perfil = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(nome_completo LIKE :search OR email LIKE :search OR cpf LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($perfil)) {
            $whereConditions[] = "perfil = :perfil";
            $params[':perfil'] = $perfil;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $query = "SELECT id, nome_completo, cpf, email, cargo, login, perfil, data_criacao, data_atualizacao 
                  FROM usuarios 
                  $whereClause
                  ORDER BY data_criacao DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $users = $stmt->fetchAll();
        
        // Contar total de registros
        $countQuery = "SELECT COUNT(*) as total FROM usuarios $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetch()['total'];
        
        return [
            'users' => $users,
            'total' => $totalRecords,
            'pages' => ceil($totalRecords / $limit),
            'current_page' => $page
        ];
    }
    
    public function create($data) {
        // Verificar se login, email ou CPF já existem
        if ($this->checkExists('login', $data['login'])) {
            return ['error' => 'Login já está em uso'];
        }
        
        if ($this->checkExists('email', $data['email'])) {
            return ['error' => 'Email já está em uso'];
        }
        
        if ($this->checkExists('cpf', $data['cpf'])) {
            return ['error' => 'CPF já está em uso'];
        }
        
        $query = "INSERT INTO usuarios (nome_completo, cpf, email, cargo, login, senha, perfil) 
                  VALUES (:nome_completo, :cpf, :email, :cargo, :login, :senha, :perfil)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nome_completo', $data['nome_completo']);
        $stmt->bindParam(':cpf', $data['cpf']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':cargo', $data['cargo']);
        $stmt->bindParam(':login', $data['login']);
        $stmt->bindValue(':senha', md5($data['senha'])); // Em produção, usar password_hash()
        $stmt->bindParam(':perfil', $data['perfil']);
        
        if ($stmt->execute()) {
            $user_id = $this->db->lastInsertId();
            $logData = $data;
            unset($logData['senha']); // Não registrar senha no log
            logSystemAction($_SESSION['user_id'], 'Usuário criado', 'usuarios', $user_id, null, $logData);
            return ['success' => true, 'id' => $user_id];
        }
        
        return ['error' => 'Erro ao criar usuário'];
    }
    
    public function update($id, $data) {
        // Buscar dados anteriores para log
        $oldData = $this->getById($id);
        
        // Verificar se login, email ou CPF já existem (exceto para o próprio usuário)
        if (isset($data['login']) && $this->checkExists('login', $data['login'], $id)) {
            return ['error' => 'Login já está em uso'];
        }
        
        if (isset($data['email']) && $this->checkExists('email', $data['email'], $id)) {
            return ['error' => 'Email já está em uso'];
        }
        
        if (isset($data['cpf']) && $this->checkExists('cpf', $data['cpf'], $id)) {
            return ['error' => 'CPF já está em uso'];
        }
        
        $fields = [];
        $params = [':id' => $id];
        
        foreach (['nome_completo', 'cpf', 'email', 'cargo', 'login', 'perfil'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        
        // Se senha foi fornecida, incluir na atualização
        if (isset($data['senha']) && !empty($data['senha'])) {
            $fields[] = "senha = :senha";
            $params[':senha'] = md5($data['senha']); // Em produção, usar password_hash()
        }
        
        if (empty($fields)) {
            return ['error' => 'Nenhum dado para atualizar'];
        }
        
        $query = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute($params)) {
            $logData = $data;
            if (isset($logData['senha'])) unset($logData['senha']); // Não registrar senha no log
            logSystemAction($_SESSION['user_id'], 'Usuário atualizado', 'usuarios', $id, $oldData, $logData);
            return ['success' => true];
        }
        
        return ['error' => 'Erro ao atualizar usuário'];
    }
    
    public function delete($id) {
        // Verificar se usuário tem tarefas ou projetos associados
        if ($this->hasAssociations($id)) {
            return ['error' => 'Não é possível excluir usuário com tarefas ou projetos associados'];
        }
        
        $oldData = $this->getById($id);
        
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Usuário excluído', 'usuarios', $id, $oldData, null);
            return ['success' => true];
        }
        
        return ['error' => 'Erro ao excluir usuário'];
    }
    
    public function getById($id) {
        $query = "SELECT id, nome_completo, cpf, email, cargo, login, perfil, data_criacao, data_atualizacao 
                  FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function changePassword($id, $current_password, $new_password) {
        // Verificar senha atual
        $query = "SELECT senha FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch();
        
        if (!$user || md5($current_password) !== $user['senha']) {
            return ['error' => 'Senha atual incorreta'];
        }
        
        // Atualizar senha
        $query = "UPDATE usuarios SET senha = :senha WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindValue(':senha', md5($new_password)); // Em produção, usar password_hash()
        
        if ($stmt->execute()) {
            logSystemAction($id, 'Senha alterada', 'usuarios', $id);
            return ['success' => true];
        }
        
        return ['error' => 'Erro ao alterar senha'];
    }
    
    public function getUserStats($user_id) {
        // Total de tarefas do usuário
        $query = "SELECT COUNT(*) as total_tarefas FROM tarefas WHERE responsavel_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $totalTarefas = $stmt->fetch()['total_tarefas'];
        
        // Tarefas concluídas
        $query = "SELECT COUNT(*) as tarefas_concluidas FROM tarefas WHERE responsavel_id = :user_id AND status = 'concluida'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $tarefasConcluidas = $stmt->fetch()['tarefas_concluidas'];
        
        // Tarefas pendentes
        $query = "SELECT COUNT(*) as tarefas_pendentes FROM tarefas WHERE responsavel_id = :user_id AND status = 'pendente'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $tarefasPendentes = $stmt->fetch()['tarefas_pendentes'];
        
        // Projetos como gerente (se aplicável)
        $projetos = 0;
        if ($this->getById($user_id)['perfil'] !== 'colaborador') {
            $query = "SELECT COUNT(*) as projetos FROM projetos WHERE gerente_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $projetos = $stmt->fetch()['projetos'];
        }
        
        return [
            'total_tarefas' => $totalTarefas,
            'tarefas_concluidas' => $tarefasConcluidas,
            'tarefas_pendentes' => $tarefasPendentes,
            'projetos_gerenciados' => $projetos,
            'percentual_conclusao' => $totalTarefas > 0 ? round(($tarefasConcluidas / $totalTarefas) * 100, 2) : 0
        ];
    }
    
    public function getUserTeams($user_id) {
        $query = "SELECT e.*, em.data_entrada 
                  FROM equipes e 
                  INNER JOIN equipe_membros em ON e.id = em.equipe_id 
                  WHERE em.usuario_id = :user_id 
                  ORDER BY em.data_entrada DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function checkExists($field, $value, $exclude_id = null) {
        $query = "SELECT id FROM usuarios WHERE $field = :value";
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':value', $value);
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    private function hasAssociations($user_id) {
        // Verificar tarefas
        $query = "SELECT COUNT(*) as count FROM tarefas WHERE responsavel_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        if ($stmt->fetch()['count'] > 0) return true;
        
        // Verificar projetos
        $query = "SELECT COUNT(*) as count FROM projetos WHERE gerente_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        if ($stmt->fetch()['count'] > 0) return true;
        
        return false;
    }
}
?>