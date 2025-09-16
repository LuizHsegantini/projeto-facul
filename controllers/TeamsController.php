<?php
// controllers/TeamsController.php
require_once 'config/database.php';
require_once 'includes/auth.php';

class TeamsController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function index($search = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(e.nome LIKE :search OR e.descricao LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $query = "SELECT e.*, 
                         COUNT(em.usuario_id) as total_membros
                  FROM equipes e 
                  LEFT JOIN equipe_membros em ON e.id = em.equipe_id 
                  $whereClause
                  GROUP BY e.id
                  ORDER BY e.data_criacao DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $teams = $stmt->fetchAll();
        
        // Contar total de registros
        $countQuery = "SELECT COUNT(*) as total FROM equipes e $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetch()['total'];
        
        return [
            'teams' => $teams,
            'total' => $totalRecords,
            'pages' => ceil($totalRecords / $limit),
            'current_page' => $page
        ];
    }
    
    public function create($data) {
        $query = "INSERT INTO equipes (nome, descricao) VALUES (:nome, :descricao)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':descricao', $data['descricao']);
        
        if ($stmt->execute()) {
            $team_id = $this->db->lastInsertId();
            logSystemAction($_SESSION['user_id'], 'Equipe criada', 'equipes', $team_id, null, $data);
            return $team_id;
        }
        
        return false;
    }
    
    public function update($id, $data) {
        // Buscar dados anteriores para log
        $oldData = $this->getById($id);
        
        $query = "UPDATE equipes SET nome = :nome, descricao = :descricao WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':descricao', $data['descricao']);
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Equipe atualizada', 'equipes', $id, $oldData, $data);
            return true;
        }
        
        return false;
    }
    
    public function delete($id) {
        $oldData = $this->getById($id);
        
        $query = "DELETE FROM equipes WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Equipe excluída', 'equipes', $id, $oldData, null);
            return true;
        }
        
        return false;
    }
    
    public function getById($id) {
        $query = "SELECT * FROM equipes WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getTeamMembers($team_id) {
        $query = "SELECT u.*, em.data_entrada 
                  FROM usuarios u 
                  INNER JOIN equipe_membros em ON u.id = em.usuario_id 
                  WHERE em.equipe_id = :team_id 
                  ORDER BY u.nome_completo";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAvailableUsers($team_id = null) {
        $whereClause = '';
        if ($team_id) {
            $whereClause = "WHERE u.id NOT IN (
                SELECT usuario_id FROM equipe_membros WHERE equipe_id = :team_id
            )";
        }
        
        $query = "SELECT * FROM usuarios u $whereClause ORDER BY u.nome_completo";
        $stmt = $this->db->prepare($query);
        
        if ($team_id) {
            $stmt->bindParam(':team_id', $team_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function addMember($team_id, $user_id) {
        $query = "INSERT INTO equipe_membros (equipe_id, usuario_id) VALUES (:team_id, :user_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Membro adicionado à equipe', 'equipe_membros', null, null, ['equipe_id' => $team_id, 'usuario_id' => $user_id]);
            return true;
        }
        
        return false;
    }
    
    public function removeMember($team_id, $user_id) {
        $query = "DELETE FROM equipe_membros WHERE equipe_id = :team_id AND usuario_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            logSystemAction($_SESSION['user_id'], 'Membro removido da equipe', 'equipe_membros', null, ['equipe_id' => $team_id, 'usuario_id' => $user_id], null);
            return true;
        }
        
        return false;
    }
    
    public function getTeamProjects($team_id) {
        $query = "SELECT p.*, pe.data_atribuicao 
                  FROM projetos p 
                  INNER JOIN projeto_equipes pe ON p.id = pe.projeto_id 
                  WHERE pe.equipe_id = :team_id 
                  ORDER BY pe.data_atribuicao DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getTeamStats($team_id) {
        // Total de projetos da equipe
        $query = "SELECT COUNT(*) as total_projetos FROM projeto_equipes WHERE equipe_id = :team_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        $totalProjetos = $stmt->fetch()['total_projetos'];
        
        // Total de tarefas da equipe
        $query = "SELECT COUNT(*) as total_tarefas 
                  FROM tarefas t 
                  INNER JOIN projeto_equipes pe ON t.projeto_id = pe.projeto_id 
                  WHERE pe.equipe_id = :team_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        $totalTarefas = $stmt->fetch()['total_tarefas'];
        
        // Tarefas concluídas
        $query = "SELECT COUNT(*) as tarefas_concluidas 
                  FROM tarefas t 
                  INNER JOIN projeto_equipes pe ON t.projeto_id = pe.projeto_id 
                  WHERE pe.equipe_id = :team_id AND t.status = 'concluida'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        $tarefasConcluidas = $stmt->fetch()['tarefas_concluidas'];
        
        return [
            'total_projetos' => $totalProjetos,
            'total_tarefas' => $totalTarefas,
            'tarefas_concluidas' => $tarefasConcluidas,
            'percentual_conclusao' => $totalTarefas > 0 ? round(($tarefasConcluidas / $totalTarefas) * 100, 2) : 0
        ];
    }
}
?>