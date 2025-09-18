<?php
// relatorios.php - Visao analitica do sistema
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/RelatoriosController.php';

date_default_timezone_set('America/Sao_Paulo');

requireLogin();

$controller = new RelatoriosController();
$currentUser = getCurrentUser();

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
    <title>Relatorios - MagicKids</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b9d;
            --accent-color: #4338ca;
            --muted-color: #6366f1;
        }

        body {
            background-color: #f5f5fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--muted-color) 100%);
            min-height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
        }

        .sidebar .company-info {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar nav {
            padding: 1.5rem 1rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            transform: translateX(6px);
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }

        .header-bar {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 12px 32px rgba(67, 56, 202, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.5rem;
            height: 100%;
            box-shadow: 0 12px 32px rgba(67, 56, 202, 0.1);
        }

        .stat-label {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            color: #818cf8;
            margin-bottom: 0.5rem;
        }

        .mini-card {
            border-radius: 14px;
            border: none;
            box-shadow: 0 8px 24px rgba(255, 107, 157, 0.15);
        }

        .timeline-item {
            border-left: 3px solid var(--accent-color);
            padding-left: 1rem;
            margin-left: 0.5rem;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            width: 12px;
            height: 12px;
            background: var(--accent-color);
            border-radius: 50%;
            position: absolute;
            left: -7.5px;
            top: 4px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div>
            <div class="company-info">
                <i class="fas fa-hat-wizard"></i>
                <div class="fw-bold">MagicKids</div>
                <p class="mb-0">Centro de Eventos</p>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard_eventos.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a>
                <a class="nav-link" href="eventos.php"><i class="fas fa-calendar-check me-2"></i>Eventos</a>
                <a class="nav-link" href="cadastro_crianca.php"><i class="fas fa-clipboard-list me-2"></i>Cadastrar crianca</a>
                <a class="nav-link" href="criancas.php"><i class="fas fa-children me-2"></i>Criancas</a>
                <a class="nav-link" href="checkin.php"><i class="fas fa-clipboard-check me-2"></i>Check-in</a>
                <a class="nav-link" href="funcionarios.php"><i class="fas fa-people-group me-2"></i>Funcionarios</a>
                <a class="nav-link" href="atividades.php"><i class="fas fa-list-check me-2"></i>Atividades</a>
                <a class="nav-link" href="equipes.php"><i class="fas fa-people-arrows me-2"></i>Equipes</a>
                <a class="nav-link active" href="relatorios.php"><i class="fas fa-chart-pie me-2"></i>Relatorios</a>
                <a class="nav-link" href="logs.php"><i class="fas fa-clipboard-list me-2"></i>Logs</a>
                <a class="nav-link text-warning" href="logout.php"><i class="fas fa-right-from-bracket me-2"></i>Sair</a>
            </nav>
        </div>
        <div class="p-3 border-top border-white-25 text-white-75">
            <div class="fw-semibold">Logado como</div>
            <div><?php echo htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="small">Perfil: <?php echo htmlspecialchars($currentUserPerfil, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>
    <main class="main-content">
        <div class="header-bar">
            <div>
                <h1 class="h3 mb-1">Relatorios</h1>
                <p class="text-muted mb-0">Panorama consolidado sobre eventos, equipes e operacoes.</p>
            </div>
            <div class="text-end">
                <div class="small text-muted">Atualizacao em</div>
                <div class="fw-semibold"><?php echo date('d/m/Y H:i'); ?></div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Eventos</div>
                    <div class="display-5 fw-bold"><?php echo (int) ($resumoGeral['total_eventos'] ?? 0); ?></div>
                    <div class="text-muted small">Registrados no sistema</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Criancas</div>
                    <div class="display-5 fw-bold"><?php echo (int) ($resumoGeral['total_criancas'] ?? 0); ?></div>
                    <div class="text-muted small">Inscritas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Atividades</div>
                    <div class="display-5 fw-bold"><?php echo (int) ($resumoGeral['total_atividades'] ?? 0); ?></div>
                    <div class="text-muted small">Em todo o catalogo</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Equipes</div>
                    <div class="display-5 fw-bold"><?php echo (int) ($resumoGeral['total_equipes'] ?? 0); ?></div>
                    <div class="text-muted small">Times ativos</div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-day me-2 text-primary"></i>Eventos por status</h5>
                        <span class="badge bg-light text-dark">Distribuicao</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($eventosStatus)): ?>
                        <p class="text-muted mb-0">Nenhum registro encontrado.</p>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($eventosStatus as $status => $total): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
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
                        <p class="text-muted mb-0">Nenhum registro encontrado.</p>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($atividadesStatus as $status => $total): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
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
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-people-group me-2 text-info"></i>Equipes e capacidade</h5>
                        <span class="badge bg-light text-dark">Membros / capacidade</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($equipesDistribuicao)): ?>
                        <p class="text-muted mb-0">Nenhuma equipe cadastrada.</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Equipe</th>
                                        <th>Membros</th>
                                        <th>Capacidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipesDistribuicao as $linha): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($linha['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int) ($linha['membros'] ?? 0); ?></td>
                                        <td><?php echo (int) ($linha['capacidade_eventos'] ?? 0); ?></td>
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
                        <h5 class="mb-0"><i class="fas fa-children me-2 text-warning"></i>Participacao das criancas</h5>
                        <span class="badge bg-light text-dark">Top 5</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($participacaoCriancas)): ?>
                        <p class="text-muted mb-0">Sem dados disponiveis.</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Total eventos</th>
                                        <th>Check-ins</th>
                                        <th>Ultima participacao</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participacaoCriancas as $crianca): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($crianca['nome_completo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int) ($crianca['total_eventos'] ?? 0); ?></td>
                                        <td><?php echo (int) ($crianca['total_checkins'] ?? 0); ?></td>
                                        <td><?php echo $crianca['ultima_participacao'] ? date('d/m/Y', strtotime($crianca['ultima_participacao'])) : '-'; ?></td>
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
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-hourglass-half me-2 text-warning"></i>Atividades pendentes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($atividadesPendentes)): ?>
                        <p class="text-muted mb-0">Sem atividades pendentes.</p>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($atividadesPendentes as $atividade): ?>
                            <li class="list-group-item">
                                <div class="fw-semibold"><?php echo htmlspecialchars($atividade['titulo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small">Evento: <?php echo htmlspecialchars($atividade['evento_nome'] ?? 'Sem evento', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small">Responsavel: <?php echo htmlspecialchars($atividade['responsavel_nome'] ?? 'Nao atribuido', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small">Fim previsto: <?php echo $atividade['data_fim_prevista'] ? date('d/m/Y', strtotime($atividade['data_fim_prevista'])) : '-'; ?></div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mini-card h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-calendar-week me-2 text-primary"></i>Eventos proximos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($eventosProximos)): ?>
                        <p class="text-muted mb-0">Nenhum evento agendado.</p>
                        <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($eventosProximos as $evento): ?>
                            <div class="timeline-item mb-3">
                                <div class="fw-semibold"><?php echo htmlspecialchars($evento['nome'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small">Inicio: <?php echo $evento['data_inicio'] ? date('d/m/Y', strtotime($evento['data_inicio'])) : '-'; ?></div>
                                <div class="text-muted small">Status: <?php echo htmlspecialchars($evento['status'], ENT_QUOTES, 'UTF-8'); ?></div>
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
                        <p class="text-muted mb-0">Nenhum log recente.</p>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($logsRecentes as $log): ?>
                            <li class="list-group-item">
                                <div class="fw-semibold"><?php echo htmlspecialchars($log['acao'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small">Usuario: <?php echo htmlspecialchars($log['usuario_nome'] ?? 'Sistema', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small">Tabela: <?php echo htmlspecialchars($log['tabela_afetada'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small">Data: <?php echo date('d/m/Y H:i', strtotime($log['data_criacao'])); ?></div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>


