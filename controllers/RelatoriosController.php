<?php
// controllers/RelatoriosController.php
require_once 'config/database.php';

class RelatoriosController
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

    public function getResumoGeral(): array
    {
        $resumo = [
            'total_eventos' => 0,
            'total_criancas' => 0,
            'total_atividades' => 0,
            'total_equipes' => 0,
        ];

        $map = [
            'total_eventos' => 'SELECT COUNT(*) FROM eventos',
            'total_criancas' => 'SELECT COUNT(*) FROM criancas_cadastro',
            'total_atividades' => 'SELECT COUNT(*) FROM tarefas',
            'total_equipes' => 'SELECT COUNT(*) FROM equipes',
        ];

        foreach ($map as $key => $sql) {
            $stmt = $this->conn->query($sql);
            $resumo[$key] = (int) ($stmt ? $stmt->fetchColumn() : 0);
        }

        return $resumo;
    }

    public function getEventosPorStatus(): array
    {
        $stmt = $this->conn->query('SELECT status, COUNT(*) AS total FROM eventos GROUP BY status');
        $dados = [];
        if ($stmt) {
            foreach ($stmt->fetchAll() as $row) {
                $dados[$row['status']] = (int) $row['total'];
            }
        }
        return $dados;
    }

    public function getAtividadesPorStatus(): array
    {
        $stmt = $this->conn->query('SELECT status, COUNT(*) AS total FROM tarefas GROUP BY status');
        $dados = [];
        if ($stmt) {
            foreach ($stmt->fetchAll() as $row) {
                $dados[$row['status']] = (int) $row['total'];
            }
        }
        return $dados;
    }

    public function getEquipesDistribuicao(): array
    {
        $stmt = $this->conn->query('SELECT e.nome, e.capacidade_eventos, COUNT(em.id) AS membros FROM equipes e LEFT JOIN equipe_membros em ON e.id = em.equipe_id GROUP BY e.id ORDER BY e.nome');
        $dados = $stmt ? $stmt->fetchAll() : [];
        return $dados ?: [];
    }

    public function getParticipacaoCriancas(int $limit = 5): array
    {
        $query = "SELECT
                    c.nome_completo,
                    COUNT(ec.id) AS total_eventos,
                    SUM(CASE WHEN ec.status_participacao = 'Check-in' THEN 1 ELSE 0 END) AS total_checkins,
                    MAX(ec.data_inscricao) AS ultima_participacao
                  FROM criancas_cadastro c
                  LEFT JOIN evento_criancas ec ON c.id = ec.crianca_id
                  GROUP BY c.id
                  ORDER BY total_eventos DESC, ultima_participacao DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetchAll();
        return $dados ?: [];
    }

    public function getAtividadesPendentes(int $limit = 5): array
    {
        $query = "SELECT
                    t.id,
                    t.titulo,
                    t.status,
                    t.data_fim_prevista,
                    e.nome AS evento_nome,
                    u.nome_completo AS responsavel_nome
                  FROM tarefas t
                  LEFT JOIN eventos e ON t.evento_id = e.id
                  LEFT JOIN usuarios u ON t.responsavel_id = u.id
                  WHERE t.status <> 'concluida'
                  ORDER BY t.data_fim_prevista IS NULL ASC, t.data_fim_prevista ASC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetchAll();
        return $dados ?: [];
    }

    public function getEventosProximos(int $limit = 5): array
    {
        $query = "SELECT id, nome, status, data_inicio, data_fim_evento FROM eventos WHERE data_inicio IS NOT NULL ORDER BY data_inicio ASC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetchAll();
        return $dados ?: [];
    }

    public function getLogsRecentes(int $limit = 10): array
    {
        $query = "SELECT l.id, l.acao, l.tabela_afetada, l.registro_id, l.data_criacao, u.nome_completo AS usuario_nome
                  FROM logs_sistema l
                  LEFT JOIN usuarios u ON l.usuario_id = u.id
                  ORDER BY l.data_criacao DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetchAll();
        return $dados ?: [];
    }
}
