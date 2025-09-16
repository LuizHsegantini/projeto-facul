<?php
// Ativar relatório de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/DashboardController.php';

// Verificar se o usuário está logado
requireLogin();

try {
    $dashboardController = new DashboardController();
    $dashboardData = $dashboardController->getDashboardData();
    $currentUser = getCurrentUser();
} catch (Exception $e) {
    error_log("Erro no dashboard: " . $e->getMessage());
    // Dados padrão em caso de erro
    $dashboardData = [
        'total_projects' => 0,
        'active_projects' => 0,
        'total_tasks' => 0,
        'pending_tasks' => 0,
        'total_teams' => 0,
        'total_users' => 0,
        'recent_projects' => [],
        'delayed_projects' => [],
        'overdue_tasks' => [],
        'project_status_summary' => ['planejado' => 0, 'em_andamento' => 0, 'concluido' => 0, 'cancelado' => 0],
        'task_status_summary' => ['pendente' => 0, 'em_execucao' => 0, 'concluida' => 0],
        'my_tasks' => []
    ];
    $currentUser = getCurrentUser();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gestão</title>
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
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
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
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        
        .stat-number {
            font-size: 2.5rem;
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
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.1;
            position: absolute;
            right: 1rem;
            top: 1rem;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1rem;
        }
        
        .progress {
            height: 8px;
            border-radius: 10px;
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
                <a class="nav-link active" href="dashboard.php">
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
            <?php if (hasPermission('administrador')): ?>
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
                <a class="nav-link" href="logs.php">
                    <i class="fas fa-history me-2"></i>Logs do Sistema
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header-bar d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Dashboard Executivo</h2>
                <p class="text-muted mb-0">Visão geral do sistema de gestão de projetos</p>
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
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card primary position-relative">
                    <div class="stat-number text-primary"><?php echo $dashboardData['total_projects']; ?></div>
                    <div class="stat-label">Total de Projetos</div>
                    <i class="fas fa-project-diagram stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card success position-relative">
                    <div class="stat-number text-success"><?php echo $dashboardData['active_projects']; ?></div>
                    <div class="stat-label">Projetos Ativos</div>
                    <i class="fas fa-play-circle stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card info position-relative">
                    <div class="stat-number text-info"><?php echo $dashboardData['total_tasks']; ?></div>
                    <div class="stat-label">Total de Tarefas</div>
                    <i class="fas fa-tasks stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card warning position-relative">
                    <div class="stat-number text-warning"><?php echo $dashboardData['pending_tasks']; ?></div>
                    <div class="stat-label">Tarefas Pendentes</div>
                    <i class="fas fa-clock stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card primary position-relative">
                    <div class="stat-number text-primary"><?php echo $dashboardData['total_teams']; ?></div>
                    <div class="stat-label">Equipes</div>
                    <i class="fas fa-users stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card info position-relative">
                    <div class="stat-number text-info"><?php echo $dashboardData['total_users']; ?></div>
                    <div class="stat-label">Usuários</div>
                    <i class="fas fa-user stat-icon"></i>
                </div>
            </div>
        </div>
        
        <!-- Alerts Section -->
        <?php if (count($dashboardData['delayed_projects']) > 0 || count($dashboardData['overdue_tasks']) > 0): ?>
        <div class="row mb-4">
            <?php if (count($dashboardData['delayed_projects']) > 0): ?>
            <div class="col-md-6 mb-3">
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Projetos em Atraso</h5>
                    <p class="mb-2">Existem <?php echo count($dashboardData['delayed_projects']); ?> projeto(s) com prazo vencido:</p>
                    <ul class="mb-0">
                        <?php foreach (array_slice($dashboardData['delayed_projects'], 0, 3) as $project): ?>
                        <li><?php echo htmlspecialchars($project['nome']); ?> - Prazo: <?php echo date('d/m/Y', strtotime($project['data_termino_prevista'])); ?></li>
                        <?php endforeach; ?>
                        <?php if (count($dashboardData['delayed_projects']) > 3): ?>
                        <li><em>E mais <?php echo count($dashboardData['delayed_projects']) - 3; ?> projeto(s)...</em></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (count($dashboardData['overdue_tasks']) > 0): ?>
            <div class="col-md-6 mb-3">
                <div class="alert alert-warning">
                    <h5><i class="fas fa-clock me-2"></i>Tarefas Atrasadas</h5>
                    <p class="mb-2">Existem <?php echo count($dashboardData['overdue_tasks']); ?> tarefa(s) em atraso:</p>
                    <ul class="mb-0">
                        <?php foreach (array_slice($dashboardData['overdue_tasks'], 0, 3) as $task): ?>
                        <li><?php echo htmlspecialchars($task['titulo']); ?> - <?php echo htmlspecialchars($task['responsavel_nome']); ?></li>
                        <?php endforeach; ?>
                        <?php if (count($dashboardData['overdue_tasks']) > 3): ?>
                        <li><em>E mais <?php echo count($dashboardData['overdue_tasks']) - 3; ?> tarefa(s)...</em></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Recent Projects -->
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-project-diagram me-2 text-primary"></i>
                            Projetos Recentes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dashboardData['recent_projects'])): ?>
                            <p class="text-muted text-center py-4">Nenhum projeto encontrado</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Projeto</th>
                                            <th>Gerente</th>
                                            <th>Status</th>
                                            <th>Prazo</th>
                                            <th>Progresso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dashboardData['recent_projects'] as $project): ?>
                                        <?php $progress = $dashboardController->getProjectProgress($project['id']); ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($project['nome']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars(substr($project['descricao'] ?? '', 0, 50)); ?>...</small>
                                            </td>
                                            <td><?php echo htmlspecialchars($project['gerente_nome'] ?? 'Não atribuído'); ?></td>
                                            <td>
                                                <?php 
                                                $statusClass = [
                                                    'planejado' => 'secondary',
                                                    'em_andamento' => 'primary',
                                                    'concluido' => 'success',
                                                    'cancelado' => 'danger'
                                                ];
                                                $statusText = [
                                                    'planejado' => 'Planejado',
                                                    'em_andamento' => 'Em Andamento',
                                                    'concluido' => 'Concluído',
                                                    'cancelado' => 'Cancelado'
                                                ];
                                                $status = $project['status'] ?? 'planejado';
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass[$status] ?? 'secondary'; ?>">
                                                    <?php echo $statusText[$status] ?? 'Desconhecido'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $project['data_termino_prevista'] ? date('d/m/Y', strtotime($project['data_termino_prevista'])) : 'Não definido'; ?></td>
                                            <td>
                                                <div class="progress mb-1" style="height: 6px;">
                                                    <div class="progress-bar" style="width: <?php echo $progress['percentage']; ?>%"></div>
                                                </div>
                                                <small class="text-muted"><?php echo $progress['percentage']; ?>% (<?php echo $progress['completed']; ?>/<?php echo $progress['total']; ?>)</small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <div class="text-center mt-3">
                            <a href="projects.php" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-2"></i>Ver Todos os Projetos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Status Summary -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2 text-success"></i>
                            Resumo por Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Projetos</h6>
                            <?php foreach ($dashboardData['project_status_summary'] as $status => $count): ?>
                            <?php if ($count > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                                <span class="badge bg-secondary"><?php echo $count; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Tarefas</h6>
                            <?php foreach ($dashboardData['task_status_summary'] as $status => $count): ?>
                            <?php if ($count > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                                <span class="badge bg-info"><?php echo $count; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($currentUser['perfil'] === 'colaborador' && !empty($dashboardData['my_tasks'])): ?>
                        <div>
                            <h6 class="text-muted mb-3">Minhas Tarefas</h6>
                            <?php foreach (array_slice($dashboardData['my_tasks'], 0, 5) as $task): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div>
                                    <small class="fw-bold"><?php echo htmlspecialchars($task['titulo']); ?></small><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($task['projeto_nome'] ?? ''); ?></small>
                                </div>
                                <span class="badge bg-<?php echo $task['status'] === 'concluida' ? 'success' : ($task['status'] === 'em_execucao' ? 'primary' : 'secondary'); ?>">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="tasks.php?user=<?php echo $currentUser['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    Ver Todas Minhas Tarefas
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh dashboard every 5 minutes
        setTimeout(function() {
            window.location.reload();
        }, 300000);
        
        // Add some interactivity to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', function() {
                // You can add navigation logic here based on the card clicked
            });
        });
    </script>
</body>
</html>