<?php
// includes/LogService.php
require_once __DIR__ . '/../config/database.php';

class LogService
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

    public static function recordLog(int $userId, string $action, ?string $table = null, ?int $recordId = null, $oldData = null, $newData = null, array $metadata = []): bool
    {
        $service = new self();
        return $service->store($userId, $action, $table, $recordId, $oldData, $newData, $metadata);
    }

    public function store(int $userId, string $action, ?string $table, ?int $recordId, $oldData, $newData, array $metadata = []): bool
    {
        $oldPayload = $this->normalisePayload($oldData);
        $newPayload = $this->normalisePayload($newData, $metadata);
        $ipAddress = $this->resolveIpAddress();

        $query = 'INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos, ip_address) '
               . 'VALUES (:usuario_id, :acao, :tabela, :registro_id, :dados_anteriores, :dados_novos, :ip)';

        $stmt = $this->conn->prepare($query);
        if ($userId > 0) {
            $stmt->bindParam(':usuario_id', $userId, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':usuario_id', null, PDO::PARAM_NULL);
        }
        $stmt->bindParam(':acao', $action);
        $stmt->bindParam(':tabela', $table);
        if ($recordId !== null) {
            $stmt->bindParam(':registro_id', $recordId, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':registro_id', null, PDO::PARAM_NULL);
        }

        if ($oldPayload !== null) {
            $stmt->bindParam(':dados_anteriores', $oldPayload);
        } else {
            $stmt->bindValue(':dados_anteriores', null, PDO::PARAM_NULL);
        }

        if ($newPayload !== null) {
            $stmt->bindParam(':dados_novos', $newPayload);
        } else {
            $stmt->bindValue(':dados_novos', null, PDO::PARAM_NULL);
        }

        if ($ipAddress !== null) {
            $stmt->bindParam(':ip', $ipAddress);
        } else {
            $stmt->bindValue(':ip', null, PDO::PARAM_NULL);
        }

        return $stmt->execute();
    }

    private function normalisePayload($payload, array $metadata = [])
    {
        $hasMetadata = !empty($metadata);

        if ($payload === null || $payload === '') {
            if (!$hasMetadata) {
                return null;
            }
            $payload = [];
        }

        if (is_string($payload)) {
            $trimmed = trim($payload);
            if ($trimmed === '') {
                return $hasMetadata
                    ? json_encode(['_meta' => $metadata], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : null;
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $decoded = is_array($decoded) ? $decoded : ['value' => $decoded];
                if ($hasMetadata) {
                    $decoded['_meta'] = $metadata;
                }
                return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $wrapper = ['value' => $trimmed];
            if ($hasMetadata) {
                $wrapper['_meta'] = $metadata;
            }

            return json_encode($wrapper, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (!is_array($payload)) {
            $payload = ['value' => $payload];
        }

        if ($hasMetadata) {
            $payload['_meta'] = $metadata;
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function resolveIpAddress(): ?string
    {
        $candidates = [
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            $_SERVER['HTTP_X_REAL_IP'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!$candidate) {
                continue;
            }
            if (strpos($candidate, ',') !== false) {
                $parts = explode(',', $candidate);
                $candidate = trim($parts[0]);
            }
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }
}
