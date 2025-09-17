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
    
    public function index($search = '', $page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(e.nome LIKE :search OR e.descricao LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $query = "SELECT e.*, COUNT(em.usuario_id) as total_membros 
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
        $countQuery = "SELECT COUNT(DISTINCT e.id) as total FROM equipes e $whereClause";
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
        $query = "SELECT u.* FROM usuarios u 
                  INNER JOIN equipe_membros em ON u.id = em.usuario_id 
                  WHERE em.equipe_id = :team_id 
                  ORDER BY u.nome_completo";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getTeamProjects($team_id) {
        $query = "SELECT p.*, u.nome_completo as gerente_nome 
                  FROM projetos p 
                  INNER JOIN projeto_equipes pe ON p.id = pe.projeto_id 
                  LEFT JOIN usuarios u ON p.gerente_id = u.id 
                  WHERE pe.equipe_id = :team_id 
                  ORDER BY p.data_criacao DESC";
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
            logSystemAction($_SESSION['user_id'], 'Membro adicionado à equipe', 'equipe_membros', null, null, 
                ['equipe_id' => $team_id, 'usuario_id' => $user_id]);
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
            logSystemAction($_SESSION['user_id'], 'Membro removido da equipe', 'equipe_membros', null, 
                ['equipe_id' => $team_id, 'usuario_id' => $user_id], null);
            return true;
        }
        
        return false;
    }
    
    public function getTeamStats($team_id) {
        $stats = [
            'total_projetos' => 0,
            'total_tarefas' => 0,
            'tarefas_concluidas' => 0,
            'percentual_conclusao' => 0
        ];
        
        // Total de projetos da equipe
        $query = "SELECT COUNT(*) as total FROM projeto_equipes WHERE equipe_id = :team_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        $stats['total_projetos'] = $stmt->fetch()['total'];
        
        // Total de tarefas dos projetos da equipe
        $query = "SELECT COUNT(t.id) as total, 
                         SUM(CASE WHEN t.status = 'concluida' THEN 1 ELSE 0 END) as concluidas
                  FROM tarefas t 
                  INNER JOIN projeto_equipes pe ON t.projeto_id = pe.projeto_id 
                  WHERE pe.equipe_id = :team_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        $result = $stmt->fetch();
        
        $stats['total_tarefas'] = $result['total'] ?? 0;
        $stats['tarefas_concluidas'] = $result['concluidas'] ?? 0;
        
        if ($stats['total_tarefas'] > 0) {
            $stats['percentual_conclusao'] = round(($stats['tarefas_concluidas'] / $stats['total_tarefas']) * 100);
        }
        
        return $stats;
    }
    
    public function getAll() {
        $query = "SELECT * FROM equipes ORDER BY nome";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getUserTeams($user_id) {
        $query = "SELECT e.* FROM equipes e 
                  INNER JOIN equipe_membros em ON e.id = em.equipe_id 
                  WHERE em.usuario_id = :user_id 
                  ORDER BY e.nome";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function isUserInTeam($team_id, $user_id) {
        $query = "SELECT COUNT(*) as total FROM equipe_membros 
                  WHERE equipe_id = :team_id AND usuario_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch()['total'] > 0;
    }
}
?>