<?php
// criancas.php - Página de gerenciamento de crianças
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/CriancasController.php';

// Verificar se o usuário está logado
requireLogin();

$criancasController = new CriancasController();
$currentUser = getCurrentUser();

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                if (hasPermission('coordenador') || hasPermission('administrador')) {
                    $result = $criancasController->update($_POST['id'], $_POST);
                    if ($result) {
                        $message = 'Dados da criança atualizados com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao atualizar dados da criança.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para editar dados das crianças.';
                    $messageType = 'danger';
                }
                break;
                
            case 'delete':
                if (hasPermission('administrador')) {
                    $result = $criancasController->delete($_POST['id']);
                    if ($result) {
                        $message = 'Criança removida com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao remover criança.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para remover crianças.';
                    $messageType = 'danger';
                }
                break;
                
            case 'toggle_status':
                if (hasPermission('coordenador') || hasPermission('administrador')) {
                    $result = $criancasController->toggleStatus($_POST['id']);
                    if ($result) {
                        $message = 'Status da criança alterado com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao alterar status da criança.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para alterar status das crianças.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Parâmetros de filtro e paginação
$search = $_GET['search'] ?? '';
$idade_min = $_GET['idade_min'] ?? '';
$idade_max = $_GET['idade_max'] ?? '';
$sexo = $_GET['sexo'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 15;

// Buscar crianças
$result = $criancasController->index($search, $idade_min, $idade_max, $sexo, $page, $limit);
$criancas = $result['criancas'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Crianças - MagicKids</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b9d;
            --secondary-color: #ffc93c;
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
            box-shadow: 0 2px 10px rgba(255, 107, 157, 0.1);
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
        
        .crianca-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 0.75rem;
        }
        
        .crianca-card {
            transition: transform 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .crianca-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 107, 157, 0.2);
        }
        
        .alergia-badge {
            background: #ffe4e1;
            color: #d63384;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            margin: 0.1rem;
            display: inline-block;
        }
        
        .aniversario-badge {
            background: linear-gradient(45deg, #ff6b9d, #ffc93c);
            color: white;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
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
                <a class="nav-link active" href="criancas.php">
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
            <?php if (hasPermission('administrador')): ?>
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
            <?php endif; ?>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Gerenciamento de Crianças</h2>
                <p class="text-muted mb-0">Visualize e gerencie todas as crianças cadastradas</p>
            </div>
            <a href="cadastro_crianca.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nova Criança
            </a>
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
                               placeholder="Nome da criança ou responsável">
                    </div>
                    <div class="col-md-2">
                        <label for="idade_min" class="form-label">Idade Mín.</label>
                        <input type="number" class="form-control" id="idade_min" name="idade_min" 
                               value="<?php echo htmlspecialchars($idade_min); ?>" 
                               min="1" max="18">
                    </div>
                    <div class="col-md-2">
                        <label for="idade_max" class="form-label">Idade Máx.</label>
                        <input type="number" class="form-control" id="idade_max" name="idade_max" 
                               value="<?php echo htmlspecialchars($idade_max); ?>" 
                               min="1" max="18">
                    </div>
                    <div class="col-md-2">
                        <label for="sexo" class="form-label">Sexo</label>
                        <select class="form-select" id="sexo" name="sexo">
                            <option value="">Todos</option>
                            <option value="Masculino" <?php echo $sexo === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                            <option value="Feminino" <?php echo $sexo === 'Feminino' ? 'selected' : ''; ?>>Feminino</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                        <a href="criancas.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de Crianças -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($criancas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-child fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma criança encontrada</h5>
                        <p class="text-muted">Faça o primeiro cadastro de criança</p>
                        <a href="cadastro_crianca.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Cadastrar Primeira Criança
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($criancas as $crianca): ?>
                        <?php 
                        $isAniversario = date('m-d') === date('m-d', strtotime($crianca['data_nascimento']));
                        $temAlergia = !empty($crianca['alergia_alimentos']) || !empty($crianca['alergia_medicamentos']);

                        $criancaJson = htmlspecialchars(json_encode($crianca, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');

                        $statusAtivo = !empty($crianca['ativo']);

                        $nomeSeguro = htmlspecialchars($crianca['nome_completo'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card crianca-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="crianca-avatar">
                                            <?php echo strtoupper(substr($crianca['nome_completo'], 0, 2)); ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="card-title mb-1">
                                                        <?php echo htmlspecialchars($crianca['nome_completo']); ?>
                                                        <?php if ($isAniversario): ?>
                                                        <i class="fas fa-birthday-cake text-warning ms-1" title="Aniversário hoje!"></i>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <p class="text-muted small mb-2">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo $crianca['idade']; ?> anos • <?php echo $crianca['sexo']; ?>
                                                    </p>
                                                </div>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary" title="Ver detalhes" data-bs-toggle="modal" data-bs-target="#detailsModal" data-crianca="<?php echo $criancaJson; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
                                                    <button type="button" class="btn btn-outline-secondary" title="Editar cadastro" data-bs-toggle="modal" data-bs-target="#editModal" data-crianca="<?php echo $criancaJson; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning" title="<?php echo $statusAtivo ? 'Desativar' : 'Ativar'; ?> crianca" data-bs-toggle="modal" data-bs-target="#statusModal" data-id="<?php echo (int) $crianca['id']; ?>" data-nome="<?php echo $nomeSeguro; ?>" data-ativo="<?php echo $statusAtivo ? '1' : '0'; ?>">
                                                        <i class="fas fa-<?php echo $statusAtivo ? 'ban' : 'check'; ?>"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if (hasPermission('administrador')): ?>
                                                    <button type="button" class="btn btn-outline-danger" title="Excluir cadastro" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo (int) $crianca['id']; ?>" data-nome="<?php echo $nomeSeguro; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?php echo htmlspecialchars($crianca['nome_responsavel']); ?>
                                                </small><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone me-1"></i>
                                                    <?php echo htmlspecialchars($crianca['telefone_principal']); ?>
                                                </small>
                                            </div>
                                            
                                            <?php if ($temAlergia): ?>
                                            <div class="mb-2">
                                                <small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Possui alergias
                                                </small><br>
                                                <?php if (!empty($crianca['alergia_alimentos'])): ?>
                                                <span class="alergia-badge">
                                                    <i class="fas fa-utensils me-1"></i>
                                                    <?php echo htmlspecialchars($crianca['alergia_alimentos']); ?>
                                                </span>
                                                <?php endif; ?>
                                                <?php if (!empty($crianca['alergia_medicamentos'])): ?>
                                                <span class="alergia-badge">
                                                    <i class="fas fa-pills me-1"></i>
                                                    <?php echo htmlspecialchars($crianca['alergia_medicamentos']); ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-<?php echo $crianca['ativo'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $crianca['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                                </span>
                                                
                                                <?php if ($isAniversario): ?>
                                                <span class="badge aniversario-badge">
                                                    <i class="fas fa-birthday-cake me-1"></i>
                                                    Aniversário!
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
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
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&idade_min=<?php echo urlencode($idade_min); ?>&idade_max=<?php echo urlencode($idade_max); ?>&sexo=<?php echo urlencode($sexo); ?>">
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
    
    <!-- Modal de Detalhes -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes da Criança</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Conteúdo será preenchido via JavaScript -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Edição -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Criança</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" id="editId" name="id">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="editResponsavel" class="form-label">Nome do Responsável</label>
                                <input type="text" class="form-control" id="editResponsavel" name="nome_responsavel" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editTelefone" class="form-label">Telefone Principal</label>
                                <input type="tel" class="form-control" id="editTelefone" name="telefone_principal" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editEmergencia" class="form-label">Contato de Emergência</label>
                                <input type="text" class="form-control" id="editEmergencia" name="nome_emergencia" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal de Status -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST">
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" id="statusCriancaId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title">Alterar status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Deseja <span id="statusActionLabel"></span> o cadastro de <strong id="statusCriancaNome"></strong>?</p>
                    <p class="text-muted small mb-0">Esta operacao pode ser revertida a qualquer momento.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="statusConfirmButton">Confirmar</button>
                </div>
            </form>
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
                    <p>Tem certeza que deseja excluir o cadastro de <strong id="deleteCriancaName"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita e removerá todos os dados da criança do sistema.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deleteCriancaId" name="id">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function parseCrianca(button) {
                var payload = button.getAttribute('data-crianca');
                if (!payload) {
                    return null;
                }
                try {
                    return JSON.parse(payload);
                } catch (error) {
                    console.error('Erro ao converter dados da crianca', error);
                    return null;
                }
            }

            function formatDate(value) {
                if (!value) {
                    return '-';
                }
                var date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return value;
                }
                return date.toLocaleDateString('pt-BR');
            }

            function formatDateTime(value) {
                if (!value) {
                    return '-';
                }
                var date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return value;
                }
                return date.toLocaleString('pt-BR');
            }

            var detailsModal = document.getElementById('detailsModal');
            if (detailsModal) {
                detailsModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) {
                        return;
                    }
                    var crianca = parseCrianca(button);
                    if (!crianca) {
                        return;
                    }
                    var content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Dados da Crianca</h6>
                        <p><strong>Nome:</strong> ${crianca.nome_completo || '-'}</p>
                        <p><strong>Data de nascimento:</strong> ${formatDate(crianca.data_nascimento)}</p>
                        <p><strong>Idade:</strong> ${crianca.idade || '-'} anos</p>
                        <p><strong>Sexo:</strong> ${crianca.sexo || '-'}</p>
                        ${crianca.alergia_alimentos ? `<p><strong>Alergia a alimentos:</strong> <span class="text-danger">${crianca.alergia_alimentos}</span></p>` : ``}
                        ${crianca.alergia_medicamentos ? `<p><strong>Alergia a medicamentos:</strong> <span class="text-danger">${crianca.alergia_medicamentos}</span></p>` : ``}
                        ${crianca.restricoes_alimentares ? `<p><strong>Restricoes alimentares:</strong> ${crianca.restricoes_alimentares}</p>` : ``}
                        ${crianca.observacoes_saude ? `<p><strong>Observacoes de saude:</strong> ${crianca.observacoes_saude}</p>` : ``}
                    </div>
                    <div class="col-md-6">
                        <h6>Dados do responsavel</h6>
                        <p><strong>Nome:</strong> ${crianca.nome_responsavel || '-'}</p>
                        <p><strong>Grau de parentesco:</strong> ${crianca.grau_parentesco || '-'}</p>
                        <p><strong>Telefone principal:</strong> ${crianca.telefone_principal || '-'}</p>
                        ${crianca.telefone_alternativo ? `<p><strong>Telefone alternativo:</strong> ${crianca.telefone_alternativo}</p>` : ``}
                        <p><strong>Endereco:</strong> ${crianca.endereco_completo || '-'}</p>
                        <p><strong>Documento:</strong> ${crianca.documento_rg_cpf || '-'}</p>
                        ${crianca.email_responsavel ? `<p><strong>E-mail:</strong> ${crianca.email_responsavel}</p>` : ``}
                        <h6 class="mt-3">Contato de emergencia</h6>
                        <p><strong>Nome:</strong> ${crianca.nome_emergencia || '-'}</p>
                        <p><strong>Telefone:</strong> ${crianca.telefone_emergencia || '-'}</p>
                        <p><strong>Parentesco:</strong> ${crianca.grau_parentesco_emergencia || '-'}</p>
                        <p><strong>Autorizacao para retirada:</strong> ${crianca.autorizacao_retirada || '-'}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        Cadastrado em: ${formatDateTime(crianca.data_cadastro)}<br>
                        Ultima atualizacao: ${formatDateTime(crianca.data_atualizacao)}
                    </small>
                </div>
            `;
                    detailsModal.querySelector('#detailsContent').innerHTML = content;
                });
            }

            var editModal = document.getElementById('editModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) {
                        return;
                    }
                    var crianca = parseCrianca(button);
                    if (!crianca) {
                        return;
                    }
                    var fieldId = editModal.querySelector('#editId');
                    if (fieldId) { fieldId.value = crianca.id || ''; }
                    var fieldResponsavel = editModal.querySelector('#editResponsavel');
                    if (fieldResponsavel) { fieldResponsavel.value = crianca.nome_responsavel || ''; }
                    var fieldTelefone = editModal.querySelector('#editTelefone');
                    if (fieldTelefone) { fieldTelefone.value = crianca.telefone_principal || ''; }
                    var fieldEmergencia = editModal.querySelector('#editEmergencia');
                    if (fieldEmergencia) { fieldEmergencia.value = crianca.nome_emergencia || ''; }
                });
            }

            var deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) {
                        return;
                    }
                    var idField = deleteModal.querySelector('#deleteCriancaId');
                    if (idField) { idField.value = button.getAttribute('data-id') || ''; }
                    var nameField = deleteModal.querySelector('#deleteCriancaName');
                    if (nameField) { nameField.textContent = button.getAttribute('data-nome') || ''; }
                });
            }

            var statusModal = document.getElementById('statusModal');
            if (statusModal) {
                statusModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) {
                        return;
                    }
                    var ativo = button.getAttribute('data-ativo') === '1';
                    var idField = statusModal.querySelector('#statusCriancaId');
                    if (idField) { idField.value = button.getAttribute('data-id') || ''; }
                    var nameField = statusModal.querySelector('#statusCriancaNome');
                    if (nameField) { nameField.textContent = button.getAttribute('data-nome') || ''; }
                    var actionLabel = statusModal.querySelector('#statusActionLabel');
                    if (actionLabel) { actionLabel.textContent = ativo ? 'desativar' : 'ativar'; }
                    var confirmBtn = statusModal.querySelector('#statusConfirmButton');
                    if (confirmBtn) {
                        confirmBtn.textContent = ativo ? 'Desativar' : 'Ativar';
                        confirmBtn.classList.toggle('btn-danger', ativo);
                        confirmBtn.classList.toggle('btn-success', !ativo);
                    }
                });
            }

            var editTelefone = document.getElementById('editTelefone');
            if (editTelefone) {
                editTelefone.addEventListener('input', function () {
                    formatPhone(this);
                });
            }

            var editDataNascimento = document.getElementById('editDataNascimento');
            if (editDataNascimento) {
                editDataNascimento.addEventListener('change', function () {
                    var birthDate = new Date(this.value);
                    var today = new Date();
                    var age = today.getFullYear() - birthDate.getFullYear();
                    if (age > 12) {
                        alert('Este sistema e destinado a criancas ate 12 anos.');
                    } else if (age < 1) {
                        alert('A crianca deve ter pelo menos 1 ano.');
                    }
                });
            }
        });

        function formatPhone(input) {
            var value = input.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d+)/, '($1) $2');
            }
            input.value = value;
        }
    </script>

</body>
</html>