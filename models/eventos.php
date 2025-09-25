<?php
// eventos.php - Página de gerenciamento de eventos
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../controllers/EventosController.php';
require_once '../controllers/CriancasController.php';

// Verificar se o usuário está logado
requireLogin();

$eventosController = new EventosController();
$criancasController = new CriancasController();
$currentUser = getCurrentUser();

// Definir permissões por perfil (igual ao dashboard)
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
        'eventos' => true,
        'criancas' => true,
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
        'eventos' => true,
        'criancas' => true,
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
        'criancas' => true,
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
        try {
            switch ($_POST['action']) {
                case 'create':
                    if (hasUserPermission('eventos')) {
                        $result = $eventosController->create($_POST);
                        if ($result) {
                            $message = 'Evento criado com sucesso!';
                            $messageType = 'success';
                        } else {
                            $message = 'Erro ao criar evento.';
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'Você não tem permissão para criar eventos.';
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (hasUserPermission('eventos')) {
                        $result = $eventosController->update($_POST['id'], $_POST);
                        if ($result) {
                            $message = 'Evento atualizado com sucesso!';
                            $messageType = 'success';
                        } else {
                            $message = 'Erro ao atualizar evento.';
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'Você não tem permissão para editar eventos.';
                        $messageType = 'danger';
                    }
                    break;
                
                case 'remove_crianca':
                    if (hasUserPermission('eventos')) {
                        $result = $eventosController->removeCriancaFromEvento($_POST['evento_id'], $_POST['crianca_id']);
                        if ($result) {
                            $message = 'Criança removida do evento com sucesso!';
                            $messageType = 'success';
                        } else {
                            $message = 'Erro ao remover criança do evento.';
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'Você não tem permissão para remover crianças do evento.';
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete':
                    if ($currentUser['perfil'] === 'administrador') {
                        $result = $eventosController->delete($_POST['id']);
                        if ($result) {
                            $message = 'Evento excluído com sucesso!';
                            $messageType = 'success';
                        } else {
                            $message = 'Erro ao excluir evento.';
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'Apenas administradores podem excluir eventos.';
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'add_crianca':
                    if (hasUserPermission('eventos')) {
                        $result = $eventosController->addCriancaToEvento($_POST['evento_id'], $_POST['crianca_id'], $_POST['observacoes'] ?? '');
                        if ($result) {
                            $message = 'Criança adicionada ao evento com sucesso!';
                            $messageType = 'success';
                        } else {
                            $message = 'Erro ao adicionar criança ao evento.';
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'Você não tem permissão para adicionar crianças ao evento.';
                        $messageType = 'danger';
                    }
                    break;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Parâmetros de filtro e paginação
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 12;

// Buscar eventos
$result = $eventosController->index($search, $status, $page, $limit);
$eventos = $result['eventos'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];

// Buscar coordenadores para formulários
$coordenadores = $eventosController->getCoordenadores();

// Verificar se está visualizando detalhes de um evento específico
$evento_detalhes = null;
$criancas_evento = [];
$criancas_disponiveis = [];
if (isset($_GET['id'])) {
    $evento_detalhes = $eventosController->getById($_GET['id']);
    if ($evento_detalhes) {
        $criancas_evento = $eventosController->getEventoCriancas($_GET['id']);
        $criancas_disponiveis = $eventosController->getCriancasDisponiveis($_GET['id']);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Eventos - MagicKids</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/eventos.css">
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-birthday-cake fa-6x shape"></i>
        <i class="fas fa-child fa-5x shape"></i>
        <i class="fas fa-heart fa-4x shape"></i>
    </div>

    <!-- Sidebar igual ao dashboard -->
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
                <a class="nav-link active" href="eventos.php">
                    <i class="fas fa-calendar-alt"></i>Eventos
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasUserPermission('criancas')): ?>
            <li class="nav-item">
                <a class="nav-link" href="criancas.php">
                    <i class="fas fa-child"></i>Crianças
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasUserPermission('cadastro_crianca')): ?>
            <li class="nav-item">
                <a class="nav-link" href="/Faculdade/cadastro_crianca.php">
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
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <?php if ($evento_detalhes): ?>
        <!-- Detalhes do Evento -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0"><?php echo htmlspecialchars($evento_detalhes['nome']); ?></h2>
                <p class="text-muted mb-0">Detalhes e gerenciamento do evento</p>
            </div>
            <div class="d-flex gap-2">
                <a href="eventos.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
                <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
                <button class="btn btn-primary" onclick="editEventoDetalhes()">
                    <i class="fas fa-edit me-2"></i>Editar
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Informações do Evento -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informações Gerais</h6>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($evento_detalhes['tipo_evento']); ?></p>
                                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($evento_detalhes['data_inicio'])); ?></p>
                                <p><strong>Duração:</strong> <?php echo $evento_detalhes['duracao_horas']; ?> horas</p>
                                <p><strong>Local:</strong> <?php echo htmlspecialchars($evento_detalhes['local_evento']); ?></p>
                                <p><strong>Coordenador:</strong> <?php echo htmlspecialchars($evento_detalhes['coordenador_nome']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Capacidade e Público</h6>
                                <p><strong>Faixa Etária:</strong> <?php echo $evento_detalhes['faixa_etaria_min']; ?> - <?php echo $evento_detalhes['faixa_etaria_max']; ?> anos</p>
                                <p><strong>Capacidade:</strong> <?php echo count($criancas_evento); ?>/<?php echo $evento_detalhes['capacidade_maxima']; ?> crianças</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-<?php 
                                        echo $evento_detalhes['status'] === 'planejado' ? 'secondary' : 
                                            ($evento_detalhes['status'] === 'em_andamento' ? 'primary' : 
                                            ($evento_detalhes['status'] === 'concluido' ? 'success' : 'danger')); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $evento_detalhes['status'])); ?>
                                    </span>
                                </p>
                                <div class="progress mb-2">
                                    <?php 
                                    $ocupacao = $evento_detalhes['capacidade_maxima'] > 0 
                                        ? (count($criancas_evento) / $evento_detalhes['capacidade_maxima']) * 100 
                                        : 0; 
                                    ?>
                                    <div class="progress-bar bg-info" style="width: <?php echo $ocupacao; ?>%"></div>
                                </div>
                                <small class="text-muted"><?php echo number_format($ocupacao, 1); ?>% de ocupação</small>
                            </div>
                        </div>
                        
                        <?php if ($evento_detalhes['descricao']): ?>
                        <div class="mt-3">
                            <h6>Descrição</h6>
                            <p><?php echo nl2br(htmlspecialchars($evento_detalhes['descricao'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Ações Rápidas</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="checkin.php?evento=<?php echo $evento_detalhes['id']; ?>" class="btn btn-success">
                                <i class="fas fa-clipboard-check me-2"></i>Check-in/Check-out
                            </a>
                            <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCriancaModal">
                                <i class="fas fa-user-plus me-2"></i>Adicionar Criança
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Crianças do Evento -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-child me-2 text-primary"></i>
                    Crianças Inscritas (<?php echo count($criancas_evento); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($criancas_evento)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Nenhuma criança inscrita</h6>
                        <p class="text-muted">Adicione crianças a este evento</p>
                        <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCriancaModal">
                            <i class="fas fa-plus me-2"></i>Adicionar Primeira Criança
                        </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($criancas_evento as $crianca): ?>
                        <div class="col-lg-6 mb-3">
                            <div class="crianca-item d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($crianca['nome_completo']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo $crianca['idade']; ?> anos • <?php echo htmlspecialchars($crianca['nome_responsavel']); ?>
                                    </small><br>
                                    <small class="text-muted">
                                        <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($crianca['telefone_principal']); ?>
                                    </small>
                                    
                                    <?php if (!empty($crianca['alergia_alimentos']) || !empty($crianca['alergia_medicamentos'])): ?>
                                    <div class="mt-1">
                                        <?php if (!empty($crianca['alergia_alimentos'])): ?>
                                        <small class="badge bg-danger me-1">
                                            <i class="fas fa-utensils me-1"></i><?php echo htmlspecialchars($crianca['alergia_alimentos']); ?>
                                        </small>
                                        <?php endif; ?>
                                        <?php if (!empty($crianca['alergia_medicamentos'])): ?>
                                        <small class="badge bg-danger">
                                            <i class="fas fa-pills me-1"></i><?php echo htmlspecialchars($crianca['alergia_medicamentos']); ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2">
                                        <span class="badge bg-<?php 
                                            echo $crianca['status_participacao'] === 'Check-in' ? 'success' : 
                                                ($crianca['status_participacao'] === 'Check-out' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo $crianca['status_participacao']; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
                                <button class="btn btn-sm btn-outline-secondary" 
                                        onclick="showCriancaActions(<?php echo $evento_detalhes['id']; ?>, <?php echo $crianca['crianca_id']; ?>, '<?php echo htmlspecialchars($crianca['nome_completo']); ?>')">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Script para inserir dados do evento atual no JavaScript -->
        <script>
            // Dados do evento atual para usar no JavaScript
            window.eventoAtualDetalhes = <?php echo json_encode($evento_detalhes); ?>;
        </script>
        
        <?php else: ?>
        <!-- Lista de Eventos -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Gerenciamento de Eventos</h2>
                <p class="text-muted mb-0">Organize e gerencie todos os eventos infantis</p>
            </div>
            <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventoModal">
                <i class="fas fa-plus me-2"></i>Novo Evento
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Mensagens -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Buscar Eventos</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nome, descrição ou local do evento">
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos os status</option>
                            <option value="planejado" <?php echo $status === 'planejado' ? 'selected' : ''; ?>>Planejado</option>
                            <option value="em_andamento" <?php echo $status === 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                            <option value="concluido" <?php echo $status === 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                            <option value="cancelado" <?php echo $status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                        <a href="eventos.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de Eventos -->
        <?php if (empty($eventos)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-star fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum evento encontrado</h5>
                    <p class="text-muted">Crie seu primeiro evento para começar</p>
                    <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventoModal">
                        <i class="fas fa-plus me-2"></i>Criar Primeiro Evento
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($eventos as $evento): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card evento-card <?php echo $evento['status']; ?> h-100">
                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    <?php echo htmlspecialchars($evento['nome']); ?>
                                </h5>
                                <button class="btn btn-sm btn-outline-secondary" 
                                        onclick="showEventoActions(<?php echo htmlspecialchars(json_encode($evento)); ?>)">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('d/m/Y', strtotime($evento['data_inicio'])); ?>
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo $evento['duracao_horas']; ?>h
                                </small>
                                <?php if ($evento['local_evento']): ?>
                                <br><small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($evento['local_evento']); ?>
                                </small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <span class="badge bg-<?php 
                                    echo $evento['status'] === 'planejado' ? 'secondary' : 
                                        ($evento['status'] === 'em_andamento' ? 'primary' : 
                                        ($evento['status'] === 'concluido' ? 'success' : 'danger')); 
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $evento['status'])); ?>
                                </span>
                                <small class="text-muted ms-2">
                                    <?php echo $evento['faixa_etaria_min']; ?>-<?php echo $evento['faixa_etaria_max']; ?> anos
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">Inscrições</small>
                                    <small class="text-muted"><?php echo $evento['total_inscricoes']; ?>/<?php echo $evento['capacidade_maxima']; ?></small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <?php 
                                    $percentual = $evento['capacidade_maxima'] > 0 
                                        ? ($evento['total_inscricoes'] / $evento['capacidade_maxima']) * 100 
                                        : 0; 
                                    ?>
                                    <div class="progress-bar bg-info" style="width: <?php echo $percentual; ?>%"></div>
                                </div>
                            </div>
                            
                            <?php if ($evento['coordenador_nome']): ?>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-user-tie me-1"></i>
                                    <?php echo htmlspecialchars($evento['coordenador_nome']); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex gap-2">
                                <a href="eventos.php?id=<?php echo $evento['id']; ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                                    <i class="fas fa-eye me-1"></i>Detalhes
                                </a>
                                <a href="checkin.php?evento=<?php echo $evento['id']; ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-clipboard-check me-1"></i>Check-in
                                </a>
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
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
    </main>
    
    <!-- Modal de Criação de Evento COM AJAX -->
<div class="modal fade" id="createEventoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Criar Novo Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createEventoForm" method="POST" onsubmit="return submitEventoForm(this)">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="status" value="planejado">
                    
                    <!-- Mensagem de erro específica -->
                    <div id="createEventoError" class="alert alert-danger d-none"></div>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="nome" class="form-label">Nome do Evento *</label>
                            <input type="text" class="form-control" id="nome" name="nome" required 
                                   onblur="validarNomeEvento(this.value)">
                            <div class="invalid-feedback" id="nomeError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="tipo_evento" class="form-label">Tipo de Evento *</label>
                            <select class="form-select" id="tipo_evento" name="tipo_evento" required>
                                <option value="">Selecione...</option>
                                <option value="Festa de Aniversário">Festa de Aniversário</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Acampamento">Acampamento</option>
                                <option value="Gincana">Gincana</option>
                                <option value="Teatro">Teatro</option>
                                <option value="Esportes">Esportes</option>
                                <option value="Arte e Pintura">Arte e Pintura</option>
                                <option value="Culinária">Culinária</option>
                                <option value="Dança">Dança</option>
                                <option value="Outros">Outros</option>
                            </select>
                            <div class="invalid-feedback" id="tipoEventoError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="coordenador_id" class="form-label">Coordenador *</label>
                            <select class="form-select" id="coordenador_id" name="coordenador_id" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($coordenadores as $coord): ?>
                                <option value="<?php echo $coord['id']; ?>"><?php echo htmlspecialchars($coord['nome_completo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="coordenadorError"></div>
                        </div>
                        
                        <div class="col-12">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                                      placeholder="Descreva as atividades e objetivos do evento"></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="data_inicio" class="form-label">Data e Hora de Início *</label>
                            <input type="datetime-local" class="form-control" id="data_inicio" name="data_inicio" required
                                   onchange="validarDataEvento(this.value)">
                            <div class="invalid-feedback" id="dataInicioError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="duracao_horas" class="form-label">Duração (horas) *</label>
                            <input type="number" class="form-control" id="duracao_horas" name="duracao_horas" 
                                   min="1" max="24" required>
                            <div class="invalid-feedback" id="duracaoError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="faixa_etaria_min" class="form-label">Idade Mínima *</label>
                            <input type="number" class="form-control" id="faixa_etaria_min" name="faixa_etaria_min" 
                                   min="1" max="18" required onchange="validarFaixaEtaria()">
                            <div class="invalid-feedback" id="idadeMinError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="faixa_etaria_max" class="form-label">Idade Máxima *</label>
                            <input type="number" class="form-control" id="faixa_etaria_max" name="faixa_etaria_max" 
                                   min="1" max="18" required onchange="validarFaixaEtaria()">
                            <div class="invalid-feedback" id="idadeMaxError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="capacidade_maxima" class="form-label">Capacidade Máxima *</label>
                            <input type="number" class="form-control" id="capacidade_maxima" name="capacidade_maxima" 
                                   min="5" max="100" required>
                            <div class="invalid-feedback" id="capacidadeError"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="local_evento" class="form-label">Local *</label>
                            <input type="text" class="form-control" id="local_evento" name="local_evento" 
                                   placeholder="Ex: Salão de Festas A" required>
                            <div class="invalid-feedback" id="localError"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="submitButton">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Criar Evento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    
    <!-- Modal de Edição de Evento -->
    <div class="modal fade" id="editEventoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editEventoForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" id="editEventoId" name="id">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="editNome" class="form-label">Nome do Evento</label>
                                <input type="text" class="form-control" id="editNome" name="nome" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editTipoEvento" class="form-label">Tipo de Evento</label>
                                <select class="form-select" id="editTipoEvento" name="tipo_evento" required>
                                    <option value="">Selecione...</option>
                                    <option value="Festa de Aniversário">Festa de Aniversário</option>
                                    <option value="Workshop">Workshop</option>
                                    <option value="Acampamento">Acampamento</option>
                                    <option value="Gincana">Gincana</option>
                                    <option value="Teatro">Teatro</option>
                                    <option value="Esportes">Esportes</option>
                                    <option value="Arte e Pintura">Arte e Pintura</option>
                                    <option value="Culinária">Culinária</option>
                                    <option value="Dança">Dança</option>
                                    <option value="Outros">Outros</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editCoordenador" class="form-label">Coordenador</label>
                                <select class="form-select" id="editCoordenador" name="coordenador_id" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($coordenadores as $coord): ?>
                                    <option value="<?php echo $coord['id']; ?>"><?php echo htmlspecialchars($coord['nome_completo']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label for="editDescricao" class="form-label">Descrição</label>
                                <textarea class="form-control" id="editDescricao" name="descricao" rows="3"></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editDataInicio" class="form-label">Data e Hora de Início</label>
                                <input type="datetime-local" class="form-control" id="editDataInicio" name="data_inicio" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editDuracao" class="form-label">Duração (horas)</label>
                                <input type="number" class="form-control" id="editDuracao" name="duracao_horas" min="1" max="24" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editIdadeMin" class="form-label">Idade Mínima</label>
                                <input type="number" class="form-control" id="editIdadeMin" name="faixa_etaria_min" min="1" max="18" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editIdadeMax" class="form-label">Idade Máxima</label>
                                <input type="number" class="form-control" id="editIdadeMax" name="faixa_etaria_max" min="1" max="18" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="editCapacidade" class="form-label">Capacidade Máxima</label>
                                <input type="number" class="form-control" id="editCapacidade" name="capacidade_maxima" min="5" max="100" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="editLocal" class="form-label">Local</label>
                                <input type="text" class="form-control" id="editLocal" name="local_evento" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="editStatus" class="form-label">Status</label>
                                <select class="form-select" id="editStatus" name="status" required>
                                    <option value="planejado">Planejado</option>
                                    <option value="em_andamento">Em Andamento</option>
                                    <option value="concluido">Concluído</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
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
    
    <!-- Modal de Adicionar Criança -->
    <?php if ($evento_detalhes): ?>
    <div class="modal fade" id="addCriancaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Criança ao Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_crianca">
                        <input type="hidden" name="evento_id" value="<?php echo $evento_detalhes['id']; ?>">
                        
                        <?php if (empty($criancas_disponiveis)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">Nenhuma criança disponível</h6>
                                <p class="text-muted">Todas as crianças compatíveis já estão inscritas ou não há crianças na faixa etária do evento.</p>
                                <a href="/Faculdade/cadastro_crianca.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Cadastrar Nova Criança
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label for="crianca_id" class="form-label">Selecionar Criança</label>
                                <select class="form-select" name="crianca_id" required>
                                    <option value="">Escolha uma criança...</option>
                                    <?php foreach ($criancas_disponiveis as $crianca): ?>
                                    <option value="<?php echo $crianca['id']; ?>">
                                        <?php echo htmlspecialchars($crianca['nome_completo']); ?> 
                                        (<?php echo $crianca['idade']; ?> anos)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="observacoes" class="form-label">Observações (opcional)</label>
                                <textarea class="form-control" name="observacoes" rows="2" 
                                          placeholder="Observações específicas para este evento"></textarea>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($criancas_disponiveis)): ?>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar ao Evento</button>
                    </div>
                    <?php endif; ?>
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
                    <p>Tem certeza que deseja excluir o evento <strong id="deleteEventoName"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita e removerá todas as inscrições associadas.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="deleteEventoId" name="id">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Ações do Evento -->
    <div class="modal fade" id="eventActionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-star me-2 text-primary"></i>
                        <span id="eventActionTitle">Ações do Evento</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-outline-primary btn-lg" onclick="viewEventDetails()">
                            <i class="fas fa-eye me-2"></i>Ver Detalhes Completos
                        </button>
                        
                        <button type="button" class="btn btn-outline-success btn-lg" onclick="goToCheckin()">
                            <i class="fas fa-clipboard-check me-2"></i>Fazer Check-in/Check-out
                        </button>
                        
                        <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
                        <hr>
                        <button type="button" class="btn btn-outline-warning btn-lg" onclick="editCurrentEvent()">
                            <i class="fas fa-edit me-2"></i>Editar Evento
                        </button>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('administrador')): ?>
                        <button type="button" class="btn btn-outline-danger btn-lg" onclick="deleteCurrentEvent()">
                            <i class="fas fa-trash me-2"></i>Excluir Evento
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Ações da Criança -->
    <div class="modal fade" id="criancaActionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-child me-2 text-success"></i>
                        <span id="criancaActionTitle">Ações da Criança</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Selecione uma ação para <strong id="criancaActionName"></strong>:
                    </div>
                    
                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-outline-primary btn-lg" onclick="viewCriancaDetails()">
                            <i class="fas fa-eye me-2"></i>Ver Perfil Completo
                        </button>
                        
                        <button type="button" class="btn btn-outline-info btn-lg" onclick="viewCriancaEvents()">
                            <i class="fas fa-calendar me-2"></i>Ver Outros Eventos
                        </button>
                        
                        <hr>
                        
                        <button type="button" class="btn btn-outline-danger btn-lg" onclick="confirmRemoveCrianca()">
                            <i class="fas fa-user-times me-2"></i>Remover do Evento
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Desabilitar dropdowns do Bootstrap para evitar conflitos
        document.addEventListener('DOMContentLoaded', function() {
            // Remover todos os data-bs-toggle="dropdown" 
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(element) {
                element.removeAttribute('data-bs-toggle');
                element.classList.remove('dropdown-toggle');
            });
            
            // Remover classes dropdown
            document.querySelectorAll('.dropdown').forEach(function(element) {
                element.classList.remove('dropdown');
            });
        });
        
        // Variáveis globais para armazenar dados temporários
        let currentEventData = null;
        let currentCriancaData = null;
        
        // Função específica para editar evento na página de detalhes
        function editEventoDetalhes() {
            if (window.eventoAtualDetalhes) {
                editEvento(window.eventoAtualDetalhes);
            } else {
                console.error('Dados do evento não encontrados');
                alert('Erro: Não foi possível carregar os dados do evento para edição.');
            }
        }
        
        // Função para mostrar ações do evento
        function showEventoActions(evento) {
            currentEventData = evento;
            document.getElementById('eventActionTitle').textContent = evento.nome;
            new bootstrap.Modal(document.getElementById('eventActionsModal')).show();
        }
        
        // Função para mostrar ações da criança
        function showCriancaActions(eventoId, criancaId, criancaNome) {
            currentCriancaData = {
                eventoId: eventoId,
                criancaId: criancaId,
                nome: criancaNome
            };
            document.getElementById('criancaActionName').textContent = criancaNome;
            new bootstrap.Modal(document.getElementById('criancaActionsModal')).show();
        }
        
        // Ações do evento
        function viewEventDetails() {
            if (currentEventData) {
                window.location.href = `eventos.php?id=${currentEventData.id}`;
            }
        }
        
        function goToCheckin() {
            if (currentEventData) {
                window.location.href = `checkin.php?evento=${currentEventData.id}`;
            }
        }
        
        function editCurrentEvent() {
            if (currentEventData) {
                bootstrap.Modal.getInstance(document.getElementById('eventActionsModal')).hide();
                editEvento(currentEventData);
            }
        }
        
        function deleteCurrentEvent() {
            if (currentEventData) {
                bootstrap.Modal.getInstance(document.getElementById('eventActionsModal')).hide();
                deleteEvento(currentEventData.id, currentEventData.nome);
            }
        }
        
        // Ações da criança
        function viewCriancaDetails() {
            if (currentCriancaData) {
                window.location.href = `criancas.php?id=${currentCriancaData.criancaId}`;
            }
        }
        
        function viewCriancaEvents() {
            if (currentCriancaData) {
                window.location.href = `criancas.php?id=${currentCriancaData.criancaId}&tab=eventos`;
            }
        }
        
        function confirmRemoveCrianca() {
            if (currentCriancaData) {
                bootstrap.Modal.getInstance(document.getElementById('criancaActionsModal')).hide();
                removeCrianca(currentCriancaData.eventoId, currentCriancaData.criancaId, currentCriancaData.nome);
            }
        }
        
        function editEvento(evento) {
            // Debug: verificar se o evento tem todos os campos necessários
            console.log('Editando evento:', evento);
            
            // Verificar se o evento tem as propriedades necessárias
            if (!evento || typeof evento !== 'object') {
                console.error('Objeto evento inválido:', evento);
                alert('Erro: Dados do evento não encontrados.');
                return;
            }
            
            // Preencher os campos do formulário
            document.getElementById('editEventoId').value = evento.id || '';
            document.getElementById('editNome').value = evento.nome || '';
            document.getElementById('editTipoEvento').value = evento.tipo_evento || '';
            document.getElementById('editCoordenador').value = evento.coordenador_id || '';
            document.getElementById('editDescricao').value = evento.descricao || '';
            
            // Formato da data para datetime-local
            if (evento.data_inicio) {
                const dataInicio = new Date(evento.data_inicio);
                // Ajustar para timezone local
                const offsetMs = dataInicio.getTimezoneOffset() * 60 * 1000;
                const localDate = new Date(dataInicio.getTime() - offsetMs);
                document.getElementById('editDataInicio').value = localDate.toISOString().slice(0, 16);
            }
            
            document.getElementById('editDuracao').value = evento.duracao_horas || '';
            document.getElementById('editIdadeMin').value = evento.faixa_etaria_min || '';
            document.getElementById('editIdadeMax').value = evento.faixa_etaria_max || '';
            document.getElementById('editCapacidade').value = evento.capacidade_maxima || '';
            document.getElementById('editLocal').value = evento.local_evento || '';
            document.getElementById('editStatus').value = evento.status || 'planejado';
            
            // Abrir o modal
            new bootstrap.Modal(document.getElementById('editEventoModal')).show();
        }
        
        function deleteEvento(id, nome) {
            document.getElementById('deleteEventoId').value = id;
            document.getElementById('deleteEventoName').textContent = nome;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        
        // Calcular automaticamente data_fim_evento baseada na duração
        document.addEventListener('DOMContentLoaded', function() {
            const dataInicioInputs = document.querySelectorAll('input[name="data_inicio"]');
            const duracaoInputs = document.querySelectorAll('input[name="duracao_horas"]');
            
            function calcularDataFim(dataInicioInput, duracaoInput) {
                const dataFimInput = dataInicioInput.closest('.modal-body').querySelector('input[name="data_fim_evento"]');
                
                if (dataInicioInput.value && duracaoInput.value && dataFimInput) {
                    const dataInicio = new Date(dataInicioInput.value);
                    const duracao = parseInt(duracaoInput.value);
                    const dataFim = new Date(dataInicio.getTime() + (duracao * 60 * 60 * 1000));
                    
                    dataFimInput.value = dataFim.toISOString().slice(0, 16);
                }
            }
            
            dataInicioInputs.forEach((input, index) => {
                input.addEventListener('change', () => calcularDataFim(input, duracaoInputs[index]));
            });
            
            duracaoInputs.forEach((input, index) => {
                input.addEventListener('change', () => calcularDataFim(dataInicioInputs[index], input));
            });
        });
        
        // Validar faixa etária
        function validarFaixaEtaria() {
            const idadeMin = parseInt(document.querySelector('input[name="faixa_etaria_min"]').value);
            const idadeMax = parseInt(document.querySelector('input[name="faixa_etaria_max"]').value);
            
            if (idadeMin && idadeMax && idadeMin > idadeMax) {
                alert('A idade mínima não pode ser maior que a idade máxima.');
                return false;
            }
            return true;
        }
        
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validarFaixaEtaria()) {
                    e.preventDefault();
                }
            });
        });
    </script>

    <script>
        function removeCrianca(eventoId, criancaId, nomeCrianca) {
    if (confirm(`Tem certeza que deseja remover ${nomeCrianca} deste evento?`)) {
        // Criar formulário temporário
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ''; // Enviar para a mesma página
        form.style.display = 'none';
        
        // Adicionar campos necessários
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'remove_crianca';
        
        const eventoInput = document.createElement('input');
        eventoInput.type = 'hidden';
        eventoInput.name = 'evento_id';
        eventoInput.value = eventoId;
        
        const criancaInput = document.createElement('input');
        criancaInput.type = 'hidden';
        criancaInput.name = 'crianca_id';
        criancaInput.value = criancaId;
        
        // Adicionar inputs ao formulário
        form.appendChild(actionInput);
        form.appendChild(eventoInput);
        form.appendChild(criancaInput);
        
        // Adicionar formulário ao DOM e submeter
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
        
        
    <script>
// Variável para armazenar dados do formulário em caso de erro
let formDataBackup = null;

// Função para submeter o formulário via AJAX
function submitEventoForm(form) {
    event.preventDefault();
    
    // Mostrar loading
    const submitBtn = document.getElementById('submitButton');
    const spinner = submitBtn.querySelector('.spinner-border');
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    // Esconder mensagem de erro anterior
    document.getElementById('createEventoError').classList.add('d-none');
    
    // Coletar dados do formulário
    const formData = new FormData(form);
    
    // Fazer backup dos dados
    formDataBackup = {};
    for (let [key, value] of formData.entries()) {
        formDataBackup[key] = value;
    }
    
    // Enviar via AJAX
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        // Verificar se a resposta contém sucesso ou erro
        if (html.includes('Evento criado com sucesso')) {
            // Sucesso - recarregar a página
            window.location.reload();
        } else {
            // Erro - extrair mensagem de erro
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const alert = tempDiv.querySelector('.alert-danger');
            
            if (alert) {
                showFormError(alert.textContent);
            } else {
                showFormError('Erro ao criar evento. Tente novamente.');
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showFormError('Erro de conexão. Tente novamente.');
    })
    .finally(() => {
        // Restaurar botão
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
    
    return false;
}

// Função para mostrar erro no formulário
function showFormError(message) {
    const errorDiv = document.getElementById('createEventoError');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
    
    // Restaurar dados do formulário do backup
    if (formDataBackup) {
        for (const [key, value] of Object.entries(formDataBackup)) {
            const element = document.querySelector(`[name="${key}"]`);
            if (element) {
                element.value = value;
            }
        }
    }
    
    // Rolagem para o topo do modal
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Validação em tempo real do nome do evento
function validarNomeEvento(nome) {
    if (nome.length < 3) {
        showFieldError('nomeError', 'O nome deve ter pelo menos 3 caracteres');
        return false;
    }
    clearFieldError('nomeError');
    return true;
}

// Validação em tempo real da data
function validarDataEvento(data) {
    const dataEvento = new Date(data);
    const dataAtual = new Date();
    
    if (dataEvento < dataAtual) {
        showFieldError('dataInicioError', 'Não é permitido criar eventos com datas passadas');
        return false;
    }
    clearFieldError('dataInicioError');
    return true;
}

// Validação em tempo real da faixa etária
function validarFaixaEtaria() {
    const idadeMin = parseInt(document.getElementById('faixa_etaria_min').value);
    const idadeMax = parseInt(document.getElementById('faixa_etaria_max').value);
    
    if (idadeMin && idadeMax && idadeMin > idadeMax) {
        showFieldError('idadeMaxError', 'A idade máxima deve ser maior ou igual à idade mínima');
        return false;
    }
    clearFieldError('idadeMinError');
    clearFieldError('idadeMaxError');
    return true;
}

// Funções auxiliares para mostrar/limpar erros de campo
function showFieldError(fieldId, message) {
    const errorElement = document.getElementById(fieldId);
    const inputElement = document.getElementById(fieldId.replace('Error', ''));
    
    if (errorElement && inputElement) {
        errorElement.textContent = message;
        inputElement.classList.add('is-invalid');
    }
}

function clearFieldError(fieldId) {
    const errorElement = document.getElementById(fieldId);
    const inputElement = document.getElementById(fieldId.replace('Error', ''));
    
    if (errorElement && inputElement) {
        errorElement.textContent = '';
        inputElement.classList.remove('is-invalid');
    }
}

// Limpar formulário quando o modal for fechado
document.getElementById('createEventoModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('createEventoForm').reset();
    document.getElementById('createEventoError').classList.add('d-none');
    formDataBackup = null;
    
    // Limpar todos os erros de campo
    const errorElements = document.querySelectorAll('.invalid-feedback');
    errorElements.forEach(element => element.textContent = '');
    
    const invalidInputs = document.querySelectorAll('.is-invalid');
    invalidInputs.forEach(input => input.classList.remove('is-invalid'));
});

// Validação antes do envio
document.getElementById('createEventoForm').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Validar todos os campos obrigatórios
    const requiredFields = this.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field.id + 'Error', 'Este campo é obrigatório');
            isValid = false;
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        showFormError('Por favor, preencha todos os campos obrigatórios.');
    }
});
</script>

    <!-- Campo escondido para data_fim_evento nos formulários -->
    <script>
        document.querySelectorAll('form').forEach(form => {
            if (form.querySelector('input[name="data_inicio"]') && !form.querySelector('input[name="data_fim_evento"]')) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'data_fim_evento';
                form.appendChild(hiddenInput);
            }
        });
    </script>
</body>
</html>