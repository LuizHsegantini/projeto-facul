<?php
// users.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/UsersController.php';

// Verificar se o usuário está logado e tem permissão
requireLogin();
if (!hasPermission('administrador')) {
    header('Location: dashboard.php');
    exit();
}

$usersController = new UsersController();
$currentUser = getCurrentUser();

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $result = $usersController->create($_POST);
                if (isset($result['success'])) {
                    $message = 'Usuário criado com sucesso!';
                    $messageType = 'success';
                } else {
                    $message = $result['error'];
                    $messageType = 'danger';
                }
                break;
                
            case 'update':
                $result = $usersController->update($_POST['id'], $_POST);
                if (isset($result['success'])) {
                    $message = 'Usuário atualizado com sucesso!';
                    $messageType = 'success';
                } else {
                    $message = $result['error'];
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                $result = $usersController->delete($_POST['id']);
                if (isset($result['success'])) {
                    $message = 'Usuário excluído com sucesso!';
                    $messageType = 'success';
                } else {
                    $message = $result['error'];
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Parâmetros de filtro e paginação
$search = $_GET['search'] ?? '';
$perfil = $_GET['perfil'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 15;

// Buscar usuários
$result = $usersController->index($search, $perfil, $page, $limit);
$users = $result['users'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Sistema de Gestão</title>
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
            margin-right: 0.75rem;
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
                <a class="nav-link active" href="users.php">
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
        </ul>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Gerenciamento de Usuários</h2>
                <p class="text-muted mb-0">Gerencie todos os usuários do sistema</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                <i class="fas fa-plus me-2"></i>Novo Usuário
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
                    <div class="col-md-6">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nome, email ou CPF">
                    </div>
                    <div class="col-md-3">
                        <label for="perfil" class="form-label">Perfil</label>
                        <select class="form-select" id="perfil" name="perfil">
                            <option value="">Todos os Perfis</option>
                            <option value="administrador" <?php echo $perfil === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                            <option value="gerente" <?php echo $perfil === 'gerente' ? 'selected' : ''; ?>>Gerente</option>
                            <option value="colaborador" <?php echo $perfil === 'colaborador' ? 'selected' : ''; ?>>Colaborador</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                        <a href="users.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de Usuários -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum usuário encontrado</h5>
                        <p class="text-muted">Comece criando seu primeiro usuário</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                            <i class="fas fa-plus me-2"></i>Criar Primeiro Usuário
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuário</th>
                                    <th>Email</th>
                                    <th>Cargo</th>
                                    <th>Perfil</th>
                                    <th>Data Criação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($user['nome_completo'], 0, 2)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($user['nome_completo']); ?></div>
                                                <small class="text-muted">CPF: <?php echo htmlspecialchars($user['cpf']); ?></small><br>
                                                <small class="text-muted">Login: <?php echo htmlspecialchars($user['login']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['cargo'] ?? 'Não definido'); ?></td>
                                    <td>
                                        <?php 
                                        $perfilClasses = [
                                            'administrador' => 'bg-danger',
                                            'gerente' => 'bg-warning',
                                            'colaborador' => 'bg-info'
                                        ];
                                        $perfilLabels = [
                                            'administrador' => 'Administrador',
                                            'gerente' => 'Gerente',
                                            'colaborador' => 'Colaborador'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $perfilClasses[$user['perfil']] ?? 'bg-secondary'; ?>">
                                            <?php echo $perfilLabels[$user['perfil']] ?? 'Desconhecido'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($user['data_criacao'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="user_details.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-outline-info" title="Ver Detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($user['id'] != $currentUser['id']): ?>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nome_completo']); ?>')"
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
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&perfil=<?php echo urlencode($perfil); ?>">
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
    
    <!-- Modal de Usuário -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="userForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="userId" name="id">
                        <input type="hidden" id="userAction" name="action" value="create">
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="nome_completo" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="nome_completo" name="nome_completo" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cpf" class="form-label">CPF *</label>
                                <input type="text" class="form-control" id="cpf" name="cpf" required 
                                       placeholder="000.000.000-00" maxlength="14">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cargo" class="form-label">Cargo</label>
                                <input type="text" class="form-control" id="cargo" name="cargo" 
                                       placeholder="Ex: Desenvolvedor Senior">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="login" class="form-label">Login *</label>
                                <input type="text" class="form-control" id="login" name="login" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="senha" class="form-label">Senha *</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="perfil" class="form-label">Perfil *</label>
                                <select class="form-select" id="user_perfil" name="perfil" required>
                                    <option value="">Selecione um perfil</option>
                                    <option value="colaborador">Colaborador</option>
                                    <option value="gerente">Gerente</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Usuário</button>
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
                    <p>Tem certeza que deseja excluir o usuário <strong id="deleteUserName"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita. O usuário será removido permanentemente do sistema.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deleteUserId" name="id">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(user) {
            document.getElementById('userModalTitle').textContent = 'Editar Usuário';
            document.getElementById('userAction').value = 'update';
            document.getElementById('userId').value = user.id;
            document.getElementById('nome_completo').value = user.nome_completo;
            document.getElementById('cpf').value = user.cpf;
            document.getElementById('email').value = user.email;
            document.getElementById('cargo').value = user.cargo || '';
            document.getElementById('login').value = user.login;
            document.getElementById('user_perfil').value = user.perfil;
            
            // Tornar senha opcional para edição
            document.getElementById('senha').required = false;
            document.getElementById('senha').placeholder = 'Deixe em branco para manter a senha atual';
            
            new bootstrap.Modal(document.getElementById('userModal')).show();
        }
        
        function deleteUser(id, name) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteUserName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Máscara para CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                e.target.value = value;
            }
        });
        
        // Limpar formulário ao fechar modal
        document.getElementById('userModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('userForm').reset();
            document.getElementById('userModalTitle').textContent = 'Novo Usuário';
            document.getElementById('userAction').value = 'create';
            document.getElementById('userId').value = '';
            document.getElementById('senha').required = true;
            document.getElementById('senha').placeholder = '';
        });
    </script>
</body>
</html>