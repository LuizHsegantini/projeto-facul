<?php
// checkin.php - Sistema de Check-in/Check-out COMPLETO E FUNCIONAL
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar timezone para Brasil
date_default_timezone_set('America/Sao_Paulo');

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/auth.php';
require_once 'controllers/CriancasController.php';
require_once 'controllers/EventosController.php';

// Verificar se o usuário está logado
requireLogin();

$criancasController = new CriancasController();
$eventosController = new EventosController();
$currentUser = getCurrentUser();

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'checkin':
                if (hasPermission('animador') || hasPermission('coordenador') || hasPermission('administrador')) {
                    $result = $eventosController->checkinCrianca($_POST['evento_id'], $_POST['crianca_id']);
                    if ($result) {
                        $message = 'Check-in realizado com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao realizar check-in. Verifique se o evento está ativo e se a criança está inscrita.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para realizar check-in.';
                    $messageType = 'danger';
                }
                break;
                
            case 'checkout':
                if (hasPermission('animador') || hasPermission('coordenador') || hasPermission('administrador')) {
                    $result = $eventosController->checkoutCrianca($_POST['evento_id'], $_POST['crianca_id']);
                    if ($result) {
                        $message = 'Check-out realizado com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao realizar check-out. Verifique se a criança fez check-in.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Você não tem permissão para realizar check-out.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Parâmetros de filtro
$evento_id = $_GET['evento_id'] ?? '';
$search = $_GET['search'] ?? '';

// Buscar eventos disponíveis para check-in
$eventosDisponiveis = $eventosController->getEventosParaCheckin();

// Buscar crianças para check-in se evento selecionado
$criancasCheckin = [];
$eventoSelecionado = null;
if ($evento_id) {
    $eventoSelecionado = $eventosController->getById($evento_id);
    $criancasCheckin = $criancasController->getCriancasCheckin($evento_id);
}

// Definir permissões por perfil
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
        'logs' => true
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
        'logs' => false
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
        'logs' => false
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
        'logs' => false
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
        'logs' => false
    ]
];

$userPermissions = $permissions[$currentUser['perfil']] ?? $permissions['auxiliar'];

function hasUserPermission($permission) {
    global $userPermissions;
    return isset($userPermissions[$permission]) && $userPermissions[$permission];
}

// Função para verificar se o evento permite check-in/check-out
function podeRealizarCheckin($evento) {
    if (!$evento) return false;
    
    $hoje = date('Y-m-d');
    $dataEvento = date('Y-m-d', strtotime($evento['data_inicio']));
    
    // Permite check-in no dia do evento ou após a data do evento (para eventos em andamento)
    return $dataEvento <= $hoje && $evento['status'] !== 'cancelado';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in/Check-out - MagicKids</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/checkin.css">
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-birthday-cake fa-6x shape"></i>
        <i class="fas fa-child fa-5x shape"></i>
        <i class="fas fa-heart fa-4x shape"></i>
    </div>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="company-info">
            <i class="fas fa-magic"></i>
            <div class="company-name">MagicKids Eventos</div>
        </div>
        
        <nav>
            <a class="nav-link" href="dashboard_eventos.php">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            
            <?php if (hasUserPermission('eventos')): ?>
            <a class="nav-link" href="eventos.php">
                <i class="fas fa-calendar-alt"></i>Eventos
            </a>
            <?php endif; ?>
            
            <?php if (hasUserPermission('criancas')): ?>
            <a class="nav-link" href="criancas.php">
                <i class="fas fa-child"></i>Crianças
            </a>
            <?php endif; ?>
            
            <?php if (hasUserPermission('cadastro_crianca')): ?>
            <a class="nav-link" href="cadastro_crianca.php">
                <i class="fas fa-user-plus"></i>Cadastrar Criança
            </a>
            <?php endif; ?>
            
            <?php if (hasUserPermission('checkin')): ?>
            <a class="nav-link active" href="checkin.php">
                <i class="fas fa-clipboard-check"></i>Check-in/Check-out
            </a>
            <?php endif; ?>
            
            <?php if (hasUserPermission('atividades')): ?>
            <a class="nav-link" href="atividades.php">
                <i class="fas fa-gamepad"></i>Atividades
            </a>
            <?php endif; ?>
            
            <?php if (hasUserPermission('equipes')): ?>
            <a class="nav-link" href="equipes.php">
                <i class="fas fa-users"></i>Equipes
            </a>
            <?php endif; ?>
            
            <?php if (hasUserPermission('funcionarios')): ?>
            <a class="nav-link" href="funcionarios.php">
                <i class="fas fa-user-tie"></i>Funcionários
            </a>
            <?php endif; ?>
            
            <?php if (hasUserPermission('relatorios')): ?>
            <a class="nav-link" href="relatorios.php">
                <i class="fas fa-chart-bar"></i>Relatórios
            </a>
            <?php endif; ?>
        </nav>
        
        <div class="mt-auto p-3">
            <a class="nav-link text-center" href="logout.php">
                <i class="fas fa-sign-out-alt"></i>Sair
            </a>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header-bar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 welcome-text">Check-in/Check-out</h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Controle de presença das crianças nos eventos
                    </p>
                    <small class="text-info">
                        <i class="fas fa-user-shield me-1"></i>
                        Acesso: <?php echo ucfirst($currentUser['perfil']); ?> - <?php echo htmlspecialchars($currentUser['nome_completo']); ?>
                    </small>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['nome_completo'], 0, 2)); ?>
                </div>
            </div>
        </div>
        
        <!-- Mensagens -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Seleção de Evento -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Selecione o Evento
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end" id="eventoForm">
                    <div class="col-md-6">
                        <label for="evento_id" class="form-label">Evento</label>
                        <select class="form-select" id="evento_id" name="evento_id">
                            <option value="">Selecione um evento...</option>
                            <?php foreach ($eventosDisponiveis as $evento): ?>
                            <option value="<?php echo $evento['id']; ?>" 
                                    <?php echo $evento_id == $evento['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($evento['nome']); ?> - 
                                <?php echo date('d/m/Y', strtotime($evento['data_inicio'])); ?>
                                <?php if ($evento['status'] === 'em_andamento'): ?>
                                <span class="text-success">(Em andamento)</span>
                                <?php elseif ($evento['status'] === 'planejado' && date('Y-m-d') == date('Y-m-d', strtotime($evento['data_inicio']))): ?>
                                <span class="text-info">(Hoje)</span>
                                <?php elseif ($evento['status'] === 'concluido'): ?>
                                <span class="text-muted">(Concluído)</span>
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($evento_id): ?>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Buscar Criança</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nome da criança...">
                        <input type="hidden" name="evento_id" value="<?php echo htmlspecialchars($evento_id); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-search me-1"></i>Buscar
                        </button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <?php if ($eventoSelecionado): ?>
        <!-- Informações do Evento Selecionado -->
        <div class="card mb-4 <?php echo podeRealizarCheckin($eventoSelecionado) ? 'evento-ativo' : 'evento-passado'; ?>">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="card-title mb-2">
                            <i class="fas fa-calendar me-2"></i>
                            <?php echo htmlspecialchars($eventoSelecionado['nome']); ?>
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <small class="text-muted">Data:</small>
                                    <strong><?php echo date('d/m/Y', strtotime($eventoSelecionado['data_inicio'])); ?></strong>
                                </p>
                                <p class="mb-1">
                                    <small class="text-muted">Duração:</small>
                                    <strong><?php echo $eventoSelecionado['duracao_horas']; ?> horas</strong>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <small class="text-muted">Local:</small>
                                    <strong><?php echo htmlspecialchars($eventoSelecionado['local_evento'] ?? 'A definir'); ?></strong>
                                </p>
                                <p class="mb-1">
                                    <small class="text-muted">Faixa etária:</small>
                                    <strong><?php echo $eventoSelecionado['faixa_etaria_min']; ?>-<?php echo $eventoSelecionado['faixa_etaria_max']; ?> anos</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
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
                        ?>
                        <span class="badge bg-<?php echo $statusClass[$eventoSelecionado['status']] ?? 'secondary'; ?> fs-6 mb-2">
                            <?php echo $statusText[$eventoSelecionado['status']] ?? 'Desconhecido'; ?>
                        </span>
                        
                        <?php if (!podeRealizarCheckin($eventoSelecionado)): ?>
                        <div class="date-restriction-alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Atenção:</strong>
                            <?php if (date('Y-m-d') < date('Y-m-d', strtotime($eventoSelecionado['data_inicio']))): ?>
                            Check-in/Check-out só será liberado no dia do evento (<?php echo date('d/m/Y', strtotime($eventoSelecionado['data_inicio'])); ?>)
                            <?php elseif ($eventoSelecionado['status'] === 'cancelado'): ?>
                            Evento cancelado - Check-in/Check-out não disponível
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista de Crianças -->
        <?php if (!empty($criancasCheckin)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    Crianças Inscritas (<?php echo count($criancasCheckin); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php 
                // Filtrar crianças se houver busca
                $criancasFiltradas = $criancasCheckin;
                if (!empty($search)) {
                    $criancasFiltradas = array_filter($criancasCheckin, function($crianca) use ($search) {
                        return stripos($crianca['nome_completo'], $search) !== false ||
                               stripos($crianca['nome_responsavel'], $search) !== false;
                    });
                }
                
                if (empty($criancasFiltradas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma criança encontrada</h5>
                        <?php if (!empty($search)): ?>
                        <p class="text-muted">Tente ajustar o termo de busca</p>
                        <a href="?evento_id=<?php echo $evento_id; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-times me-2"></i>Limpar Busca
                        </a>
                        <?php else: ?>
                        <p class="text-muted">Nenhuma criança inscrita neste evento</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($criancasFiltradas as $crianca): ?>
                        <?php 
                        $statusParticipacao = $crianca['status_participacao'] ?? 'Inscrito';
                        $temAlergia = !empty($crianca['alergia_alimentos']) || !empty($crianca['alergia_medicamentos']);
                        $podeCheckin = podeRealizarCheckin($eventoSelecionado);
                        ?>
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card crianca-card <?php echo strtolower(str_replace('-', '', $statusParticipacao)); ?> h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="crianca-avatar">
                                            <?php echo strtoupper(substr($crianca['nome_completo'], 0, 2)); ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="card-title mb-1">
                                                        <?php echo htmlspecialchars($crianca['nome_completo']); ?>
                                                    </h6>
                                                    <p class="text-muted small mb-1">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo $crianca['idade']; ?> anos • <?php echo $crianca['sexo']; ?>
                                                    </p>
                                                    <p class="text-muted small mb-2">
                                                        <i class="fas fa-user me-1"></i>
                                                        <?php echo htmlspecialchars($crianca['nome_responsavel']); ?>
                                                    </p>
                                                </div>
                                                <span class="status-badge badge bg-<?php 
                                                    echo $statusParticipacao === 'Check-in' ? 'success' : 
                                                        ($statusParticipacao === 'Check-out' ? 'warning' : 
                                                        ($statusParticipacao === 'Confirmado' ? 'info' : 'secondary')); 
                                                ?>">
                                                    <?php echo $statusParticipacao; ?>
                                                </span>
                                            </div>
                                            
                                            <?php if ($temAlergia): ?>
                                            <div class="mb-2">
                                                <?php if (!empty($crianca['alergia_alimentos'])): ?>
                                                <span class="alergia-alert">
                                                    <i class="fas fa-utensils me-1"></i>
                                                    <?php echo htmlspecialchars($crianca['alergia_alimentos']); ?>
                                                </span>
                                                <?php endif; ?>
                                                <?php if (!empty($crianca['alergia_medicamentos'])): ?>
                                                <span class="alergia-alert">
                                                    <i class="fas fa-pills me-1"></i>
                                                    <?php echo htmlspecialchars($crianca['alergia_medicamentos']); ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-phone me-1"></i>
                                                    <?php echo htmlspecialchars($crianca['telefone_principal']); ?>
                                                </small>
                                            </div>
                                            
                                            <!-- Botões de Ação -->
                                            <div class="mt-3">
                                                <?php if (!$podeCheckin): ?>
                                                    <div class="d-grid">
                                                        <button class="btn btn-disabled" disabled>
                                                            <i class="fas fa-lock me-2"></i>
                                                            <?php if ($eventoSelecionado['status'] === 'cancelado'): ?>
                                                            Evento Cancelado
                                                            <?php else: ?>
                                                            Aguardando Data do Evento
                                                            <?php endif; ?>
                                                        </button>
                                                    </div>
                                                <?php elseif ($statusParticipacao === 'Inscrito' || $statusParticipacao === 'Confirmado'): ?>
                                                    <div class="d-grid">
                                                        <form method="POST" style="display: inline;" class="checkin-form">
                                                            <input type="hidden" name="action" value="checkin">
                                                            <input type="hidden" name="evento_id" value="<?php echo $evento_id; ?>">
                                                            <input type="hidden" name="crianca_id" value="<?php echo $crianca['crianca_id']; ?>">
                                                            <button type="submit" class="btn btn-checkin w-100">
                                                                <i class="fas fa-sign-in-alt me-2"></i>Fazer Check-in
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php elseif ($statusParticipacao === 'Check-in'): ?>
                                                    <div class="d-grid">
                                                        <form method="POST" style="display: inline;" class="checkout-form">
                                                            <input type="hidden" name="action" value="checkout">
                                                            <input type="hidden" name="evento_id" value="<?php echo $evento_id; ?>">
                                                            <input type="hidden" name="crianca_id" value="<?php echo $crianca['crianca_id']; ?>">
                                                            <button type="submit" class="btn btn-checkout w-100">
                                                                <i class="fas fa-sign-out-alt me-2"></i>Fazer Check-out
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <small class="text-muted mt-1 d-block text-center">
                                                        Check-in: <?php echo $crianca['data_checkin'] ? date('H:i', strtotime($crianca['data_checkin'])) : '-'; ?>
                                                    </small>
                                                <?php elseif ($statusParticipacao === 'Check-out'): ?>
                                                    <div class="d-grid">
                                                        <button class="btn btn-success" disabled>
                                                            <i class="fas fa-check-circle me-2"></i>Check-out Realizado
                                                        </button>
                                                    </div>
                                                    <small class="text-muted mt-1 d-block text-center">
                                                        Check-in: <?php echo $crianca['data_checkin'] ? date('H:i', strtotime($crianca['data_checkin'])) : '-'; ?><br>
                                                        Check-out: <?php echo $crianca['data_checkout'] ? date('H:i', strtotime($crianca['data_checkout'])) : '-'; ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif ($evento_id): ?>
        <!-- Nenhuma criança inscrita -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhuma criança inscrita</h5>
                <p class="text-muted">Este evento ainda não possui crianças inscritas</p>
                <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
                <a href="eventos.php?id=<?php echo $evento_id; ?>&action=inscrever" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Inscrever Crianças
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php if (empty($eventosDisponiveis)): ?>
        <!-- Nenhum evento disponível -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum evento disponível</h5>
                <p class="text-muted">Não há eventos ativos para realizar check-in/check-out</p>
                <?php if (hasPermission('coordenador') || hasPermission('administrador')): ?>
                <a href="eventos.php" class="btn btn-primary">
                    <i class="fas fa-calendar-alt me-2"></i>Gerenciar Eventos
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CORREÇÃO: Auto-submit quando selecionar evento
            const eventoSelect = document.getElementById('evento_id');
            if (eventoSelect) {
                eventoSelect.addEventListener('change', function() {
                    // Limpar busca ao trocar evento
                    const searchInput = document.getElementById('search');
                    if (searchInput) {
                        searchInput.value = '';
                    }
                    
                    // Submit do formulário
                    document.getElementById('eventoForm').submit();
                });
            }
            
            // Auto-dismiss alerts após 5 segundos
            document.querySelectorAll('.alert').forEach(function(alert) {
                setTimeout(function() {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 5000);
            });
            
            // Busca automática com delay
            const searchInput = document.getElementById('search');
            if (searchInput) {
                let timeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        this.form.submit();
                    }, 500);
                });
            }
            
            // Confirmação melhorada para check-in/check-out
            document.querySelectorAll('.checkin-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const criancaNome = this.closest('.crianca-card').querySelector('.card-title').textContent.trim();
                    const confirmMessage = `Confirmar CHECK-IN de ${criancaNome}?\n\nEsta ação registrará a entrada da criança no evento.`;
                    
                    if (!confirm(confirmMessage)) {
                        e.preventDefault();
                    }
                });
            });
            
            document.querySelectorAll('.checkout-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const criancaNome = this.closest('.crianca-card').querySelector('.card-title').textContent.trim();
                    const confirmMessage = `Confirmar CHECK-OUT de ${criancaNome}?\n\nEsta ação registrará a saída da criança do evento.`;
                    
                    if (!confirm(confirmMessage)) {
                        e.preventDefault();
                    }
                });
            });
            
            // Feedback visual para botões
            document.querySelectorAll('.btn-checkin, .btn-checkout').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!this.disabled) {
                        const originalHTML = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';
                        this.disabled = true;
                        
                        // Reabilitar botão se houver erro
                        setTimeout(() => {
                            this.innerHTML = originalHTML;
                            this.disabled = false;
                        }, 5000);
                    }
                });
            });
            
            // Destacar crianças com alergias
            document.querySelectorAll('.alergia-alert').forEach(alert => {
                alert.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1)';
                    this.style.boxShadow = '0 4px 15px rgba(214, 51, 132, 0.3)';
                });
                
                alert.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = '0 2px 6px rgba(214, 51, 132, 0.1)';
                });
            });
            
            // Efeito de pulsação para cards de crianças que fizeram check-in
            document.querySelectorAll('.crianca-card.checkin').forEach(card => {
                setInterval(function() {
                    card.style.boxShadow = '0 10px 25px rgba(16, 185, 129, 0.3)';
                    setTimeout(function() {
                        card.style.boxShadow = '0 10px 25px rgba(255, 107, 157, 0.2)';
                    }, 1000);
                }, 3000);
            });
            
            // Filtro rápido por status
            function filterByStatus(status) {
                document.querySelectorAll('.crianca-card').forEach(card => {
                    const cardStatus = card.querySelector('.status-badge').textContent.toLowerCase().replace('-', '');
                    if (status === 'all' || cardStatus === status) {
                        card.closest('.col-lg-6').style.display = 'block';
                    } else {
                        card.closest('.col-lg-6').style.display = 'none';
                    }
                });
            }
            
            // Adicionar botões de filtro se houver crianças
            if (document.querySelectorAll('.crianca-card').length > 0) {
                const cardHeader = document.querySelector('.card-header h5');
                if (cardHeader && cardHeader.textContent.includes('Crianças Inscritas')) {
                    const filterDiv = document.createElement('div');
                    filterDiv.className = 'mt-2';
                    filterDiv.innerHTML = `
                        <small class="text-muted d-block mb-2">Filtrar por status:</small>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active" onclick="filterByStatus('all')">Todos</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="filterByStatus('inscrito')">Inscritos</button>
                            <button type="button" class="btn btn-outline-info" onclick="filterByStatus('confirmado')">Confirmados</button>
                            <button type="button" class="btn btn-outline-success" onclick="filterByStatus('checkin')">Check-in</button>
                            <button type="button" class="btn btn-outline-warning" onclick="filterByStatus('checkout')">Check-out</button>
                        </div>
                    `;
                    cardHeader.parentNode.appendChild(filterDiv);
                }
            }
            
            // Tornar função filterByStatus global
            window.filterByStatus = filterByStatus;
            
            // Formatar telefones automaticamente
            document.querySelectorAll('.crianca-card').forEach(card => {
                const phoneElement = card.querySelector('small i.fa-phone');
                if (phoneElement && phoneElement.parentNode) {
                    const phoneText = phoneElement.parentNode.textContent.trim();
                    const phoneNumbers = phoneText.replace(/[^\d]/g, '');
                    
                    if (phoneNumbers.length === 11) {
                        const formatted = phoneNumbers.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                        phoneElement.parentNode.innerHTML = '<i class="fas fa-phone me-1"></i>' + formatted;
                    } else if (phoneNumbers.length === 10) {
                        const formatted = phoneNumbers.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                        phoneElement.parentNode.innerHTML = '<i class="fas fa-phone me-1"></i>' + formatted;
                    }
                }
            });
            
            // Atualização automática da página a cada 30 segundos se houver evento selecionado
            <?php if ($evento_id): ?>
            let autoRefreshInterval = setInterval(function() {
                // Verifica se não há formulários sendo enviados
                const formsSubmitting = document.querySelectorAll('form button[disabled]');
                if (formsSubmitting.length === 0) {
                    // Recarregar página mantendo parâmetros
                    window.location.reload();
                }
            }, 30000);
            
            // Pausar auto-refresh quando formulário é enviado
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    clearInterval(autoRefreshInterval);
                });
            });
            <?php endif; ?>
            
            // Contador de crianças por status
            function updateStatusCounters() {
                const statusCounts = {
                    inscrito: 0,
                    confirmado: 0,
                    checkin: 0,
                    checkout: 0
                };
                
                document.querySelectorAll('.status-badge').forEach(badge => {
                    const status = badge.textContent.toLowerCase().replace('-', '');
                    if (statusCounts.hasOwnProperty(status)) {
                        statusCounts[status]++;
                    }
                });
                
                // Log para debug
                console.log('Status counters:', statusCounts);
            }
            
            updateStatusCounters();
            
            // Notificação sonora para ações (se suportado pelo navegador)
            function playNotificationSound(type) {
                if ('AudioContext' in window || 'webkitAudioContext' in window) {
                    try {
                        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();
                        
                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);
                        
                        if (type === 'success') {
                            oscillator.frequency.value = 800;
                            gainNode.gain.value = 0.1;
                            oscillator.start();
                            oscillator.stop(audioContext.currentTime + 0.2);
                        }
                    } catch (e) {
                        console.log('Audio context not supported');
                    }
                }
            }
            
            // Aplicar som de sucesso quando há mensagem de sucesso
            <?php if ($messageType === 'success'): ?>
            playNotificationSound('success');
            <?php endif; ?>
            
            // Inicializar tooltips do Bootstrap se disponível
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            
            // Log de debug para desenvolvimento
            console.log('Sistema de Check-in carregado com sucesso');
            console.log('Evento selecionado:', <?php echo $evento_id ? $evento_id : 'null'; ?>);
            console.log('Crianças encontradas:', <?php echo count($criancasCheckin); ?>);
        });
    </script>

</body>
</html>