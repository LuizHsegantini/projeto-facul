<?php
// logs.php - Sistema de logs adaptado para MagicKids Eventos
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/LogsController.php';

date_default_timezone_set('America/Sao_Paulo');

// Verificar se o usuário está logado e tem permissão de administrador
requireLogin();
if (!hasPermission('administrador')) {
    header('Location: dashboard_eventos.php');
    exit();
}

$logsController = new LogsController();
$message = null;

// Processar ações
if (isset($_GET['action'])) {
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
            header('Content-Disposition: attachment; filename="logs_magickids_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8
            
            if (!empty($logsData)) {
                fputcsv($output, ['ID', 'Usuário', 'Ação', 'Tabela', 'Registro ID', 'IP', 'Data']);
                foreach ($logsData as $log) {
                    fputcsv($output, [
                        $log['id'],
                        $log['usuario_nome'] ?? 'Sistema',
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

if (!function_exists('renderLogData')) {
    function renderLogData(?string $payload): string
    {
        if ($payload === null || $payload === '') {
            return '<em class="text-muted">Sem dados</em>';
        }

        $decoded = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $formatted = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            $formatted = $payload;
        }

        return '<pre class="mb-0">' . htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8') . '</pre>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs do Sistema - MagicKids Eventos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b9d;
            --secondary-color: #ffc93c;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06bcf4;
        }
        
        body {
            background-color: #fef7ff;
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
            box-shadow: 0 2px 10px rgba(255, 107, 157, 0.1);
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 2px 10px rgba(255, 107, 157, 0.1);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(255, 107, 157, 0.1);
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
            max-height: 200px;
            overflow-y: auto;
        }
        
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(255, 107, 157, 0.1);
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            opacity: 0.05;
            animation: float 8s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            top: 10%;
            left: 70%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            top: 50%;
            right: 5%;
            animation-delay: 3s;
        }
        
        .shape:nth-child(3) {
            bottom: 20%;
            left: 75%;
            animation-delay: 6s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }

        .action-badge {
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-history fa-6x shape"></i>
        <i class="fas fa-shield-alt fa-5x shape"></i>
        <i class="fas fa-database fa-4x shape"></i>
    </div>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="company-info">
            <i class="fas fa-magic"></i>
            <div class="company-name">MagicKids Eventos</div>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard_eventos.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="eventos.php">
                    <i class="fas fa-calendar-star me-2"></i>Eventos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="criancas.php">
                    <i class="fas fa-child me-2"></i>Crianças
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cadastro_crianca.php">
                    <i class="fas fa-user-plus me-2"></i>Cadastrar Criança
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="checkin.php">
                    <i class="fas fa-clipboard-check me-2"></i>Check-in/Check-out
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="atividades.php">
                    <i class="fas fa-gamepad me-2"></i>Atividades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="equipes.php">
                    <i class="fas fa-users me-2"></i>Equipes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="funcionarios.php">
                    <i class="fas fa-user-tie me-2"></i>Funcionários
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="relatorios.php">
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
                    <small class="text-muted">Bem-vindo(a),</small><br>
                    <strong><?php echo htmlspecialchars($currentUser['nome_completo'] ?? 'Usuário'); ?></strong>
                    <span class="badge bg-primary ms-2"><?php echo ucfirst($currentUser['perfil'] ?? 'usuario'); ?></span>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['nome_completo'] ?? 'U', 0, 2)); ?>
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
                    <label for="search" class="form-label">
                        <i class="fas fa-search me-1"></i>Buscar
                    </label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Buscar por ação, usuário...">
                </div>
                <div class="col-md-2">
                    <label for="user_id" class="form-label">
                        <i class="fas fa-user me-1"></i>Usuário
                    </label>
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
                    <label for="action_filter" class="form-label">
                        <i class="fas fa-cog me-1"></i>Ação
                    </label>
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
                    <label for="table" class="form-label">
                        <i class="fas fa-table me-1"></i>Tabela
                    </label>
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
                    <label for="start_date" class="form-label">
                        <i class="fas fa-calendar me-1"></i>Data Início
                    </label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-1">
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
                                   <i class="fas fa-calendar me-2"></i>Logs > 30 dias</a></li>
                            <li><a class="dropdown-item" href="?action=clean&days=90" 
                                   onclick="return confirm('Remover logs com mais de 90 dias?')">
                                   <i class="fas fa-calendar me-2"></i>Logs > 90 dias</a></li>
                            <li><a class="dropdown-item" href="?action=clean&days=365" 
                                   onclick="return confirm('Remover logs com mais de 1 ano?')">
                                   <i class="fas fa-calendar me-2"></i>Logs > 1 ano</a></li>
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
                        <a href="logs.php" class="btn btn-outline-primary">
                            <i class="fas fa-refresh me-2"></i>Ver Todos os Logs
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="15%">Data/Hora</th>
                                    <th width="15%">Usuário</th>
                                    <th width="20%">Ação</th>
                                    <th width="15%">Tabela</th>
                                    <th width="10%">IP</th>
                                    <th width="25%">Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logsData['logs'] as $log): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong><?php echo date('d/m/Y', strtotime($log['data_criacao'])); ?></strong>
                                            <small class="text-muted">
                                                <?php echo date('H:i:s', strtotime($log['data_criacao'])); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($log['usuario_nome']): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-2" style="width: 25px; height: 25px; font-size: 0.7rem;">
                                                    <?php echo strtoupper(substr($log['usuario_nome'], 0, 2)); ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($log['usuario_nome']); ?></strong>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-robot me-1"></i>Sistema
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $actionClass = 'secondary';
                                        $actionIcon = 'fas fa-cog';
                                        
                                        $actionLower = strtolower($log['acao']);
                                        if (strpos($actionLower, 'login') !== false) {
                                            $actionClass = 'success';
                                            $actionIcon = 'fas fa-sign-in-alt';
                                        } elseif (strpos($actionLower, 'logout') !== false) {
                                            $actionClass = 'warning';
                                            $actionIcon = 'fas fa-sign-out-alt';
                                        } elseif (strpos($actionLower, 'criado') !== false || strpos($actionLower, 'cadastro') !== false) {
                                            $actionClass = 'success';
                                            $actionIcon = 'fas fa-plus';
                                        } elseif (strpos($actionLower, 'atualizada') !== false || strpos($actionLower, 'atualizado') !== false) {
                                            $actionClass = 'info';
                                            $actionIcon = 'fas fa-edit';
                                        } elseif (strpos($actionLower, 'excluída') !== false || strpos($actionLower, 'removid') !== false) {
                                            $actionClass = 'danger';
                                            $actionIcon = 'fas fa-trash';
                                        } elseif (strpos($actionLower, 'check-in') !== false) {
                                            $actionClass = 'primary';
                                            $actionIcon = 'fas fa-clipboard-check';
                                        } elseif (strpos($actionLower, 'evento') !== false) {
                                            $actionClass = 'info';
                                            $actionIcon = 'fas fa-calendar-star';
                                        } elseif (strpos($actionLower, 'criança') !== false) {
                                            $actionClass = 'warning';
                                            $actionIcon = 'fas fa-child';
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $actionClass; ?> action-badge">
                                            <i class="<?php echo $actionIcon; ?> me-1"></i>
                                            <?php echo htmlspecialchars($log['acao']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($log['tabela_afetada']): ?>
                                            <code class="small"><?php echo htmlspecialchars($log['tabela_afetada']); ?></code>
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
                                        <div class="d-flex align-items-center">
                                            <?php if ($log['registro_id']): ?>
                                                <small class="me-2">ID: <?php echo $log['registro_id']; ?></small>
                                            <?php endif; ?>
                                            
                                            <?php if ($log['dados_anteriores'] || $log['dados_novos']): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    data-bs-toggle="modal" data-bs-target="#logModal<?php echo $log['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <!-- Modal para detalhes do log -->
                                            <div class="modal fade" id="logModal<?php echo $log['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-info-circle me-2"></i>
                                                                Detalhes do Log #<?php echo $log['id']; ?>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <strong>Usuário:</strong> <?php echo htmlspecialchars($log['usuario_nome'] ?? 'Sistema'); ?>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <strong>IP:</strong> <?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?>
                                                                </div>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <strong>Ação:</strong> <?php echo htmlspecialchars($log['acao']); ?>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <strong>Data:</strong> <?php echo date('d/m/Y H:i:s', strtotime($log['data_criacao'])); ?>
                                                                </div>
                                                            </div>
                                                            <?php if ($log['tabela_afetada']): ?>
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <strong>Tabela:</strong> <code><?php echo htmlspecialchars($log['tabela_afetada']); ?></code>
                                                                </div>
                                                                <?php if ($log['registro_id']): ?>
                                                                <div class="col-md-6">
                                                                    <strong>ID do Registro:</strong> <?php echo $log['registro_id']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($log['dados_anteriores']): ?>
                                                            <div class="mb-3">
                                                                <h6 class="text-primary">
                                                                    <i class="fas fa-history me-2"></i>Dados Anteriores:
                                                                </h6>
                                                                <div class="log-details">
                                                                    <?php echo renderLogData($log['dados_anteriores']); ?>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($log['dados_novos']): ?>
                                                            <div class="mb-3">
                                                                <h6 class="text-success">
                                                                    <i class="fas fa-plus-circle me-2"></i>Dados Novos:
                                                                </h6>
                                                                <div class="log-details">
                                                                    <?php echo renderLogData($log['dados_novos']); ?>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
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
                                <?php if ($logsData['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $logsData['current_page'] - 1])); ?>">
                                        <i class="fas fa-chevron-left"></i> Anterior
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $logsData['current_page'] - 2);
                                $endPage = min($logsData['pages'], $logsData['current_page'] + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++): 
                                ?>
                                <li class="page-item <?php echo $i == $logsData['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($logsData['current_page'] < $logsData['pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $logsData['current_page'] + 1])); ?>">
                                        Próximo <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Mostrando página <?php echo $logsData['current_page']; ?> de <?php echo $logsData['pages']; ?>
                                (<?php echo $logsData['total']; ?> registros no total)
                            </small>
                        </div>
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
                        <h6 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>
                            Top 10 Ações Mais Frequentes
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($statistics['logs_por_acao'])): ?>
                            <?php foreach (array_slice($statistics['logs_por_acao'], 0, 10) as $stat): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div>
                                    <strong><?php echo htmlspecialchars($stat['acao']); ?></strong>
                                </div>
                                <span class="badge bg-primary"><?php echo $stat['total']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Nenhum dado disponível
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-users me-2 text-info"></i>
                            Top 10 Usuários Mais Ativos
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($statistics['logs_por_usuario'])): ?>
                            <?php foreach (array_slice($statistics['logs_por_usuario'], 0, 10) as $stat): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width: 25px; height: 25px; font-size: 0.7rem;">
                                        <?php echo strtoupper(substr($stat['nome_completo'] ?? 'S', 0, 2)); ?>
                                    </div>
                                    <strong><?php echo htmlspecialchars($stat['nome_completo'] ?? 'Sistema'); ?></strong>
                                </div>
                                <span class="badge bg-info"><?php echo $stat['total']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Nenhum dado disponível
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <?php if (!empty($statistics['logs_por_dia'])): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2 text-success"></i>
                            Atividade dos Últimos Dias
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach (array_slice($statistics['logs_por_dia'], 0, 7) as $stat): ?>
                            <div class="col-md text-center mb-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body py-2">
                                        <div class="h5 mb-1 text-primary"><?php echo $stat['total']; ?></div>
                                        <small class="text-muted">
                                            <?php echo date('d/m', strtotime($stat['data'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2 text-warning"></i>
                            Informações Rápidas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h4 class="text-primary mb-0"><?php echo $statistics['total_logs'] ?? 0; ?></h4>
                                    <small class="text-muted">Total de Logs</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h4 class="text-success mb-0">
                                        <?php 
                                        $logs_hoje = 0;
                                        if (!empty($statistics['logs_por_dia'])) {
                                            foreach ($statistics['logs_por_dia'] as $dia) {
                                                if ($dia['data'] === date('Y-m-d')) {
                                                    $logs_hoje = $dia['total'];
                                                    break;
                                                }
                                            }
                                        }
                                        echo $logs_hoje;
                                        ?>
                                    </h4>
                                    <small class="text-muted">Logs Hoje</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h4 class="text-info mb-0"><?php echo count($statistics['logs_por_usuario'] ?? []); ?></h4>
                                    <small class="text-muted">Usuários Ativos</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-warning mb-0"><?php echo count($statistics['logs_por_acao'] ?? []); ?></h4>
                                <small class="text-muted">Tipos de Ação</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh a cada 5 minutos (opcional - pode ser desabilitado)
        let autoRefreshEnabled = false;
        if (autoRefreshEnabled) {
            setTimeout(function() {
                window.location.reload();
            }, 300000);
        }
        
        // Confirmar limpeza de logs
        document.querySelectorAll('[href*="action=clean"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                const days = this.getAttribute('href').match(/days=(\d+)/)?.[1] || 'alguns';
                if (!confirm(`Tem certeza que deseja remover logs com mais de ${days} dias?\n\nEsta ação não pode ser desfeita e afetará o histórico de auditoria do sistema.`)) {
                    e.preventDefault();
                }
            });
        });

        // Adicionar tooltips aos badges de ação
        document.querySelectorAll('.action-badge').forEach(function(badge) {
            const action = badge.textContent.trim();
            let tooltip = 'Ação do sistema';
            
            if (action.toLowerCase().includes('login')) {
                tooltip = 'Usuário fez login no sistema';
            } else if (action.toLowerCase().includes('logout')) {
                tooltip = 'Usuário fez logout do sistema';
            } else if (action.toLowerCase().includes('criado') || action.toLowerCase().includes('cadastro')) {
                tooltip = 'Novo registro foi criado';
            } else if (action.toLowerCase().includes('atualizada') || action.toLowerCase().includes('atualizado')) {
                tooltip = 'Registro foi modificado';
            } else if (action.toLowerCase().includes('excluída') || action.toLowerCase().includes('removid')) {
                tooltip = 'Registro foi removido';
            } else if (action.toLowerCase().includes('check-in')) {
                tooltip = 'Check-in de criança em evento';
            }
            
            badge.setAttribute('title', tooltip);
            badge.setAttribute('data-bs-toggle', 'tooltip');
        });

        // Inicializar tooltips do Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Filtro rápido por data
        document.addEventListener('DOMContentLoaded', function() {
            // Botões de filtro rápido
            const quickFilters = document.createElement('div');
            quickFilters.className = 'mb-3';
            quickFilters.innerHTML = `
                <div class="btn-group me-2" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickDateFilter('today')">
                        <i class="fas fa-calendar-day me-1"></i>Hoje
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickDateFilter('week')">
                        <i class="fas fa-calendar-week me-1"></i>Esta Semana
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickDateFilter('month')">
                        <i class="fas fa-calendar-alt me-1"></i>Este Mês
                    </button>
                </div>
            `;
            
            const filterSection = document.querySelector('.filter-section form');
            if (filterSection) {
                filterSection.parentNode.insertBefore(quickFilters, filterSection);
            }
        });

        function setQuickDateFilter(period) {
            const today = new Date();
            let startDate, endDate;
            
            switch(period) {
                case 'today':
                    startDate = endDate = today.toISOString().split('T')[0];
                    break;
                case 'week':
                    const weekStart = new Date(today.setDate(today.getDate() - today.getDay()));
                    startDate = weekStart.toISOString().split('T')[0];
                    endDate = new Date().toISOString().split('T')[0];
                    break;
                case 'month':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    endDate = new Date().toISOString().split('T')[0];
                    break;
            }
            
            document.getElementById('start_date').value = startDate;
            document.getElementById('end_date').value = endDate;
            document.querySelector('.filter-section form').submit();
        }

        // Realçar logs importantes
        document.addEventListener('DOMContentLoaded', function() {
            const logRows = document.querySelectorAll('tbody tr');
            logRows.forEach(function(row) {
                const actionBadge = row.querySelector('.action-badge');
                if (actionBadge) {
                    const action = actionBadge.textContent.toLowerCase();
                    if (action.includes('erro') || action.includes('falha')) {
                        row.classList.add('table-danger');
                    } else if (action.includes('login') && action.includes('falhado')) {
                        row.classList.add('table-warning');
                    }
                }
            });
        });
    </script>
</body>
</html>