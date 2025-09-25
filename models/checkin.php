<?php
// checkin.php - Sistema de check-in/check-out para eventos
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

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'checkin':
                $result = $eventosController->checkinCrianca($_POST['evento_id'], $_POST['crianca_id']);
                if ($result) {
                    $message = 'Check-in realizado com sucesso!';
                    $messageType = 'success';
                } else {
                    $message = 'Erro ao realizar check-in.';
                    $messageType = 'danger';
                }
                break;
                
            case 'checkout':
                $result = $eventosController->checkoutCrianca($_POST['evento_id'], $_POST['crianca_id']);
                if ($result) {
                    $message = 'Check-out realizado com sucesso!';
                    $messageType = 'success';
                } else {
                    $message = 'Erro ao realizar check-out.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Buscar eventos ativos ou próximos
$eventosAtivos = $eventosController->index('', '', 1, 50)['eventos'];
$evento_id = $_GET['evento'] ?? '';

// Se um evento foi selecionado, buscar as crianças
$criancasEvento = [];
if ($evento_id) {
    $criancasEvento = $eventosController->getEventoCriancas($evento_id);
}

// Buscar todos os check-ins/check-outs recentes
$checkins_recentes = $criancasController->getCriancasCheckin();

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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in/Check-out - MagicKids</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/checkin.css">
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-birthday-cake fa-6x shape"></i>
        <i class="fas fa-child fa-5x shape"></i>
        <i class="fas fa-heart fa-4x shape"></i>
    </div>

    <!-- Sidebar IDÊNTICO ao dashboard_eventos.php -->
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
                <a class="nav-link active" href="checkin.php">
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
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Check-in / Check-out</h2>
                <p class="text-muted mb-0">Controle de presença das crianças nos eventos</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#searchModal">
                    <i class="fas fa-search me-2"></i>Busca Rápida
                </button>
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-sync me-2"></i>Atualizar
                </button>
            </div>
        </div>
        
        <!-- Mensagens -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Seleção de Evento -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-star me-2 text-primary"></i>
                    Selecionar Evento
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <select class="form-select" id="eventoSelect" onchange="changeEvento()">
                            <option value="">Selecione um evento...</option>
                            <?php foreach ($eventosAtivos as $evento): ?>
                            <option value="<?php echo $evento['id']; ?>" <?php echo $evento_id == $evento['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($evento['nome']); ?> - 
                                <?php echo date('d/m/Y', strtotime($evento['data_inicio'])); ?>
                                (<?php echo $evento['total_inscricoes']; ?>/<?php echo $evento['capacidade_maxima']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <?php if ($evento_id): ?>
                        <div class="d-flex gap-2">
                            <span class="badge bg-info">
                                <i class="fas fa-users me-1"></i>
                                <?php echo count($criancasEvento); ?> crianças
                            </span>
                            <span class="badge bg-success">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                <?php echo count(array_filter($criancasEvento, function($c) { return $c['status_participacao'] === 'Check-in'; })); ?> check-ins
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($evento_id && !empty($criancasEvento)): ?>
        <!-- Lista de Crianças do Evento -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-child me-2 text-success"></i>
                    Crianças do Evento
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($criancasEvento as $crianca): ?>
                    <div class="col-lg-6 col-xl-4 mb-3">
                        <div class="card crianca-card <?php echo strtolower(str_replace('-', '', $crianca['status_participacao'])); ?>">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start">
                                    <div class="crianca-avatar">
                                        <?php echo strtoupper(substr($crianca['nome_completo'], 0, 2)); ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($crianca['nome_completo']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo $crianca['idade']; ?> anos • 
                                                    <?php echo htmlspecialchars($crianca['nome_responsavel']); ?>
                                                </small>
                                            </div>
                                            <span class="badge status-badge bg-<?php 
                                                echo $crianca['status_participacao'] === 'Check-in' ? 'success' : 
                                                    ($crianca['status_participacao'] === 'Check-out' ? 'warning' : 
                                                    ($crianca['status_participacao'] === 'Confirmado' ? 'info' : 'secondary')); 
                                            ?>">
                                                <?php echo $crianca['status_participacao']; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-phone me-1"></i>
                                                <?php echo htmlspecialchars($crianca['telefone_principal']); ?>
                                            </small>
                                        </div>
                                        
                                        <?php if (!empty($crianca['alergia_alimentos']) || !empty($crianca['alergia_medicamentos'])): ?>
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
                                        
                                        <?php if ($crianca['data_checkin']): ?>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-sign-in-alt me-1"></i>
                                            Check-in: <?php echo date('H:i', strtotime($crianca['data_checkin'])); ?>
                                            <?php if ($crianca['usuario_checkin_nome']): ?>
                                            por <?php echo htmlspecialchars($crianca['usuario_checkin_nome']); ?>
                                            <?php endif; ?>
                                        </small>
                                        <?php endif; ?>
                                        
                                        <?php if ($crianca['data_checkout']): ?>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-sign-out-alt me-1"></i>
                                            Check-out: <?php echo date('H:i', strtotime($crianca['data_checkout'])); ?>
                                            <?php if ($crianca['usuario_checkout_nome']): ?>
                                            por <?php echo htmlspecialchars($crianca['usuario_checkout_nome']); ?>
                                            <?php endif; ?>
                                        </small>
                                        <?php endif; ?>
                                        
                                        <div class="mt-3 d-flex gap-2">
                                            <?php if ($crianca['status_participacao'] !== 'Check-in' && $crianca['status_participacao'] !== 'Check-out'): ?>
                                            <button type="button" class="btn btn-success btn-sm flex-grow-1" 
                                                    onclick="realizarCheckin(<?php echo $evento_id; ?>, <?php echo $crianca['crianca_id']; ?>, '<?php echo htmlspecialchars($crianca['nome_completo']); ?>')">
                                                <i class="fas fa-sign-in-alt me-1"></i>Check-in
                                            </button>
                                            <?php elseif ($crianca['status_participacao'] === 'Check-in'): ?>
                                            <button type="button" class="btn btn-warning btn-sm flex-grow-1" 
                                                    onclick="realizarCheckout(<?php echo $evento_id; ?>, <?php echo $crianca['crianca_id']; ?>, '<?php echo htmlspecialchars($crianca['nome_completo']); ?>')">
                                                <i class="fas fa-sign-out-alt me-1"></i>Check-out
                                            </button>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Finalizado</span>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn btn-outline-info btn-sm" 
                                                    onclick="verDetalhes(<?php echo htmlspecialchars(json_encode($crianca)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <?php elseif ($evento_id): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhuma criança inscrita neste evento</h5>
                <p class="text-muted">Adicione crianças ao evento para começar o check-in</p>
                <a href="eventos.php?id=<?php echo $evento_id; ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Gerenciar Inscrições
                </a>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Resumo Geral de Check-ins -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2 text-info"></i>
                    Check-ins Recentes
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($checkins_recentes)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum check-in registrado recentemente</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Criança</th>
                                    <th>Evento</th>
                                    <th>Status</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Responsável pelo Check</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($checkins_recentes, 0, 20) as $checkin): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($checkin['nome_completo']); ?></strong><br>
                                        <small class="text-muted"><?php echo $checkin['idade']; ?> anos</small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($checkin['evento_nome']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $checkin['status_participacao'] === 'Check-in' ? 'success' : 
                                                ($checkin['status_participacao'] === 'Check-out' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo $checkin['status_participacao']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($checkin['data_checkin']): ?>
                                            <?php echo date('d/m H:i', strtotime($checkin['data_checkin'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($checkin['data_checkout']): ?>
                                            <?php echo date('d/m H:i', strtotime($checkin['data_checkout'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?php if ($checkin['usuario_checkin_nome']): ?>
                                                In: <?php echo htmlspecialchars($checkin['usuario_checkin_nome']); ?>
                                            <?php endif; ?>
                                            <?php if ($checkin['usuario_checkout_nome']): ?>
                                                <br>Out: <?php echo htmlspecialchars($checkin['usuario_checkout_nome']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <!-- Modal de Busca Rápida -->
    <div class="modal fade" id="searchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Busca Rápida de Criança</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control" id="quickSearch" placeholder="Digite o nome da criança ou responsável">
                    <div id="searchResults" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Detalhes -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes da Criança</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Preenchido via JavaScript -->
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="confirmCheckinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Check-in</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="checkinConfirmText">Confirmar CHECK-IN de <span id="checkinChildName"></span>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmCheckinBtn">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Check-out -->
<div class="modal fade" id="confirmCheckoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Check-out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="checkoutConfirmText">Confirmar CHECK-OUT de <span id="checkoutChildName"></span>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="confirmCheckoutBtn">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    // Variáveis globais para armazenar os dados temporários
    let currentEventoId = null;
    let currentCriancaId = null;
    
    function changeEvento() {
        const select = document.getElementById('eventoSelect');
        const eventoId = select.value;
        
        const url = new URL(window.location.href);
        
        if (eventoId) {
            url.searchParams.set('evento', eventoId);
        } else {
            url.searchParams.delete('evento');
        }
        
        window.location.href = url.toString();
    }
    
    function realizarCheckin(eventoId, criancaId, nomeCrianca) {
        currentEventoId = eventoId;
        currentCriancaId = criancaId;
        
        document.getElementById('checkinChildName').textContent = nomeCrianca;
        
        const modal = new bootstrap.Modal(document.getElementById('confirmCheckinModal'));
        modal.show();
    }
    
    function realizarCheckout(eventoId, criancaId, nomeCrianca) {
        currentEventoId = eventoId;
        currentCriancaId = criancaId;
        
        document.getElementById('checkoutChildName').textContent = nomeCrianca;
        
        const modal = new bootstrap.Modal(document.getElementById('confirmCheckoutModal'));
        modal.show();
    }
    
    // Configurar os botões de confirmação
    document.getElementById('confirmCheckinBtn').addEventListener('click', function() {
        if (currentEventoId && currentCriancaId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="checkin">
                <input type="hidden" name="evento_id" value="${currentEventoId}">
                <input type="hidden" name="crianca_id" value="${currentCriancaId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        // Fechar o modal
        bootstrap.Modal.getInstance(document.getElementById('confirmCheckinModal')).hide();
    });
    
    document.getElementById('confirmCheckoutBtn').addEventListener('click', function() {
        if (currentEventoId && currentCriancaId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="checkout">
                <input type="hidden" name="evento_id" value="${currentEventoId}">
                <input type="hidden" name="crianca_id" value="${currentCriancaId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        // Fechar o modal
        bootstrap.Modal.getInstance(document.getElementById('confirmCheckoutModal')).hide();
    });
    
    function verDetalhes(crianca) {
        const content = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Informações da Criança</h6>
                    <p><strong>Nome:</strong> ${crianca.nome_completo}</p>
                    <p><strong>Idade:</strong> ${crianca.idade} anos</p>
                    <p><strong>Responsável:</strong> ${crianca.nome_responsavel}</p>
                    <p><strong>Telefone:</strong> ${crianca.telefone_principal}</p>
                    ${crianca.alergia_alimentos ? `<p><strong>Alergia Alimentos:</strong> <span class="text-danger">${crianca.alergia_alimentos}</span></p>` : ''}
                    ${crianca.alergia_medicamentos ? `<p><strong>Alergia Medicamentos:</strong> <span class="text-danger">${crianca.alergia_medicamentos}</span></p>` : ''}
                    ${crianca.observacoes_saude ? `<p><strong>Observações:</strong> ${crianca.observacoes_saude}</p>` : ''}
                </div>
                <div class="col-md-6">
                    <h6>Status no Evento</h6>
                    <p><strong>Status:</strong> <span class="badge bg-info">${crianca.status_participacao}</span></p>
                    ${crianca.data_checkin ? `<p><strong>Check-in:</strong> ${new Date(crianca.data_checkin).toLocaleString('pt-BR')}</p>` : ''}
                    ${crianca.data_checkout ? `<p><strong>Check-out:</strong> ${new Date(crianca.data_checkout).toLocaleString('pt-BR')}</p>` : ''}
                    ${crianca.usuario_checkin_nome ? `<p><strong>Check-in por:</strong> ${crianca.usuario_checkin_nome}</p>` : ''}
                    ${crianca.usuario_checkout_nome ? `<p><strong>Check-out por:</strong> ${crianca.usuario_checkout_nome}</p>` : ''}
                </div>
            </div>
        `;
        
        document.getElementById('detailsContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('detailsModal')).show();
    }
    
    // Busca rápida (mantido igual)
    // Busca rápida - OTIMIZADA
let searchTimeout;
let lastSearchTerm = '';

document.getElementById('quickSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const termo = this.value.trim();
    
    if (termo === lastSearchTerm) return;
    lastSearchTerm = termo;
    
    if (termo.length < 2) {
        document.getElementById('searchResults').innerHTML = '';
        return;
    }
    
    // Mostrar loading
    document.getElementById('searchResults').innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
            Buscando...
        </div>
    `;
    
    searchTimeout = setTimeout(() => {
        // Usar FormData para melhor compatibilidade
        const formData = new FormData();
        formData.append('termo', termo);
        
        fetch('../ajax/buscar_criancas.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Erro na rede');
            return response.json();
        })
        .then(data => {
            let html = '';
            
            if (data.success && data.criancas && data.criancas.length > 0) {
                html = '<div class="search-results-container">';
                
                data.criancas.forEach(crianca => {
                    const iniciais = crianca.nome_completo.substring(0, 2).toUpperCase();
                    const statusClass = crianca.ativo ? 'status-active' : 'status-inactive';
                    
                    html += `
                        <div class="search-result-item" onclick="selecionarCrianca(${crianca.id}, '${crianca.nome_completo.replace(/'/g, "\\'")}')">
                            <div class="search-result-avatar">${iniciais}</div>
                            <div class="search-result-info">
                                <h6 class="mb-1">
                                    <span class="status-indicator ${statusClass}"></span>
                                    ${crianca.nome_completo}
                                </h6>
                                <p class="mb-1">
                                    <strong>Idade:</strong> ${crianca.idade} anos | 
                                    <strong>Responsável:</strong> ${crianca.nome_responsavel}
                                </p>
                                <p class="mb-0 text-sm">
                                    <strong>Telefone:</strong> ${crianca.telefone_principal}
                                    ${crianca.alergia_alimentos ? `| <span class="text-danger">⚠️ Alergia: ${crianca.alergia_alimentos}</span>` : ''}
                                </p>
                            </div>
                            <div class="search-result-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); verDetalhesBusca(${crianca.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                html += `</div>`;
            } else {
                html = `
                    <div class="text-center py-4">
                        <i class="fas fa-search fa-2x text-muted mb-3"></i>
                        <p class="text-muted mb-2">Nenhuma criança encontrada</p>
                        <small class="text-muted">Tente buscar por nome ou responsável</small>
                    </div>
                `;
            }
            
            document.getElementById('searchResults').innerHTML = html;
        })
        .catch(error => {
            console.error('Erro na busca:', error);
            document.getElementById('searchResults').innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erro temporário na busca. Tente novamente.
                </div>
            `;
        });
    }, 400); // Delay aumentado para reduzir requisições
});

// Função para selecionar criança da busca
function selecionarCrianca(criancaId, nomeCrianca) {
    // Fechar modal de busca
    const modal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
    modal.hide();
    
    // Limpar busca
    document.getElementById('quickSearch').value = '';
    document.getElementById('searchResults').innerHTML = '';
    
    // Mostrar mensagem de sucesso
    showTempMessage(`Criança "${nomeCrianca}" selecionada`, 'success');
    
    // Aqui você pode adicionar lógica para redirecionar ou mostrar detalhes
    // Por exemplo, abrir os detalhes da criança
    verDetalhesBusca(criancaId);
}

// Função para ver detalhes da criança da busca
function verDetalhesBusca(criancaId) {
    // Fechar modal de busca
    const modal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
    modal.hide();
    
    // Aqui você pode implementar a busca dos detalhes completos
    fetch(`../ajax/detalhes_crianca.php?id=${criancaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarDetalhesCompletos(data.crianca);
            }
        })
        .catch(error => {
            showTempMessage('Erro ao carregar detalhes', 'danger');
        });
}

// Função para mostrar mensagens temporárias
function showTempMessage(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.main-content').insertBefore(alert, document.querySelector('.main-content').firstChild);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

// Função auxiliar para mostrar detalhes completos
function mostrarDetalhesCompletos(crianca) {
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6>Informações Pessoais</h6>
                <div class="mb-3">
                    <strong>Nome:</strong> ${crianca.nome_completo}<br>
                    <strong>Idade:</strong> ${crianca.idade} anos<br>
                    <strong>Data Nasc.:</strong> ${new Date(crianca.data_nascimento).toLocaleDateString('pt-BR')}<br>
                    <strong>Sexo:</strong> ${crianca.sexo}
                </div>
                
                <h6>Responsável</h6>
                <div class="mb-3">
                    <strong>Nome:</strong> ${crianca.nome_responsavel}<br>
                    <strong>Telefone:</strong> ${crianca.telefone_principal}<br>
                    <strong>Parentesco:</strong> ${crianca.grau_parentesco}
                </div>
            </div>
            
            <div class="col-md-6">
                <h6>Informações de Saúde</h6>
                <div class="mb-3">
                    ${crianca.alergia_alimentos ? `<strong>Alergia Alimentos:</strong> <span class="text-danger">${crianca.alergia_alimentos}</span><br>` : ''}
                    ${crianca.alergia_medicamentos ? `<strong>Alergia Medicamentos:</strong> <span class="text-danger">${crianca.alergia_medicamentos}</span><br>` : ''}
                    ${crianca.observacoes_saude ? `<strong>Observações:</strong> ${crianca.observacoes_saude}` : 'Nenhuma observação'}
                </div>
                
                <h6>Contato Emergencial</h6>
                <div class="mb-3">
                    <strong>Nome:</strong> ${crianca.nome_emergencia}<br>
                    <strong>Telefone:</strong> ${crianca.telefone_emergencia}<br>
                    <strong>Parentesco:</strong> ${crianca.grau_parentesco_emergencia}
                </div>
            </div>
        </div>

    `;
    
    document.getElementById('detailsContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

// Funções auxiliares (placeholder)
function adicionarAEvento(criancaId) {
    alert(`Adicionar criança ${criancaId} a evento - Implementar esta função`);
}

function editarCrianca(criancaId) {
    window.location.href = `cadastro_crianca.php?id=${criancaId}`;
}
    
    // Auto-refresh a cada 30 segundos se estiver em um evento
    <?php if ($evento_id): ?>
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 30000);
    <?php endif; ?>
</script>
</body>
</html>