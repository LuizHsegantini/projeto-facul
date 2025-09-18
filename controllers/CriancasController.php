<?php
// controllers/CriancasController.php
require_once 'config/database.php';

class CriancasController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function index($search = '', $idade_min = '', $idade_max = '', $sexo = '', $page = 1, $limit = 15) {
        try {
            $offset = ($page - 1) * $limit;
            $conditions = ['c.ativo = 1']; // Só mostrar crianças ativas por padrão
            $params = [];
            
            if (!empty($search)) {
                $conditions[] = "(c.nome_completo LIKE :search OR c.nome_responsavel LIKE :search OR c.telefone_principal LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            if (!empty($idade_min)) {
                $conditions[] = "c.idade >= :idade_min";
                $params[':idade_min'] = (int)$idade_min;
            }
            
            if (!empty($idade_max)) {
                $conditions[] = "c.idade <= :idade_max";
                $params[':idade_max'] = (int)$idade_max;
            }
            
            if (!empty($sexo)) {
                $conditions[] = "c.sexo = :sexo";
                $params[':sexo'] = $sexo;
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            
            // Buscar crianças
            $query = "SELECT c.*, 
                      COUNT(DISTINCT ec.id) as total_eventos,
                      COUNT(DISTINCT CASE WHEN ec.status_participacao = 'Check-in' THEN ec.id END) as eventos_checkin
                      FROM criancas_cadastro c
                      LEFT JOIN evento_criancas ec ON c.id = ec.crianca_id
                      $whereClause
                      GROUP BY c.id
                      ORDER BY c.nome_completo ASC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $criancas = $stmt->fetchAll();
            
            // Contar total para paginação
            $countQuery = "SELECT COUNT(DISTINCT c.id) as total FROM criancas_cadastro c $whereClause";
            $countStmt = $this->conn->prepare($countQuery);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $totalRecords = $countStmt->fetch()['total'];
            
            return [
                'criancas' => $criancas,
                'total' => $totalRecords,
                'pages' => ceil($totalRecords / $limit),
                'current_page' => $page
            ];
            
        } catch (Exception $e) {
            error_log("Erro no CriancasController::index: " . $e->getMessage());
            return ['criancas' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1];
        }
    }
    
    public function getById($id) {
        try {
            $query = "SELECT c.*, 
                      COUNT(DISTINCT ec.id) as total_eventos,
                      COUNT(DISTINCT CASE WHEN ec.status_participacao IN ('Check-in', 'Check-out') THEN ec.id END) as eventos_participou
                      FROM criancas_cadastro c
                      LEFT JOIN evento_criancas ec ON c.id = ec.crianca_id
                      WHERE c.id = :id
                      GROUP BY c.id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Erro no CriancasController::getById: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $data) {
        try {
            // Buscar dados antigos
            $oldData = $this->getById($id);
            
            // Calcular idade automaticamente
            $idade = date_diff(date_create($data['data_nascimento']), date_create('today'))->y;
            
            $query = "UPDATE criancas_cadastro SET 
                      nome_completo = :nome_completo,
                      data_nascimento = :data_nascimento,
                      idade = :idade,
                      sexo = :sexo,
                      alergia_alimentos = :alergia_alimentos,
                      alergia_medicamentos = :alergia_medicamentos,
                      restricoes_alimentares = :restricoes_alimentares,
                      observacoes_saude = :observacoes_saude,
                      nome_responsavel = :nome_responsavel,
                      telefone_principal = :telefone_principal,
                      nome_emergencia = :nome_emergencia,
                      data_atualizacao = NOW()
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome_completo', $data['nome_completo']);
            $stmt->bindParam(':data_nascimento', $data['data_nascimento']);
            $stmt->bindParam(':idade', $idade);
            $stmt->bindParam(':sexo', $data['sexo']);
            $stmt->bindParam(':alergia_alimentos', $data['alergia_alimentos'] ?? '');
            $stmt->bindParam(':alergia_medicamentos', $data['alergia_medicamentos'] ?? '');
            $stmt->bindParam(':restricoes_alimentares', $data['restricoes_alimentares'] ?? '');
            $stmt->bindParam(':observacoes_saude', $data['observacoes_saude'] ?? '');
            $stmt->bindParam(':nome_responsavel', $data['nome_responsavel']);
            $stmt->bindParam(':telefone_principal', $data['telefone_principal']);
            $stmt->bindParam(':nome_emergencia', $data['nome_emergencia']);
            
            if ($stmt->execute()) {
                $this->logAction($_SESSION['user_id'], 'Criança atualizada', 'criancas_cadastro', $id, 
                               json_encode($oldData), json_encode($data));
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro no CriancasController::update: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        try {
            $oldData = $this->getById($id);
            
            // Verificar se a criança tem eventos associados
            $queryCheck = "SELECT COUNT(*) as total FROM evento_criancas WHERE crianca_id = :id";
            $stmtCheck = $this->conn->prepare($queryCheck);
            $stmtCheck->bindParam(':id', $id);
            $stmtCheck->execute();
            $hasEvents = $stmtCheck->fetch()['total'] > 0;
            
            if ($hasEvents) {
                // Se tem eventos, apenas desativar
                $query = "UPDATE criancas_cadastro SET ativo = 0, data_atualizacao = NOW() WHERE id = :id";
                $action = 'Criança desativada (tinha eventos associados)';
            } else {
                // Se não tem eventos, pode excluir completamente
                $query = "DELETE FROM criancas_cadastro WHERE id = :id";
                $action = 'Criança excluída';
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $this->logAction($_SESSION['user_id'], $action, 'criancas_cadastro', $id, 
                               json_encode($oldData), null);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro no CriancasController::delete: " . $e->getMessage());
            return false;
        }
    }
    
    public function toggleStatus($id) {
        try {
            $oldData = $this->getById($id);
            $newStatus = $oldData['ativo'] ? 0 : 1;
            
            $query = "UPDATE criancas_cadastro SET ativo = :status, data_atualizacao = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $newStatus);
            
            if ($stmt->execute()) {
                $action = $newStatus ? 'Criança ativada' : 'Criança desativada';
                $this->logAction($_SESSION['user_id'], $action, 'criancas_cadastro', $id, 
                               json_encode($oldData), json_encode(['ativo' => $newStatus]));
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro no CriancasController::toggleStatus: " . $e->getMessage());
            return false;
        }
    }
    
    public function getCriancasForEvent($evento_id = null, $idade_min = null, $idade_max = null) {
        try {
            $conditions = ['c.ativo = 1'];
            $params = [];
            
            if ($evento_id) {
                $conditions[] = "c.id NOT IN (SELECT crianca_id FROM evento_criancas WHERE evento_id = :evento_id)";
                $params[':evento_id'] = $evento_id;
            }
            
            if ($idade_min !== null) {
                $conditions[] = "c.idade >= :idade_min";
                $params[':idade_min'] = $idade_min;
            }
            
            if ($idade_max !== null) {
                $conditions[] = "c.idade <= :idade_max";
                $params[':idade_max'] = $idade_max;
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            
            $query = "SELECT c.id, c.nome_completo, c.idade, c.sexo, c.data_nascimento,
                      c.alergia_alimentos, c.alergia_medicamentos, c.observacoes_saude,
                      c.nome_responsavel, c.telefone_principal
                      FROM criancas_cadastro c
                      $whereClause
                      ORDER BY c.nome_completo";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro no CriancasController::getCriancasForEvent: " . $e->getMessage());
            return [];
        }
    }
    
    public function getCriancasCheckin($evento_id = null) {
        try {
            $whereClause = '';
            $params = [];
            
            if ($evento_id) {
                $whereClause = "WHERE ec.evento_id = :evento_id";
                $params[':evento_id'] = $evento_id;
            } else {
                $whereClause = "WHERE DATE(ec.data_inscricao) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            }
            
            $query = "SELECT c.*, ec.*, e.nome as evento_nome,
                      ec.status_participacao, ec.data_checkin, ec.data_checkout,
                      u1.nome_completo as usuario_checkin_nome,
                      u2.nome_completo as usuario_checkout_nome
                      FROM evento_criancas ec
                      JOIN criancas_cadastro c ON ec.crianca_id = c.id
                      JOIN eventos e ON ec.evento_id = e.id
                      LEFT JOIN usuarios u1 ON ec.usuario_checkin = u1.id
                      LEFT JOIN usuarios u2 ON ec.usuario_checkout = u2.id
                      $whereClause
                      ORDER BY e.data_inicio DESC, c.nome_completo ASC";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro no CriancasController::getCriancasCheckin: " . $e->getMessage());
            return [];
        }
    }
    
    public function getEventosDaCrianca($crianca_id) {
        try {
            $query = "SELECT e.*, ec.status_participacao, ec.data_checkin, ec.data_checkout,
                      ec.observacoes as observacoes_evento
                      FROM evento_criancas ec
                      JOIN eventos e ON ec.evento_id = e.id
                      WHERE ec.crianca_id = :crianca_id
                      ORDER BY e.data_inicio DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':crianca_id', $crianca_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro no CriancasController::getEventosDaCrianca: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAniversariantes($mes = null) {
        try {
            $whereClause = $mes ? "WHERE MONTH(data_nascimento) = :mes" : "WHERE MONTH(data_nascimento) = MONTH(CURDATE())";
            $params = [];
            
            if ($mes) {
                $params[':mes'] = $mes;
            }
            
            $query = "SELECT *, 
                      DAY(data_nascimento) as dia_aniversario,
                      CASE WHEN DATE_FORMAT(data_nascimento, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d') 
                           THEN 1 ELSE 0 END as aniversario_hoje
                      FROM criancas_cadastro 
                      $whereClause AND ativo = 1
                      ORDER BY DAY(data_nascimento), nome_completo";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro no CriancasController::getAniversariantes: " . $e->getMessage());
            return [];
        }
    }
    
    public function searchCriancas($termo) {
        try {
            $query = "SELECT id, nome_completo, idade, sexo, nome_responsavel, telefone_principal,
                      alergia_alimentos, alergia_medicamentos, observacoes_saude
                      FROM criancas_cadastro 
                      WHERE (nome_completo LIKE :termo OR nome_responsavel LIKE :termo)
                      AND ativo = 1
                      ORDER BY nome_completo
                      LIMIT 20";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':termo', "%$termo%");
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro no CriancasController::searchCriancas: " . $e->getMessage());
            return [];
        }
    }
    
    private function logAction($user_id, $action, $table = null, $record_id = null, $old_data = null, $new_data = null) {
        try {
            $query = "INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, 
                      dados_anteriores, dados_novos, ip_address) 
                      VALUES (:user_id, :action, :table, :record_id, :old_data, :new_data, :ip)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':table', $table);
            $stmt->bindParam(':record_id', $record_id);
            $stmt->bindParam(':old_data', $old_data);
            $stmt->bindParam(':new_data', $new_data);
            $stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Erro no log: " . $e->getMessage());
            return false;
        }
    }
}
?>