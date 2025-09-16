<?php
// teams.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/TeamsController.php';

// Verificar se o usuário está logado
requireLogin();

$teamsController = new TeamsController();
$currentUser = getCurrentUser();

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                if (hasPermission('gerente')) {
                    $result = $teamsController->create($_POST);
                    if ($result) {
                        $message = 'Equipe criada com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao criar equipe.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para criar equipes.';
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                if (hasPermission('gerente')) {
                    $result = $teamsController->update($_POST['id'], $_POST);
                    if ($result) {
                        $message = 'Equipe atualizada com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao atualizar equipe.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para editar equipes.';
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                if (hasPermission('administrador')) {
                    $result = $teamsController->delete($_POST['id']);
                    if ($result) {
                        $message = 'Equipe excluída com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao excluir equipe.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para excluir equipes.';
                    $messageType = 'danger';
                }
                break;
                
            case 'add_member':
                if (hasPermission('gerente')) {
                    $result = $teamsController->addMember($_POST['team_id'], $_POST['user_id']);
                    if ($result) {
                        $message = 'Membro adicionado à equipe com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao adicionar membro à equipe.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para gerenciar membros.';
                    $messageType = 'danger';
                }
                break;
                
            case 'remove_member':
                if (hasPermission('gerente')) {
                    $result = $teamsController->removeMember($_POST['team_id'], $_POST['user_id']);
                    if ($result) {
                        $message = 'Membro removido da equipe com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao remover membro da equipe.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para gerenciar membros.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Parâmetros de filtro e paginação
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 12;

// Buscar equipes
$result = $teamsController->index($search, $page, $limit);
$teams = $result['teams'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipes - Sistema de Gestão</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
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
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .team-card {
            height: 100%;
        }
        
        .team-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        
        .member-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary-color);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
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
                <h2 class="mb-0">Gerenciamento de Equipes</h2>
                <p class="text-muted mb-0">Organize e gerencie as equipes da empresa</p>
            </div>
            <?php if (hasPermission('gerente')): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#teamModal">
                <i class="fas fa-plus me-2"></i>Nova Equipe
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Mensagens -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Buscar Equipes</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nome ou descrição da equipe">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                        <a href="teams.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de Equipes -->
        <?php if (empty($teams)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma equipe encontrada</h5>
                        <p class="text-muted">Comece criando sua primeira equipe</p>
                        <?php if (hasPermission('gerente')): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#teamModal">
                            <i class="fas fa-plus me-2"></i>Criar Primeira Equipe
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($teams as $team): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card team-card">
                        <div class="card-body text-center">
                            <div class="team-avatar">
                                <?php echo strtoupper(substr($team['nome'], 0, 2)); ?>
                            </div>
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($team['nome']); ?></h5>
                            
                            <?php if ($team['descricao']): ?>
                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars(substr($team['descricao'], 0, 100)); ?>
                                <?php echo strlen($team['descricao']) > 100 ? '...' : ''; ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="row text-center mb-3">
                                <div class="col">
                                    <div class="fw-bold text-primary"><?php echo $team['total_membros']; ?></div>
                                    <small class="text-muted">Membros</small>
                                </div>
                                <div class="col">
                                    <?php 
                                    $stats = $teamsController->getTeamStats($team['id']);
                                    ?>
                                    <div class="fw-bold text-info"><?php echo $stats['total_projetos']; ?></div>
                                    <small class="text-muted">Projetos</small>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-success"><?php echo $stats['percentual_conclusao']; ?>%</div>
                                    <small class="text-muted">Concluído</small>
                                </div>
                            </div>
                            
                            <?php 
                            $members = $teamsController->getTeamMembers($team['id']);
                            if (!empty($members)):
                            ?>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">Membros:</small>
                                <div class="d-flex justify-content-center flex-wrap">
                                    <?php foreach (array_slice($members, 0, 5) as $member): ?>
                                    <div class="member-avatar" title="<?php echo htmlspecialchars($member['nome_completo']); ?>">
                                        <?php echo strtoupper(substr($member['nome_completo'], 0, 2)); ?>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (count($members) > 5): ?>
                                    <div class="member-avatar bg-secondary" title="Mais <?php echo count($members) - 5; ?> membros">
                                        +<?php echo count($members) - 5; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <a href="team_details.php?id=<?php echo $team['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>Ver Detalhes
                                </a>
                                
                                <?php if (hasPermission('gerente')): ?>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" 
                                            onclick="editTeam(<?php echo htmlspecialchars(json_encode($team)); ?>)">
                                        <i class="fas fa-edit me-1"></i>Editar
                                    </button>
                                    <button type="button" class="btn btn-outline-info" 
                                            onclick="manageMembers(<?php echo $team['id']; ?>, '<?php echo htmlspecialchars($team['nome']); ?>')">
                                        <i class="fas fa-users me-1"></i>Membros
                                    </button>
                                    <?php if (hasPermission('administrador')): ?>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteTeam(<?php echo $team['id']; ?>, '<?php echo htmlspecialchars($team['nome']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Paginação">
                <ul class="pagination justify-content-center mt-4">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    
    <!-- Modal de Equipe -->
    <?php if (hasPermission('gerente')): ?>
    <div class="modal fade" id="teamModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="teamModalTitle">Nova Equipe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="teamForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="teamId" name="id">
                        <input type="hidden" id="teamAction" name="action" value="create">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome da Equipe *</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                                    placeholder="Descreva o propósito e responsabilidades da equipe"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Equipe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal de Gerenciar Membros -->
    <div class="modal fade" id="membersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="membersModalTitle">Gerenciar Membros</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="membersContent">
                        <!-- Conteúdo será carregado via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a equipe <strong id="deleteTeamName"></strong>?</p>
                    <p class="text-danger"><small>Esta ação removerá todos os membros da equipe e suas associações com projetos.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deleteTeamId" name="id">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function editTeam(team) {
            document.getElementById('teamModalTitle').textContent = 'Editar Equipe';
            document.getElementById('teamAction').value = 'update';
            document.getElementById('teamId').value = team.id;
            document.getElementById('nome').value = team.nome;
            document.getElementById('descricao').value = team.descricao || '';
            
            new bootstrap.Modal(document.getElementById('teamModal')).show();
        }
        
        function deleteTeam(id, name) {
            document.getElementById('deleteTeamId').value = id;
            document.getElementById('deleteTeamName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        function manageMembers(teamId, teamName) {
            document.getElementById('membersModalTitle').textContent = 'Gerenciar Membros - ' + teamName;
            
            // Carregar membros via AJAX
            fetch(`team_members.php?team_id=${teamId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('membersContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('membersModal')).show();
                })
                .catch(error => {
                    console.error('Erro ao carregar membros:', error);
                    document.getElementById('membersContent').innerHTML = 
                        '<div class="alert alert-danger">Erro ao carregar membros da equipe.</div>';
                    new bootstrap.Modal(document.getElementById('membersModal')).show();
                });
        }
        
        // Limpar formulário ao fechar modal
        document.getElementById('teamModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('teamForm').reset();
            document.getElementById('teamModalTitle').textContent = 'Nova Equipe';
            document.getElementById('teamAction').value = 'create';
            document.getElementById('teamId').value = '';
        });
    </script>
</body>
</html>