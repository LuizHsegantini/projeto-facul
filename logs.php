<?php
// Ativar relatório de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/LogsController.php';

// Verificar se o usuário está logado e tem permissão de administrador
requireLogin();
if (!hasPermission('administrador')) {
    header('Location: dashboard.php');
    exit();
}

// Processar ações
if (isset($_GET['action'])) {
    $logsController = new LogsController();
    
    switch ($_GET['action']) {
        case 'clean':
            $days = $_GET['days'] ?? 90;
            $deletedRows = $logsController->cleanOldLogs($days);
            $message = "Limpeza realizada com sucesso! $deletedRows registros removidos.";
            break;
            
        case 'export':
            $filters = [
                'search' => $_GET['search'] ?? '',
                'user_id' => $_GET['user_id'] ?? '',
                'start_date' => $_GET['start_date'] ?? '',
                'end_date' => $_GET['end_date'] ?? ''
            ];
            $logsData = $logsController->exportLogs($filters);
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="logs_sistema_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8
            
            if (!empty($logsData)) {
                fputcsv($output, ['ID', 'Usuário', 'Ação', 'Tabela', 'Registro ID', 'IP', 'Data']);
                foreach ($logsData as $log) {
                    fputcsv($output, [
                        $log['id'],
                        $log['usuario'],
                        $log['acao'],
                        $log['tabela_afetada'],
                        $log['registro_id'],
                        $log['ip_address'],
                        $log['data_criacao']
                    ]);
                }
            }
            fclose($output);
            exit();
    }
}

// Parâmetros de filtro
$search = $_GET['search'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$action = $_GET['action_filter'] ?? '';
$table = $_GET['table'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$page = max(1, $_GET['page'] ?? 1);

try {
    $logsController = new LogsController();
    $logsData = $logsController->index($search, $user_id, $action, $table, $start_date, $end_date, $page);
    $users = $logsController->getUsers();
    $actions = $logsController->getActions();
    $tables = $logsController->getTables();
    $statistics = $logsController->getLogStatistics($start_date, $end_date);
    $currentUser = getCurrentUser();
} catch (Exception $e) {
    error_log("Erro no logs: " . $e->getMessage());
    $logsData = ['logs' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1];
    $users = [];
    $actions = [];
    $tables = [];
    $statistics = [];
    $currentUser = getCurrentUser();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs do Sistema - Sistema de Gestão</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            padding: 0;
            position: fixed;
            width: 250px;
            z-index: 1000;
        }
        
        .sidebar .company-info {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar .company-info i {
            font-size: 2rem;
            color: white;
            margin-bottom: 0.5rem;
        }
        
        .sidebar .company-name {
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            margin: 0.25rem 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .header-bar {
            background: white;
            border-radius: 15px;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .log-details {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 5px;
        }
        
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="company-info">
            <i class="fas fa-building"></i>
            <div class="company-name">TechCorp Solutions</div>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="projects.php">
                    <i class="fas fa-project-diagram me-2"></i>Projetos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="tasks.php">
                    <i class="fas fa-tasks me-2"></i>Tarefas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="teams.php">
                    <i class="fas fa-users me-2"></i>Equipes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.php">
                    <i class="fas fa-user-cog me-2"></i>Usuários
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar me-2"></i>Relatórios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="logs.php">
                    <i class="fas fa-history me-2"></i>Logs do Sistema
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header-bar d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Logs do Sistema</h2>
                <p class="text-muted mb-0">Monitoramento e auditoria das atividades do sistema</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <small class="text-muted">Bem-vindo,</small><br>
                    <strong><?php echo htmlspecialchars($currentUser['nome']); ?></strong>
                    <span class="badge bg-primary ms-2"><?php echo ucfirst($currentUser['perfil']); ?></span>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['nome'], 0, 2)); ?>
                </div>
                <div class="dropdown ms-2">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card primary">
                    <div class="stat-number text-primary"><?php echo $statistics['total_logs'] ?? 0; ?></div>
                    <div class="stat-label">Total de Logs</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card info">
                    <div class="stat-number text-info"><?php echo count($statistics['logs_por_usuario'] ?? []); ?></div>
                    <div class="stat-label">Usuários Ativos</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card warning">
                    <div class="stat-number text-warning"><?php echo count($statistics['logs_por_acao'] ?? []); ?></div>
                    <div class="stat-label">Tipos de Ação</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card success">
                    <div class="stat-number text-success"><?php echo count($statistics['logs_por_dia'] ?? []); ?></div>
                    <div class="stat-label">Dias com Atividade</div>
                </div>
            </div>
        </div>

        <?php if (isset($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Buscar por ação, usuário...">
                </div>
                <div class="col-md-2">
                    <label for="user_id" class="form-label">Usuário</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">Todos</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" 
                                <?php echo $user_id == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['nome_completo']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="action_filter" class="form-label">Ação</label>
                    <select class="form-select" id="action_filter" name="action_filter">
                        <option value="">Todas</option>
                        <?php foreach ($actions as $act): ?>
                        <option value="<?php echo htmlspecialchars($act['acao']); ?>" 
                                <?php echo $action == $act['acao'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($act['acao']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="table" class="form-label">Tabela</label>
                    <select class="form-select" id="table" name="table">
                        <option value="">Todas</option>
                        <?php foreach ($tables as $tbl): ?>
                        <option value="<?php echo htmlspecialchars($tbl['tabela_afetada']); ?>" 
                                <?php echo $table == $tbl['tabela_afetada'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tbl['tabela_afetada']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="start_date" class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-2">
                    <label for="end_date" class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <a href="logs.php" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-times me-2"></i>Limpar
                    </a>
                    <a href="?action=export&<?php echo http_build_query($_GET); ?>" class="btn btn-success ms-2">
                        <i class="fas fa-download me-2"></i>Exportar CSV
                    </a>
                    <div class="btn-group ms-2" role="group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-trash me-2"></i>Limpeza
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?action=clean&days=30" 
                                   onclick="return confirm('Remover logs com mais de 30 dias?')">
                                   Logs > 30 dias</a></li>
                            <li><a class="dropdown-item" href="?action=clean&days=90" 
                                   onclick="return confirm('Remover logs com mais de 90 dias?')">
                                   Logs > 90 dias</a></li>
                            <li><a class="dropdown-item" href="?action=clean&days=365" 
                                   onclick="return confirm('Remover logs com mais de 1 ano?')">
                                   Logs > 1 ano</a></li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2 text-primary"></i>
                    Registros do Sistema
                    <?php if ($logsData['total'] > 0): ?>
                    <span class="badge bg-primary ms-2"><?php echo $logsData['total']; ?> registros</span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($logsData['logs'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum log encontrado com os filtros aplicados</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Usuário</th>
                                    <th>Ação</th>
                                    <th>Tabela</th>
                                    <th>IP</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logsData['logs'] as $log): ?>
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i:s', strtotime($log['data_criacao'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($log['usuario_nome']): ?>
                                            <strong><?php echo htmlspecialchars($log['usuario_nome']); ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">Sistema</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo strpos(strtolower($log['acao']), 'login') !== false ? 'success' :
                                                (strpos(strtolower($log['acao']), 'erro') !== false ? 'danger' :
                                                (strpos(strtolower($log['acao']), 'delete') !== false ? 'warning' : 'primary')); 
                                        ?>">
                                            <?php echo htmlspecialchars($log['acao']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($log['tabela_afetada']): ?>
                                            <code><?php echo htmlspecialchars($log['tabela_afetada']); ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($log['registro_id']): ?>
                                            <small>ID: <?php echo $log['registro_id']; ?></small>
                                        <?php endif; ?>
                                        
                                        <?php if ($log['dados_anteriores'] || $log['dados_novos']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-info ms-2" 
                                                data-bs-toggle="modal" data-bs-target="#logModal<?php echo $log['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <!-- Modal para detalhes do log -->
                                        <div class="modal fade" id="logModal<?php echo $log['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detalhes do Log #<?php echo $log['id']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php if ($log['dados_anteriores']): ?>
                                                        <h6>Dados Anteriores:</h6>
                                                        <div class="log-details mb-3">
                                                            <?php echo htmlspecialchars($log['dados_anteriores']); ?>
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($log['dados_novos']): ?>
                                                        <h6>Dados Novos:</h6>
                                                        <div class="log-details">
                                                            <?php echo htmlspecialchars($log['dados_novos']); ?>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($logsData['pages'] > 1): ?>
                    <div class="card-footer bg-white">
                        <nav aria-label="Navegação de logs">
                            <ul class="pagination justify-content-center mb-0">
                                <?php for ($i = 1; $i <= $logsData['pages']; $i++): ?>
                                <li class="page-item <?php echo $i == $logsData['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Section -->
        <?php if (!empty($statistics)): ?>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">Top Ações</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($statistics['logs_por_acao'])): ?>
                            <?php foreach (array_slice($statistics['logs_por_acao'], 0, 5) as $stat): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo htmlspecialchars($stat['acao']); ?></span>
                                <span class="badge bg-primary"><?php echo $stat['total']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Nenhum dado disponível</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">Top Usuários</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($statistics['logs_por_usuario'])): ?>
                            <?php foreach (array_slice($statistics['logs_por_usuario'], 0, 5) as $stat): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo htmlspecialchars($stat['nome_completo'] ?? 'Sistema'); ?></span>
                                <span class="badge bg-info"><?php echo $stat['total']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Nenhum dado disponível</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh a cada 2 minutos (opcional)
        // setTimeout(function() {
        //     window.location.reload();
        // }, 120000);
        
        // Confirmar limpeza de logs
        document.querySelectorAll('[href*="action=clean"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja remover estes logs? Esta ação não pode ser desfeita.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>