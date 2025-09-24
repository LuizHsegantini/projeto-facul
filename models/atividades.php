﻿<?php
// atividades.php - Gestao de atividades
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../controllers/AtividadesController.php';

requireLogin();

$controller = new AtividadesController();
$currentUser = getCurrentUser();
$canManageAtividades = hasPermission('coordenador') || hasPermission('administrador');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int) ($_SESSION['user_id'] ?? 0);

    try {
        switch ($action) {
            case 'create':
                if (!$canManageAtividades) {
                    $messageType = 'danger';
                    $message = 'Voce nao tem permissao para criar atividades.';
                    break;
                }

                $result = $controller->create($_POST, $userId);
                $messageType = $result['type'];
                $message = $result['message'];
                break;

            case 'update':
                if (!$canManageAtividades) {
                    $messageType = 'danger';
                    $message = 'Voce nao tem permissao para editar atividades.';
                    break;
                }

                $id = (int) ($_POST['id'] ?? 0);
                $result = $controller->update($id, $_POST, $userId);
                $messageType = $result['type'];
                $message = $result['message'];
                break;

            case 'delete':
                if (!hasPermission('administrador')) {
                    $messageType = 'danger';
                    $message = 'Somente administradores podem remover atividades.';
                    break;
                }

                $id = (int) ($_POST['id'] ?? 0);
                $result = $controller->delete($id, $userId);
                $messageType = $result['type'];
                $message = $result['message'];
                break;

            case 'status':
                if (!$canManageAtividades) {
                    $messageType = 'danger';
                    $message = 'Voce nao tem permissao para alterar status.';
                    break;
                }

                $id = (int) ($_POST['id'] ?? 0);
                $status = $_POST['status'] ?? 'pendente';
                $result = $controller->updateStatus($id, $status, $userId);
                $messageType = $result['type'];
                $message = $result['message'];
                break;
        }
    } catch (Throwable $e) {
        error_log('Erro em atividades.php: ' . $e->getMessage());
        $messageType = 'danger';
        $message = 'Nao foi possivel concluir a operacao. Tente novamente.';
    }
}

$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$eventoFilter = $_GET['evento'] ?? '';
$responsavelFilter = $_GET['responsavel'] ?? '';
$page = (int) ($_GET['page'] ?? 1);
$page = $page > 0 ? $page : 1;

$listData = $controller->index($search, $statusFilter, $eventoFilter, $responsavelFilter, $page, 12);
$atividades = $listData['atividades'];
$totalPages = $listData['pages'];
$currentPage = $listData['current_page'];

$eventos = $controller->getEventos();
$responsaveis = $controller->getResponsaveis();
$tiposAtividade = $controller->getTiposAtividade();
$resumo = $controller->getResumo();

$currentUserName = $currentUser['nome_completo'] ?? ($currentUser['nome'] ?? '');
$currentUserPerfil = $currentUser['perfil'] ?? '';

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
    <title>Atividades - MagicKids</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/atividades.css">
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-birthday-cake fa-6x shape"></i>
        <i class="fas fa-child fa-5x shape"></i>
        <i class="fas fa-heart fa-4x shape"></i>
    </div>

    <!-- Sidebar atualizada -->
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
                <a class="nav-link active" href="atividades.php">
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
        <div class="p-3 border-top border-white-25 text-white-75">
            <div class="fw-semibold">Logado como</div>
            <div><?php echo htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="small">Perfil: <?php echo htmlspecialchars($currentUserPerfil, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </nav>

    <main class="main-content">
        <div class="header-bar">
            <div>
                <h1 class="h3 mb-1">Atividades</h1>
                <p class="text-muted mb-0">Organize tarefas e atribuicoes da equipe MagicKids.</p>
            </div>
            <?php if ($canManageAtividades): ?>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAtividadeModal">
                    <i class="fas fa-plus me-2"></i>Nova atividade
                </button>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : ($messageType === 'danger' ? 'danger' : ($messageType === 'warning' ? 'warning' : 'info')); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-title">Total</div>
                    <div class="stat-number"><?php echo (int) $resumo['total']; ?></div>
                    <div class="text-muted small">Atividades cadastradas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="stat-title">Pendente</div>
                    <div class="stat-number"><?php echo (int) $resumo['pendente']; ?></div>
                    <div class="text-muted small">Aguardando inicio</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-title">Em execucao</div>
                    <div class="stat-number"><?php echo (int) $resumo['em_execucao']; ?></div>
                    <div class="text-muted small">Andamento atual</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="stat-title">Concluidas</div>
                    <div class="stat-number"><?php echo (int) $resumo['concluida']; ?></div>
                    <div class="text-muted small">Finalizadas recentemente</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="get" action="atividades.php">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Titulo ou descricao">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <option value="pendente" <?php echo $statusFilter === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="em_execucao" <?php echo $statusFilter === 'em_execucao' ? 'selected' : ''; ?>>Em execucao</option>
                            <option value="concluida" <?php echo $statusFilter === 'concluida' ? 'selected' : ''; ?>>Concluida</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="evento" class="form-label">Evento</label>
                        <select class="form-select" id="evento" name="evento">
                            <option value="">Todos</option>
                            <?php foreach ($eventos as $evento): ?>
                            <option value="<?php echo (int) $evento['id']; ?>" <?php echo (string) $eventoFilter === (string) $evento['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($evento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="responsavel" class="form-label">Responsavel</label>
                        <select class="form-select" id="responsavel" name="responsavel">
                            <option value="">Todos</option>
                            <?php foreach ($responsaveis as $responsavel): ?>
                            <option value="<?php echo (int) $responsavel['id']; ?>" <?php echo (string) $responsavelFilter === (string) $responsavel['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($responsavel['nome_completo'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search me-2"></i>Filtrar</button>
                        <a href="atividades.php" class="btn btn-outline-secondary"><i class="fas fa-rotate-left me-2"></i>Limpar</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de atividades</h5>
                <span class="badge bg-light text-dark">Resultado: <?php echo count($atividades); ?> registros</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Titulo</th>
                            <th>Evento</th>
                            <th>Responsavel</th>
                            <th>Status</th>
                            <th>Inicio</th>
                            <th>Fim previsto</th>
                            <th>Fim real</th>
                            <?php if ($canManageAtividades): ?>
                            <th class="text-end">Acoes</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($atividades)): ?>
                        <tr>
                            <td colspan="<?php echo $canManageAtividades ? '8' : '7'; ?>" class="text-center text-muted py-5">
                                Nenhuma atividade encontrada.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($atividades as $atividade): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold mb-1"><?php echo htmlspecialchars($atividade['titulo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small">
                                    <?php echo htmlspecialchars($atividade['tipo_atividade'] ?? 'Sem tipo', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($atividade['evento_nome'] ?? 'Nao informado', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($atividade['responsavel_nome'] ?? 'Nao atribuido', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge-status <?php echo htmlspecialchars($atividade['status'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($atividade['status'])), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($atividade['data_inicio'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($atividade['data_fim_prevista'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($atividade['data_fim_real'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <?php if ($canManageAtividades): ?>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAtividadeModal"
                                        data-id="<?php echo (int) $atividade['id']; ?>"
                                        data-titulo="<?php echo htmlspecialchars($atividade['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-tipo="<?php echo htmlspecialchars($atividade['tipo_atividade'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-descricao="<?php echo htmlspecialchars($atividade['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-material="<?php echo htmlspecialchars($atividade['material_necessario'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-publico="<?php echo htmlspecialchars($atividade['publico_alvo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-evento="<?php echo (int) $atividade['evento_id']; ?>"
                                        data-responsavel="<?php echo (int) ($atividade['responsavel_id'] ?? 0); ?>"
                                        data-status="<?php echo htmlspecialchars($atividade['status'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-inicio="<?php echo htmlspecialchars($atividade['data_inicio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-prevista="<?php echo htmlspecialchars($atividade['data_fim_prevista'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-real="<?php echo htmlspecialchars($atividade['data_fim_real'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#statusAtividadeModal"
                                        data-id="<?php echo (int) $atividade['id']; ?>"
                                        data-status="<?php echo htmlspecialchars($atividade['status'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                    <?php if (hasPermission('administrador')): ?>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAtividadeModal"
                                        data-id="<?php echo (int) $atividade['id']; ?>"
                                        data-nome="<?php echo htmlspecialchars($atividade['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white border-0">
                <nav>
                    <ul class="pagination justify-content-end mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo htmlspecialchars('atividades.php?' . http_build_query(array_merge($_GET, ['page' => $i])), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php if ($canManageAtividades): ?>
<!-- Modais completos -->
<div class="modal fade" id="createAtividadeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Atividade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form method="post" action="atividades.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="titulo" class="form-label">Título *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo_atividade" class="form-label">Tipo de Atividade *</label>
                            <input type="text" class="form-control" id="tipo_atividade" name="tipo_atividade" list="tipoAtividadeList" required>
                        </div>
                        <div class="col-12">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="evento_id" class="form-label">Evento *</label>
                            <select class="form-select" id="evento_id" name="evento_id" required>
                                <option value="">Selecione um evento</option>
                                <?php foreach ($eventos as $evento): ?>
                                <option value="<?php echo (int) $evento['id']; ?>">
                                    <?php echo htmlspecialchars($evento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="responsavel_id" class="form-label">Responsável</label>
                            <select class="form-select" id="responsavel_id" name="responsavel_id">
                                <option value="">Não atribuído</option>
                                <?php foreach ($responsaveis as $responsavel): ?>
                                <option value="<?php echo (int) $responsavel['id']; ?>">
                                    <?php echo htmlspecialchars($responsavel['nome_completo'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="data_inicio" class="form-label">Data de Início</label>
                            <input type="datetime-local" class="form-control" id="data_inicio" name="data_inicio">
                        </div>
                        <div class="col-md-6">
                            <label for="data_fim_prevista" class="form-label">Data Fim Prevista</label>
                            <input type="datetime-local" class="form-control" id="data_fim_prevista" name="data_fim_prevista">
                        </div>
                        <div class="col-12">
                            <label for="material_necessario" class="form-label">Material Necessário</label>
                            <textarea class="form-control" id="material_necessario" name="material_necessario" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="publico_alvo" class="form-label">Público-Alvo</label>
                            <input type="text" class="form-control" id="publico_alvo" name="publico_alvo">
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pendente">Pendente</option>
                                <option value="em_execucao">Em execução</option>
                                <option value="concluida">Concluída</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Atividade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editAtividadeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Atividade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form method="post" action="atividades.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="editAtividadeId" name="id">
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editTitulo" class="form-label">Título *</label>
                            <input type="text" class="form-control" id="editTitulo" name="titulo" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editTipo" class="form-label">Tipo de Atividade *</label>
                            <input type="text" class="form-control" id="editTipo" name="tipo_atividade" list="tipoAtividadeList" required>
                        </div>
                        <div class="col-12">
                            <label for="editDescricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="editDescricao" name="descricao" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="editEvento" class="form-label">Evento *</label>
                            <select class="form-select" id="editEvento" name="evento_id" required>
                                <option value="">Selecione um evento</option>
                                <?php foreach ($eventos as $evento): ?>
                                <option value="<?php echo (int) $evento['id']; ?>">
                                    <?php echo htmlspecialchars($evento['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editResponsavel" class="form-label">Responsável</label>
                            <select class="form-select" id="editResponsavel" name="responsavel_id">
                                <option value="">Não atribuído</option>
                                <?php foreach ($responsaveis as $responsavel): ?>
                                <option value="<?php echo (int) $responsavel['id']; ?>">
                                    <?php echo htmlspecialchars($responsavel['nome_completo'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="editInicio" class="form-label">Data de Início</label>
                            <input type="datetime-local" class="form-control" id="editInicio" name="data_inicio">
                        </div>
                        <div class="col-md-4">
                            <label for="editPrevista" class="form-label">Data Fim Prevista</label>
                            <input type="datetime-local" class="form-control" id="editPrevista" name="data_fim_prevista">
                        </div>
                        <div class="col-md-4">
                            <label for="editReal" class="form-label">Data Fim Real</label>
                            <input type="datetime-local" class="form-control" id="editReal" name="data_fim_real">
                        </div>
                        <div class="col-12">
                            <label for="editMaterial" class="form-label">Material Necessário</label>
                            <textarea class="form-control" id="editMaterial" name="material_necessario" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="editPublico" class="form-label">Público-Alvo</label>
                            <input type="text" class="form-control" id="editPublico" name="publico_alvo">
                        </div>
                        <div class="col-md-6">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-select" id="editStatus" name="status">
                                <option value="pendente">Pendente</option>
                                <option value="em_execucao">Em execução</option>
                                <option value="concluida">Concluída</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atualizar Atividade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="statusAtividadeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alterar Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form method="post" action="atividades.php">
                <input type="hidden" name="action" value="status">
                <input type="hidden" id="statusAtividadeId" name="id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="statusAtividadeSelect" class="form-label">Novo Status</label>
                        <select class="form-select" id="statusAtividadeSelect" name="status" required>
                            <option value="pendente">Pendente</option>
                            <option value="em_execucao">Em execução</option>
                            <option value="concluida">Concluída</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atualizar Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (hasPermission('administrador')): ?>
<div class="modal fade" id="deleteAtividadeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form method="post" action="atividades.php">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="deleteAtividadeId" name="id">
                
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a atividade <strong class="atividade-nome"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir Atividade</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>
    <datalist id="tipoAtividadeList">
        <?php foreach ($tiposAtividade as $tipo): ?>
        <option value="<?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?>"></option>
        <?php endforeach; ?>
    </datalist>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script JavaScript permanece igual
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = document.getElementById('editAtividadeModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) { return; }

                    editModal.querySelector('#editAtividadeId').value = button.getAttribute('data-id') || '';
                    editModal.querySelector('#editTitulo').value = button.getAttribute('data-titulo') || '';
                    editModal.querySelector('#editTipo').value = button.getAttribute('data-tipo') || '';
                    editModal.querySelector('#editDescricao').value = button.getAttribute('data-descricao') || '';
                    editModal.querySelector('#editMaterial').value = button.getAttribute('data-material') || '';
                    editModal.querySelector('#editPublico').value = button.getAttribute('data-publico') || '';
                    editModal.querySelector('#editEvento').value = button.getAttribute('data-evento') || '';
                    editModal.querySelector('#editResponsavel').value = button.getAttribute('data-responsavel') || '';
                    editModal.querySelector('#editStatus').value = button.getAttribute('data-status') || 'pendente';
                    editModal.querySelector('#editInicio').value = button.getAttribute('data-inicio') || '';
                    editModal.querySelector('#editPrevista').value = button.getAttribute('data-prevista') || '';
                    editModal.querySelector('#editReal').value = button.getAttribute('data-real') || '';
                });
            }

            var statusModal = document.getElementById('statusAtividadeModal');
            if (statusModal) {
                statusModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) { return; }

                    statusModal.querySelector('#statusAtividadeId').value = button.getAttribute('data-id') || '';
                    statusModal.querySelector('#statusAtividadeSelect').value = button.getAttribute('data-status') || 'pendente';
                });
            }

            var deleteModal = document.getElementById('deleteAtividadeModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) { return; }

                    deleteModal.querySelector('#deleteAtividadeId').value = button.getAttribute('data-id') || '';
                    var nameTarget = deleteModal.querySelector('.atividade-nome');
                    if (nameTarget) {
                        nameTarget.textContent = button.getAttribute('data-nome') || '';
                    }
                });
            }
        });
    </script>
</body>
</html>