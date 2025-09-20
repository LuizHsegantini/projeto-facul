<?php
// funcionarios.php - Gerenciamento de funcionarios
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/FuncionariosController.php';

requireLogin();

$controller = new FuncionariosController();
$availableProfiles = $controller->getAvailableProfiles();
$message = '';
$messageType = '';
$currentUser = getCurrentUser();
$canManageFuncionarios = hasPermission('administrador');

function formatDateTime(?string $timestamp): string
{
    if (!$timestamp) {
        return '-';
    }

    $time = strtotime($timestamp);
    if ($time === false) {
        return '-';
    }

    return date('d/m/Y H:i', $time);
}

function buildInitials(string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return '--';
    }

    $parts = preg_split('/\s+/', $name);
    $initials = '';

    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }

        $initials .= strtoupper($part[0]);
        if (strlen($initials) >= 2) {
            break;
        }
    }

    return substr($initials . '--', 0, 2);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int) ($_SESSION['user_id'] ?? 0);

    try {
        switch ($action) {
            case 'create':
                if (!$canManageFuncionarios) {
                    $messageType = 'danger';
                    $message = 'Voce nao tem permissao para cadastrar funcionarios.';
                    break;
                }

                $result = $controller->createFuncionario([
                    'nome_completo' => $_POST['nome_completo'] ?? '',
                    'cpf' => $_POST['cpf'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'cargo' => $_POST['cargo'] ?? '',
                    'login' => $_POST['login'] ?? '',
                    'senha' => $_POST['senha'] ?? '',
                    'perfil' => $_POST['perfil'] ?? '',
                ], $userId);

                $messageType = $result['type'];
                $message = $result['message'];
                break;

            case 'update':
                if (!$canManageFuncionarios) {
                    $messageType = 'danger';
                    $message = 'Voce nao tem permissao para editar funcionarios.';
                    break;
                }

                $id = (int) ($_POST['id'] ?? 0);
                $result = $controller->updateFuncionario($id, [
                    'nome_completo' => $_POST['nome_completo'] ?? '',
                    'cpf' => $_POST['cpf'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'cargo' => $_POST['cargo'] ?? '',
                    'login' => $_POST['login'] ?? '',
                    'senha' => $_POST['senha'] ?? '',
                    'perfil' => $_POST['perfil'] ?? '',
                ], $userId);

                $messageType = $result['type'];
                $message = $result['message'];
                break;

            case 'delete':
                if (!$canManageFuncionarios) {
                    $messageType = 'danger';
                    $message = 'Voce nao tem permissao para remover funcionarios.';
                    break;
                }

                $id = (int) ($_POST['id'] ?? 0);
                $result = $controller->deleteFuncionario($id, $userId);

                $messageType = $result['type'];
                $message = $result['message'];
                break;
        }
    } catch (Throwable $e) {
        error_log('Erro em funcionarios.php: ' . $e->getMessage());
        $messageType = 'danger';
        $message = 'Nao foi possivel concluir a operacao. Tente novamente.';
    }
}

$search = trim($_GET['search'] ?? '');
$perfilFilter = $_GET['perfil'] ?? '';

$listData = $controller->listFuncionarios($search, $perfilFilter);
$funcionarios = $listData['funcionarios'];
$filteredCount = $listData['count'];
$perfilFilter = $listData['perfil'];

$profileCounts = $controller->getProfileCounts();
$totalFuncionarios = array_sum($profileCounts);
$activeProfileCount = 0;
foreach ($profileCounts as $count) {
    if ($count > 0) {
        $activeProfileCount++;
    }
}

$recentFuncionarios = $controller->getRecentFuncionarios();

$currentUserName = $currentUser['nome_completo'] ?? ($currentUser['nome'] ?? '');
$currentUserPerfil = $currentUser['perfil'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionarios - MagicKids</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link  rel="stylesheet" href="assets/css/funcionarios.css">
</head>
<body>
    <div class="sidebar">
        <div>
            <div class="company-info">
                <i class="fas fa-hat-wizard"></i>
                <div class="company-name">MagicKids</div>
                <p class="company-tagline">Centro de Eventos</p>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard_eventos.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a>
                <a class="nav-link" href="eventos.php"><i class="fas fa-calendar-check me-2"></i>Eventos</a>
                <a class="nav-link" href="cadastro_crianca.php"><i class="fas fa-clipboard-list me-2"></i>Cadastrar crianca</a>
                <a class="nav-link" href="criancas.php"><i class="fas fa-children me-2"></i>Criancas</a>
                <a class="nav-link" href="checkin.php"><i class="fas fa-clipboard-check me-2"></i>Check-in</a>
                <a class="nav-link active" href="funcionarios.php"><i class="fas fa-people-group me-2"></i>Funcionarios</a>
                <a class="nav-link" href="logs.php"><i class="fas fa-clipboard-list me-2"></i>Logs</a>
                <a class="nav-link text-warning" href="logout.php"><i class="fas fa-right-from-bracket me-2"></i>Sair</a>
            </nav>
        </div>
        <div class="sidebar-footer text-white">
            <div class="fw-semibold">Logado como</div>
            <div><?php echo htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="text-white-50"><?php echo htmlspecialchars($currentUserPerfil, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>
    <main class="main-content">
        <div class="header-bar">
            <div>
                <h1>Funcionarios</h1>
                <p>Gerencie acessos e perfis da equipe MagicKids.</p>
            </div>
            <?php if ($canManageFuncionarios): ?>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFuncionarioModal">
                    <i class="fas fa-user-plus me-2"></i>Novo funcionario
                </button>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : ($messageType === 'danger' ? 'danger' : 'info'); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-title">Total cadastrados</div>
                    <div class="stat-number"><?php echo $totalFuncionarios; ?></div>
                    <div class="text-muted small">Usuarios ativos no sistema</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card secondary">
                    <div class="stat-title">Resultado do filtro</div>
                    <div class="stat-number"><?php echo $filteredCount; ?></div>
                    <div class="text-muted small">Registros exibidos na lista</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card success">
                    <div class="stat-title">Perfis ativos</div>
                    <div class="stat-number"><?php echo $activeProfileCount; ?></div>
                    <div class="text-muted small">Tipos de perfil com usuarios</div>
                </div>
            </div>
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="get" action="funcionarios.php">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nome, email ou login">
                    </div>
                    <div class="col-md-3">
                        <label for="perfil" class="form-label">Perfil</label>
                        <select class="form-select" id="perfil" name="perfil">
                            <option value="">Todos</option>
                            <?php foreach ($availableProfiles as $profileOption): ?>
                            <option value="<?php echo $profileOption; ?>" <?php echo $perfilFilter === $profileOption ? 'selected' : ''; ?>>
                                <?php echo ucfirst($profileOption); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                        <a href="funcionarios.php" class="btn btn-outline-secondary flex-fill">
                            <i class="fas fa-rotate-left me-2"></i>Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Lista de funcionarios</h5>
                        <?php if ($canManageFuncionarios): ?>
                        <span class="badge bg-light text-dark">Permissoes de edicao habilitadas</span>
                        <?php else: ?>
                        <span class="badge bg-light text-muted">Visualizacao apenas</span>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Funcionario</th>
                                    <th>Cargo</th>
                                    <th>Perfil</th>
                                    <th>Login</th>
                                    <th>CPF</th>
                                    <th>Criado em</th>
                                    <th>Atualizado em</th>
                                    <?php if ($canManageFuncionarios): ?>
                                    <th class="text-end">Acoes</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($funcionarios)): ?>
                                <tr>
                                    <td colspan="<?php echo $canManageFuncionarios ? '8' : '7'; ?>" class="text-center text-muted py-5">
                                        Nenhum funcionario encontrado para os filtros selecionados.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($funcionarios as $funcionario): ?>
                                <?php
                                    $isCurrent = isset($currentUser['id']) && (int) $currentUser['id'] === (int) $funcionario['id'];
                                    $initials = buildInitials($funcionario['nome_completo'] ?? '');
                                    $cargoValue = trim((string) ($funcionario['cargo'] ?? ''));
                                    $cargoDisplay = $cargoValue !== '' ? $cargoValue : 'Sem cargo definido';
                                    $perfilClass = $funcionario['perfil'] ?? '';
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div>
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars($funcionario['nome_completo'], ENT_QUOTES, 'UTF-8'); ?>
                                                    <?php if ($isCurrent): ?>
                                                    <span class="badge bg-warning text-dark ms-2">Voce</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($funcionario['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($cargoDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="profile-badge <?php echo htmlspecialchars($perfilClass, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars(ucfirst($funcionario['perfil']), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($funcionario['login'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($funcionario['cpf'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(formatDateTime($funcionario['data_criacao']), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(formatDateTime($funcionario['data_atualizacao']), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php if ($canManageFuncionarios): ?>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button"
                                                    class="btn btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editFuncionarioModal"
                                                    data-id="<?php echo (int) $funcionario['id']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($funcionario['nome_completo'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-cpf="<?php echo htmlspecialchars($funcionario['cpf'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-email="<?php echo htmlspecialchars($funcionario['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-cargo="<?php echo htmlspecialchars($funcionario['cargo'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-login="<?php echo htmlspecialchars($funcionario['login'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-perfil="<?php echo htmlspecialchars($funcionario['perfil'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteFuncionarioModal"
                                                    data-id="<?php echo (int) $funcionario['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($funcionario['nome_completo'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    <?php echo $isCurrent ? 'disabled' : ''; ?>>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-layer-group me-2 text-primary"></i>Resumo por perfil</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($profileCounts as $perfil => $count): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="profile-badge <?php echo htmlspecialchars($perfil, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(ucfirst($perfil), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <span class="fw-semibold text-dark"><?php echo $count; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-star me-2 text-warning"></i>Novos cadastros</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentFuncionarios)): ?>
                        <p class="text-muted small mb-0">Nenhum cadastro recente encontrado.</p>
                        <?php else: ?>
                        <ul class="list-unstyled recent-list mb-0">
                            <?php foreach ($recentFuncionarios as $recent): ?>
                            <li class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($recent['nome_completo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars(formatDateTime($recent['data_criacao']), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <span class="profile-badge <?php echo htmlspecialchars($recent['perfil'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(ucfirst($recent['perfil']), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php if ($canManageFuncionarios): ?>
    <div class="modal fade" id="createFuncionarioModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i>Novo funcionario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="createNomeCompleto" class="form-label">Nome completo</label>
                            <input type="text" class="form-control" id="createNomeCompleto" name="nome_completo" required>
                        </div>
                        <div class="col-md-3">
                            <label for="createCpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="createCpf" name="cpf" required>
                        </div>
                        <div class="col-md-3">
                            <label for="createPerfil" class="form-label">Perfil</label>
                            <select class="form-select" id="createPerfil" name="perfil" required>
                                <option value="">Selecione</option>
                                <?php foreach ($availableProfiles as $profileOption): ?>
                                <option value="<?php echo $profileOption; ?>"><?php echo ucfirst($profileOption); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="createEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="createEmail" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="createCargo" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="createCargo" name="cargo" placeholder="Opcional">
                        </div>
                        <div class="col-md-6">
                            <label for="createLogin" class="form-label">Login</label>
                            <input type="text" class="form-control" id="createLogin" name="login" required>
                        </div>
                        <div class="col-md-6">
                            <label for="createSenha" class="form-label">Senha inicial</label>
                            <input type="password" class="form-control" id="createSenha" name="senha" minlength="6" required>
                            <div class="form-text">Minimo de 6 caracteres.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar cadastro</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editFuncionarioModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editFuncionarioId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-gear me-2 text-primary"></i>Editar funcionario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editNomeCompleto" class="form-label">Nome completo</label>
                            <input type="text" class="form-control" id="editNomeCompleto" name="nome_completo" required>
                        </div>
                        <div class="col-md-3">
                            <label for="editCpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="editCpf" name="cpf" required>
                        </div>
                        <div class="col-md-3">
                            <label for="editPerfil" class="form-label">Perfil</label>
                            <select class="form-select" id="editPerfil" name="perfil" required>
                                <?php foreach ($availableProfiles as $profileOption): ?>
                                <option value="<?php echo $profileOption; ?>"><?php echo ucfirst($profileOption); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editCargo" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="editCargo" name="cargo" placeholder="Opcional">
                        </div>
                        <div class="col-md-6">
                            <label for="editLogin" class="form-label">Login</label>
                            <input type="text" class="form-control" id="editLogin" name="login" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editSenha" class="form-label">Nova senha</label>
                            <input type="password" class="form-control" id="editSenha" name="senha" minlength="6" placeholder="Deixe em branco para manter">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar alteracoes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteFuncionarioModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteFuncionarioId">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-triangle-exclamation me-2"></i>Remover funcionario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Tem certeza de que deseja remover o funcionario <strong class="funcionario-name"></strong>?</p>
                    <p class="text-muted small mb-0">Esta acao nao pode ser desfeita.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Remover</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = document.getElementById('editFuncionarioModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) {
                        return;
                    }

                    editModal.querySelector('#editFuncionarioId').value = button.getAttribute('data-id') || '';
                    editModal.querySelector('#editNomeCompleto').value = button.getAttribute('data-nome') || '';
                    editModal.querySelector('#editCpf').value = button.getAttribute('data-cpf') || '';
                    editModal.querySelector('#editEmail').value = button.getAttribute('data-email') || '';
                    editModal.querySelector('#editCargo').value = button.getAttribute('data-cargo') || '';
                    editModal.querySelector('#editLogin').value = button.getAttribute('data-login') || '';
                    editModal.querySelector('#editPerfil').value = button.getAttribute('data-perfil') || '';
                    editModal.querySelector('#editSenha').value = '';
                });
            }

            var deleteModal = document.getElementById('deleteFuncionarioModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) {
                        return;
                    }

                    deleteModal.querySelector('#deleteFuncionarioId').value = button.getAttribute('data-id') || '';
                    var nameTarget = deleteModal.querySelector('.funcionario-name');
                    if (nameTarget) {
                        nameTarget.textContent = button.getAttribute('data-name') || '';
                    }
                });
            }
        });
    </script>
</body>
</html>
