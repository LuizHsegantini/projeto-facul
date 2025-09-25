<?php
// controllers/EventosController.php
require_once '../config/database.php';

class EventosController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getDashboardData() {
        try {
            $data = [];
            
            // Total de eventos
            $query = "SELECT COUNT(*) as total FROM eventos WHERE status != 'cancelado'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data['total_eventos'] = $stmt->fetch()['total'];
            
            // Eventos ativos
            $query = "SELECT COUNT(*) as total FROM eventos WHERE status = 'em_andamento'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data['eventos_ativos'] = $stmt->fetch()['total'];
            
            // Total de crianças cadastradas
            $query = "SELECT COUNT(*) as total FROM criancas_cadastro WHERE ativo = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data['total_criancas'] = $stmt->fetch()['total'];
            
            // Check-ins hoje
            $query = "SELECT COUNT(*) as total FROM evento_criancas WHERE DATE(data_checkin) = CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data['criancas_checkin'] = $stmt->fetch()['total'];
            
            // Total de equipes
            $query = "SELECT COUNT(*) as total FROM equipes";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data['total_equipes'] = $stmt->fetch()['total'];
            
            // Total de funcionários
            $query = "SELECT COUNT(*) as total FROM usuarios";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data['total_funcionarios'] = $stmt->fetch()['total'];
            
            // Próximos eventos
            $query = "SELECT e.*, u.nome_completo as coordenador_nome,
                      COUNT(ec.id) as total_inscricoes
                      FROM eventos e 
                      LEFT JOIN usuarios u ON e.coordenador_id = u.id
                      LEFT JOIN evento_criancas ec ON e.id = ec.evento_id
                      WHERE e.data_inicio >= CURDATE() 
                      AND e.status != 'cancelado'
                      GROUP BY e.id
                      ORDER BY e.data_inicio ASC 
                      LIMIT 5";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data['proximos_eventos'] = $stmt->fetchAll();
            
            // Eventos hoje
            $query = "SELECT * FROM eventos WHERE DATE(data_inicio) = CURDATE() AND status != 'cancelado'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data['eventos_hoje'] = $stmt->fetchAll();
            
            // Aniversariantes do mês
            $query = "SELECT nome_completo, data_nascimento, idade 
                      FROM criancas_cadastro 
                      WHERE MONTH(data_nascimento) = MONTH(CURDATE()) 
                      AND ativo = 1
                      ORDER BY DAY(data_nascimento)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data['aniversariantes_mes'] = $stmt->fetchAll();
            
            // Resumo de status dos eventos
            $query = "SELECT status, COUNT(*) as total FROM eventos GROUP BY status";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $statusResults = $stmt->fetchAll();
            $data['evento_status_summary'] = [];
            foreach ($statusResults as $status) {
                $data['evento_status_summary'][$status['status']] = $status['total'];
            }
            
            // Resumo de check-ins
            $query = "SELECT status_participacao, COUNT(*) as total FROM evento_criancas GROUP BY status_participacao";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $checkinResults = $stmt->fetchAll();
            $data['checkin_status_summary'] = [];
            foreach ($checkinResults as $checkin) {
                $data['checkin_status_summary'][$checkin['status_participacao']] = $checkin['total'];
            }
            
            // Minhas atividades (para animadores e monitores)
            if (isset($_SESSION['user_id'])) {
                $query = "SELECT t.*, e.nome as evento_nome 
                          FROM tarefas t 
                          LEFT JOIN eventos e ON t.evento_id = e.id 
                          WHERE t.responsavel_id = :user_id 
                          AND e.data_inicio >= CURDATE()
                          ORDER BY t.data_fim_prevista ASC 
                          LIMIT 10";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $data['minhas_atividades'] = $stmt->fetchAll();
            } else {
                $data['minhas_atividades'] = [];
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log("Erro no getDashboardData: " . $e->getMessage());
            return [
                'total_eventos' => 0,
                'eventos_ativos' => 0,
                'total_criancas' => 0,
                'criancas_checkin' => 0,
                'total_equipes' => 0,
                'total_funcionarios' => 0,
                'proximos_eventos' => [],
                'eventos_hoje' => [],
                'aniversariantes_mes' => [],
                'evento_status_summary' => [],
                'checkin_status_summary' => [],
                'minhas_atividades' => []
            ];
        }
    }
    
    public function index($search = '', $status = '', $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $conditions = [];
            $params = [];
            
            if (!empty($search)) {
                $conditions[] = "(e.nome LIKE :search OR e.descricao LIKE :search OR e.local_evento LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            if (!empty($status)) {
                $conditions[] = "e.status = :status";
                $params[':status'] = $status;
            }
            
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            
            // Buscar eventos
            $query = "SELECT e.*, u.nome_completo as coordenador_nome,
                      COUNT(DISTINCT ec.id) as total_inscricoes,
                      COUNT(DISTINCT CASE WHEN ec.status_participacao = 'Check-in' THEN ec.id END) as total_checkins
                      FROM eventos e 
                      LEFT JOIN usuarios u ON e.coordenador_id = u.id
                      LEFT JOIN evento_criancas ec ON e.id = ec.evento_id
                      $whereClause
                      GROUP BY e.id
                      ORDER BY e.data_inicio DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $eventos = $stmt->fetchAll();
            
            // Contar total para paginação
            $countQuery = "SELECT COUNT(DISTINCT e.id) as total FROM eventos e $whereClause";
            $countStmt = $this->conn->prepare($countQuery);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $totalRecords = $countStmt->fetch()['total'];
            
            return [
                'eventos' => $eventos,
                'total' => $totalRecords,
                'pages' => ceil($totalRecords / $limit),
                'current_page' => $page
            ];
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::index: " . $e->getMessage());
            return ['eventos' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1];
        }
    }
    
    // MÉTODO: Verificar se evento já existe
    private function eventoExiste($nome, $data_inicio, $local_evento, $evento_id = null) {
        try {
            $query = "SELECT COUNT(*) as total FROM eventos 
                      WHERE nome = :nome 
                      AND data_inicio = :data_inicio 
                      AND local_evento = :local_evento";
            
            if ($evento_id) {
                $query .= " AND id != :evento_id";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':local_evento', $local_evento);
            
            if ($evento_id) {
                $stmt->bindParam(':evento_id', $evento_id);
            }
            
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $result['total'] > 0;
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::eventoExiste: " . $e->getMessage());
            return false;
        }
    }
    
    // MÉTODO: Validar data (não permitir datas passadas)
    private function validarData($data_inicio) {
        try {
            // Definir timezone para Brasil
            date_default_timezone_set('America/Sao_Paulo');
            
            $dataEvento = new DateTime($data_inicio);
            $dataAtual = new DateTime();
            
            // Verificar se a data do evento é anterior à data atual
            if ($dataEvento < $dataAtual) {
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::validarData: " . $e->getMessage());
            return false;
        }
    }
    
    // MÉTODO: Validar idade da criança para o evento
    public function validarIdadeCrianca($crianca_id, $evento_id) {
        try {
            // Buscar idade da criança
            $queryCrianca = "SELECT idade FROM criancas_cadastro WHERE id = :crianca_id AND ativo = 1";
            $stmtCrianca = $this->conn->prepare($queryCrianca);
            $stmtCrianca->bindParam(':crianca_id', $crianca_id);
            $stmtCrianca->execute();
            $crianca = $stmtCrianca->fetch();
            
            if (!$crianca) {
                return false;
            }
            
            // Buscar faixa etária do evento
            $queryEvento = "SELECT faixa_etaria_min, faixa_etaria_max FROM eventos WHERE id = :evento_id";
            $stmtEvento = $this->conn->prepare($queryEvento);
            $stmtEvento->bindParam(':evento_id', $evento_id);
            $stmtEvento->execute();
            $evento = $stmtEvento->fetch();
            
            if (!$evento) {
                return false;
            }
            
            $idadeCrianca = $crianca['idade'];
            $idadeMinima = $evento['faixa_etaria_min'];
            $idadeMaxima = $evento['faixa_etaria_max'];
            
            // Verificar se a idade está dentro da faixa permitida
            return ($idadeCrianca >= $idadeMinima && $idadeCrianca <= $idadeMaxima);
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::validarIdadeCrianca: " . $e->getMessage());
            return false;
        }
    }
    
    // MÉTODO AUXILIAR: Obter nome do campo para mensagens de erro
    private function getFieldName($field) {
        $names = [
            'nome' => 'Nome do Evento',
            'tipo_evento' => 'Tipo de Evento',
            'data_inicio' => 'Data de Início',
            'coordenador_id' => 'Coordenador',
            'faixa_etaria_min' => 'Idade Mínima',
            'faixa_etaria_max' => 'Idade Máxima',
            'capacidade_maxima' => 'Capacidade Máxima',
            'local_evento' => 'Local do Evento'
        ];
        
        return $names[$field] ?? $field;
    }
    
    public function create($data) {
        try {
            // VALIDAÇÃO 1: Campos obrigatórios
            $requiredFields = ['nome', 'tipo_evento', 'data_inicio', 'coordenador_id', 
                              'faixa_etaria_min', 'faixa_etaria_max', 'capacidade_maxima', 'local_evento'];
            
            foreach ($requiredFields as $field) {
                if (empty(trim($data[$field] ?? ''))) {
                    throw new Exception("O campo " . $this->getFieldName($field) . " é obrigatório.");
                }
            }

            // VALIDAÇÃO 2: Verificar se evento já existe
            if ($this->eventoExiste($data['nome'], $data['data_inicio'], $data['local_evento'])) {
                throw new Exception("Já existe um evento com o mesmo nome, data e local.");
            }
            
            // VALIDAÇÃO 3: Verificar se a data não é passada
            if (!$this->validarData($data['data_inicio'])) {
                throw new Exception("Não é permitido criar eventos com datas passadas.");
            }
            
            // VALIDAÇÃO 4: Verificar faixa etária
            if ($data['faixa_etaria_min'] > $data['faixa_etaria_max']) {
                throw new Exception("A idade mínima não pode ser maior que a idade máxima.");
            }
            
            // Calcular data_fim automaticamente
            $data_inicio = new DateTime($data['data_inicio']);
            $duracao_horas = intval($data['duracao_horas']);
            $data_fim = $data_inicio->add(new DateInterval('PT' . $duracao_horas . 'H'));
            $data_fim_evento = $data_fim->format('Y-m-d H:i:s');
            
            $query = "INSERT INTO eventos (nome, tipo_evento, descricao, data_inicio, data_fim_evento, 
                      faixa_etaria_min, faixa_etaria_max, capacidade_maxima, local_evento, 
                      duracao_horas, status, coordenador_id) 
                      VALUES (:nome, :tipo_evento, :descricao, :data_inicio, :data_fim_evento,
                      :faixa_etaria_min, :faixa_etaria_max, :capacidade_maxima, :local_evento,
                      :duracao_horas, :status, :coordenador_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nome', $data['nome']);
            $stmt->bindParam(':tipo_evento', $data['tipo_evento']);
            $stmt->bindParam(':descricao', $data['descricao']);
            $stmt->bindParam(':data_inicio', $data['data_inicio']);
            $stmt->bindParam(':data_fim_evento', $data_fim_evento);
            $stmt->bindParam(':faixa_etaria_min', $data['faixa_etaria_min']);
            $stmt->bindParam(':faixa_etaria_max', $data['faixa_etaria_max']);
            $stmt->bindParam(':capacidade_maxima', $data['capacidade_maxima']);
            $stmt->bindParam(':local_evento', $data['local_evento']);
            $stmt->bindParam(':duracao_horas', $data['duracao_horas']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':coordenador_id', $data['coordenador_id']);
            
            if ($stmt->execute()) {
                $evento_id = $this->conn->lastInsertId();
                $this->logAction($_SESSION['user_id'], 'Evento criado', 'eventos', $evento_id, null, json_encode($data));
                return $evento_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::create: " . $e->getMessage());
            throw $e; // Re-lançar a exceção para tratamento na interface
        }
    }
    
    public function update($id, $data) {
        try {
            // VALIDAÇÃO 1: Verificar se evento já existe (excluindo o atual)
            if ($this->eventoExiste($data['nome'], $data['data_inicio'], $data['local_evento'], $id)) {
                throw new Exception("Já existe outro evento com o mesmo nome, data e local.");
            }
            
            // VALIDAÇÃO 2: Verificar se a data não é passada (apenas se estiver modificando a data)
            $oldData = $this->getById($id);
            if ($oldData['data_inicio'] != $data['data_inicio'] && !$this->validarData($data['data_inicio'])) {
                throw new Exception("Não é permitido alterar para uma data passada.");
            }
            
            // VALIDAÇÃO 3: Verificar faixa etária
            if ($data['faixa_etaria_min'] > $data['faixa_etaria_max']) {
                throw new Exception("A idade mínima não pode ser maior que a idade máxima.");
            }
            
            // Buscar dados antigos
            $oldData = $this->getById($id);
            
            // Calcular data_fim automaticamente
            $data_inicio = new DateTime($data['data_inicio']);
            $duracao_horas = intval($data['duracao_horas']);
            $data_fim = $data_inicio->add(new DateInterval('PT' . $duracao_horas . 'H'));
            $data_fim_evento = $data_fim->format('Y-m-d H:i:s');
            
            $query = "UPDATE eventos SET nome = :nome, tipo_evento = :tipo_evento, 
                      descricao = :descricao, data_inicio = :data_inicio, data_fim_evento = :data_fim_evento,
                      faixa_etaria_min = :faixa_etaria_min, faixa_etaria_max = :faixa_etaria_max,
                      capacidade_maxima = :capacidade_maxima, local_evento = :local_evento,
                      duracao_horas = :duracao_horas, status = :status, coordenador_id = :coordenador_id
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $data['nome']);
            $stmt->bindParam(':tipo_evento', $data['tipo_evento']);
            $stmt->bindParam(':descricao', $data['descricao']);
            $stmt->bindParam(':data_inicio', $data['data_inicio']);
            $stmt->bindParam(':data_fim_evento', $data_fim_evento);
            $stmt->bindParam(':faixa_etaria_min', $data['faixa_etaria_min']);
            $stmt->bindParam(':faixa_etaria_max', $data['faixa_etaria_max']);
            $stmt->bindParam(':capacidade_maxima', $data['capacidade_maxima']);
            $stmt->bindParam(':local_evento', $data['local_evento']);
            $stmt->bindParam(':duracao_horas', $data['duracao_horas']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':coordenador_id', $data['coordenador_id']);
            
            if ($stmt->execute()) {
                $this->logAction($_SESSION['user_id'], 'Evento atualizado', 'eventos', $id, 
                               json_encode($oldData), json_encode($data));
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::update: " . $e->getMessage());
            throw $e; // Re-lançar a exceção para tratamento na interface
        }
    }
    
    public function delete($id) {
        try {
            $oldData = $this->getById($id);
            
            $query = "DELETE FROM eventos WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $this->logAction($_SESSION['user_id'], 'Evento excluído', 'eventos', $id, 
                               json_encode($oldData), null);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::delete: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($id) {
        try {
            $query = "SELECT e.*, u.nome_completo as coordenador_nome
                      FROM eventos e 
                      LEFT JOIN usuarios u ON e.coordenador_id = u.id
                      WHERE e.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::getById: " . $e->getMessage());
            return false;
        }
    }
    
    public function addCriancaToEvento($evento_id, $crianca_id, $observacoes = '') {
        try {
            // VALIDAÇÃO: Verificar se a criança tem idade adequada para o evento
            if (!$this->validarIdadeCrianca($crianca_id, $evento_id)) {
                throw new Exception("A criança não está na faixa etária permitida para este evento.");
            }
            
            // Verificar se a criança já está inscrita no evento
            $queryCheck = "SELECT COUNT(*) as total FROM evento_criancas 
                          WHERE evento_id = :evento_id AND crianca_id = :crianca_id";
            $stmtCheck = $this->conn->prepare($queryCheck);
            $stmtCheck->bindParam(':evento_id', $evento_id);
            $stmtCheck->bindParam(':crianca_id', $crianca_id);
            $stmtCheck->execute();
            $alreadyRegistered = $stmtCheck->fetch()['total'] > 0;
            
            if ($alreadyRegistered) {
                throw new Exception("A criança já está inscrita neste evento.");
            }
            
            // Verificar capacidade do evento
            $queryCapacity = "SELECT capacidade_maxima FROM eventos WHERE id = :evento_id";
            $stmtCapacity = $this->conn->prepare($queryCapacity);
            $stmtCapacity->bindParam(':evento_id', $evento_id);
            $stmtCapacity->execute();
            $capacidade = $stmtCapacity->fetch()['capacidade_maxima'];
            
            $queryCount = "SELECT COUNT(*) as total FROM evento_criancas WHERE evento_id = :evento_id";
            $stmtCount = $this->conn->prepare($queryCount);
            $stmtCount->bindParam(':evento_id', $evento_id);
            $stmtCount->execute();
            $inscritos = $stmtCount->fetch()['total'];
            
            if ($inscritos >= $capacidade) {
                throw new Exception("O evento já atingiu sua capacidade máxima.");
            }
            
            $query = "INSERT INTO evento_criancas (evento_id, crianca_id, observacoes) 
                      VALUES (:evento_id, :crianca_id, :observacoes)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':evento_id', $evento_id);
            $stmt->bindParam(':crianca_id', $crianca_id);
            $stmt->bindParam(':observacoes', $observacoes);
            
            if ($stmt->execute()) {
                $this->logAction($_SESSION['user_id'], 'Criança adicionada ao evento', 'evento_criancas', 
                               null, null, json_encode(['evento_id' => $evento_id, 'crianca_id' => $crianca_id]));
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::addCriancaToEvento: " . $e->getMessage());
            throw $e; // Re-lançar a exceção para tratamento na interface
        }
    }
    
    public function removeCriancaFromEvento($evento_id, $crianca_id) {
        try {
            $query = "DELETE FROM evento_criancas WHERE evento_id = :evento_id AND crianca_id = :crianca_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':evento_id', $evento_id);
            $stmt->bindParam(':crianca_id', $crianca_id);
            
            if ($stmt->execute()) {
                $this->logAction($_SESSION['user_id'], 'Criança removida do evento', 'evento_criancas',
                               null, json_encode(['evento_id' => $evento_id, 'crianca_id' => $crianca_id]), null);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::removeCriancaFromEvento: " . $e->getMessage());
            return false;
        }
    }
    
    public function checkinCrianca($evento_id, $crianca_id) {
        try {
            $query = "UPDATE evento_criancas 
                      SET status_participacao = 'Check-in', 
                          data_checkin = NOW(), 
                          usuario_checkin = :user_id 
                      WHERE evento_id = :evento_id AND crianca_id = :crianca_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':evento_id', $evento_id);
            $stmt->bindParam(':crianca_id', $crianca_id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $this->logAction($_SESSION['user_id'], 'Check-in realizado', 'evento_criancas',
                               null, null, json_encode(['evento_id' => $evento_id, 'crianca_id' => $crianca_id]));
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::checkinCrianca: " . $e->getMessage());
            return false;
        }
    }
    
    public function checkoutCrianca($evento_id, $crianca_id) {
        try {
            $query = "UPDATE evento_criancas 
                      SET status_participacao = 'Check-out', 
                          data_checkout = NOW(), 
                          usuario_checkout = :user_id 
                      WHERE evento_id = :evento_id AND crianca_id = :crianca_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':evento_id', $evento_id);
            $stmt->bindParam(':crianca_id', $crianca_id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $this->logAction($_SESSION['user_id'], 'Check-out realizado', 'evento_criancas',
                               null, null, json_encode(['evento_id' => $evento_id, 'crianca_id' => $crianca_id]));
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::checkoutCrianca: " . $e->getMessage());
            return false;
        }
    }
    
    public function getCoordenadores() {
        try {
            $query = "SELECT id, nome_completo FROM usuarios 
                      WHERE perfil IN ('administrador', 'coordenador') 
                      ORDER BY nome_completo";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::getCoordenadores: " . $e->getMessage());
            return [];
        }
    }
    
    public function getEventoCriancas($evento_id) {
        try {
            $query = "SELECT ec.*, c.nome_completo, c.idade, c.alergia_alimentos, 
                      c.alergia_medicamentos, c.restricoes_alimentares, c.observacoes_saude,
                      c.nome_responsavel, c.telefone_principal,
                      u1.nome_completo as usuario_checkin_nome,
                      u2.nome_completo as usuario_checkout_nome
                      FROM evento_criancas ec
                      JOIN criancas_cadastro c ON ec.crianca_id = c.id
                      LEFT JOIN usuarios u1 ON ec.usuario_checkin = u1.id
                      LEFT JOIN usuarios u2 ON ec.usuario_checkout = u2.id
                      WHERE ec.evento_id = :evento_id
                      ORDER BY c.nome_completo";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':evento_id', $evento_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::getEventoCriancas: " . $e->getMessage());
            return [];
        }
    }
    
    public function getCriancasDisponiveis($evento_id = null) {
        try {
            $whereClause = '';
            $params = [];
            
            if ($evento_id) {
                // Buscar faixa etária do evento
                $queryEvento = "SELECT faixa_etaria_min, faixa_etaria_max FROM eventos WHERE id = :evento_id";
                $stmtEvento = $this->conn->prepare($queryEvento);
                $stmtEvento->bindParam(':evento_id', $evento_id);
                $stmtEvento->execute();
                $evento = $stmtEvento->fetch();
                
                if ($evento) {
                    $whereClause = "WHERE c.id NOT IN (
                        SELECT crianca_id FROM evento_criancas WHERE evento_id = :evento_id
                    ) AND c.idade BETWEEN :idade_min AND :idade_max";
                    $params[':evento_id'] = $evento_id;
                    $params[':idade_min'] = $evento['faixa_etaria_min'];
                    $params[':idade_max'] = $evento['faixa_etaria_max'];
                } else {
                    $whereClause = "WHERE c.id NOT IN (
                        SELECT crianca_id FROM evento_criancas WHERE evento_id = :evento_id
                    )";
                    $params[':evento_id'] = $evento_id;
                }
            }
            
            $query = "SELECT c.id, c.nome_completo, c.idade, c.data_nascimento
                      FROM criancas_cadastro c 
                      $whereClause
                      AND c.ativo = 1
                      ORDER BY c.nome_completo";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::getCriancasDisponiveis: " . $e->getMessage());
            return [];
        }
    }
    
    // MÉTODO: Obter eventos para check-in
    public function getEventosParaCheckin() {
        try {
            // Buscar eventos que estão ativos ou que ocorrem hoje/futuramente
            // e que não estão cancelados
            $query = "SELECT e.*, u.nome_completo as coordenador_nome,
                      COUNT(DISTINCT ec.id) as total_inscricoes
                      FROM eventos e 
                      LEFT JOIN usuarios u ON e.coordenador_id = u.id
                      LEFT JOIN evento_criancas ec ON e.id = ec.evento_id
                      WHERE (
                          e.status IN ('planejado', 'em_andamento', 'concluido') 
                          AND DATE(e.data_inicio) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      )
                      OR (
                          e.status = 'em_andamento'
                      )
                      GROUP BY e.id
                      ORDER BY 
                          CASE 
                              WHEN e.status = 'em_andamento' THEN 1
                              WHEN DATE(e.data_inicio) = CURDATE() THEN 2
                              WHEN DATE(e.data_inicio) > CURDATE() THEN 3
                              ELSE 4
                          END,
                          e.data_inicio ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Erro no EventosController::getEventosParaCheckin: " . $e->getMessage());
            return [];
        }
    }
    
    private function logAction($user_id, $action, $table = null, $record_id = null, $old_data = null, $new_data = null) {
        try {
            $query = "INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, 
                      dados_anteriores, dados_novos, ip_address) 
                      VALUES (:user_id, :action, :table, :record_id, :old_data, :new_data, :ip)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':action', $action);
            $stmt->bindValue(':table', $table);
            $stmt->bindValue(':record_id', $record_id);
            $stmt->bindValue(':old_data', $old_data);
            $stmt->bindValue(':new_data', $new_data);
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Erro no log: " . $e->getMessage());
            return false;
        }
    }
    
}
?>