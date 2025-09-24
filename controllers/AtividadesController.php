<?php
// controllers/AtividadesController.php
require_once '../config/database.php';

class AtividadesController
{
    /** @var PDO */
    private $conn;

    public function __construct($connection = null)
    {
        if ($connection instanceof PDO) {
            $this->conn = $connection;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function index(string $search = '', string $status = '', string $eventoId = '', string $responsavelId = '', int $page = 1, int $limit = 12): array
    {
        $offset = max(0, ($page - 1) * $limit);

        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(t.titulo LIKE :search OR t.descricao LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $conditions[] = 't.status = :status';
            $params[':status'] = $status;
        }

        if ($eventoId !== '') {
            $conditions[] = 't.evento_id = :evento_id';
            $params[':evento_id'] = (int) $eventoId;
        }

        if ($responsavelId !== '') {
            $conditions[] = 't.responsavel_id = :responsavel_id';
            $params[':responsavel_id'] = (int) $responsavelId;
        }

        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $query = "SELECT
                    t.id,
                    t.titulo,
                    t.tipo_atividade,
                    t.descricao,
                    t.material_necessario,
                    t.publico_alvo,
                    t.evento_id,
                    t.responsavel_id,
                    t.status,
                    t.data_inicio,
                    t.data_fim_prevista,
                    t.data_fim_real,
                    t.data_criacao,
                    t.data_atualizacao,
                    e.nome AS evento_nome,
                    u.nome_completo AS responsavel_nome
                  FROM tarefas t
                  LEFT JOIN eventos e ON t.evento_id = e.id
                  LEFT JOIN usuarios u ON t.responsavel_id = u.id
                  $whereClause
                  ORDER BY t.data_criacao DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $atividades = $stmt->fetchAll();

        $countQuery = "SELECT COUNT(*) FROM tarefas t $whereClause";
        $countStmt = $this->conn->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return [
            'atividades' => $atividades ?: [],
            'total' => $total,
            'pages' => $limit > 0 ? (int) ceil($total / $limit) : 1,
            'current_page' => $page,
        ];
    }

    public function getById(int $id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM tarefas WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $atividade = $stmt->fetch();

        return $atividade ?: null;
    }

    public function create(array $data, int $userId): array
    {
        $titulo = trim($data['titulo'] ?? '');
        $tipo = trim($data['tipo_atividade'] ?? '');
        $descricao = trim($data['descricao'] ?? '');
        $material = trim($data['material_necessario'] ?? '');
        $publico = trim($data['publico_alvo'] ?? '');
        $eventoId = (int) ($data['evento_id'] ?? 0);
        $responsavelId = (int) ($data['responsavel_id'] ?? 0);
        $status = trim($data['status'] ?? 'pendente');
        $dataInicio = $data['data_inicio'] ?? null;
        $dataPrevista = $data['data_fim_prevista'] ?? null;

        if ($titulo === '' || $tipo === '' || $eventoId <= 0) {
            return $this->buildResult(false, 'danger', 'Preencha os campos obrigatorios da atividade.');
        }

        if (!in_array($status, ['pendente', 'em_execucao', 'concluida'], true)) {
            $status = 'pendente';
        }

        try {
            $query = 'INSERT INTO tarefas (titulo, tipo_atividade, descricao, material_necessario, publico_alvo, evento_id, responsavel_id, status, data_inicio, data_fim_prevista)
                      VALUES (:titulo, :tipo, :descricao, :material, :publico, :evento_id, :responsavel_id, :status, :data_inicio, :data_prevista)';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':material', $material);
            $stmt->bindParam(':publico', $publico);
            $stmt->bindParam(':evento_id', $eventoId, PDO::PARAM_INT);
            if ($responsavelId > 0) {
                $stmt->bindParam(':responsavel_id', $responsavelId, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':responsavel_id', null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':data_inicio', $dataInicio);
            $stmt->bindParam(':data_prevista', $dataPrevista);
            $stmt->execute();

            $newId = (int) $this->conn->lastInsertId();
            $this->logAction($userId, 'Atividade criada', $newId, null, [
                'id' => $newId,
                'titulo' => $titulo,
                'evento_id' => $eventoId,
                'responsavel_id' => $responsavelId,
                'status' => $status,
            ]);

            return $this->buildResult(true, 'success', 'Atividade criada com sucesso.', ['id' => $newId]);
        } catch (PDOException $e) {
            error_log('Erro ao criar atividade: ' . $e->getMessage());
            return $this->buildResult(false, 'danger', 'Nao foi possivel criar a atividade.');
        }
    }

    public function update(int $id, array $data, int $userId): array
    {
        if ($id <= 0) {
            return $this->buildResult(false, 'danger', 'Registro invalido informado.');
        }

        $existing = $this->getById($id);
        if (!$existing) {
            return $this->buildResult(false, 'danger', 'Atividade nao encontrada.');
        }

        $titulo = trim($data['titulo'] ?? '');
        $tipo = trim($data['tipo_atividade'] ?? '');
        $descricao = trim($data['descricao'] ?? '');
        $material = trim($data['material_necessario'] ?? '');
        $publico = trim($data['publico_alvo'] ?? '');
        $eventoId = (int) ($data['evento_id'] ?? 0);
        $responsavelId = (int) ($data['responsavel_id'] ?? 0);
        $status = trim($data['status'] ?? 'pendente');
        $dataInicio = $data['data_inicio'] ?? null;
        $dataPrevista = $data['data_fim_prevista'] ?? null;
        $dataReal = $data['data_fim_real'] ?? null;

        if ($titulo === '' || $tipo === '' || $eventoId <= 0) {
            return $this->buildResult(false, 'danger', 'Preencha os campos obrigatorios da atividade.');
        }

        if (!in_array($status, ['pendente', 'em_execucao', 'concluida'], true)) {
            $status = 'pendente';
        }

        try {
            $query = 'UPDATE tarefas SET
                        titulo = :titulo,
                        tipo_atividade = :tipo,
                        descricao = :descricao,
                        material_necessario = :material,
                        publico_alvo = :publico,
                        evento_id = :evento_id,
                        responsavel_id = :responsavel_id,
                        status = :status,
                        data_inicio = :data_inicio,
                        data_fim_prevista = :data_prevista,
                        data_fim_real = :data_real
                      WHERE id = :id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':material', $material);
            $stmt->bindParam(':publico', $publico);
            $stmt->bindParam(':evento_id', $eventoId, PDO::PARAM_INT);
            if ($responsavelId > 0) {
                $stmt->bindParam(':responsavel_id', $responsavelId, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':responsavel_id', null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':data_inicio', $dataInicio);
            $stmt->bindParam(':data_prevista', $dataPrevista);
            $stmt->bindParam(':data_real', $dataReal);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->logAction($userId, 'Atividade atualizada', $id, $existing, [
                'titulo' => $titulo,
                'evento_id' => $eventoId,
                'responsavel_id' => $responsavelId,
                'status' => $status,
            ]);

            return $this->buildResult(true, 'success', 'Atividade atualizada com sucesso.');
        } catch (PDOException $e) {
            error_log('Erro ao atualizar atividade: ' . $e->getMessage());
            return $this->buildResult(false, 'danger', 'Nao foi possivel atualizar a atividade.');
        }
    }

    public function updateStatus(int $id, string $status, int $userId): array
    {
        if (!in_array($status, ['pendente', 'em_execucao', 'concluida'], true)) {
            return $this->buildResult(false, 'danger', 'Status informado nao e valido.');
        }

        $existing = $this->getById($id);
        if (!$existing) {
            return $this->buildResult(false, 'danger', 'Atividade nao encontrada.');
        }

        try {
            $stmt = $this->conn->prepare('UPDATE tarefas SET status = :status WHERE id = :id');
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->logAction($userId, 'Status da atividade atualizado', $id, $existing, ['status' => $status]);

            return $this->buildResult(true, 'success', 'Status atualizado com sucesso.');
        } catch (PDOException $e) {
            error_log('Erro ao atualizar status da atividade: ' . $e->getMessage());
            return $this->buildResult(false, 'danger', 'Nao foi possivel atualizar o status.');
        }
    }

    public function delete(int $id, int $userId): array
    {
        if ($id <= 0) {
            return $this->buildResult(false, 'danger', 'Registro invalido informado.');
        }

        $existing = $this->getById($id);
        if (!$existing) {
            return $this->buildResult(false, 'danger', 'Atividade nao encontrada.');
        }

        try {
            $stmt = $this->conn->prepare('DELETE FROM tarefas WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->logAction($userId, 'Atividade removida', $id, $existing, null);

            return $this->buildResult(true, 'success', 'Atividade removida com sucesso.');
        } catch (PDOException $e) {
            error_log('Erro ao remover atividade: ' . $e->getMessage());
            return $this->buildResult(false, 'danger', 'Nao foi possivel remover a atividade.');
        }
    }

    public function getEventos(): array
    {
        $stmt = $this->conn->query('SELECT id, nome FROM eventos ORDER BY nome ASC');
        $registros = $stmt ? $stmt->fetchAll() : [];
        return $registros ?: [];
    }

    public function getResponsaveis(): array
    {
        $stmt = $this->conn->query("SELECT id, nome_completo FROM usuarios ORDER BY nome_completo ASC");
        $registros = $stmt ? $stmt->fetchAll() : [];
        return $registros ?: [];
    }

    public function getTiposAtividade(): array
    {
        $stmt = $this->conn->query("SELECT DISTINCT tipo_atividade FROM tarefas ORDER BY tipo_atividade");
        $tipos = [];
        if ($stmt) {
            foreach ($stmt->fetchAll() as $row) {
                if (!empty($row['tipo_atividade'])) {
                    $tipos[] = $row['tipo_atividade'];
                }
            }
        }
        return $tipos;
    }

    public function getResumo(): array
    {
        $resumo = [
            'total' => 0,
            'pendente' => 0,
            'em_execucao' => 0,
            'concluida' => 0,
        ];

        $stmt = $this->conn->query("SELECT status, COUNT(*) AS total FROM tarefas GROUP BY status");
        if ($stmt) {
            foreach ($stmt->fetchAll() as $row) {
                $status = $row['status'];
                $total = (int) $row['total'];
                $resumo[$status] = $total;
                $resumo['total'] += $total;
            }
        }

        return $resumo;
    }

    private function logAction(int $userId, string $action, int $recordId, $oldData, $newData): void
    {
        if (function_exists('logSystemAction') && $userId > 0) {
            logSystemAction($userId, $action, 'tarefas', $recordId, $oldData, $newData);
        }
    }

    private function buildResult(bool $success, string $type, string $message, array $extra = []): array
    {
        return array_merge([
            'success' => $success,
            'type' => $type,
            'message' => $message,
        ], $extra);
    }
}
