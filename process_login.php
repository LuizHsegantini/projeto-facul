<?php
// models/User.php
require_once 'config/database.php';

class User {
    private $conn;
    private $table = 'usuarios';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function authenticate($login, $senha) {
        try {
            // Usar MD5 como no banco de dados original
            $query = "SELECT * FROM " . $this->table . " WHERE login = :login AND senha = MD5(:senha)";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':senha', $senha);
            
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Erro na autenticação: " . $e->getMessage());
            return false;
        }
    }
    
    public function findById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    public function logAction($usuario_id, $acao, $tabela_afetada = null, $registro_id = null, $dados_anteriores = null, $dados_novos = null) {
        try {
            $query = "INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos, ip_address) 
                     VALUES (:usuario_id, :acao, :tabela_afetada, :registro_id, :dados_anteriores, :dados_novos, :ip_address)";
            
            $stmt = $this->conn->prepare($query);
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':acao', $acao);
            $stmt->bindParam(':tabela_afetada', $tabela_afetada);
            $stmt->bindParam(':registro_id', $registro_id);
            $stmt->bindParam(':dados_anteriores', $dados_anteriores);
            $stmt->bindParam(':dados_novos', $dados_novos);
            $stmt->bindParam(':ip_address', $ip);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
            return false;
        }
    }
}