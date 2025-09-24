<?php
// criancas.php - Página de gerenciamento de crianças
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/auth.php';
require_once '../controllers/CriancasController.php';

// Verificar se o usuário está logado
requireLogin();

$criancasController = new CriancasController();
$currentUser = getCurrentUser();

// Definir permissões por perfil (igual ao dashboard_eventos.php)
$permissions = [
    'administrador' => [
        'dashboard' => true,
        'eventos' => true,
        'criancas' => true,
        'cadastro_crianca' => true,
        'checkin' => true,
        'atividades' => true,
        'equipes' => true,
        'funcionarios' => true,
        'relatorios' => true,
        'logs' => true,
        'quick_actions' => ['cadastro_crianca', 'criar_evento', 'checkin', 'relatorios']
    ],
    'coordenador' => [
        'dashboard' => true,
        'eventos' => true,
        'criancas' => true,
        'cadastro_crianca' => true,
        'checkin' => true,
        'atividades' => true,
        'equipes' => true,
        'funcionarios' => false,
        'relatorios' => true,
        'logs' => false,
        'quick_actions' => ['cadastro_crianca', 'criar_evento', 'checkin', 'relatorios']
    ],
    'animador' => [
        'dashboard' => true,
        'eventos' => true, // visualizar apenas
        'criancas' => true, // visualizar apenas
        'cadastro_crianca' => true,
        'checkin' => true,
        'atividades' => true,
        'equipes' => false,
        'funcionarios' => false,
        'relatorios' => false,
        'logs' => false,
        'quick_actions' => ['cadastro_crianca', 'checkin']
    ],
    'monitor' => [
        'dashboard' => true,
        'eventos' => true, // visualizar apenas
        'criancas' => true, // visualizar apenas
        'cadastro_crianca' => true,
        'checkin' => true,
        'atividades' => true,
        'equipes' => false,
        'funcionarios' => false,
        'relatorios' => false,
        'logs' => false,
        'quick_actions' => ['cadastro_crianca', 'checkin']
    ],
    'auxiliar' => [
        'dashboard' => true,
        'eventos' => false,
        'criancas' => true, // visualizar apenas
        'cadastro_crianca' => false,
        'checkin' => true,
        'atividades' => false,
        'equipes' => false,
        'funcionarios' => false,
        'relatorios' => false,
        'logs' => false,
        'quick_actions' => ['checkin']
    ]
];

$userPermissions = $permissions[$currentUser['perfil']] ?? $permissions['auxiliar'];

function hasUserPermission($permission) {
    global $userPermissions;
    return isset($userPermissions[$permission]) && $userPermissions[$permission];
}

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                if (hasUserPermission('criancas')) {
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
                if ($currentUser['perfil'] === 'administrador') {
                    $result = $criancasController->forceDelete($_POST['id']);
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
                if (hasUserPermission('criancas')) {
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
$status = $_GET['status'] ?? 'ativo'; // Padrão mostrar apenas ativas
$page = (int)($_GET['page'] ?? 1);
$limit = 15;

// Buscar crianças
$result = $criancasController->index($search, $idade_min, $idade_max, $sexo, $page, $limit, $status);
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
    <link rel="stylesheet" href="../assets/css/criancas.css">
</head>
<body>
    <!-- Sidebar idêntica ao dashboard_eventos.php -->
    <nav class="sidebar">
        <div>
            <div class="company-info">
                <i class="fas fa-magic"></i>
                <div class="fw-bold">MagicKids Eventos</div>
                <p class="mb-0">Sistema de gestão</p>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard_eventos.php">
                        <i class="fas fa-tachometer-alt"></i>Dashboard
                    </a>
                </li>
                
                <?php if (hasUserPermission('eventos')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="eventos.php">
                        <i class="fas fa-calendar-alt"></i>Eventos
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link active" href="criancas.php">
                        <i class="fas fa-child"></i>Crianças
                    </a>
                </li>
                
                <?php if (hasUserPermission('cadastro_crianca')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="cadastro_crianca.php">
                        <i class="fas fa-user-plus"></i>Cadastrar Criança
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasUserPermission('checkin')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="checkin.php">
                        <i class="fas fa-clipboard-check"></i>Check-in/Check-out
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasUserPermission('atividades')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="atividades.php">
                        <i class="fas fa-gamepad"></i>Atividades
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasUserPermission('equipes')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="equipes.php">
                        <i class="fas fa-users"></i>Equipes
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasUserPermission('funcionarios')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="funcionarios.php">
                        <i class="fas fa-user-tie"></i>Funcionários
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasUserPermission('relatorios')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="relatorios.php">
                        <i class="fas fa-chart-bar"></i>Relatórios
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasUserPermission('logs')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="logs.php">
                        <i class="fas fa-history"></i>Logs do Sistema
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">
                    Gerenciamento de Crianças
                    <?php if ($status === 'inativo'): ?>
                    <span class="badge bg-warning ms-2">Mostrando Inativos</span>
                    <?php elseif ($status === 'todos'): ?>
                    <span class="badge bg-info ms-2">Mostrando Todos</span>
                    <?php endif; ?>
                </h2>
                <p class="text-muted mb-0">
                    Visualize e gerencie todas as crianças cadastradas
                    <?php if ($status === 'inativo'): ?>
                    <br><small class="text-warning"><i class="fas fa-info-circle"></i> Você está vendo crianças inativas. Para ver as ativas, mude o filtro de Status.</small>
                    <?php endif; ?>
                </p>
            </div>
            <a href="cadastro_crianca.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nova Criança
            </a>
        </div>
        
        <!-- Mensagens -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
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
                    <div class="col-md-1">
                        <label for="sexo" class="form-label">Sexo</label>
                        <select class="form-select" id="sexo" name="sexo">
                            <option value="">Todos</option>
                            <option value="Masculino" <?php echo $sexo === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                            <option value="Feminino" <?php echo $sexo === 'Feminino' ? 'selected' : ''; ?>>Feminino</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="todos" <?php echo ($_GET['status'] ?? 'ativo') === 'todos' ? 'selected' : ''; ?>>Todos</option>
                            <option value="ativo" <?php echo ($_GET['status'] ?? 'ativo') === 'ativo' ? 'selected' : ''; ?>>Ativos</option>
                            <option value="inativo" <?php echo ($_GET['status'] ?? 'ativo') === 'inativo' ? 'selected' : ''; ?>>Inativos</option>
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
                        <?php if ($status === 'inativo'): ?>
                            <h5 class="text-muted">Nenhuma criança inativa encontrada</h5>
                            <p class="text-muted">Todas as crianças estão com status ativo.</p>
                            <a href="?status=ativo" class="btn btn-primary">
                                <i class="fas fa-eye me-2"></i>Ver Crianças Ativas
                            </a>
                        <?php elseif ($status === 'todos'): ?>
                            <h5 class="text-muted">Nenhuma criança encontrada com os filtros aplicados</h5>
                            <p class="text-muted">Tente ajustar os filtros ou limpe-os para ver todos os cadastros.</p>
                            <a href="criancas.php" class="btn btn-outline-primary me-2">
                                <i class="fas fa-times me-2"></i>Limpar Filtros
                            </a>
                            <a href="cadastro_crianca.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Cadastrar Criança
                            </a>
                        <?php else: ?>
                            <h5 class="text-muted">Nenhuma criança encontrada</h5>
                            <p class="text-muted">Faça o primeiro cadastro de criança</p>
                            <a href="cadastro_crianca.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Cadastrar Primeira Criança
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($criancas as $crianca): ?>
                        <?php 
                        $isAniversario = date('m-d') === date('m-d', strtotime($crianca['data_nascimento']));
                        $temAlergia = !empty($crianca['alergia_alimentos']) || !empty($crianca['alergia_medicamentos']);
                        $statusAtivo = (bool)$crianca['ativo'];
                        
                        // Preparar dados JSON de forma segura
                        $criancaData = [
                            'id' => $crianca['id'],
                            'nome_completo' => $crianca['nome_completo'],
                            'data_nascimento' => $crianca['data_nascimento'],
                            'idade' => $crianca['idade'],
                            'sexo' => $crianca['sexo'],
                            'alergia_alimentos' => $crianca['alergia_alimentos'] ?? '',
                            'alergia_medicamentos' => $crianca['alergia_medicamentos'] ?? '',
                            'restricoes_alimentares' => $crianca['restricoes_alimentares'] ?? '',
                            'observacoes_saude' => $crianca['observacoes_saude'] ?? '',
                            'nome_responsavel' => $crianca['nome_responsavel'],
                            'grau_parentesco' => $crianca['grau_parentesco'] ?? '',
                            'telefone_principal' => $crianca['telefone_principal'],
                            'telefone_alternativo' => $crianca['telefone_alternativo'] ?? '',
                            'endereco_completo' => $crianca['endereco_completo'] ?? '',
                            'documento_rg_cpf' => $crianca['documento_rg_cpf'] ?? '',
                            'email_responsavel' => $crianca['email_responsavel'] ?? '',
                            'nome_emergencia' => $crianca['nome_emergencia'],
                            'telefone_emergencia' => $crianca['telefone_emergencia'] ?? '',
                            'grau_parentesco_emergencia' => $crianca['grau_parentesco_emergencia'] ?? '',
                            'autorizacao_retirada' => $crianca['autorizacao_retirada'] ?? '',
                            'data_cadastro' => $crianca['data_cadastro'] ?? '',
                            'data_atualizacao' => $crianca['data_atualizacao'] ?? '',
                            'ativo' => $statusAtivo
                        ];
                        
                        $criancaJson = htmlspecialchars(json_encode($criancaData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
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
                                                    <button type="button" class="btn btn-outline-primary" title="Ver detalhes" 
                                                            data-bs-toggle="modal" data-bs-target="#detailsModal" 
                                                            data-crianca="<?php echo $criancaJson; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
                                                    <button type="button" class="btn btn-outline-secondary" title="Editar cadastro" 
                                                            data-bs-toggle="modal" data-bs-target="#editModal" 
                                                            data-crianca="<?php echo $criancaJson; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning" 
                                                            title="<?php echo $statusAtivo ? 'Desativar' : 'Ativar'; ?> criança" 
                                                            data-bs-toggle="modal" data-bs-target="#statusModal" 
                                                            data-id="<?php echo (int)$crianca['id']; ?>" 
                                                            data-nome="<?php echo $nomeSeguro; ?>" 
                                                            data-ativo="<?php echo $statusAtivo ? '1' : '0'; ?>">
                                                        <i class="fas fa-<?php echo $statusAtivo ? 'ban' : 'check'; ?>"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if (hasPermission('administrador')): ?>
                                                    <button type="button" class="btn btn-outline-danger" title="Excluir cadastro" 
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                                            data-id="<?php echo (int)$crianca['id']; ?>" 
                                                            data-nome="<?php echo $nomeSeguro; ?>">
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
                                                <span class="badge bg-<?php echo $statusAtivo ? 'success' : 'secondary'; ?>">
                                                    <?php echo $statusAtivo ? 'Ativo' : 'Inativo'; ?>
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
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&idade_min=<?php echo urlencode($idade_min); ?>&idade_max=<?php echo urlencode($idade_max); ?>&sexo=<?php echo urlencode($sexo); ?>&status=<?php echo urlencode($status); ?>">
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
                            <div class="col-md-6">
                                <label for="editNomeCompleto" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="editNomeCompleto" name="nome_completo" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editDataNascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" id="editDataNascimento" name="data_nascimento" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editSexo" class="form-label">Sexo</label>
                                <select class="form-select" id="editSexo" name="sexo" required>
                                    <option value="">Selecione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Feminino">Feminino</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
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
                            
                            <div class="col-12">
                                <label for="editAlergiaAlimentos" class="form-label">Alergia a Alimentos</label>
                                <input type="text" class="form-control" id="editAlergiaAlimentos" name="alergia_alimentos">
                            </div>
                            
                            <div class="col-12">
                                <label for="editAlergiaMedicamentos" class="form-label">Alergia a Medicamentos</label>
                                <input type="text" class="form-control" id="editAlergiaMedicamentos" name="alergia_medicamentos">
                            </div>
                            
                            <div class="col-12">
                                <label for="editObservacoes" class="form-label">Observações de Saúde</label>
                                <textarea class="form-control" id="editObservacoes" name="observacoes_saude" rows="2"></textarea>
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
                    <h5 class="modal-title">Alterar Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Deseja <span id="statusActionLabel"></span> o cadastro de <strong id="statusCriancaNome"></strong>?</p>
                    <p class="text-muted small mb-0">Esta operação pode ser revertida a qualquer momento.</p>
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
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atenção!</strong> Esta ação irá excluir permanentemente o cadastro.
                    </div>
                    <p>Tem certeza que deseja excluir o cadastro de <strong id="deleteCriancaName"></strong>?</p>
                    <p class="text-danger"><small><strong>Esta ação não pode ser desfeita</strong> e removerá todos os dados da criança do sistema, incluindo histórico de eventos.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deleteCriancaId" name="id">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Excluir Permanentemente
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            function parseCrianca(button) {
                var payload = button.getAttribute('data-crianca');
                if (!payload) {
                    console.error('Dados da criança não encontrados');
                    return null;
                }
                try {
                    return JSON.parse(payload);
                } catch (error) {
                    console.error('Erro ao converter dados da criança:', error);
                    return null;
                }
            }

            function formatDate(value) {
                if (!value || value === '0000-00-00') {
                    return '-';
                }
                try {
                    var date = new Date(value + 'T00:00:00');
                    if (isNaN(date.getTime())) {
                        return value;
                    }
                    return date.toLocaleDateString('pt-BR');
                } catch (e) {
                    return value;
                }
            }

            function formatDateTime(value) {
                if (!value) {
                    return '-';
                }
                try {
                    var date = new Date(value);
                    if (isNaN(date.getTime())) {
                        return value;
                    }
                    return date.toLocaleString('pt-BR');
                } catch (e) {
                    return value;
                }
            }

            function escapeHtml(unsafe) {
                if (unsafe == null || unsafe === undefined) {
                    return '';
                }
                return String(unsafe)
                     .replace(/&/g, "&amp;")
                     .replace(/</g, "&lt;")
                     .replace(/>/g, "&gt;")
                     .replace(/"/g, "&quot;")
                     .replace(/'/g, "&#039;");
            }

            // Modal de detalhes
            var detailsModal = document.getElementById('detailsModal');
            if (detailsModal) {
                detailsModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    if (!button) return;
                    
                    var crianca = parseCrianca(button);
                    if (!crianca) return;

                    var content = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Dados da Criança</h6>
                                <p><strong>Nome:</strong> ${escapeHtml(crianca.nome_completo || '-')}</p>
                                <p><strong>Data de nascimento:</strong> ${formatDate(crianca.data_nascimento)}</p>
                                <p><strong>Idade:</strong> ${crianca.idade || '-'} anos</p>
                                <p><strong>Sexo:</strong> ${escapeHtml(crianca.sexo || '-')}</p>
                                ${crianca.alergia_alimentos ? `<p><strong>Alergia a alimentos:</strong> <span class="text-danger">${escapeHtml(crianca.alergia_alimentos)}</span></p>` : ''}
                                ${crianca.alergia_medicamentos ? `<p><strong>Alergia a medicamentos:</strong> <span class="text-danger">${escapeHtml(crianca.alergia_medicamentos)}</span></p>` : ''}
                                ${crianca.restricoes_alimentares ? `<p><strong>Restrições alimentares:</strong> ${escapeHtml(crianca.restricoes_alimentares)}</p>` : ''}
                                ${crianca.observacoes_saude ? `<p><strong>Observações de saúde:</strong> ${escapeHtml(crianca.observacoes_saude)}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <h6>Dados do Responsável</h6>
                                <p><strong>Nome:</strong> ${escapeHtml(crianca.nome_responsavel || '-')}</p>
                                <p><strong>Grau de parentesco:</strong> ${escapeHtml(crianca.grau_parentesco || '-')}</p>
                                <p><strong>Telefone principal:</strong> ${escapeHtml(crianca.telefone_principal || '-')}</p>
                                ${crianca.telefone_alternativo ? `<p><strong>Telefone alternativo:</strong> ${escapeHtml(crianca.telefone_alternativo)}</p>` : ''}
                                <p><strong>Endereço:</strong> ${escapeHtml(crianca.endereco_completo || '-')}</p>
                                <p><strong>Documento:</strong> ${escapeHtml(crianca.documento_rg_cpf || '-')}</p>
                                ${crianca.email_responsavel ? `<p><strong>E-mail:</strong> ${escapeHtml(crianca.email_responsavel)}</p>` : ''}
                                
                                <h6 class="mt-3">Contato de Emergência</h6>
                                <p><strong>Nome:</strong> ${escapeHtml(crianca.nome_emergencia || '-')}</p>
                                <p><strong>Telefone:</strong> ${escapeHtml(crianca.telefone_emergencia || '-')}</p>
                                <p><strong>Parentesco:</strong> ${escapeHtml(crianca.grau_parentesco_emergencia || '-')}</p>
                                <p><strong>Autorização para retirada:</strong> ${escapeHtml(crianca.autorizacao_retirada || '-')}</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                Cadastrado em: ${formatDateTime(crianca.data_cadastro)}<br>
                                Última atualização: ${formatDateTime(crianca.data_atualizacao)}
                            </small>
                        </div>
                    `;
                    
                    detailsModal.querySelector('#detailsContent').innerHTML = content;
                });
            }

            // Modal de edição
            var editModal = document.getElementById('editModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    if (!button) return;
                    
                    var crianca = parseCrianca(button);
                    if (!crianca) return;

                    // Preencher campos do formulário
                    var form = editModal.querySelector('#editForm');
                    if (form) {
                        var fields = {
                            editId: crianca.id || '',
                            editNomeCompleto: crianca.nome_completo || '',
                            editDataNascimento: crianca.data_nascimento || '',
                            editSexo: crianca.sexo || '',
                            editResponsavel: crianca.nome_responsavel || '',
                            editTelefone: crianca.telefone_principal || '',
                            editEmergencia: crianca.nome_emergencia || '',
                            editAlergiaAlimentos: crianca.alergia_alimentos || '',
                            editAlergiaMedicamentos: crianca.alergia_medicamentos || '',
                            editObservacoes: crianca.observacoes_saude || ''
                        };

                        Object.keys(fields).forEach(function(fieldId) {
                            var field = form.querySelector('#' + fieldId);
                            if (field) {
                                field.value = fields[fieldId];
                            }
                        });
                    }
                });
            }

            // Modal de exclusão
            var deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    if (!button) return;

                    var idField = deleteModal.querySelector('#deleteCriancaId');
                    var nameField = deleteModal.querySelector('#deleteCriancaName');
                    
                    if (idField) idField.value = button.getAttribute('data-id') || '';
                    if (nameField) nameField.textContent = button.getAttribute('data-nome') || '';
                });
            }

            // Modal de status
            var statusModal = document.getElementById('statusModal');
            if (statusModal) {
                statusModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    if (!button) return;

                    var ativo = button.getAttribute('data-ativo') === '1';
                    var idField = statusModal.querySelector('#statusCriancaId');
                    var nameField = statusModal.querySelector('#statusCriancaNome');
                    var actionLabel = statusModal.querySelector('#statusActionLabel');
                    var confirmBtn = statusModal.querySelector('#statusConfirmButton');
                    
                    if (idField) idField.value = button.getAttribute('data-id') || '';
                    if (nameField) nameField.textContent = button.getAttribute('data-nome') || '';
                    if (actionLabel) actionLabel.textContent = ativo ? 'desativar' : 'ativar';
                    
                    if (confirmBtn) {
                        confirmBtn.textContent = ativo ? 'Desativar' : 'Ativar';
                        confirmBtn.className = ativo ? 'btn btn-warning' : 'btn btn-success';
                    }
                });
            }

            // Formatação de telefone
            var editTelefone = document.getElementById('editTelefone');
            if (editTelefone) {
                editTelefone.addEventListener('input', function() {
                    formatPhone(this);
                });
            }

            // Validação de data de nascimento
            var editDataNascimento = document.getElementById('editDataNascimento');
            if (editDataNascimento) {
                editDataNascimento.addEventListener('change', function() {
                    var birthDate = new Date(this.value);
                    var today = new Date();
                    var age = today.getFullYear() - birthDate.getFullYear();
                    var monthDiff = today.getMonth() - birthDate.getMonth();
                    
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    
                    if (age > 18) {
                        alert('Este sistema é destinado a crianças até 18 anos.');
                        this.value = '';
                    } else if (age < 0) {
                        alert('A data de nascimento não pode ser no futuro.');
                        this.value = '';
                    }
                });
            }
        });

        // Função para formatação de telefone
        function formatPhone(input) {
            var value = input.value.replace(/\D/g, '');
            
            if (value.length >= 11) {
                // Celular: (XX) XXXXX-XXXX
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 10) {
                // Fixo: (XX) XXXX-XXXX
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 6) {
                value = value.replace(/(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d+)/, '($1) $2');
            }
            
            input.value = value;
        }

        // Auto-dismiss alerts após 5 segundos
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>

</body>
</html>