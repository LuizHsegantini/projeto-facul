<?php
// controllers/FuncionariosController.php
require_once 'config/database.php';

class FuncionariosController
{
    /** @var PDO */
    private $conn;

    /** @var array */
    private $availableProfiles = ['administrador', 'coordenador', 'animador', 'monitor', 'auxiliar'];

    public function __construct($connection = null)
    {
        if ($connection instanceof PDO) {
            $this->conn = $connection;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function getAvailableProfiles(): array
    {
        return $this->availableProfiles;
    }

    public function createFuncionario(array $data, int $performedBy): array
    {
        $nomeCompleto = trim($data['nome_completo'] ?? '');
        $cpf = trim($data['cpf'] ?? '');
        $email = trim($data['email'] ?? '');
        $cargo = trim($data['cargo'] ?? '');
        $login = trim($data['login'] ?? '');
        $senha = trim($data['senha'] ?? '');
        $perfil = $data['perfil'] ?? '';

        if ($nomeCompleto === '' || $cpf === '' || $email === '' || $login === '' || $senha === '') {
            return $this->buildResult(false, 'danger', 'Preencha todos os campos obrigatorios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->buildResult(false, 'danger', 'Informe um email valido.');
        }

        if (!in_array($perfil, $this->availableProfiles, true)) {
            return $this->buildResult(false, 'danger', 'Perfil selecionado nao e valido.');
        }

        if (strlen($senha) < 6) {
            return $this->buildResult(false, 'danger', 'A senha deve ter ao menos 6 caracteres.');
        }

        try {
            $stmt = $this->conn->prepare('INSERT INTO usuarios (nome_completo, cpf, email, cargo, login, senha, perfil) VALUES (:nome, :cpf, :email, :cargo, :login, :senha, :perfil)');
            $senhaHash = $this->hashPassword($senha);
            $stmt->bindParam(':nome', $nomeCompleto);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':cargo', $cargo);
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':senha', $senhaHash);
            $stmt->bindParam(':perfil', $perfil);
            $stmt->execute();

            $newId = (int) $this->conn->lastInsertId();

            $this->logAction($performedBy, 'Funcionario criado', $newId, null, [
                'id' => $newId,
                'nome_completo' => $nomeCompleto,
                'cpf' => $cpf,
                'email' => $email,
                'cargo' => $cargo,
                'login' => $login,
                'perfil' => $perfil
            ]);

            return $this->buildResult(true, 'success', 'Funcionario cadastrado com sucesso.', ['id' => $newId]);
        } catch (PDOException $e) {
            error_log('Erro ao criar funcionario: ' . $e->getMessage());

            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                return $this->buildResult(false, 'danger', 'Ja existe um funcionario com os dados informados (login, email ou CPF).');
            }

            return $this->buildResult(false, 'danger', 'Nao foi possivel concluir a operacao. Tente novamente.');
        }
    }

    public function updateFuncionario(int $id, array $data, int $performedBy): array
    {
        if ($id <= 0) {
            return $this->buildResult(false, 'danger', 'Registro invalido informado.');
        }

        $nomeCompleto = trim($data['nome_completo'] ?? '');
        $cpf = trim($data['cpf'] ?? '');
        $email = trim($data['email'] ?? '');
        $cargo = trim($data['cargo'] ?? '');
        $login = trim($data['login'] ?? '');
        $senha = trim($data['senha'] ?? '');
        $perfil = $data['perfil'] ?? '';

        if ($nomeCompleto === '' || $cpf === '' || $email === '' || $login === '') {
            return $this->buildResult(false, 'danger', 'Preencha todos os campos obrigatorios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->buildResult(false, 'danger', 'Informe um email valido.');
        }

        if (!in_array($perfil, $this->availableProfiles, true)) {
            return $this->buildResult(false, 'danger', 'Perfil selecionado nao e valido.');
        }

        if ($senha !== '' && strlen($senha) < 6) {
            return $this->buildResult(false, 'danger', 'A senha deve ter ao menos 6 caracteres.');
        }

        try {
            $existing = $this->findById($id);
            if (!$existing) {
                return $this->buildResult(false, 'danger', 'Funcionario nao encontrado.');
            }

            $fields = [
                'nome_completo = :nome',
                'cpf = :cpf',
                'email = :email',
                'cargo = :cargo',
                'login = :login',
                'perfil = :perfil',
                'data_atualizacao = NOW()'
            ];

            if ($senha !== '') {
                $fields[] = 'senha = :senha';
            }

            $query = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nome', $nomeCompleto);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':cargo', $cargo);
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':perfil', $perfil);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($senha !== '') {
                $senhaHash = $this->hashPassword($senha);
                $stmt->bindParam(':senha', $senhaHash);
            }

            $stmt->execute();

            $newData = [
                'id' => $id,
                'nome_completo' => $nomeCompleto,
                'cpf' => $cpf,
                'email' => $email,
                'cargo' => $cargo,
                'login' => $login,
                'perfil' => $perfil,
                'senha_atualizada' => $senha !== ''
            ];

            $this->logAction($performedBy, 'Funcionario atualizado', $id, $existing, $newData);

            return $this->buildResult(true, 'success', 'Dados do funcionario atualizados com sucesso.');
        } catch (PDOException $e) {
            error_log('Erro ao atualizar funcionario: ' . $e->getMessage());

            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                return $this->buildResult(false, 'danger', 'Ja existe um funcionario com os dados informados (login, email ou CPF).');
            }

            return $this->buildResult(false, 'danger', 'Nao foi possivel concluir a operacao. Tente novamente.');
        }
    }

    public function deleteFuncionario(int $id, int $performedBy): array
    {
        if ($id <= 0) {
            return $this->buildResult(false, 'danger', 'Registro invalido informado.');
        }

        if ($id === $performedBy) {
            return $this->buildResult(false, 'danger', 'Voce nao pode remover o proprio usuario.');
        }

        try {
            $existing = $this->findById($id);
            if (!$existing) {
                return $this->buildResult(false, 'danger', 'Funcionario nao encontrado.');
            }

            $stmt = $this->conn->prepare('DELETE FROM usuarios WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->logAction($performedBy, 'Funcionario removido', $id, $existing, null);

            return $this->buildResult(true, 'success', 'Funcionario removido com sucesso.');
        } catch (PDOException $e) {
            error_log('Erro ao remover funcionario: ' . $e->getMessage());
            return $this->buildResult(false, 'danger', 'Nao foi possivel concluir a operacao. Tente novamente.');
        }
    }

    public function listFuncionarios(string $search = '', string $perfil = ''): array
    {
        $search = trim($search);
        $perfil = trim($perfil);

        if ($perfil !== '' && !in_array($perfil, $this->availableProfiles, true)) {
            $perfil = '';
        }

        $query = 'SELECT id, nome_completo, cpf, email, cargo, login, perfil, data_criacao, data_atualizacao FROM usuarios';
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(nome_completo LIKE :search OR email LIKE :search OR login LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if ($perfil !== '') {
            $conditions[] = 'perfil = :perfil';
            $params[':perfil'] = $perfil;
        }

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY nome_completo ASC';

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $records = $stmt->fetchAll();

        return [
            'funcionarios' => $records ?: [],
            'count' => $records ? count($records) : 0,
            'perfil' => $perfil,
        ];
    }

    public function getProfileCounts(): array
    {
        $profileCounts = array_fill_keys($this->availableProfiles, 0);

        $stmt = $this->conn->query('SELECT perfil, COUNT(*) AS total FROM usuarios GROUP BY perfil');
        if ($stmt) {
            $results = $stmt->fetchAll();
            foreach ($results as $row) {
                $perfil = $row['perfil'] ?? '';
                if (array_key_exists($perfil, $profileCounts)) {
                    $profileCounts[$perfil] = (int) $row['total'];
                }
            }
        }

        return $profileCounts;
    }

    public function getRecentFuncionarios(int $limit = 3): array
    {
        $stmt = $this->conn->prepare('SELECT nome_completo, perfil, data_criacao FROM usuarios ORDER BY data_criacao DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $records = $stmt->fetchAll();

        return $records ?: [];
    }

    public function getUserDependencyMessages(int $userId): array
    {
        $checks = [
            [
                'query' => 'SELECT COUNT(*) AS total FROM eventos WHERE coordenador_id = :id',
                'params' => [':id' => $userId],
                'label' => 'eventos coordenados'
            ],
            [
                'query' => 'SELECT COUNT(*) AS total FROM tarefas WHERE responsavel_id = :id',
                'params' => [':id' => $userId],
                'label' => 'tarefas atribuidas'
            ],
            [
                'query' => 'SELECT COUNT(*) AS total FROM evento_criancas WHERE usuario_checkin = :checkin OR usuario_checkout = :checkout',
                'params' => [':checkin' => $userId, ':checkout' => $userId],
                'label' => 'registros de check-in ou check-out'
            ],
            [
                'query' => 'SELECT COUNT(*) AS total FROM logs_sistema WHERE usuario_id = :id',
                'params' => [':id' => $userId],
                'label' => 'historico de logs'
            ],
        ];

        $messages = [];

        foreach ($checks as $check) {
            $stmt = $this->conn->prepare($check['query']);
            foreach ($check['params'] as $param => $value) {
                $stmt->bindValue($param, $value, PDO::PARAM_INT);
            }
            $stmt->execute();
            $total = (int) $stmt->fetchColumn();
            if ($total > 0) {
                $messages[] = $check['label'];
            }
        }

        return $messages;
    }

    private function findById(int $id)
    {
        $stmt = $this->conn->prepare('SELECT id, nome_completo, cpf, email, cargo, login, perfil FROM usuarios WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $record = $stmt->fetch();

        return $record ?: null;
    }

    private function hashPassword(string $password): string
    {
        return md5($password);
    }

    private function logAction(int $userId, string $action, int $recordId, $oldData, $newData): void
    {
        if (function_exists('logSystemAction')) {
            logSystemAction($userId, $action, 'usuarios', $recordId, $oldData, $newData);
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
