<?php
// controllers/LogsController.php
require_once 'config/database.php';
require_once 'includes/auth.php';

class LogsController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function index($search = '', $user_id = '', $action = '', $table = '', $start_date = '', $end_date = '', $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(l.acao LIKE :search OR l.tabela_afetada LIKE :search OR u.nome_completo LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($user_id)) {
            $whereConditions[] = "l.usuario_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        
        if (!empty($action)) {
            $whereConditions[] = "l.acao LIKE :action";
            $params[':action'] = "%$action%";
        }
        
        if (!empty($table)) {
            $whereConditions[] = "l.tabela_afetada = :table";
            $params[':table'] = $table;
        }
        
        if (!empty($start_date) && !empty($end_date)) {
            $whereConditions[] = "DATE(l.data_criacao) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $start_date;
            $params[':end_date'] = $end_date;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $query = "SELECT l.*, u.nome_completo as usuario_nome 
                  FROM logs_sistema l 
                  LEFT JOIN usuarios u ON l.usuario_id = u.id 
                  $whereClause
                  ORDER BY l.data_criacao DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $logs = $stmt->fetchAll();
        
        // Contar total de registros
        $countQuery = "SELECT COUNT(*) as total FROM logs_sistema l 
                       LEFT JOIN usuarios u ON l.usuario_id = u.id 
                       $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetch()['total'];
        
        return [
            'logs' => $logs,
            'total' => $totalRecords,
            'pages' => ceil($totalRecords / $limit),
            'current_page' => $page
        ];
    }
    
    public function getLogDetails($id) {
        $query = "SELECT l.*, u.nome_completo as usuario_nome 
                  FROM logs_sistema l 
                  LEFT JOIN usuarios u ON l.usuario_id = u.id 
                  WHERE l.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getUsers() {
        $query = "SELECT DISTINCT u.id, u.nome_completo 
                  FROM usuarios u 
                  INNER JOIN logs_sistema l ON u.id = l.usuario_id 
                  ORDER BY u.nome_completo";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getActions() {
        $query = "SELECT DISTINCT acao FROM logs_sistema ORDER BY acao";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getTables() {
        $query = "SELECT DISTINCT tabela_afetada FROM logs_sistema WHERE tabela_afetada IS NOT NULL ORDER BY tabela_afetada";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getLogStatistics($start_date = null, $end_date = null) {
        $whereClause = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $whereClause = "WHERE DATE(data_criacao) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $start_date;
            $params[':end_date'] = $end_date;
        }
        
        $stats = [];
        
        // Total de logs
        $query = "SELECT COUNT(*) as total FROM logs_sistema $whereClause";
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $stats['total_logs'] = $stmt->fetch()['total'];
        
        // Logs por ação
        $query = "SELECT acao, COUNT(*) as total FROM logs_sistema $whereClause GROUP BY acao ORDER BY total DESC";
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $stats['logs_por_acao'] = $stmt->fetchAll();
        
        // Logs por usuário
        $query = "SELECT u.nome_completo, COUNT(l.id) as total 
                  FROM logs_sistema l 
                  LEFT JOIN usuarios u ON l.usuario_id = u.id 
                  $whereClause
                  GROUP BY l.usuario_id 
                  ORDER BY total DESC 
                  LIMIT 10";
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $stats['logs_por_usuario'] = $stmt->fetchAll();
        
        // Logs por tabela
        $query = "SELECT tabela_afetada, COUNT(*) as total 
                  FROM logs_sistema 
                  $whereClause AND tabela_afetada IS NOT NULL 
                  GROUP BY tabela_afetada 
                  ORDER BY total DESC";
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $stats['logs_por_tabela'] = $stmt->fetchAll();
        
        // Logs por dia (últimos 7 dias)
        $query = "SELECT 
                    DATE(data_criacao) as data,
                    COUNT(*) as total 
                  FROM logs_sistema 
                  WHERE DATE(data_criacao) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  GROUP BY DATE(data_criacao) 
                  ORDER BY data DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['logs_por_dia'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    public function cleanOldLogs($days = 90) {
        $query = "DELETE FROM logs_sistema WHERE data_criacao < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':days', $days);
        
        if ($stmt->execute()) {
            $deletedRows = $stmt->rowCount();
            logSystemAction($_SESSION['user_id'], "Limpeza de logs realizada - $deletedRows registros removidos", 'logs_sistema');
            return $deletedRows;
        }
        
        return false;
    }
    
    public function exportLogs($filters = []) {
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(l.acao LIKE :search OR l.tabela_afetada LIKE :search OR u.nome_completo LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['user_id'])) {
            $whereConditions[] = "l.usuario_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $whereConditions[] = "DATE(l.data_criacao) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $query = "SELECT 
                    l.id,
                    u.nome_completo as usuario,
                    l.acao,
                    l.tabela_afetada,
                    l.registro_id,
                    l.ip_address,
                    l.data_criacao
                  FROM logs_sistema l 
                  LEFT JOIN usuarios u ON l.usuario_id = u.id 
                  $whereClause
                  ORDER BY l.data_criacao DESC";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>