<?php
// projects.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/ProjectsController.php';

// Verificar se o usuário está logado
requireLogin();

$projectsController = new ProjectsController();
$currentUser = getCurrentUser();

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                if (hasPermission('gerente')) {
                    $result = $projectsController->create($_POST);
                    if ($result) {
                        $message = 'Projeto criado com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao criar projeto.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para criar projetos.';
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                if (hasPermission('gerente')) {
                    $result = $projectsController->update($_POST['id'], $_POST);
                    if ($result) {
                        $message = 'Projeto atualizado com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao atualizar projeto.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para editar projetos.';
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                if (hasPermission('administrador')) {
                    $result = $projectsController->delete($_POST['id']);
                    if ($result) {
                        $message = 'Projeto excluído com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao excluir projeto.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para excluir projetos.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Parâmetros de filtro e paginação
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 10;

// Buscar projetos
$result = $projectsController->index($search, $status, $page, $limit);
$projects = $result['projects'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];

// Buscar gerentes para o formulário
$managers = $projectsController->getManagers();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos - Sistema de Gestão</title>
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
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
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
                <h2 class="mb-0">Gerenciamento de Projetos</h2>
                <p class="text-muted mb-0">Gerencie todos os projetos da empresa</p>
            </div>
            <?php if (hasPermission('gerente')): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectModal">
                <i class="fas fa-plus me-2"></i>Novo Projeto
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
                    <div class="col-md-4">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nome ou descrição do projeto">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos os Status</option>
                            <option value="planejado" <?php echo $status === 'planejado' ? 'selected' : ''; ?>>Planejado</option>
                            <option value="em_andamento" <?php echo $status === 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                            <option value="concluido" <?php echo $status === 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                            <option value="cancelado" <?php echo $status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                        <a href="projects.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de Projetos -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($projects)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum projeto encontrado</h5>
                        <p class="text-muted">Comece criando seu primeiro projeto</p>
                        <?php if (hasPermission('gerente')): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectModal">
                            <i class="fas fa-plus me-2"></i>Criar Primeiro Projeto
                        </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Projeto</th>
                                    <th>Gerente</th>
                                    <th>Status</th>
                                    <th>Início</th>
                                    <th>Prazo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($project['nome']); ?></strong>
                                            <?php if ($project['descricao']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($project['descricao'], 0, 80)); ?>...</small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($project['gerente_nome'] ?? 'Não atribuído'); ?></td>
                                    <td>
                                        <?php 
                                        $statusClasses = [
                                            'planejado' => 'bg-secondary',
                                            'em_andamento' => 'bg-primary',
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
                                        <span class="badge <?php echo $statusClasses[$project['status']] ?? 'bg-secondary'; ?>">
                                            <?php echo $statusLabels[$project['status']] ?? 'Desconhecido'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $project['data_inicio'] ? date('d/m/Y', strtotime($project['data_inicio'])) : '-'; ?></td>
                                    <td>
                                        <?php if ($project['data_termino_prevista']): ?>
                                            <?php 
                                            $prazo = date('d/m/Y', strtotime($project['data_termino_prevista']));
                                            $isOverdue = strtotime($project['data_termino_prevista']) < time() && 
                                                        !in_array($project['status'], ['concluido', 'cancelado']);
                                            ?>
                                            <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ''; ?>">
                                                <?php echo $prazo; ?>
                                                <?php if ($isOverdue): ?>
                                                    <i class="fas fa-exclamation-triangle ms-1"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Não definido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="project_details.php?id=<?php echo $project['id']; ?>" 
                                               class="btn btn-outline-info" title="Ver Detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (hasPermission('gerente')): ?>
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="editProject(<?php echo htmlspecialchars(json_encode($project)); ?>)"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if (hasPermission('administrador')): ?>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteProject(<?php echo $project['id']; ?>, '<?php echo htmlspecialchars($project['nome']); ?>')"
                                                    title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Paginação">
                        <ul class="pagination justify-content-center mt-4">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Modal de Projeto -->
    <?php if (hasPermission('gerente')): ?>
    <div class="modal fade" id="projectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectModalTitle">Novo Projeto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="projectForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="projectId" name="id">
                        <input type="hidden" id="projectAction" name="action" value="create">
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="nome" class="form-label">Nome do Projeto *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="data_inicio" class="form-label">Data de Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="data_termino_prevista" class="form-label">Prazo Previsto</label>
                                <input type="date" class="form-control" id="data_termino_prevista" name="data_termino_prevista">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="project_status" name="status">
                                    <option value="planejado">Planejado</option>
                                    <option value="em_andamento">Em Andamento</option>
                                    <option value="concluido">Concluído</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="gerente_id" class="form-label">Gerente</label>
                                <select class="form-select" id="gerente_id" name="gerente_id">
                                    <option value="">Selecione um gerente</option>
                                    <?php foreach ($managers as $manager): ?>
                                    <option value="<?php echo $manager['id']; ?>">
                                        <?php echo htmlspecialchars($manager['nome_completo']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Projeto</button>
                    </div>
                </form>
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
                    <p>Tem certeza que deseja excluir o projeto <strong id="deleteProjectName"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita e excluirá todas as tarefas associadas.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deleteProjectId" name="id">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProject(project) {
            document.getElementById('projectModalTitle').textContent = 'Editar Projeto';
            document.getElementById('projectAction').value = 'update';
            document.getElementById('projectId').value = project.id;
            document.getElementById('nome').value = project.nome;
            document.getElementById('descricao').value = project.descricao || '';
            document.getElementById('data_inicio').value = project.data_inicio || '';
            document.getElementById('data_termino_prevista').value = project.data_termino_prevista || '';
            document.getElementById('project_status').value = project.status;
            document.getElementById('gerente_id').value = project.gerente_id || '';
            
            new bootstrap.Modal(document.getElementById('projectModal')).show();
        }
        
        function deleteProject(id, name) {
            document.getElementById('deleteProjectId').value = id;
            document.getElementById('deleteProjectName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Limpar formulário ao fechar modal
        document.getElementById('projectModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('projectForm').reset();
            document.getElementById('projectModalTitle').textContent = 'Novo Projeto';
            document.getElementById('projectAction').value = 'create';
            document.getElementById('projectId').value = '';
        });
    </script>
</body>
</html>