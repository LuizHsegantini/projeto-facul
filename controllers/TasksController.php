<?php
// controllers/TasksController.php
require_once 'config/database.php';
require_once 'includes/auth.php';

class TasksController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function index($search = '', $status = '', $project_id = '', $user_id = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(t.titulo LIKE :search OR t.descricao LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($status)) {
            $whereConditions[] = "t.status = :status";
            $params[':status'] = $status;
        }
        
        if (!empty($project_id)) {
            $whereConditions[] = "t.projeto_id = :project_id";
            $params[':project_id'] = $project_id;
        }
        
        if (!empty($user_id)) {
            $whereConditions[] = "t.responsavel_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $query = "SELECT t.*, p.nome as projeto_nome, u.nome_completo as responsavel_nome 
                  FROM tarefas t 
                  LEFT JOIN projetos p ON t.projeto_id = p.id 
                  LEFT JOIN usuarios u ON t.responsavel_id = u.id 
                  $whereClause
                  ORDER BY t.data_criacao DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $tasks = $stmt->fetchAll();
        
        // Contar total de registros
        $countQuery = "SELECT COUNT(*) as total FROM tarefas t 
                       LEFT JOIN projetos p ON t.projeto_id = p.id 
                       LEFT JOIN usuarios u ON t.responsavel_id = u.id 
                       $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetch()['total'];
        
        return [
            'tasks' => $tasks,
            'total' => $totalRecords,
            'pages' => ceil($totalRecords / $limit),
            'current_page' => $page
        ];
    }
    
    public function create($data) {
        $query = "INSERT INTO tarefas (titulo, descricao, projeto_id, responsavel_id, status, data_inicio, data_fim_prevista) 
                  VALUES (:titulo, :descricao, :projeto_id, :responsavel_id, :status, :data_inicio, :data_fim_prevista)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':titulo', $data['titulo']);
        $stmt->bindParam(':descricao', $data['descricao']);
        $stmt->bindParam(':projeto_id', $data['projeto_id']);
        $stmt->bindParam(':responsavel_id', $data['responsavel_id']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':data_inicio', $data['data_inicio']);
        $stmt->bindParam(':data_fim_prevista', $data['data_fim_prevista']);
        
        if ($stmt->execute()) {
            $task_id = $this->db->lastInsertId();
            logSystemAction($_SESSION['user_id'], 'Tarefa criada', 'tarefas', $task_id, null, $data);
            return $task_id;
        }
        
        return false;
    }
    
    public function update($id, $data) {
        // Buscar dados anteriores para log
        $oldData = $this->getById($id);
        
        // Se status mudou para 'concluida', definir data_fim_real
        if ($data['status'] === 'concluida' && $oldData['status'] !== 'concluida') {
            $data['data_fim_real'] = date('Y-m-d');
        }
        
        $query = "UPDATE tarefas 
                  SET titulo = :titulo, descricao = :descricao, projeto_id = :projeto_id, 
                      responsavel_id = :responsavel_id, status = :status, data_inicio = :data_inicio, 
                      data_fim_prevista = :data_fim_prevista" . 
                  (isset($data['data_fim_real']) ? ", data_fim_real = :data_fim_real" : "") . 
                  " WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titulo', $data['titulo']);
        $stmt->bindParam(':descricao', $data['descricao']);
        $stmt->bindParam(':projeto_id', $data['projeto_id']);
        $stmt->bindParam(':responsavel_id', $data['responsavel_id']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':data_inicio', $data['data_inicio']);
        $stmt->bindParam(':data_fim_prevista', $data['data_fim_prevista']);
        
        if (isset($data['data_fim_real'])) {
            $stmt->bindParam(':data_fim_real', $data['data_fim_real']);
        }
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Tarefa atualizada', 'tarefas', $id, $oldData, $data);
            return true;
        }
        
        return false;
    }
    
    public function delete($id) {
        $oldData = $this->getById($id);
        
        $query = "DELETE FROM tarefas WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Tarefa excluída', 'tarefas', $id, $oldData, null);
            return true;
        }
        
        return false;
    }
    
    public function getById($id) {
        $query = "SELECT t.*, p.nome as projeto_nome, u.nome_completo as responsavel_nome 
                  FROM tarefas t 
                  LEFT JOIN projetos p ON t.projeto_id = p.id 
                  LEFT JOIN usuarios u ON t.responsavel_id = u.id 
                  WHERE t.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getProjects() {
        $query = "SELECT id, nome FROM projetos WHERE status != 'cancelado' ORDER BY nome";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getUsers() {
        $query = "SELECT id, nome_completo FROM usuarios ORDER BY nome_completo";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $status) {
        $oldData = $this->getById($id);
        
        $updateData = ['status' => $status];
        
        // Se status mudou para 'concluida', definir data_fim_real
        if ($status === 'concluida' && $oldData['status'] !== 'concluida') {
            $updateData['data_fim_real'] = date('Y-m-d');
        }
        
        $query = "UPDATE tarefas SET status = :status" . 
                 ($status === 'concluida' && $oldData['status'] !== 'concluida' ? ", data_fim_real = :data_fim_real" : "") . 
                 " WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        
        if ($status === 'concluida' && $oldData['status'] !== 'concluida') {
            $stmt->bindParam(':data_fim_real', $updateData['data_fim_real']);
        }
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Status da tarefa atualizado', 'tarefas', $id, $oldData, $updateData);
            return true;
        }
        
        return false;
    }
    
    public function getTasksByProject($project_id) {
        $query = "SELECT t.*, u.nome_completo as responsavel_nome 
                  FROM tarefas t 
                  LEFT JOIN usuarios u ON t.responsavel_id = u.id 
                  WHERE t.projeto_id = :project_id 
                  ORDER BY t.data_criacao DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getTasksByUser($user_id) {
        $query = "SELECT t.*, p.nome as projeto_nome 
                  FROM tarefas t 
                  LEFT JOIN projetos p ON t.projeto_id = p.id 
                  WHERE t.responsavel_id = :user_id 
                  ORDER BY t.data_fim_prevista ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getOverdueTasks() {
        $query = "SELECT t.*, p.nome as projeto_nome, u.nome_completo as responsavel_nome 
                  FROM tarefas t 
                  LEFT JOIN projetos p ON t.projeto_id = p.id 
                  LEFT JOIN usuarios u ON t.responsavel_id = u.id 
                  WHERE t.status != 'concluida' 
                  AND t.data_fim_prevista < CURDATE() 
                  ORDER BY t.data_fim_prevista ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>