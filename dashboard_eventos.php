<?php
// dashboard_eventos.php - Dashboard adaptado para eventos infantis
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/EventosController.php';

// Verificar se o usuário está logado
requireLogin();

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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MagicKids Eventos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b9d;
            --secondary-color: #ffc93c;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06bcf4;
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
        
        .header-bar {
            background: white;
            border-radius: 15px;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(255, 107, 157, 0.1);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(255, 107, 157, 0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .stat-icon {
            font-size: 3rem;
            opacity: 0.1;
            position: absolute;
            right: 1rem;
            top: 1rem;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 2px 10px rgba(255, 107, 157, 0.1);
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1rem;
        }
        
        .progress {
            height: 8px;
            border-radius: 10px;
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
        }
        
        .evento-card {
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .evento-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(255, 107, 157, 0.2);
        }
        
        .aniversariante-card {
            background: linear-gradient(45deg, #ff6b9d, #ffc93c);
            color: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            opacity: 0.05;
            animation: float 8s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            top: 10%;
            left: 70%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            top: 50%;
            right: 5%;
            animation-delay: 3s;
        }
        
        .shape:nth-child(3) {
            bottom: 20%;
            left: 75%;
            animation-delay: 6s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }
    </style>
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
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard_eventos.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="eventos.php">
                    <i class="fas fa-calendar-star me-2"></i>Eventos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="criancas.php">
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
        <div class="header-bar d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Dashboard Executivo</h2>
                <p class="text-muted mb-0">Sistema de gestão de eventos infantis</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <small class="text-muted">Bem-vindo(a),</small><br>
                    <strong><?php echo htmlspecialchars($currentUser['nome']); ?></strong>
                    <span class="badge bg-primary ms-2"><?php echo ucfirst($currentUser['perfil']); ?></span>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['nome'], 0, 2)); ?>
                </div>
                <div class="dropdown ms-2">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card primary position-relative">
                    <div class="stat-number text-primary"><?php echo $dashboardData['total_eventos']; ?></div>
                    <div class="stat-label">Total de Eventos</div>
                    <i class="fas fa-calendar-star stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card success position-relative">
                    <div class="stat-number text-success"><?php echo $dashboardData['eventos_ativos']; ?></div>
                    <div class="stat-label">Eventos Ativos</div>
                    <i class="fas fa-play-circle stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card info position-relative">
                    <div class="stat-number text-info"><?php echo $dashboardData['total_criancas']; ?></div>
                    <div class="stat-label">Crianças Cadastradas</div>
                    <i class="fas fa-child stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card warning position-relative">
                    <div class="stat-number text-warning"><?php echo $dashboardData['criancas_checkin']; ?></div>
                    <div class="stat-label">Check-ins Hoje</div>
                    <i class="fas fa-clipboard-check stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card primary position-relative">
                    <div class="stat-number text-primary"><?php echo $dashboardData['total_equipes']; ?></div>
                    <div class="stat-label">Equipes Ativas</div>
                    <i class="fas fa-users stat-icon"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stat-card info position-relative">
                    <div class="stat-number text-info"><?php echo $dashboardData['total_funcionarios']; ?></div>
                    <div class="stat-label">Funcionários</div>
                    <i class="fas fa-user-tie stat-icon"></i>
                </div>
            </div>
        </div>
        
        <!-- Alerts Section -->
        <?php if (count($dashboardData['eventos_hoje']) > 0): ?>
        <div class="alert alert-info mb-4">
            <h5><i class="fas fa-calendar-day me-2"></i>Eventos Hoje</h5>
            <p class="mb-2">Temos <?php echo count($dashboardData['eventos_hoje']); ?> evento(s) acontecendo hoje:</p>
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
            <p class="mb-2">Temos <?php echo count($dashboardData['aniversariantes_mes']); ?> aniversariante(s) este mês:</p>
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
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-star me-2 text-primary"></i>
                            Próximos Eventos
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dashboardData['proximos_eventos'])): ?>
                            <p class="text-muted text-center py-4">Nenhum evento próximo agendado</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
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
                                                <div class="progress mb-1" style="height: 6px;">
                                                    <?php 
                                                    $inscricoes = $evento['total_inscricoes'] ?? 0;
                                                    $capacidade = $evento['capacidade_maxima'];
                                                    $percentual = $capacidade > 0 ? ($inscricoes / $capacidade) * 100 : 0;
                                                    ?>
                                                    <div class="progress-bar bg-info" style="width: <?php echo $percentual; ?>%"></div>
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
            
            <!-- Resumo de Status -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2 text-success"></i>
                            Resumo por Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Eventos</h6>
                            <?php foreach ($dashboardData['evento_status_summary'] as $status => $count): ?>
                            <?php if ($count > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                                <span class="badge bg-secondary"><?php echo $count; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Check-ins</h6>
                            <?php foreach ($dashboardData['checkin_status_summary'] as $status => $count): ?>
                            <?php if ($count > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo ucfirst(str_replace(['_', '-'], ' ', $status)); ?></span>
                                <span class="badge bg-info"><?php echo $count; ?></span>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($currentUser['perfil'] === 'animador' && !empty($dashboardData['minhas_atividades'])): ?>
                        <div>
                            <h6 class="text-muted mb-3">Minhas Atividades</h6>
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
                            <div class="text-center mt-3">
                                <a href="atividades.php?user=<?php echo $currentUser['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    Ver Todas Minhas Atividades
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
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
                            <div class="col-md-3">
                                <a href="cadastro_crianca.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>
                                    Cadastrar Criança
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="eventos.php?action=create" class="btn btn-outline-success w-100">
                                    <i class="fas fa-calendar-plus fa-2x mb-2 d-block"></i>
                                    Criar Evento
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="checkin.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-clipboard-check fa-2x mb-2 d-block"></i>
                                    Check-in/Check-out
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="relatorios.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-chart-line fa-2x mb-2 d-block"></i>
                                    Relatórios
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh dashboard every 5 minutes
        setTimeout(function() {
            window.location.reload();
        }, 300000);
        
        // Add some interactivity to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', function() {
                // You can add navigation logic here based on the card clicked
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
    </script>
</body>
</html>