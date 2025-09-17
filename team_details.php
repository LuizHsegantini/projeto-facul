<?php
// team_details.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/TeamsController.php';

// Verificar se o usuário está logado
requireLogin();

// Verificar se foi fornecido ID da equipe
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: teams.php');
    exit();
}

$team_id = (int)$_GET['id'];
$teamsController = new TeamsController();
$currentUser = getCurrentUser();

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_member':
                if (hasPermission('gerente')) {
                    $result = $teamsController->addMember($team_id, $_POST['user_id']);
                    if ($result) {
                        $message = 'Membro adicionado à equipe com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao adicionar membro à equipe.';
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'remove_member':
                if (hasPermission('gerente')) {
                    $result = $teamsController->removeMember($team_id, $_POST['user_id']);
                    if ($result) {
                        $message = 'Membro removido da equipe com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao remover membro da equipe.';
                        $messageType = 'danger';
                    }
                }
                break;
        }
    }
}

// Buscar dados da equipe
try {
    $team = $teamsController->getById($team_id);
    if (!$team) {
        header('Location: teams.php');
        exit();
    }
    
    $members = $teamsController->getTeamMembers($team_id);
    $projects = $teamsController->getTeamProjects($team_id);
    $availableUsers = $teamsController->getAvailableUsers($team_id);
    $stats = $teamsController->getTeamStats($team_id);
    
} catch (Exception $e) {
    error_log("Erro ao buscar detalhes da equipe: " . $e->getMessage());
    header('Location: teams.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['nome']); ?> - Sistema de Gestão</title>
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
        
        .team-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .team-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .member-card {
            transition: transform 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .member-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .member-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin: 0 auto 0.5rem;
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
                <a class="nav-link active" href="teams.php">
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
                        <li class="breadcrumb-item"><a href="teams.php">Equipes</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($team['nome']); ?></li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="teams.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
                <?php if (hasPermission('gerente')): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="fas fa-user-plus me-2"></i>Adicionar Membro
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
        
        <!-- Team Header -->
        <div class="team-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="team-avatar">
                        <?php echo strtoupper(substr($team['nome'], 0, 2)); ?>
                    </div>
                    <h1 class="mb-2"><?php echo htmlspecialchars($team['nome']); ?></h1>
                    <?php if ($team['descricao']): ?>
                    <p class="mb-3 opacity-75"><?php echo htmlspecialchars($team['descricao']); ?></p>
                    <?php endif; ?>
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="badge bg-light text-dark me-3">
                            <i class="fas fa-users me-1"></i>
                            <?php echo count($members); ?> Membros
                        </span>
                        <span class="text-light opacity-75">
                            <i class="fas fa-calendar me-1"></i>
                            Criada em: <?php echo date('d/m/Y', strtotime($team['data_criacao'])); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-number text-primary"><?php echo count($members); ?></div>
                    <div class="stat-label">Total de Membros</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info">
                    <div class="stat-number text-info"><?php echo $stats['total_projetos']; ?></div>
                    <div class="stat-label">Projetos Ativos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="stat-number text-warning"><?php echo $stats['total_tarefas']; ?></div>
                    <div class="stat-label">Tarefas Totais</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="stat-number text-success"><?php echo $stats['percentual_conclusao']; ?>%</div>
                    <div class="stat-label">Taxa de Conclusão</div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Team Members -->
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2 text-primary"></i>
                            Membros da Equipe
                        </h5>
                        <?php if (hasPermission('gerente') && !empty($availableUsers)): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            <i class="fas fa-user-plus me-1"></i>Adicionar
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($members)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum membro na equipe</h5>
                                <p class="text-muted">Adicione membros para começar a trabalhar</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($members as $member): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="member-card p-3 rounded text-center">
                                        <div class="member-avatar">
                                            <?php echo strtoupper(substr($member['nome_completo'], 0, 2)); ?>
                                        </div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($member['nome_completo']); ?></h6>
                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($member['cargo'] ?? 'Cargo não informado'); ?></p>
                                        <span class="badge bg-<?php echo $member['perfil'] === 'administrador' ? 'danger' : ($member['perfil'] === 'gerente' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($member['perfil']); ?>
                                        </span>
                                        <?php if (hasPermission('gerente')): ?>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="removeMember(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['nome_completo']); ?>')"
                                                    title="Remover da equipe">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Team Projects -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-project-diagram me-2 text-success"></i>
                            Projetos da Equipe
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($projects)): ?>
                            <p class="text-muted text-center py-3">
                                <i class="fas fa-project-diagram fa-2x mb-2 d-block"></i>
                                Nenhum projeto atribuído a esta equipe
                            </p>
                        <?php else: ?>
                            <?php foreach ($projects as $project): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div>
                                    <a href="project_details.php?id=<?php echo $project['id']; ?>" class="text-decoration-none">
                                        <strong><?php echo htmlspecialchars($project['nome']); ?></strong>
                                    </a>
                                    <?php if ($project['gerente_nome']): ?>
                                    <br><small class="text-muted">Gerente: <?php echo htmlspecialchars($project['gerente_nome']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-<?php 
                                    echo $project['status'] === 'concluido' ? 'success' : 
                                        ($project['status'] === 'em_andamento' ? 'primary' : 
                                        ($project['status'] === 'cancelado' ? 'danger' : 'secondary')); 
                                ?>">
                                    <?php 
                                    $statusLabels = [
                                        'planejado' => 'Planejado',
                                        'em_andamento' => 'Em Andamento',
                                        'concluido' => 'Concluído',
                                        'cancelado' => 'Cancelado'
                                    ];
                                    echo $statusLabels[$project['status']] ?? 'Desconhecido';
                                    ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Modal para Adicionar Membro -->
    <?php if (hasPermission('gerente') && !empty($availableUsers)): ?>
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Membro à Equipe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_member">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Selecione o Usuário</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Escolha um usuário...</option>
                                <?php foreach ($availableUsers as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['nome_completo']); ?>
                                    <?php if ($user['cargo']): ?>
                                    - <?php echo htmlspecialchars($user['cargo']); ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar à Equipe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Modal de Confirmação de Remoção -->
    <?php if (hasPermission('gerente')): ?>
    <div class="modal fade" id="removeMemberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Remoção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja remover <strong id="removeMemberName"></strong> da equipe?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="remove_member">
                        <input type="hidden" id="removeMemberId" name="user_id">
                        <button type="submit" class="btn btn-danger">Remover</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function removeMember(userId, userName) {
            document.getElementById('removeMemberId').value = userId;
            document.getElementById('removeMemberName').textContent = userName;
            new bootstrap.Modal(document.getElementById('removeMemberModal')).show();
        }
    </script>
</body>
</html>