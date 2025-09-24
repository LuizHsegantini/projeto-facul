<?php
// controllers/EquipesController.php
require_once '../config/database.php';

class EquipesController
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

    public function index(string $search = '', string $especialidade = '', int $page = 1, int $limit = 12): array
    {
        $offset = max(0, ($page - 1) * $limit);
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(e.nome LIKE :search OR e.descricao LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if ($especialidade !== '') {
            $conditions[] = 'e.especialidade = :especialidade';
            $params[':especialidade'] = $especialidade;
        }

        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $query = "SELECT
                    e.id,
                    e.nome,
                    e.especialidade,
                    e.capacidade_eventos,
                    e.descricao,
                    e.data_criacao,
                    e.data_atualizacao,
                    COUNT(em.id) AS total_membros
                  FROM equipes e
                  LEFT JOIN equipe_membros em ON e.id = em.equipe_id
                  $whereClause
                  GROUP BY e.id
                  ORDER BY e.nome ASC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $equipes = $stmt->fetchAll();

        $countQuery = "SELECT COUNT(*) FROM equipes e $whereClause";
        $countStmt = $this->conn->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return [
            'equipes' => $equipes ?: [],
            'total' => $total,
            'pages' => $limit > 0 ? (int) ceil($total / $limit) : 1,
            'current_page' => $page,
        ];
    }

    public function getById(int $id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM equipes WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $equipe = $stmt->fetch();

        if (!$equipe) {
            return null;
        }

        $equipe['membros'] = $this->getMembros($id);
        return $equipe;
    }

    public function create(array $data, int $userId): array
    {
        $nome = trim($data['nome'] ?? '');
        $especialidade = trim($data['especialidade'] ?? '');
        $capacidade = (int) ($data['capacidade_eventos'] ?? 0);
        $descricao = trim($data['descricao'] ?? '');

        if ($nome === '') {
            return $this->buildResult(false, 'danger', 'Informe o nome da equipe.');
        }

        if ($capacidade <= 0) {
            $capacidade = 1;
        }

        try {
            $stmt = $this->conn->prepare('INSERT INTO equipes (nome, especialidade, capacidade_eventos, descricao) VALUES (:nome, :especialidade, :capacidade, :descricao)');
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':especialidade', $especialidade);
            $stmt->bindParam(':capacidade', $capacidade, PDO::PARAM_INT);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->execute();

            $newId = (int) $this->conn->lastInsertId();
            $this->logAction($userId, 'Equipe criada', $newId, null, [
                'id' => $newId,
                'nome' => $nome,
                'especialidade' => $especialidade,
                'capacidade_eventos' => $capacidade,
            ]);

            return $this->buildResult(true, 'success', 'Equipe criada com sucesso.', ['id' => $newId]);
        } catch (PDOException $e) {
            error_log('Erro ao criar equipe: ' . $e->getMessage());
            return $this->buildResult(false, 'danger', 'Nao foi possivel criar a equipe.');
        }
    }

    public function update(int $id, array $data, int $userId): array
    {
        if ($id <= 0) {
            return $this->buildResult(false, 'danger', 'Registro invalido informado.');
        }

        $existing = $this->getById($id);
        if (!$existing) {
            return $this->buildResult(false, 'danger', 'Equipe nao encontrada.');
        }

        $nome = trim($data['nome'] ?? '');
        $especialidade = trim($data['especialidade'] ?? '');
        $capacidade = (int) ($data['capacidade_eventos'] ?? 0);
        $descricao = trim($data['descricao'] ?? '');

        if ($nome === '') {
            return $this->buildResult(false, 'danger', 'Informe o nome da equipe.');
        }

        if ($capacidade <= 0) {
            $capacidade = 1;
        }

        try {
            $stmt = $this->conn->prepare('UPDATE equipes SET nome = :nome, especialidade = :especialidade, capacidade_eventos = :capacidade, descricao = :descricao WHERE id = :id');
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':especialidade', $especialidade);
            $stmt->bindParam(':capacidade', $capacidade, PDO::PARAM_INT);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->logAction($userId, 'Equipe atualizada', $id, $existing, [
                'nome' => $nome,
                'especialidade' => $especialidade,
                'capacidade_eventos' => $capacidade,
            ]);

            return $this->buildResult(true, 'success', 'Equipe atualizada com sucesso.');
        } catch (PDOException $e) {
            error_log('Erro ao atualizar equipe: ' . $e->getMessage());
            return $this->buildResult(false, 'danger', 'Nao foi possivel atualizar a equipe.');
        }
    }

    public function delete(int $id, int $userId): array
    {
        if ($id <= 0) {
            return $this->buildResult(false, 'danger', 'Registro invalido informado.');
        }

        $existing = $this->getById($id);
        if (!$existing) {
            return $this->buildResult(false, 'danger', 'Equipe nao encontrada.');
        }

        try {
            $this->conn->beginTransaction();

            $stmtMembers = $this->conn->prepare('DELETE FROM equipe_membros WHERE equipe_id = :id');
            $stmtMembers->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtMembers->execute();

            $stmt = $this->conn->prepare('DELETE FROM equipes WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();

            $this->logAction($userId, 'Equipe removida', $id, $existing, null);

            return $this->buildResult(true, 'success', 'Equipe removida com sucesso.');
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log('Erro ao remover equipe: ' . $e->getMessage());
            return $this->buildResult(false, 'danger', 'Nao foi possivel remover a equipe.');
        }
    }

    public function getMembros(int $equipeId): array
    {
        $stmt = $this->conn->prepare('SELECT em.usuario_id, em.data_entrada, u.nome_completo, u.perfil FROM equipe_membros em INNER JOIN usuarios u ON em.usuario_id = u.id WHERE em.equipe_id = :equipe_id ORDER BY u.nome_completo');
        $stmt->bindParam(':equipe_id', $equipeId, PDO::PARAM_INT);
        $stmt->execute();
        $membros = $stmt->fetchAll();
        return $membros ?: [];
    }

    public function addMembro(int $equipeId, int $usuarioId, int $userId): array
    {
        if ($equipeId <= 0 || $usuarioId <= 0) {
            return $this->buildResult(false, 'danger', 'Selecione uma equipe e um usuario validos.');
        }

        try {
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM equipe_membros WHERE equipe_id = :equipe_id AND usuario_id = :usuario_id');
            $stmt->bindParam(':equipe_id', $equipeId, PDO::PARAM_INT);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            if ((int) $stmt->fetchColumn() > 0) {
                return $this->buildResult(false, 'warning', 'Este usuario ja faz parte da equipe.');
            }

            $insert = $this->conn->prepare('INSERT INTO equipe_membros (equipe_id, usuario_id) VALUES (:equipe_id, :usuario_id)');
            $insert->bindParam(':equipe_id', $equipeId, PDO::PARAM_INT);
            $insert->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $insert->execute();

            $this->logAction($userId, 'Membro adicionado a equipe', $equipeId, null, [
                'usuario_id' => $usuarioId,
            ]);

            return $this->buildResult(true, 'success', 'Membro adicionado com sucesso.');
        } catch (PDOException $e) {
            error_log('Erro ao adicionar membro na equipe: ' . $e->getMessage());
            return $this->buildResult(false, 'danger', 'Nao foi possivel adicionar o membro.');
        }
    }

    public function removeMembro(int $equipeId, int $usuarioId, int $userId): array
    {
        if ($equipeId <= 0 || $usuarioId <= 0) {
            return $this->buildResult(false, 'danger', 'Selecione uma equipe e um usuario validos.');
        }

        try {
            $stmt = $this->conn->prepare('DELETE FROM equipe_membros WHERE equipe_id = :equipe_id AND usuario_id = :usuario_id');
            $stmt->bindParam(':equipe_id', $equipeId, PDO::PARAM_INT);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();

            $this->logAction($userId, 'Membro removido da equipe', $equipeId, ['usuario_id' => $usuarioId], null);

            return $this->buildResult(true, 'success', 'Membro removido com sucesso.');
        } catch (PDOException $e) {
            error_log('Erro ao remover membro da equipe: ' . $e->getMessage());
            return $this->buildResult(false, 'danger', 'Nao foi possivel remover o membro.');
        }
    }

    public function getUsuariosDisponiveis(int $equipeId = 0): array
    {
        if ($equipeId > 0) {
            $stmt = $this->conn->prepare('SELECT id, nome_completo FROM usuarios WHERE id NOT IN (SELECT usuario_id FROM equipe_membros WHERE equipe_id = :equipe_id) ORDER BY nome_completo');
            $stmt->bindParam(':equipe_id', $equipeId, PDO::PARAM_INT);
            $stmt->execute();
            $usuarios = $stmt->fetchAll();
        } else {
            $stmt = $this->conn->query('SELECT id, nome_completo FROM usuarios ORDER BY nome_completo');
            $usuarios = $stmt ? $stmt->fetchAll() : [];
        }

        return $usuarios ?: [];
    }

    public function getEspecialidades(): array
    {
        $stmt = $this->conn->query('SELECT DISTINCT especialidade FROM equipes ORDER BY especialidade');
        $especialidades = [];
        if ($stmt) {
            foreach ($stmt->fetchAll() as $row) {
                if (!empty($row['especialidade'])) {
                    $especialidades[] = $row['especialidade'];
                }
            }
        }

        return $especialidades;
    }

    private function logAction(int $userId, string $action, int $recordId, $oldData, $newData): void
    {
        if (function_exists('logSystemAction') && $userId > 0) {
            logSystemAction($userId, $action, 'equipes', $recordId, $oldData, $newData);
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
