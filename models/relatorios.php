<?php
// relatorios.php - Visao analitica do sistema
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../controllers/RelatoriosController.php';

date_default_timezone_set('America/Sao_Paulo');

requireLogin();

$controller = new RelatoriosController();
$currentUser = getCurrentUser();

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
        'export' => true, // Adicionada permissão de exportação
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
        'export' => true, // Adicionada permissão de exportação
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
        'export' => false, // Adicionada permissão de exportação
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
        'export' => false, // Adicionada permissão de exportação
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
        'export' => false, // Adicionada permissão de exportação
        'quick_actions' => ['checkin']
    ]
];

$userPermissions = $permissions[$currentUser['perfil']] ?? $permissions['auxiliar'];

function hasUserPermission($permission) {
    global $userPermissions;
    return isset($userPermissions[$permission]) && $userPermissions[$permission];
}

// Verificar se o usuário tem permissão para acessar relatórios
if (!hasUserPermission('relatorios')) {
    header('Location: dashboard_eventos.php');
    exit();
}

// Processar solicitação de exportação
if (isset($_GET['export']) && hasUserPermission('export')) {
    $exportType = $_GET['export'];
    
    switch ($exportType) {
        case 'pdf':
            // Implementar exportação PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="relatorio_' . date('Y-m-d_H-i-s') . '.pdf"');
            // TODO: Implementar geração do PDF
            exit();
            
        case 'excel':
            // Implementar exportação Excel
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="relatorio_' . date('Y-m-d_H-i-s') . '.xlsx"');
            // TODO: Implementar geração do Excel
            exit();
            
        case 'csv':
            // Implementar exportação CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="relatorio_' . date('Y-m-d_H-i-s') . '.csv"');
            // TODO: Implementar geração do CSV
            exit();
    }
}

$resumoGeral = $controller->getResumoGeral();
$eventosStatus = $controller->getEventosPorStatus();
$atividadesStatus = $controller->getAtividadesPorStatus();
$equipesDistribuicao = $controller->getEquipesDistribuicao();
$participacaoCriancas = $controller->getParticipacaoCriancas();
$atividadesPendentes = $controller->getAtividadesPendentes();
$eventosProximos = $controller->getEventosProximos();
$logsRecentes = $controller->getLogsRecentes();

$currentUserName = $currentUser['nome_completo'] ?? ($currentUser['nome'] ?? '');
$currentUserPerfil = $currentUser['perfil'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - MagicKids</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/relatorios.css">    
</head>
<body>
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
                <a class="nav-link active" href="relatorios.php">
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
        <div class="p-3 border-top border-white-25 text-white-75">
            <div class="fw-semibold">Logado como</div>
            <div><?php echo htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="small">Perfil: <?php echo htmlspecialchars($currentUserPerfil, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </nav>
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header-bar">
            <div>
                <h1 class="h3 mb-1">Relatórios</h1>
                <p class="text-muted mb-0">Panorama consolidado sobre eventos, equipes e operações.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <?php if (hasUserPermission('export')): ?>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download me-2"></i>Exportar Relatório
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="?export=pdf"><i class="fas fa-file-pdf me-2 text-danger"></i>PDF</a></li>
                        <li><a class="dropdown-item" href="?export=excel"><i class="fas fa-file-excel me-2 text-success"></i>Excel</a></li>
                        <li><a class="dropdown-item" href="?export=csv"><i class="fas fa-file-csv me-2 text-info"></i>CSV</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                <div class="text-end">
                    <div class="small text-muted">Atualização em</div>
                    <div class="fw-semibold"><?php echo date('d/m/Y H:i'); ?></div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Eventos</div>
                    <div class="stat-number"><?php echo (int) ($resumoGeral['total_eventos'] ?? 0); ?></div>
                    <div class="text-muted small">Registrados no sistema</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Crianças</div>
                    <div class="stat-number"><?php echo (int) ($resumoGeral['total_criancas'] ?? 0); ?></div>
                    <div class="text-muted small">Inscritas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Atividades</div>
                    <div class="stat-number"><?php echo (int) ($resumoGeral['total_atividades'] ?? 0); ?></div>
                    <div class="text-muted small">Em todo o catálogo</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Equipes</div>
                    <div class="stat-number"><?php echo (int) ($resumoGeral['total_equipes'] ?? 0); ?></div>
                    <div class="text-muted small">Times ativos</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-day me-2 text-primary"></i>Eventos por status</h5>
                        <span class="badge bg-light text-dark">Distribuição</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($eventosStatus)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum registro encontrado.</p>
                        </div>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($eventosStatus as $status => $total): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $status), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="badge bg-primary-subtle text-primary-emphasis"><?php echo (int) $total; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list-check me-2 text-success"></i>Atividades por status</h5>
                        <span class="badge bg-light text-dark">Resumo</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($atividadesStatus)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum registro encontrado.</p>
                        </div>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($atividadesStatus as $status => $total): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $status), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="badge bg-success-subtle text-success-emphasis"><?php echo (int) $total; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables Row -->
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-people-group me-2 text-info"></i>Equipes e capacidade</h5>
                        <span class="badge bg-light text-dark">Membros / capacidade</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($equipesDistribuicao)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhuma equipe cadastrada.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Equipe</th>
                                        <th class="text-center">Membros</th>
                                        <th class="text-center">Capacidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipesDistribuicao as $linha): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($linha['nome'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                        <td class="text-center"><span class="badge bg-info"><?php echo (int) ($linha['membros'] ?? 0); ?></span></td>
                                        <td class="text-center"><span class="badge bg-secondary"><?php echo (int) ($linha['capacidade_eventos'] ?? 0); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-children me-2 text-warning"></i>Participação das crianças</h5>
                        <span class="badge bg-light text-dark">Top 5</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($participacaoCriancas)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-child fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Sem dados disponíveis.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nome</th>
                                        <th class="text-center">Eventos</th>
                                        <th class="text-center">Check-ins</th>
                                        <th class="text-center">Última</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participacaoCriancas as $crianca): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($crianca['nome_completo'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                        <td class="text-center"><span class="badge bg-primary"><?php echo (int) ($crianca['total_eventos'] ?? 0); ?></span></td>
                                        <td class="text-center"><span class="badge bg-success"><?php echo (int) ($crianca['total_checkins'] ?? 0); ?></span></td>
                                        <td class="text-center">
                                            <small><?php echo $crianca['ultima_participacao'] ? date('d/m/Y', strtotime($crianca['ultima_participacao'])) : '-'; ?></small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Cards Row -->
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-hourglass-half me-2 text-warning"></i>Atividades pendentes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($atividadesPendentes)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted mb-0">Sem atividades pendentes.</p>
                        </div>
                        <?php else: ?>
                        <div class="activity-list">
                            <?php foreach ($atividadesPendentes as $atividade): ?>
                            <div class="activity-item">
                                <div class="fw-semibold text-primary"><?php echo htmlspecialchars($atividade['titulo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo htmlspecialchars($atividade['evento_nome'] ?? 'Sem evento', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo htmlspecialchars($atividade['responsavel_nome'] ?? 'Não atribuído', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo $atividade['data_fim_prevista'] ? date('d/m/Y', strtotime($atividade['data_fim_prevista'])) : 'Sem prazo'; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-calendar-week me-2 text-primary"></i>Eventos próximos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($eventosProximos)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum evento agendado.</p>
                        </div>
                        <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($eventosProximos as $evento): ?>
                            <div class="timeline-item mb-3">
                                <div class="fw-semibold text-primary"><?php echo htmlspecialchars($evento['nome'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-calendar-day me-1"></i>
                                    <?php echo $evento['data_inicio'] ? date('d/m/Y', strtotime($evento['data_inicio'])) : 'Data a definir'; ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <span class="badge bg-<?php echo $evento['status'] === 'concluido' ? 'success' : ($evento['status'] === 'em_andamento' ? 'primary' : 'secondary'); ?> text-white">
                                        <?php echo htmlspecialchars($evento['status'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2 text-danger"></i>Logs recentes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($logsRecentes)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Nenhum log recente.</p>
                        </div>
                        <?php else: ?>
                        <div class="log-list">
                            <?php foreach ($logsRecentes as $log): ?>
                            <div class="log-item">
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($log['acao'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo htmlspecialchars($log['usuario_nome'] ?? 'Sistema', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-table me-1"></i>
                                    <?php echo htmlspecialchars($log['tabela_afetada'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($log['data_criacao'])); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <!-- Biblioteca para exportação PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- Biblioteca para exportação Excel (opcional) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <script src="../assets/js/relatorios-export.js"></script>
    <script>
        // Auto refresh relatórios every 5 minutes
        setTimeout(function() {
            window.location.reload();
        }, 300000);
        
        // Add hover effects to cards
        document.querySelectorAll('.mini-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 12px 30px rgba(255, 107, 157, 0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 8px 25px rgba(255, 107, 157, 0.1)';
            });
        });
        
        // Add hover effects to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
                this.style.boxShadow = '0 15px 35px rgba(255, 107, 157, 0.2)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 10px 30px rgba(14, 165, 233, 0.08)';
            });
        });
        
        // Add animation to activity and log items
        document.querySelectorAll('.activity-item, .log-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(4px)';
                this.style.boxShadow = '0 4px 15px rgba(255, 107, 157, 0.1)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
                this.style.boxShadow = 'none';
            });
        });
        
        // Add animation to timeline items
        document.querySelectorAll('.timeline-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.paddingLeft = '1.5rem';
                this.style.backgroundColor = 'rgba(255, 107, 157, 0.02)';
                this.style.borderRadius = '8px';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.paddingLeft = '1rem';
                this.style.backgroundColor = 'transparent';
                this.style.borderRadius = '0';
            });
        });
        
        // Add loading state for export buttons
        document.querySelectorAll('[href*="export="]').forEach(link => {
            link.addEventListener('click', function(e) {
                const btn = this.closest('.dropdown').querySelector('.dropdown-toggle');
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';
                btn.disabled = true;
                
                // Restaurar botão após 3 segundos (tempo simulado)
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 3000);
            });
        });
        
        // Add smooth scroll for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Add real-time clock update
        function updateDateTime() {
            const now = new Date();
            const options = {
                timeZone: 'America/Sao_Paulo',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            
            const dateTimeString = now.toLocaleDateString('pt-BR', options);
            const clockElement = document.querySelector('.header-bar .fw-semibold');
            
            if (clockElement) {
                clockElement.textContent = dateTimeString;
            }
        }
        
        // Update clock every minute
        setInterval(updateDateTime, 60000);
        updateDateTime(); // Initial call
        
        // Add fade-in animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .mini-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        // Add counter animation for stat numbers
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                const duration = 2000; // 2 seconds
                const increment = target / (duration / 16); // 60fps
                let current = 0;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        counter.textContent = Math.floor(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                // Start animation when element is in viewport
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            updateCounter();
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                observer.observe(counter);
            });
        }
        
        // Initialize counter animation
        setTimeout(animateCounters, 500);
        
        // Add responsive sidebar toggle for mobile
        function createMobileToggle() {
            if (window.innerWidth <= 768) {
                const toggleBtn = document.createElement('button');
                toggleBtn.className = 'btn btn-primary position-fixed';
                toggleBtn.style.cssText = 'top: 1rem; left: 1rem; z-index: 1100; display: none;';
                toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
                
                toggleBtn.addEventListener('click', function() {
                    const sidebar = document.querySelector('.sidebar');
                    sidebar.classList.toggle('show');
                });
                
                document.body.appendChild(toggleBtn);
                
                if (window.innerWidth <= 768) {
                    toggleBtn.style.display = 'block';
                }
            }
        }
        
        createMobileToggle();
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const toggleBtn = document.querySelector('.btn.position-fixed');
            if (toggleBtn) {
                toggleBtn.style.display = window.innerWidth <= 768 ? 'block' : 'none';
            }
        });
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + E para exportar
            if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                e.preventDefault();
                const exportBtn = document.getElementById('exportDropdown');
                if (exportBtn) {
                    exportBtn.click();
                }
            }
            
            // Ctrl/Cmd + R para atualizar
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                window.location.reload();
            }
            
            // Esc para fechar dropdowns
            if (e.key === 'Escape') {
                const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
                openDropdowns.forEach(dropdown => {
                    const toggle = dropdown.previousElementSibling;
                    if (toggle) {
                        bootstrap.Dropdown.getInstance(toggle)?.hide();
                    }
                });
            }
        });
        
        // Add accessibility improvements
        document.querySelectorAll('.stat-card').forEach(card => {
            card.setAttribute('role', 'button');
            card.setAttribute('tabindex', '0');
            card.setAttribute('aria-label', `Estatística: ${card.querySelector('.stat-label')?.textContent}`);
        });
        
        // Add focus management
        document.querySelectorAll('.stat-card, .mini-card').forEach(card => {
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });
    </script>
</body>
</html>
