<?php
// controllers/LogsController.php
require_once 'config/database.php';

class LogsController
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

        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function index(
        $search = '',
        $userId = '',
        $action = '',
        $table = '',
        $startDate = '',
        $endDate = '',
        $page = 1,
        $limit = 20
    ): array {
        $page = max(1, (int) $page);
        $limit = max(1, (int) $limit);
        $offset = ($page - 1) * $limit;

        $filters = [
            'search' => $search,
            'user_id' => $userId,
            'action' => $action,
            'table' => $table,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        [$conditions, $params] = $this->buildFilters($filters);
        $whereClause = $this->buildWhereClause($conditions);

        $sql = "SELECT \
                    l.id,\
                    l.usuario_id,\
                    l.acao,\
                    l.tabela_afetada,\
                    l.registro_id,\
                    l.dados_anteriores,\
                    l.dados_novos,\
                    l.ip_address,\
                    l.data_criacao,\
                    u.nome_completo AS usuario_nome\
                FROM logs_sistema l\
                LEFT JOIN usuarios u ON u.id = l.usuario_id\
                $whereClause\
                ORDER BY l.data_criacao DESC\
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $logs = array_map(function (array $row) {
            return $this->formatLogRow($row);
        }, $stmt->fetchAll());

        $countSql = "SELECT COUNT(*) AS total\
                     FROM logs_sistema l\
                     LEFT JOIN usuarios u ON u.id = l.usuario_id\
                     $whereClause";
        $countStmt = $this->conn->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = (int) $countStmt->fetchColumn();

        return [
            'logs' => $logs,
            'total' => $totalRecords,
            'pages' => (int) ceil($totalRecords / $limit),
            'current_page' => $page,
            'per_page' => $limit,
        ];
    }

    public function exportLogs($filters = [], int $limit = 2000): array
    {
        if (!is_array($filters)) {
            $filters = [];
        }

        $limit = max(1, $limit);
        [$conditions, $params] = $this->buildFilters($filters);
        $whereClause = $this->buildWhereClause($conditions);

        $sql = "SELECT \
                    l.id,\
                    l.usuario_id,\
                    l.acao,\
                    l.tabela_afetada,\
                    l.registro_id,\
                    l.dados_anteriores,\
                    l.dados_novos,\
                    l.ip_address,\
                    l.data_criacao,\
                    u.nome_completo AS usuario_nome\
                FROM logs_sistema l\
                LEFT JOIN usuarios u ON u.id = l.usuario_id\
                $whereClause\
                ORDER BY l.data_criacao DESC\
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(function (array $row) {
            $formatted = $this->formatLogRow($row);

            return [
                'id' => $formatted['id'],
                'usuario_nome' => $formatted['usuario_nome'] ?? 'Sistema',
                'acao' => $formatted['acao'],
                'tabela_afetada' => $formatted['tabela_afetada'] ?? '-',
                'registro_id' => $formatted['registro_id'] ?? '-',
                'ip_address' => $formatted['ip_address'] ?? '-',
                'data_criacao' => $formatted['data_criacao'],
                'dados_anteriores' => $formatted['dados_anteriores'],
                'dados_novos' => $formatted['dados_novos'],
            ];
        }, $stmt->fetchAll());
    }

    public function getLogStatistics(
        $startDate = null,
        $endDate = null,
        $search = '',
        $userId = '',
        $action = '',
        $table = ''
    ): array {
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'search' => $search,
            'user_id' => $userId,
            'action' => $action,
            'table' => $table,
        ];

        [$conditions, $params] = $this->buildFilters($filters);
        $whereClause = $this->buildWhereClause($conditions);

        $stats = [
            'total_logs' => 0,
            'logs_por_acao' => [],
            'logs_por_usuario' => [],
            'logs_por_dia' => [],
        ];

        // Total de logs
        $totalSql = "SELECT COUNT(*) FROM logs_sistema l LEFT JOIN usuarios u ON u.id = l.usuario_id $whereClause";
        $stmt = $this->conn->prepare($totalSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $stats['total_logs'] = (int) $stmt->fetchColumn();

        // Logs por ação
        $acaoSql = "SELECT l.acao, COUNT(*) AS total\
                    FROM logs_sistema l\
                    LEFT JOIN usuarios u ON u.id = l.usuario_id\
                    $whereClause\
                    GROUP BY l.acao\
                    ORDER BY total DESC";
        $stmt = $this->conn->prepare($acaoSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $stats['logs_por_acao'] = $stmt->fetchAll();

        // Logs por usuário
        $usuarioSql = "SELECT COALESCE(u.nome_completo, 'Sistema') AS nome_completo, COUNT(l.id) AS total\
                       FROM logs_sistema l\
                       LEFT JOIN usuarios u ON u.id = l.usuario_id\
                       $whereClause\
                       GROUP BY l.usuario_id, u.nome_completo\
                       ORDER BY total DESC\
                       LIMIT 10";
        $stmt = $this->conn->prepare($usuarioSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $stats['logs_por_usuario'] = $stmt->fetchAll();

        // Logs por dia (últimos 7 dias considerando filtros)
        $diaConditions = $conditions;
        if (!$this->hasDateFilter($filters)) {
            $diaConditions[] = "DATE(l.data_criacao) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        }
        $diaWhere = $this->buildWhereClause($diaConditions);

        $diaSql = "SELECT DATE(l.data_criacao) AS data, COUNT(*) AS total\
                   FROM logs_sistema l\
                   LEFT JOIN usuarios u ON u.id = l.usuario_id\
                   $diaWhere\
                   GROUP BY DATE(l.data_criacao)\
                   ORDER BY data DESC";
        $stmt = $this->conn->prepare($diaSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $stats['logs_por_dia'] = $stmt->fetchAll();

        return $stats;
    }

    public function getUsers(): array
    {
        $sql = "SELECT DISTINCT u.id, u.nome_completo\
                FROM logs_sistema l\
                INNER JOIN usuarios u ON u.id = l.usuario_id\
                ORDER BY u.nome_completo";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActions(): array
    {
        $sql = "SELECT DISTINCT acao FROM logs_sistema ORDER BY acao";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTables(): array
    {
        $sql = "SELECT DISTINCT tabela_afetada\
                FROM logs_sistema\
                WHERE tabela_afetada IS NOT NULL AND tabela_afetada <> ''\
                ORDER BY tabela_afetada";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function cleanOldLogs(int $days = 90)
    {
        $days = max(1, $days);
        $sql = "DELETE FROM logs_sistema WHERE data_criacao < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $removed = $stmt->rowCount();
            logSystemAction(
                $_SESSION['user_id'] ?? 0,
                'Limpeza de logs realizada',
                'logs_sistema',
                null,
                null,
                [
                    'removed' => $removed,
                    'days_threshold' => $days,
                ]
            );
            return $removed;
        }

        return false;
    }

    private function buildFilters(array $filters): array
    {
        $conditions = [];
        $params = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(l.acao LIKE :search OR l.tabela_afetada LIKE :search OR u.nome_completo LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $userId = trim((string) ($filters['user_id'] ?? ''));
        if ($userId !== '') {
            $conditions[] = 'l.usuario_id = :user_id';
            $params[':user_id'] = (int) $userId;
        }

        $action = trim((string) ($filters['action'] ?? ''));
        if ($action !== '') {
            $conditions[] = 'l.acao = :action';
            $params[':action'] = $action;
        }

        $table = trim((string) ($filters['table'] ?? ''));
        if ($table !== '') {
            $conditions[] = 'l.tabela_afetada = :table';
            $params[':table'] = $table;
        }

        $start = $this->normalizeDate($filters['start_date'] ?? null);
        $end = $this->normalizeDate($filters['end_date'] ?? null);

        if ($start && $end) {
            $conditions[] = 'DATE(l.data_criacao) BETWEEN :start_date AND :end_date';
            $params[':start_date'] = $start;
            $params[':end_date'] = $end;
        } elseif ($start) {
            $conditions[] = 'DATE(l.data_criacao) >= :start_date';
            $params[':start_date'] = $start;
        } elseif ($end) {
            $conditions[] = 'DATE(l.data_criacao) <= :end_date';
            $params[':end_date'] = $end;
        }

        return [$conditions, $params];
    }

    private function buildWhereClause(array $conditions): string
    {
        if (empty($conditions)) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $conditions);
    }

    private function hasDateFilter(array $filters): bool
    {
        return !empty($filters['start_date']) || !empty($filters['end_date']);
    }

    private function normalizeDate($value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);
        return $date instanceof DateTime ? $date->format('Y-m-d') : null;
    }

    private function formatLogRow(array $row): array
    {
        $row['dados_anteriores'] = $this->formatJsonString($row['dados_anteriores']);
        $row['dados_novos'] = $this->formatJsonString($row['dados_novos']);
        $row['usuario_nome'] = $row['usuario_nome'] ?? null;

        if (!empty($row['data_criacao'])) {
            $date = new DateTime($row['data_criacao']);
            $row['data_criacao'] = $date->format('Y-m-d H:i:s');
        }

        return $row;
    }

    private function formatJsonString(?string $payload): ?string
    {
        if ($payload === null || trim($payload) === '') {
            return null;
        }

        $decoded = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return trim($payload);
    }
}
