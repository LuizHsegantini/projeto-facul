<?php
// controllers/ReportsController.php
require_once 'config/database.php';
require_once 'includes/auth.php';

class ReportsController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function getProjectsReport($start_date = null, $end_date = null) {
        $whereClause = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $whereClause = "WHERE p.data_criacao BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $start_date;
            $params[':end_date'] = $end_date;
        }
        
        $query = "SELECT 
                    p.*, 
                    u.nome_completo as gerente_nome,
                    COUNT(t.id) as total_tarefas,
                    SUM(CASE WHEN t.status = 'concluida' THEN 1 ELSE 0 END) as tarefas_concluidas,
                    COUNT(pe.equipe_id) as total_equipes
                  FROM projetos p 
                  LEFT JOIN usuarios u ON p.gerente_id = u.id 
                  LEFT JOIN tarefas t ON p.id = t.projeto_id
                  LEFT JOIN projeto_equipes pe ON p.id = pe.projeto_id
                  $whereClause
                  GROUP BY p.id
                  ORDER BY p.data_criacao DESC";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getTasksReport($start_date = null, $end_date = null, $project_id = null, $user_id = null) {
        $whereConditions = [];
        $params = [];
        
        if ($start_date && $end_date) {
            $whereConditions[] = "t.data_criacao BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $start_date;
            $params[':end_date'] = $end_date;
        }
        
        if ($project_id) {
            $whereConditions[] = "t.projeto_id = :project_id";
            $params[':project_id'] = $project_id;
        }
        
        if ($user_id) {
            $whereConditions[] = "t.responsavel_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $query = "SELECT 
                    t.*,
                    p.nome as projeto_nome,
                    u.nome_completo as responsavel_nome,
                    DATEDIFF(COALESCE(t.data_fim_real, CURDATE()), t.data_inicio) as dias_execucao,
                    CASE 
                        WHEN t.data_fim_prevista < CURDATE() AND t.status != 'concluida' THEN 'Atrasada'
                        WHEN t.data_fim_prevista < t.data_fim_real THEN 'Concluída com Atraso'
                        WHEN t.status = 'concluida' THEN 'Concluída no Prazo'
                        ELSE 'Em Andamento'
                    END as situacao
                  FROM tarefas t 
                  LEFT JOIN projetos p ON t.projeto_id = p.id 
                  LEFT JOIN usuarios u ON t.responsavel_id = u.id 
                  $whereClause
                  ORDER BY t.data_criacao DESC";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getUsersReport() {
        $query = "SELECT 
                    u.*,
                    COUNT(DISTINCT t.id) as total_tarefas,
                    SUM(CASE WHEN t.status = 'concluida' THEN 1 ELSE 0 END) as tarefas_concluidas,
                    COUNT(DISTINCT p.id) as projetos_gerenciados,
                    COUNT(DISTINCT em.equipe_id) as equipes_participando
                  FROM usuarios u 
                  LEFT JOIN tarefas t ON u.id = t.responsavel_id 
                  LEFT JOIN projetos p ON u.id = p.gerente_id
                  LEFT JOIN equipe_membros em ON u.id = em.usuario_id
                  GROUP BY u.id
                  ORDER BY u.nome_completo";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getTeamsReport() {
        $query = "SELECT 
                    e.*,
                    COUNT(DISTINCT em.usuario_id) as total_membros,
                    COUNT(DISTINCT pe.projeto_id) as total_projetos,
                    COUNT(DISTINCT t.id) as total_tarefas,
                    SUM(CASE WHEN t.status = 'concluida' THEN 1 ELSE 0 END) as tarefas_concluidas
                  FROM equipes e 
                  LEFT JOIN equipe_membros em ON e.id = em.equipe_id 
                  LEFT JOIN projeto_equipes pe ON e.id = pe.equipe_id
                  LEFT JOIN tarefas t ON pe.projeto_id = t.projeto_id
                  GROUP BY e.id
                  ORDER BY e.nome";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getProductivityReport($start_date = null, $end_date = null) {
        $whereClause = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $whereClause = "WHERE t.data_criacao BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $start_date;
            $params[':end_date'] = $end_date;
        }
        
        $query = "SELECT 
                    u.nome_completo,
                    u.cargo,
                    COUNT(t.id) as total_tarefas,
                    SUM(CASE WHEN t.status = 'concluida' THEN 1 ELSE 0 END) as tarefas_concluidas,
                    SUM(CASE WHEN t.status = 'pendente' THEN 1 ELSE 0 END) as tarefas_pendentes,
                    SUM(CASE WHEN t.status = 'em_execucao' THEN 1 ELSE 0 END) as tarefas_em_execucao,
                    AVG(DATEDIFF(t.data_fim_real, t.data_inicio)) as media_dias_conclusao,
                    SUM(CASE WHEN t.data_fim_prevista < t.data_fim_real THEN 1 ELSE 0 END) as tarefas_atrasadas,
                    ROUND((SUM(CASE WHEN t.status = 'concluida' THEN 1 ELSE 0 END) / COUNT(t.id)) * 100, 2) as percentual_conclusao
                  FROM usuarios u 
                  LEFT JOIN tarefas t ON u.id = t.responsavel_id 
                  $whereClause
                  GROUP BY u.id
                  HAVING total_tarefas > 0
                  ORDER BY percentual_conclusao DESC, total_tarefas DESC";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getDashboardMetrics($start_date = null, $end_date = null) {
        $whereClause = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $whereClause = "WHERE DATE(data_criacao) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $start_date;
            $params[':end_date'] = $end_date;
        }
        
        // Métricas gerais
        $metrics = [];
        
        // Projetos por status
        $query = "SELECT status, COUNT(*) as total FROM projetos $whereClause GROUP BY status";
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $metrics['projetos_por_status'] = $stmt->fetchAll();
        
        // Tarefas por status
        $query = "SELECT status, COUNT(*) as total FROM tarefas $whereClause GROUP BY status";
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $metrics['tarefas_por_status'] = $stmt->fetchAll();
        
        // Projetos criados por mês
        $query = "SELECT 
                    YEAR(data_criacao) as ano,
                    MONTH(data_criacao) as mes,
                    COUNT(*) as total
                  FROM projetos 
                  WHERE data_criacao >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                  GROUP BY YEAR(data_criacao), MONTH(data_criacao)
                  ORDER BY ano, mes";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $metrics['projetos_por_mes'] = $stmt->fetchAll();
        
        // Tarefas concluídas por mês
        $query = "SELECT 
                    YEAR(data_fim_real) as ano,
                    MONTH(data_fim_real) as mes,
                    COUNT(*) as total
                  FROM tarefas 
                  WHERE status = 'concluida' 
                  AND data_fim_real >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                  GROUP BY YEAR(data_fim_real), MONTH(data_fim_real)
                  ORDER BY ano, mes";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $metrics['tarefas_concluidas_por_mes'] = $stmt->fetchAll();
        
        return $metrics;
    }
    
    public function getDelayReport() {
        $query = "SELECT 
                    p.nome as projeto_nome,
                    p.data_termino_prevista,
                    p.status as projeto_status,
                    u.nome_completo as gerente_nome,
                    DATEDIFF(CURDATE(), p.data_termino_prevista) as dias_atraso,
                    COUNT(t.id) as total_tarefas,
                    SUM(CASE WHEN t.status = 'concluida' THEN 1 ELSE 0 END) as tarefas_concluidas
                  FROM projetos p 
                  LEFT JOIN usuarios u ON p.gerente_id = u.id
                  LEFT JOIN tarefas t ON p.id = t.projeto_id
                  WHERE p.data_termino_prevista < CURDATE() 
                  AND p.status NOT IN ('concluido', 'cancelado')
                  GROUP BY p.id
                  ORDER BY dias_atraso DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function exportToCsv($data, $filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if (!empty($data)) {
            // Cabeçalhos
            fputcsv($output, array_keys($data[0]));
            
            // Dados
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
    }
    
    public function getSystemLogs($start_date = null, $end_date = null, $user_id = null, $action = null, $limit = 100) {
        $whereConditions = [];
        $params = [];
        
        if ($start_date && $end_date) {
            $whereConditions[] = "DATE(l.data_criacao) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $start_date;
            $params[':end_date'] = $end_date;
        }
        
        if ($user_id) {
            $whereConditions[] = "l.usuario_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        
        if ($action) {
            $whereConditions[] = "l.acao LIKE :action";
            $params[':action'] = "%$action%";
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $query = "SELECT 
                    l.*,
                    u.nome_completo as usuario_nome
                  FROM logs_sistema l 
                  LEFT JOIN usuarios u ON l.usuario_id = u.id 
                  $whereClause
                  ORDER BY l.data_criacao DESC 
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getProjectTimeline($project_id) {
        $query = "SELECT 
                    'projeto' as tipo,
                    'Projeto Criado' as evento,
                    p.data_criacao as data_evento,
                    u.nome_completo as responsavel,
                    p.nome as detalhes
                  FROM projetos p 
                  LEFT JOIN usuarios u ON p.gerente_id = u.id 
                  WHERE p.id = :project_id
                  
                  UNION ALL
                  
                  SELECT 
                    'tarefa' as tipo,
                    CONCAT('Tarefa: ', t.titulo) as evento,
                    t.data_criacao as data_evento,
                    u.nome_completo as responsavel,
                    t.status as detalhes
                  FROM tarefas t 
                  LEFT JOIN usuarios u ON t.responsavel_id = u.id 
                  WHERE t.projeto_id = :project_id
                  
                  UNION ALL
                  
                  SELECT 
                    'equipe' as tipo,
                    CONCAT('Equipe Atribuída: ', e.nome) as evento,
                    pe.data_atribuicao as data_evento,
                    '' as responsavel,
                    e.descricao as detalhes
                  FROM projeto_equipes pe 
                  INNER JOIN equipes e ON pe.equipe_id = e.id 
                  WHERE pe.projeto_id = :project_id
                  
                  ORDER BY data_evento DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>