<?php
// controllers/ProjectsController.php
require_once 'config/database.php';
require_once 'includes/auth.php';

class ProjectsController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function index($search = '', $status = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(p.nome LIKE :search OR p.descricao LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($status)) {
            $whereConditions[] = "p.status = :status";
            $params[':status'] = $status;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $query = "SELECT p.*, u.nome_completo as gerente_nome 
                  FROM projetos p 
                  LEFT JOIN usuarios u ON p.gerente_id = u.id 
                  $whereClause
                  ORDER BY p.data_criacao DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $projects = $stmt->fetchAll();
        
        // Contar total de registros
        $countQuery = "SELECT COUNT(*) as total FROM projetos p $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetch()['total'];
        
        return [
            'projects' => $projects,
            'total' => $totalRecords,
            'pages' => ceil($totalRecords / $limit),
            'current_page' => $page
        ];
    }
    
    public function create($data) {
        $query = "INSERT INTO projetos (nome, descricao, data_inicio, data_termino_prevista, status, gerente_id) 
                  VALUES (:nome, :descricao, :data_inicio, :data_termino_prevista, :status, :gerente_id)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':descricao', $data['descricao']);
        $stmt->bindParam(':data_inicio', $data['data_inicio']);
        $stmt->bindParam(':data_termino_prevista', $data['data_termino_prevista']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':gerente_id', $data['gerente_id']);
        
        if ($stmt->execute()) {
            $project_id = $this->db->lastInsertId();
            logSystemAction($_SESSION['user_id'], 'Projeto criado', 'projetos', $project_id, null, $data);
            return $project_id;
        }
        
        return false;
    }
    
    public function update($id, $data) {
        // Buscar dados anteriores para log
        $oldData = $this->getById($id);
        
        $query = "UPDATE projetos 
                  SET nome = :nome, descricao = :descricao, data_inicio = :data_inicio, 
                      data_termino_prevista = :data_termino_prevista, status = :status, gerente_id = :gerente_id 
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':descricao', $data['descricao']);
        $stmt->bindParam(':data_inicio', $data['data_inicio']);
        $stmt->bindParam(':data_termino_prevista', $data['data_termino_prevista']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':gerente_id', $data['gerente_id']);
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Projeto atualizado', 'projetos', $id, $oldData, $data);
            return true;
        }
        
        return false;
    }
    
    public function delete($id) {
        $oldData = $this->getById($id);
        
        $query = "DELETE FROM projetos WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Projeto excluído', 'projetos', $id, $oldData, null);
            return true;
        }
        
        return false;
    }
    
    public function getById($id) {
        $query = "SELECT p.*, u.nome_completo as gerente_nome 
                  FROM projetos p 
                  LEFT JOIN usuarios u ON p.gerente_id = u.id 
                  WHERE p.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getManagers() {
        $query = "SELECT id, nome_completo FROM usuarios WHERE perfil IN ('gerente', 'administrador') ORDER BY nome_completo";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getProjectTasks($project_id) {
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
    
    public function getProjectTeams($project_id) {
        $query = "SELECT e.*, pe.data_atribuicao 
                  FROM equipes e 
                  INNER JOIN projeto_equipes pe ON e.id = pe.equipe_id 
                  WHERE pe.projeto_id = :project_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAvailableTeams($project_id = null) {
        $whereClause = '';
        if ($project_id) {
            $whereClause = "WHERE e.id NOT IN (
                SELECT equipe_id FROM projeto_equipes WHERE projeto_id = :project_id
            )";
        }
        
        $query = "SELECT * FROM equipes e $whereClause ORDER BY e.nome";
        $stmt = $this->db->prepare($query);
        
        if ($project_id) {
            $stmt->bindParam(':project_id', $project_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function assignTeam($project_id, $team_id) {
        $query = "INSERT INTO projeto_equipes (projeto_id, equipe_id) VALUES (:project_id, :team_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':team_id', $team_id);
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Equipe atribuída ao projeto', 'projeto_equipes', null, null, ['projeto_id' => $project_id, 'equipe_id' => $team_id]);
            return true;
        }
        
        return false;
    }
    
    public function removeTeam($project_id, $team_id) {
        $query = "DELETE FROM projeto_equipes WHERE projeto_id = :project_id AND equipe_id = :team_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':team_id', $team_id);
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Equipe removida do projeto', 'projeto_equipes', null, ['projeto_id' => $project_id, 'equipe_id' => $team_id], null);
            return true;
        }
        
        return false;
    }
}
?>