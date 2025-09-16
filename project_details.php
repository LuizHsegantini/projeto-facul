<?php
// project_details.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/ProjectsController.php';

// Verificar se o usuário está logado
requireLogin();

// Verificar se foi fornecido ID do projeto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: projects.php');
    exit();
}

$project_id = (int)$_GET['id'];
$projectsController = new ProjectsController();
$currentUser = getCurrentUser();

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'assign_team':
                if (hasPermission('gerente')) {
                    $result = $projectsController->assignTeam($project_id, $_POST['team_id']);
                    if ($result) {
                        $message = 'Equipe atribuída com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao atribuir equipe.';
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'remove_team':
                if (hasPermission('gerente')) {
                    $result = $projectsController->removeTeam($project_id, $_POST['team_id']);
                    if ($result) {
                        $message = 'Equipe removida com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao remover equipe.';
                        $messageType = 'danger';
                    }
                }
                break;
        }
    }
}

// Buscar dados do projeto
try {
    $project = $projectsController->getById($project_id);
    if (!$project) {
        header('Location: projects.php');
        exit();
    }
    
    $tasks = $projectsController->getProjectTasks($project_id);
    $teams = $projectsController->getProjectTeams($project_id);
    $availableTeams = $projectsController->getAvailableTeams($project_id);
    
    // Calcular estatísticas do projeto
    $totalTasks = count($tasks);
    $completedTasks = array_filter($tasks, function($task) { return $task['status'] === 'concluida'; });
    $pendingTasks = array_filter($tasks, function($task) { return $task['status'] === 'pendente'; });
    $inProgressTasks = array_filter($tasks, function($task) { return $task['status'] === 'em_execucao'; });
    
    $progressPercentage = $totalTasks > 0 ? round((count($completedTasks) / $totalTasks) * 100) : 0;
    
    // Verificar se o projeto está atrasado
    $isOverdue = false;
    if ($project['data_termino_prevista'] && 
        strtotime($project['data_termino_prevista']) < time() && 
        !in_array($project['status'], ['concluido', 'cancelado'])) {
        $isOverdue = true;
    }
    
} catch (Exception $e) {
    error_log("Erro ao buscar detalhes do projeto: " . $e->getMessage());
    header('Location: projects.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['nome']); ?> - Sistema de Gestão</title>
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
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        
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
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress {
            height: 10px;
            border-radius: 10px;
        }
        
        .project-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
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
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="projects.php">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="projects.php">Projetos</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($project['nome']); ?></li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="projects.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
                <?php if (hasPermission('gerente')): ?>
                <button type="button" class="btn btn-primary" onclick="editProject(<?php echo htmlspecialchars(json_encode($project)); ?>)">
                    <i class="fas fa-edit me-2"></i>Editar Projeto
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Mensagens -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Project Header -->
        <div class="project-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2"><?php echo htmlspecialchars($project['nome']); ?></h1>
                    <?php if ($project['descricao']): ?>
                    <p class="mb-3 opacity-75"><?php echo htmlspecialchars($project['descricao']); ?></p>
                    <?php endif; ?>
                    <div class="d-flex align-items-center flex-wrap">
                        <?php 
                        $statusClasses = [
                            'planejado' => 'bg-light text-dark',
                            'em_andamento' => 'bg-warning text-dark',
                            'concluido' => 'bg-success',
                            'cancelado' => 'bg-danger'
                        ];
                        $statusLabels = [
                            'planejado' => 'Planejado',
                            'em_andamento' => 'Em Andamento',
                            'concluido' => 'Concluído',
                            'cancelado' => 'Cancelado'
                        ];
                        ?>
                        <span class="badge <?php echo $statusClasses[$project['status']] ?? 'bg-secondary'; ?> me-3">
                            <?php echo $statusLabels[$project['status']] ?? 'Desconhecido'; ?>
                        </span>
                        
                        <?php if ($isOverdue): ?>
                        <span class="badge bg-danger me-3">
                            <i class="fas fa-exclamation-triangle me-1"></i>Atrasado
                        </span>
                        <?php endif; ?>
                        
                        <span class="text-light opacity-75">
                            <i class="fas fa-user me-1"></i>
                            Gerente: <?php echo htmlspecialchars($project['gerente_nome'] ?? 'Não atribuído'); ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="mb-2">
                        <h4><?php echo $progressPercentage; ?>%</h4>
                        <div class="progress bg-light bg-opacity-25" style="height: 8px;">
                            <div class="progress-bar bg-light" style="width: <?php echo $progressPercentage; ?>%"></div>
                        </div>
                        <small class="opacity-75">Progresso do Projeto</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-number text-primary"><?php echo $totalTasks; ?></div>
                    <div class="stat-label">Total de Tarefas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="stat-number text-success"><?php echo count($completedTasks); ?></div>
                    <div class="stat-label">Tarefas Concluídas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="stat-number text-warning"><?php echo count($inProgressTasks); ?></div>
                    <div class="stat-label">Em Execução</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info">
                    <div class="stat-number text-info"><?php echo count($teams); ?></div>
                    <div class="stat-label">Equipes Atribuídas</div>
                </div>
            </div>
        </div>
        
        <!-- Project Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            Informações do Projeto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <strong>Data de Início:</strong><br>
                                <span class="text-muted">
                                    <?php echo $project['data_inicio'] ? date('d/m/Y', strtotime($project['data_inicio'])) : 'Não definida'; ?>
                                </span>
                            </div>
                            <div class="col-6">
                                <strong>Prazo Previsto:</strong><br>
                                <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                    <?php echo $project['data_termino_prevista'] ? date('d/m/Y', strtotime($project['data_termino_prevista'])) : 'Não definido'; ?>
                                    <?php if ($isOverdue): ?>
                                        <i class="fas fa-exclamation-triangle ms-1"></i>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <strong>Criado em:</strong><br>
                                <span class="text-muted">
                                    <?php echo date('d/m/Y H:i', strtotime($project['data_criacao'])); ?>
                                </span>
                            </div>
                            <div class="col-6">
                                <strong>Última Atualização:</strong><br>
                                <span class="text-muted">
                                    <?php echo date('d/m/Y H:i', strtotime($project['data_atualizacao'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2 text-success"></i>
                            Equipes do Projeto
                        </h5>
                        <?php if (hasPermission('gerente') && !empty($availableTeams)): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignTeamModal">
                            <i class="fas fa-plus me-1"></i>Atribuir Equipe
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($teams)): ?>
                            <p class="text-muted text-center py-3">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Nenhuma equipe atribuída a este projeto
                            </p>
                        <?php else: ?>
                            <?php foreach ($teams as $team): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div>
                                    <strong><?php echo htmlspecialchars($team['nome']); ?></strong>
                                    <?php if ($team['descricao']): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($team['descricao']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php if (hasPermission('gerente')): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="remove_team">
                                    <input type="hidden" name="team_id" value="<?php echo $team['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Remover esta equipe do projeto?')"
                                            title="Remover Equipe">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tasks Section -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tasks me-2 text-primary"></i>
                    Tarefas do Projeto
                </h5>
                <a href="tasks.php?project=<?php echo $project_id; ?>" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>Nova Tarefa
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($tasks)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma tarefa criada</h5>
                        <p class="text-muted">Comece criando a primeira tarefa para este projeto</p>
                        <a href="tasks.php?project=<?php echo $project_id; ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Criar Primeira Tarefa
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tarefa</th>
                                    <th>Responsável</th>
                                    <th>Status</th>
                                    <th>Prazo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($task['titulo']); ?></strong>
                                        <?php if ($task['descricao']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($task['descricao'], 0, 60)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($task['responsavel_nome'] ?? 'Não atribuído'); ?></td>
                                    <td>
                                        <?php 
                                        $taskStatusClasses = [
                                            'pendente' => 'bg-secondary',
                                            'em_execucao' => 'bg-primary',
                                            'concluida' => 'bg-success'
                                        ];
                                        $taskStatusLabels = [
                                            'pendente' => 'Pendente',
                                            'em_execucao' => 'Em Execução',
                                            'concluida' => 'Concluída'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $taskStatusClasses[$task['status']] ?? 'bg-secondary'; ?>">
                                            <?php echo $taskStatusLabels[$task['status']] ?? 'Desconhecido'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($task['data_fim_prevista']): ?>
                                            <?php 
                                            $prazo = date('d/m/Y', strtotime($task['data_fim_prevista']));
                                            $taskOverdue = strtotime($task['data_fim_prevista']) < time() && $task['status'] !== 'concluida';
                                            ?>
                                            <span class="<?php echo $taskOverdue ? 'text-danger fw-bold' : ''; ?>">
                                                <?php echo $prazo; ?>
                                                <?php if ($taskOverdue): ?>
                                                    <i class="fas fa-exclamation-triangle ms-1"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Não definido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="task_details.php?id=<?php echo $task['id']; ?>" 
                                           class="btn btn-sm btn-outline-info" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="tasks.php?project=<?php echo $project_id; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Ver Todas as Tarefas
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Modal para Atribuir Equipe -->
    <?php if (hasPermission('gerente') && !empty($availableTeams)): ?>
    <div class="modal fade" id="assignTeamModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atribuir Equipe ao Projeto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="assign_team">
                        <div class="mb-3">
                            <label for="team_id" class="form-label">Selecione a Equipe</label>
                            <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Escolha uma equipe...</option>
                                <?php foreach ($availableTeams as $team): ?>
                                <option value="<?php echo $team['id']; ?>">
                                    <?php echo htmlspecialchars($team['nome']); ?>
                                    <?php if ($team['descricao']): ?>
                                    - <?php echo htmlspecialchars(substr($team['descricao'], 0, 50)); ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Atribuir Equipe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProject(project) {
            // Redirecionar para a página de projetos com parâmetros de edição
            window.location.href = 'projects.php?edit=' + project.id;
        }
    </script>
</body>
</html>