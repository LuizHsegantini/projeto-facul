<?php
// dashboard_eventos.php - Dashboard adaptado para eventos infantis com logout modal integrado
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar timezone para Brasil
date_default_timezone_set('America/Sao_Paulo');

require_once '../includes/auth.php';
require_once '../controllers/EventosController.php';

// Verificar se o usuário está logado
requireLogin();

// REMOVIDO: Processamento de logout duplicado - já está no auth.php

try {
    $eventosController = new EventosController();
    $dashboardData = $eventosController->getDashboardData();
    $currentUser = getCurrentUser();
} catch (Exception $e) {
    error_log("Erro no dashboard: " . $e->getMessage());
    // Dados padrão em caso de erro
    $dashboardData = [
        'total_eventos' => 0,
        'eventos_ativos' => 0,
        'total_criancas' => 0,
        'criancas_checkin' => 0,
        'total_equipes' => 0,
        'total_funcionarios' => 0,
        'proximos_eventos' => [],
        'eventos_hoje' => [],
        'aniversariantes_mes' => [],
        'evento_status_summary' => ['planejado' => 0, 'em_andamento' => 0, 'concluido' => 0, 'cancelado' => 0],
        'checkin_status_summary' => ['inscrito' => 0, 'check-in' => 0, 'check-out' => 0],
        'minhas_atividades' => []
    ];
    $currentUser = getCurrentUser();
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

// FUNÇÃO NOVA: Identificar a página atual para marcar o menu ativo
function isActivePage($pageName) {
    $currentFile = basename($_SERVER['PHP_SELF']);
    
    $pageMap = [
        'dashboard' => 'dashboard_eventos.php',
        'eventos' => 'eventos.php',
        'criancas' => 'criancas.php',
        'cadastro_crianca' => 'cadastro_crianca.php',
        'checkin' => 'checkin.php',
        'atividades' => 'atividades.php',
        'equipes' => 'equipes.php',
        'funcionarios' => 'funcionarios.php',
        'relatorios' => 'relatorios.php',
        'logs' => 'logs.php'
    ];
    
    return isset($pageMap[$pageName]) && $pageMap[$pageName] === $currentFile;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MagicKids Eventos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard_eventos.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-birthday-cake fa-6x shape"></i>
        <i class="fas fa-child fa-5x shape"></i>
        <i class="fas fa-heart fa-4x shape"></i>
    </div>

    <!-- Sidebar -->
    <!-- Sidebar -->
<nav class="sidebar">
    <div>
        <div class="company-info">
            <i class="fas fa-magic"></i>
            <div class="fw-bold">MagicKids Eventos</div>
            <p class="mb-0">Sistema de gestão</p>
        </div>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('dashboard') ? 'active' : ''; ?>" href="dashboard_eventos.php">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
        </li>
        
        <?php if (hasUserPermission('eventos')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('eventos') ? 'active' : ''; ?>" href="eventos.php">
                <i class="fas fa-calendar-alt"></i>Eventos
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasUserPermission('criancas')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('criancas') ? 'active' : ''; ?>" href="criancas.php">
                <i class="fas fa-child"></i>Crianças
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasUserPermission('cadastro_crianca')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('cadastro_crianca') ? 'active' : ''; ?>" href="/Faculdade/cadastro_crianca.php">
                <i class="fas fa-user-plus"></i>Cadastrar Criança
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasUserPermission('checkin')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('checkin') ? 'active' : ''; ?>" href="checkin.php">
                <i class="fas fa-clipboard-check"></i>Check-in/Check-out
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasUserPermission('atividades')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('atividades') ? 'active' : ''; ?>" href="atividades.php">
                <i class="fas fa-gamepad"></i>Atividades
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasUserPermission('equipes')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('equipes') ? 'active' : ''; ?>" href="equipes.php">
                <i class="fas fa-users"></i>Equipes
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasUserPermission('funcionarios')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('funcionarios') ? 'active' : ''; ?>" href="funcionarios.php">
                <i class="fas fa-user-tie"></i>Funcionários
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasUserPermission('relatorios')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('relatorios') ? 'active' : ''; ?>" href="relatorios.php">
                <i class="fas fa-chart-bar"></i>Relatórios
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (hasUserPermission('logs')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo isActivePage('logs') ? 'active' : ''; ?>" href="logs.php">
                <i class="fas fa-history"></i>Logs do Sistema
            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header-bar d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1 welcome-text">Dashboard Executivo</h2>
                <p class="text-muted mb-0"><i class="fas fa-sparkles me-2"></i>Sistema de gestão de eventos infantis</p>
                <small class="text-info">
                    <i class="fas fa-user-shield me-1"></i>
                    Acesso: <?php echo ucfirst($currentUser['perfil']); ?>
                </small>
            </div>
            <div class="d-flex align-items-center">
                <div class="me-3 text-end">
                    <small class="text-muted d-block">Bem-vindo(a),</small>
                    <strong class="d-block"><?php echo htmlspecialchars($currentUser['nome_completo']); ?></strong>
                    <span class="badge bg-gradient" style="background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));">
                        <?php echo ucfirst($currentUser['perfil']); ?>
                    </span>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['nome_completo'], 0, 2)); ?>
                </div>
                <div class="dropdown ms-3">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" id="logout-trigger"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php if (hasUserPermission('eventos')): ?>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card primary position-relative">
                    <div class="stat-number"><?php echo $dashboardData['total_eventos']; ?></div>
                    <div class="stat-label">Total de Eventos</div>
                    <i class="fas fa-calendar-alt stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card success position-relative">
                    <div class="stat-number"><?php echo $dashboardData['eventos_ativos']; ?></div>
                    <div class="stat-label">Eventos Ativos</div>
                    <i class="fas fa-play-circle stat-icon"></i>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (hasUserPermission('criancas')): ?>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card info position-relative">
                    <div class="stat-number"><?php echo $dashboardData['total_criancas']; ?></div>
                    <div class="stat-label">Crianças Cadastradas</div>
                    <i class="fas fa-child stat-icon"></i>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (hasUserPermission('checkin')): ?>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card warning position-relative">
                    <div class="stat-number"><?php echo $dashboardData['criancas_checkin']; ?></div>
                    <div class="stat-label">Check-ins Hoje</div>
                    <i class="fas fa-clipboard-check stat-icon"></i>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (hasUserPermission('equipes')): ?>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card purple position-relative">
                    <div class="stat-number"><?php echo $dashboardData['total_equipes']; ?></div>
                    <div class="stat-label">Equipes Ativas</div>
                    <i class="fas fa-users stat-icon"></i>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (hasUserPermission('funcionarios')): ?>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card info position-relative">
                    <div class="stat-number"><?php echo $dashboardData['total_funcionarios']; ?></div>
                    <div class="stat-label">Funcionários</div>
                    <i class="fas fa-user-tie stat-icon"></i>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Alerts Section -->
        <?php if (count($dashboardData['eventos_hoje']) > 0): ?>
        <div class="alert alert-info mb-4">
            <h5><i class="fas fa-calendar-day me-2"></i>Eventos Hoje</h5>
            <p class="mb-2">Temos <strong><?php echo count($dashboardData['eventos_hoje']); ?></strong> evento(s) acontecendo hoje:</p>
            <ul class="mb-0">
                <?php foreach ($dashboardData['eventos_hoje'] as $evento): ?>
                <li><strong><?php echo htmlspecialchars($evento['nome']); ?></strong> - <?php echo $evento['local_evento']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (count($dashboardData['aniversariantes_mes']) > 0): ?>
        <div class="alert alert-warning mb-4">
            <h5><i class="fas fa-birthday-cake me-2"></i>Aniversariantes do Mês</h5>
            <p class="mb-3">Temos <strong><?php echo count($dashboardData['aniversariantes_mes']); ?></strong> aniversariante(s) este mês:</p>
            <div class="row">
                <?php foreach (array_slice($dashboardData['aniversariantes_mes'], 0, 3) as $aniversariante): ?>
                <div class="col-md-4">
                    <div class="aniversariante-card">
                        <i class="fas fa-birthday-cake me-2"></i>
                        <strong><?php echo htmlspecialchars($aniversariante['nome_completo']); ?></strong><br>
                        <small><?php echo date('d/m', strtotime($aniversariante['data_nascimento'])); ?> - <?php echo $aniversariante['idade']; ?> anos</small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Próximos Eventos -->
            <?php if (hasUserPermission('eventos')): ?>
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            Próximos Eventos
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dashboardData['proximos_eventos'])): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhum evento próximo agendado</p>
                                <?php if (in_array($currentUser['perfil'], ['administrador', 'coordenador'])): ?>
                                <a href="eventos.php?action=create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Criar Primeiro Evento
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Evento</th>
                                            <th>Data</th>
                                            <th>Local</th>
                                            <th>Capacidade</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dashboardData['proximos_eventos'] as $evento): ?>
                                        <tr class="evento-card">
                                            <td>
                                                <strong><?php echo htmlspecialchars($evento['nome']); ?></strong><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-child me-1"></i>
                                                    <?php echo $evento['faixa_etaria_min']; ?>-<?php echo $evento['faixa_etaria_max']; ?> anos
                                                </small>
                                            </td>
                                            <td>
                                                <strong><?php echo date('d/m/Y', strtotime($evento['data_inicio'])); ?></strong><br>
                                                <small class="text-muted"><?php echo $evento['duracao_horas']; ?>h</small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($evento['local_evento'] ?? 'A definir'); ?></small>
                                            </td>
                                            <td>
                                                <div class="progress mb-1" style="height: 8px;">
                                                    <?php 
                                                    $inscricoes = $evento['total_inscricoes'] ?? 0;
                                                    $capacidade = $evento['capacidade_maxima'];
                                                    $percentual = $capacidade > 0 ? ($inscricoes / $capacidade) * 100 : 0;
                                                    ?>
                                                    <div class="progress-bar bg-gradient" style="width: <?php echo $percentual; ?>%; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));"></div>
                                                </div>
                                                <small class="text-muted"><?php echo $inscricoes; ?>/<?php echo $capacidade; ?></small>
                                            </td>
                                            <td>
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
                                                <span class="badge bg-<?php echo $statusClass[$evento['status']] ?? 'secondary'; ?>">
                                                    <?php echo $statusText[$evento['status']] ?? 'Desconhecido'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <div class="text-center mt-3">
                            <a href="eventos.php" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-2"></i>Ver Todos os Eventos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Resumo de Status -->
            <div class="<?php echo hasUserPermission('eventos') ? 'col-lg-4' : 'col-lg-12'; ?> mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2 text-success"></i>
                            Resumo por Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (hasUserPermission('eventos')): ?>
                        <div class="mb-4">
                            <h6 class="text-muted mb-3"><i class="fas fa-calendar-alt me-2"></i>Eventos</h6>
                            <?php foreach ($dashboardData['evento_status_summary'] as $status => $count): ?>
                            <?php if ($count > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background: rgba(255, 107, 157, 0.05);">
                                <span><i class="fas fa-circle me-2" style="font-size: 0.6rem; color: var(--primary-color);"></i><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                                <span class="badge bg-secondary"><?php echo $count; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (hasUserPermission('checkin')): ?>
                        <div class="mb-4">
                            <h6 class="text-muted mb-3"><i class="fas fa-clipboard-check me-2"></i>Check-ins</h6>
                            <?php foreach ($dashboardData['checkin_status_summary'] as $status => $count): ?>
                            <?php if ($count > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background: rgba(6, 188, 244, 0.05);">
                                <span><i class="fas fa-circle me-2" style="font-size: 0.6rem; color: var(--info-color);"></i><?php echo ucfirst(str_replace(['_', '-'], ' ', $status)); ?></span>
                                <span class="badge bg-info"><?php echo $count; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($currentUser['perfil'] === 'animador' && !empty($dashboardData['minhas_atividades'])): ?>
                        <div>
                            <h6 class="text-muted mb-3"><i class="fas fa-tasks me-2"></i>Minhas Atividades</h6>
                            <?php foreach (array_slice($dashboardData['minhas_atividades'], 0, 5) as $atividade): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div>
                                    <small class="fw-bold"><?php echo htmlspecialchars($atividade['titulo']); ?></small><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($atividade['evento_nome'] ?? ''); ?></small>
                                </div>
                                <span class="badge bg-<?php echo $atividade['status'] === 'concluida' ? 'success' : ($atividade['status'] === 'em_execucao' ? 'primary' : 'secondary'); ?>">
                                    <?php echo ucfirst($atividade['status']); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                            <?php if (hasUserPermission('atividades')): ?>
                            <div class="text-center mt-3">
                                <a href="atividades.php?user=<?php echo $currentUser['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Ver Todas Minhas Atividades
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!hasUserPermission('eventos') && !hasUserPermission('checkin') && empty($dashboardData['minhas_atividades'])): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Informações de status disponíveis conforme suas permissões</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <?php if (!empty($userPermissions['quick_actions'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2 text-warning"></i>
                            Ações Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php if (in_array('cadastro_crianca', $userPermissions['quick_actions'])): ?>
                            <div class="col-md-<?php echo count($userPermissions['quick_actions']) >= 4 ? '3' : '4'; ?>">
                                <a href="/Faculdade/cadastro_crianca.php" class="quick-action-btn">
                                    <i class="fas fa-user-plus fa-2x mb-3 d-block" style="color: var(--primary-color);"></i>
                                    <strong>Cadastrar Criança</strong>
                                    <small class="d-block text-muted mt-1">Adicionar nova criança</small>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array('criar_evento', $userPermissions['quick_actions'])): ?>
                            <div class="col-md-<?php echo count($userPermissions['quick_actions']) >= 4 ? '3' : '4'; ?>">
                                <a href="eventos.php?action=create" class="quick-action-btn">
                                    <i class="fas fa-calendar-plus fa-2x mb-3 d-block" style="color: var(--success-color);"></i>
                                    <strong>Criar Evento</strong>
                                    <small class="d-block text-muted mt-1">Novo evento infantil</small>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array('checkin', $userPermissions['quick_actions'])): ?>
                            <div class="col-md-<?php echo count($userPermissions['quick_actions']) >= 4 ? '3' : '4'; ?>">
                                <a href="checkin.php" class="quick-action-btn">
                                    <i class="fas fa-clipboard-check fa-2x mb-3 d-block" style="color: var(--warning-color);"></i>
                                    <strong>Check-in/Check-out</strong>
                                    <small class="d-block text-muted mt-1">Controle de presença</small>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array('relatorios', $userPermissions['quick_actions'])): ?>
                            <div class="col-md-<?php echo count($userPermissions['quick_actions']) >= 4 ? '3' : '4'; ?>">
                                <a href="relatorios.php" class="quick-action-btn">
                                    <i class="fas fa-chart-line fa-2x mb-3 d-block" style="color: var(--info-color);"></i>
                                    <strong>Relatórios</strong>
                                    <small class="d-block text-muted mt-1">Análises e estatísticas</small>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Footer with System Info -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-3">
                        <small class="text-muted">
                            <i class="fas fa-magic me-2"></i>
                            MagicKids Eventos - Sistema de Gestão de Eventos Infantis
                            <span class="mx-3">|</span>
                            <i class="fas fa-clock me-2"></i>
                            Última atualização: <?php echo date('d/m/Y H:i:s '); ?>
                            <span class="mx-3">|</span>
                            <i class="fas fa-heart me-2" style="color: var(--danger-color);"></i>
                            Feito com amor para as crianças
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content logout-modal">
                <div class="modal-header border-0 text-center">
                    <div class="w-100">
                        <div class="logout-icon-modal">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <h4 class="modal-title mt-3" id="logoutModalLabel">Confirmar Logout</h4>
                    </div>
                </div>
                <div class="modal-body text-center">
                    <p class="text-muted mb-4">
                        Olá, <strong><?php echo htmlspecialchars($currentUser['nome_completo']); ?></strong>!<br>
                        Tem certeza que deseja sair do sistema?
                    </p>
                    <div class="d-flex gap-3 justify-content-center">
                        <button type="button" class="btn btn-logout-confirm" id="confirm-logout-btn">
                            <i class="fas fa-sign-out-alt me-2"></i>Sim, Sair
                        </button>
                        <button type="button" class="btn btn-cancel-logout" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Progresso do Logout -->
    <div class="modal fade" id="logoutProgressModal" tabindex="-1" aria-labelledby="logoutProgressLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content logout-modal">
                <div class="modal-body text-center py-5">
                    <div class="logout-progress-icon">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <h4 class="mt-3 mb-3">Realizando Logout...</h4>
                    <p class="text-muted mb-4">Aguarde enquanto finalizamos sua sessão</p>
                    
                    <div class="progress mb-3 logout-progress-bar">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    
                    <div class="logout-steps">
                        <small class="text-muted">
                            <span id="step-text">Salvando dados da sessão...</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Sucesso do Logout -->
    <div class="modal fade" id="logoutSuccessModal" tabindex="-1" aria-labelledby="logoutSuccessLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content logout-modal">
                <div class="modal-body text-center py-5">
                    <div class="success-icon-modal">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4 class="mt-3 mb-3 text-success">Logout Realizado!</h4>
                    <p class="text-muted mb-4">
                        Sua sessão foi finalizada com sucesso.<br>
                        Redirecionando para a página de login...
                    </p>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout.js"></script>
    <script>
        // Auto refresh dashboard every 5 minutes
        setTimeout(function() {
            window.location.reload();
        }, 300000);
        
        // Add click animation to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
        
        // Birthday cake animation for birthday alerts
        document.querySelectorAll('.fa-birthday-cake').forEach(cake => {
            cake.addEventListener('mouseover', function() {
                this.style.animation = 'bounce 0.5s ease-in-out';
            });
            
            cake.addEventListener('animationend', function() {
                this.style.animation = '';
            });
        });
        
        // Add sparkle effect to welcome text
        document.addEventListener('DOMContentLoaded', function() {
            const welcomeText = document.querySelector('.welcome-text');
            if (welcomeText) {
                welcomeText.addEventListener('mouseover', function() {
                    this.style.textShadow = '0 0 20px rgba(255, 107, 157, 0.5)';
                });
                
                welcomeText.addEventListener('mouseout', function() {
                    this.style.textShadow = '';
                });
            }
        });
        
        // Add hover effects to navigation links
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.background = 'rgba(255, 255, 255, 0.25)';
            });
            
            link.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.style.background = '';
                }
            });
        });
        
        // Add floating animation to shapes
        document.querySelectorAll('.shape').forEach((shape, index) => {
            shape.addEventListener('mouseover', function() {
                this.style.opacity = '0.1';
                this.style.transform = 'scale(1.2)';
            });
            
            shape.addEventListener('mouseout', function() {
                this.style.opacity = '0.03';
                this.style.transform = '';
            });
        });
        
        // Smooth scroll to sections
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Add success feedback for quick actions
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const icon = this.querySelector('i');
                icon.style.transform = 'scale(1.2)';
                icon.style.color = 'var(--success-color)';
                
                setTimeout(() => {
                    icon.style.transform = '';
                    icon.style.color = '';
                }, 300);
            });
        });
        
        // Display real-time clock in Brazilian time
        function updateClock() {
            const now = new Date();
            const options = {
                timeZone: 'America/Sao_Paulo',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZoneName: 'short'
            };
            
            const formatter = new Intl.DateTimeFormat('pt-BR', options);
            const timeString = formatter.format(now);
            
            // Update any elements with real-time clock if needed
            const clockElements = document.querySelectorAll('.real-time-clock');
            clockElements.forEach(element => {
                element.textContent = timeString;
            });
        }
        
        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call
    </script>
    
    <!-- Add custom CSS animation -->
    <style>
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
    </style>
</body>
</html>