<?php
// models/BaseModel.php
require_once 'config/database.php';

abstract class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function findAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    protected function logActivity($action, $table, $recordId = null, $oldData = null, $newData = null) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $query = "INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos, ip_address) 
                  VALUES (:usuario_id, :acao, :tabela, :registro_id, :dados_anteriores, :dados_novos, :ip_address)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $userId);
        $stmt->bindParam(':acao', $action);
        $stmt->bindParam(':tabela', $table);
        $stmt->bindParam(':registro_id', $recordId);
        $stmt->bindParam(':dados_anteriores', $oldData);
        $stmt->bindParam(':dados_novos', $newData);
        $stmt->bindParam(':ip_address', $ipAddress);
        
        return $stmt->execute();
    }
}
?>