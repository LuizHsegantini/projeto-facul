<?php
// tasks.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/TasksController.php';

// Verificar se o usuário está logado
requireLogin();

$tasksController = new TasksController();
$currentUser = getCurrentUser();

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $result = $tasksController->create($_POST);
                if ($result) {
                    $message = 'Tarefa criada com sucesso!';
                    $messageType = 'success';
                } else {
                    $message = 'Erro ao criar tarefa.';
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                $result = $tasksController->update($_POST['id'], $_POST);
                if ($result) {
                    $message = 'Tarefa atualizada com sucesso!';
                    $messageType = 'success';
                } else {
                    $message = 'Erro ao atualizar tarefa.';
                    $messageType = 'danger';
                }
                break;
                
            case 'update_status':
                $result = $tasksController->updateStatus($_POST['id'], $_POST['status']);
                if ($result) {
                    $message = 'Status da tarefa atualizado com sucesso!';
                    $messageType = 'success';
                } else {
                    $message = 'Erro ao atualizar status da tarefa.';
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                if (hasPermission('gerente')) {
                    $result = $tasksController->delete($_POST['id']);
                    if ($result) {
                        $message = 'Tarefa excluída com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao excluir tarefa.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para excluir tarefas.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Parâmetros de filtro e paginação
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$project_id = $_GET['project'] ?? '';
$user_id = $_GET['user'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 15;

// Buscar tarefas
$result = $tasksController->index($search, $status, $project_id, $user_id, $page, $limit);
$tasks = $result['tasks'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];

// Buscar projetos e usuários para os filtros
$projects = $tasksController->getProjects();
$users = $tasksController->getUsers();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarefas - Sistema de Gestão</title>
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
        
        .task-priority {
            width: 4px;
            height: 100%;
            position: absolute;
            left: 0;
            top: 0;
        }
        
        .task-card {
            position: relative;
            border-left: 4px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .task-card.overdue {
            border-left-color: #dc3545;
        }
        
        .task-card.due-soon {
            border-left-color: #ffc107;
        }
        
        .task-card.completed {
            border-left-color: #28a745;
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
                <a class="nav-link active" href="tasks.php">
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
                <h2 class="mb-0">Gerenciamento de Tarefas</h2>
                <p class="text-muted mb-0">Organize e acompanhe todas as tarefas</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
                <i class="fas fa-plus me-2"></i>Nova Tarefa
            </button>
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
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Título ou descrição da tarefa">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <option value="pendente" <?php echo $status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="em_execucao" <?php echo $status === 'em_execucao' ? 'selected' : ''; ?>>Em Execução</option>
                            <option value="concluida" <?php echo $status === 'concluida' ? 'selected' : ''; ?>>Concluída</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="project" class="form-label">Projeto</label>
                        <select class="form-select" id="project" name="project">
                            <option value="">Todos</option>
                            <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" <?php echo $project_id == $project['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['nome']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="user" class="form-label">Responsável</label>
                        <select class="form-select" id="user" name="user">
                            <option value="">Todos</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo $user_id == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['nome_completo']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                        <a href="tasks.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de Tarefas -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($tasks)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma tarefa encontrada</h5>
                        <p class="text-muted">Comece criando sua primeira tarefa</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
                            <i class="fas fa-plus me-2"></i>Criar Primeira Tarefa
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tarefa</th>
                                    <th>Projeto</th>
                                    <th>Responsável</th>
                                    <th>Status</th>
                                    <th>Prazo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                <?php
                                $isOverdue = $task['data_fim_prevista'] && 
                                           strtotime($task['data_fim_prevista']) < time() && 
                                           $task['status'] !== 'concluida';
                                $isDueSoon = $task['data_fim_prevista'] && 
                                           strtotime($task['data_fim_prevista']) <= strtotime('+3 days') && 
                                           strtotime($task['data_fim_prevista']) >= time() && 
                                           $task['status'] !== 'concluida';
                                ?>
                                <tr class="<?php echo $isOverdue ? 'table-danger' : ($isDueSoon ? 'table-warning' : ''); ?>">
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($task['titulo']); ?></strong>
                                            <?php if ($task['descricao']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($task['descricao'], 0, 100)); ?>...</small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($task['projeto_nome']): ?>
                                            <a href="project_details.php?id=<?php echo $task['projeto_id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($task['projeto_nome']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Projeto não encontrado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($task['responsavel_nome'] ?? 'Não atribuído'); ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <?php 
                                            $statusClasses = [
                                                'pendente' => 'bg-secondary',
                                                'em_execucao' => 'bg-primary',
                                                'concluida' => 'bg-success'
                                            ];
                                            $statusLabels = [
                                                'pendente' => 'Pendente',
                                                'em_execucao' => 'Em Execução',
                                                'concluida' => 'Concluída'
                                            ];
                                            ?>
                                            <button class="btn btn-sm badge <?php echo $statusClasses[$task['status']] ?? 'bg-secondary'; ?> dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <?php echo $statusLabels[$task['status']] ?? 'Desconhecido'; ?>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'pendente')">Pendente</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'em_execucao')">Em Execução</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'concluida')">Concluída</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($task['data_fim_prevista']): ?>
                                            <?php 
                                            $prazo = date('d/m/Y', strtotime($task['data_fim_prevista']));
                                            ?>
                                            <div class="d-flex align-items-center">
                                                <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ($isDueSoon ? 'text-warning fw-bold' : ''); ?>">
                                                    <?php echo $prazo; ?>
                                                </span>
                                                <?php if ($isOverdue): ?>
                                                    <i class="fas fa-exclamation-triangle text-danger ms-1" title="Atrasada"></i>
                                                <?php elseif ($isDueSoon): ?>
                                                    <i class="fas fa-clock text-warning ms-1" title="Vence em breve"></i>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Não definido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="editTask(<?php echo htmlspecialchars(json_encode($task)); ?>)"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (hasPermission('gerente')): ?>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteTask(<?php echo $task['id']; ?>, '<?php echo htmlspecialchars($task['titulo']); ?>')"
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
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&project=<?php echo urlencode($project_id); ?>&user=<?php echo urlencode($user_id); ?>">
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
    
    <!-- Modal de Tarefa -->
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalTitle">Nova Tarefa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="taskForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="taskId" name="id">
                        <input type="hidden" id="taskAction" name="action" value="create">
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="titulo" class="form-label">Título da Tarefa *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="descricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="projeto_id" class="form-label">Projeto *</label>
                                <select class="form-select" id="projeto_id" name="projeto_id" required>
                                    <option value="">Selecione um projeto</option>
                                    <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>">
                                        <?php echo htmlspecialchars($project['nome']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="responsavel_id" class="form-label">Responsável</label>
                                <select class="form-select" id="responsavel_id" name="responsavel_id">
                                    <option value="">Selecione um responsável</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['nome_completo']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="task_status" class="form-label">Status</label>
                                <select class="form-select" id="task_status" name="status">
                                    <option value="pendente">Pendente</option>
                                    <option value="em_execucao">Em Execução</option>
                                    <option value="concluida">Concluída</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="data_inicio" class="form-label">Data de Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="data_fim_prevista" class="form-label">Prazo Previsto</label>
                                <input type="date" class="form-control" id="data_fim_prevista" name="data_fim_prevista">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Tarefa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a tarefa <strong id="deleteTaskName"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deleteTaskId" name="id">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function editTask(task) {
            document.getElementById('taskModalTitle').textContent = 'Editar Tarefa';
            document.getElementById('taskAction').value = 'update';
            document.getElementById('taskId').value = task.id;
            document.getElementById('titulo').value = task.titulo;
            document.getElementById('descricao').value = task.descricao || '';
            document.getElementById('projeto_id').value = task.projeto_id || '';
            document.getElementById('responsavel_id').value = task.responsavel_id || '';
            document.getElementById('task_status').value = task.status;
            document.getElementById('data_inicio').value = task.data_inicio || '';
            document.getElementById('data_fim_prevista').value = task.data_fim_prevista || '';
            
            new bootstrap.Modal(document.getElementById('taskModal')).show();
        }
        
        function deleteTask(id, name) {
            document.getElementById('deleteTaskId').value = id;
            document.getElementById('deleteTaskName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        function updateTaskStatus(taskId, status) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" value="${taskId}">
                <input type="hidden" name="status" value="${status}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        // Limpar formulário ao fechar modal
        document.getElementById('taskModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('taskForm').reset();
            document.getElementById('taskModalTitle').textContent = 'Nova Tarefa';
            document.getElementById('taskAction').value = 'create';
            document.getElementById('taskId').value = '';
        });
    </script>
</body>
</html>