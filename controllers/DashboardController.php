<?php
// controllers/DashboardController.php
require_once 'config/database.php';

class DashboardController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function getDashboardData() {
        $data = [];
        
        // Estatísticas gerais
        $data['total_projects'] = $this->getTotalProjects();
        $data['active_projects'] = $this->getActiveProjects();
        $data['total_tasks'] = $this->getTotalTasks();
        $data['pending_tasks'] = $this->getPendingTasks();
        $data['total_teams'] = $this->getTotalTeams();
        $data['total_users'] = $this->getTotalUsers();
        
        // Projetos recentes
        $data['recent_projects'] = $this->getRecentProjects();
        
        // Projetos atrasados
        $data['delayed_projects'] = $this->getDelayedProjects();
        
        // Tarefas em atraso
        $data['overdue_tasks'] = $this->getOverdueTasks();
        
        // Resumo por status
        $data['project_status_summary'] = $this->getProjectStatusSummary();
        $data['task_status_summary'] = $this->getTaskStatusSummary();
        
        // Tarefas do usuário atual
        $data['my_tasks'] = $this->getMyTasks();
        
        return $data;
    }
    
    private function getTotalProjects() {
        $query = "SELECT COUNT(*) as total FROM projetos";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    private function getActiveProjects() {
        $query = "SELECT COUNT(*) as total FROM projetos WHERE status = 'em_andamento'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    private function getTotalTasks() {
        $query = "SELECT COUNT(*) as total FROM tarefas";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    private function getPendingTasks() {
        $query = "SELECT COUNT(*) as total FROM tarefas WHERE status = 'pendente'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    private function getTotalTeams() {
        $query = "SELECT COUNT(*) as total FROM equipes";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    private function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM usuarios";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    private function getRecentProjects() {
        $query = "SELECT p.*, u.nome_completo as gerente_nome 
                  FROM projetos p 
                  LEFT JOIN usuarios u ON p.gerente_id = u.id 
                  ORDER BY p.data_criacao DESC 
                  LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getDelayedProjects() {
        $query = "SELECT * FROM projetos 
                  WHERE status != 'concluido' 
                  AND status != 'cancelado' 
                  AND data_termino_prevista < CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getOverdueTasks() {
        $query = "SELECT t.*, u.nome_completo as responsavel_nome 
                  FROM tarefas t 
                  LEFT JOIN usuarios u ON t.responsavel_id = u.id 
                  WHERE t.status != 'concluida' 
                  AND t.data_fim_prevista < CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getProjectStatusSummary() {
        $query = "SELECT status, COUNT(*) as total FROM projetos GROUP BY status";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $summary = ['planejado' => 0, 'em_andamento' => 0, 'concluido' => 0, 'cancelado' => 0];
        foreach ($results as $result) {
            $summary[$result['status']] = $result['total'];
        }
        
        return $summary;
    }
    
    private function getTaskStatusSummary() {
        $query = "SELECT status, COUNT(*) as total FROM tarefas GROUP BY status";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $summary = ['pendente' => 0, 'em_execucao' => 0, 'concluida' => 0];
        foreach ($results as $result) {
            $summary[$result['status']] = $result['total'];
        }
        
        return $summary;
    }
    
    private function getMyTasks() {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        
        $query = "SELECT t.*, p.nome as projeto_nome 
                  FROM tarefas t 
                  LEFT JOIN projetos p ON t.projeto_id = p.id 
                  WHERE t.responsavel_id = :user_id 
                  ORDER BY t.data_fim_prevista ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getProjectProgress($project_id) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) as completed
                  FROM tarefas 
                  WHERE projeto_id = :project_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        $result = $stmt->fetch();
        
        $total = $result['total'] ?? 0;
        $completed = $result['completed'] ?? 0;
        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
        
        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $percentage
        ];
    }
}
?>