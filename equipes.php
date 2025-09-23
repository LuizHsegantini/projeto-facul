<?php
// equipes.php - Gestao de equipes
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/EquipesController.php';

requireLogin();

$controller = new EquipesController();
$currentUser = getCurrentUser();
$canManageEquipes = hasPermission('coordenador') || hasPermission('administrador');
$canRemoverEquipe = hasPermission('administrador');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int) ($_SESSION['user_id'] ?? 0);

    try {
        switch ($action) {
            case 'create':
                if (!$canManageEquipes) {
                    $messageType = 'danger';
                    $message = 'Voce nao tem permissao para criar equipes.';
                    break;
                }
                $result = $controller->create($_POST, $userId);
                $messageType = $result['type'];
                $message = $result['message'];
                break;

            case 'update':
                if (!$canManageEquipes) {
                    $messageType = 'danger';
                    $message = 'Voce nao tem permissao para editar equipes.';
                    break;
                }
                $id = (int) ($_POST['id'] ?? 0);
                $result = $controller->update($id, $_POST, $userId);
                $messageType = $result['type'];
                $message = $result['message'];
                break;

            case 'delete':
                if (!$canRemoverEquipe) {
                    $messageType = 'danger';
                    $message = 'Somente administradores podem remover equipes.';
                    break;
                }
                $id = (int) ($_POST['id'] ?? 0);
                $result = $controller->delete($id, $userId);
                $messageType = $result['type'];
                $message = $result['message'];
                break;

            case 'add_member':
                if (!$canManageEquipes) {
                    $messageType = 'danger';
                    $message = 'Voce nao tem permissao para gerenciar membros.';
                    break;
                }
                $equId = (int) ($_POST['equipe_id'] ?? 0);
                $userToAdd = (int) ($_POST['usuario_id'] ?? 0);
                $result = $controller->addMembro($equId, $userToAdd, $userId);
                $messageType = $result['type'];
                $message = $result['message'];
                break;

            case 'remove_member':
                if (!$canManageEquipes) {
                    $messageType = 'danger';
                    $message = 'Voce nao tem permissao para gerenciar membros.';
                    break;
                }
                $equId = (int) ($_POST['equipe_id'] ?? 0);
                $userToRemove = (int) ($_POST['usuario_id'] ?? 0);
                $result = $controller->removeMembro($equId, $userToRemove, $userId);
                $messageType = $result['type'];
                $message = $result['message'];
                break;
        }
    } catch (Throwable $e) {
        error_log('Erro em equipes.php: ' . $e->getMessage());
        $messageType = 'danger';
        $message = 'Nao foi possivel concluir a operacao. Tente novamente.';
    }
}

$search = trim($_GET['search'] ?? '');
$especialidade = $_GET['especialidade'] ?? '';
$page = (int) ($_GET['page'] ?? 1);
$page = $page > 0 ? $page : 1;

$listData = $controller->index($search, $especialidade, $page, 9);
$equipes = $listData['equipes'];
$totalPages = $listData['pages'];
$currentPage = $listData['current_page'];
$totalEquipes = $listData['total'];

$totalMembros = 0;
foreach ($equipes as $equipeResumo) {
    $totalMembros += (int) ($equipeResumo['total_membros'] ?? 0);
}

$usuarios = $controller->getUsuariosDisponiveis();
$especialidadesDisponiveis = $controller->getEspecialidades();

$currentUserName = $currentUser['nome_completo'] ?? ($currentUser['nome'] ?? '');
$currentUserPerfil = $currentUser['perfil'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipes - MagicKids</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/equipes.css">
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
                <a class="nav-link active" href="equipes.php"><i class="fas fa-people-arrows me-2"></i>Equipes</a>
                <a class="nav-link" href="relatorios.php"><i class="fas fa-chart-pie me-2"></i>Relatorios</a>
                <a class="nav-link" href="logs.php"><i class="fas fa-clipboard-list me-2"></i>Logs</a>
                
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
                <h1 class="h3 mb-1">Equipes</h1>
                <p class="text-muted mb-0">Monte times e acompanhe distribuicao de talentos.</p>
            </div>
            <?php if ($canManageEquipes): ?>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEquipeModal">
                    <i class="fas fa-people-line me-2"></i>Nova equipe
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
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="text-uppercase text-muted small">Total de equipes</div>
                    <div class="display-6 fw-bold"><?php echo (int) $totalEquipes; ?></div>
                    <div class="small text-muted">Times cadastrados</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card accent">
                    <div class="text-uppercase text-muted small">Membros contabilizados</div>
                    <div class="display-6 fw-bold"><?php echo (int) $totalMembros; ?></div>
                    <div class="small text-muted">Usuarios distribuidos</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card success">
                    <div class="text-uppercase text-muted small">Media por equipe</div>
                    <div class="display-6 fw-bold"><?php echo $totalEquipes > 0 ? number_format($totalMembros / $totalEquipes, 1) : '0.0'; ?></div>
                    <div class="small text-muted">Referente apenas aos listados</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="get" action="equipes.php">
                    <div class="col-md-5">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nome ou descricao">
                    </div>
                    <div class="col-md-4">
                        <label for="especialidade" class="form-label">Especialidade</label>
                        <select class="form-select" id="especialidade" name="especialidade">
                            <option value="">Todas</option>
                            <?php foreach ($especialidadesDisponiveis as $esp): ?>
                            <option value="<?php echo htmlspecialchars($esp, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $especialidade === $esp ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($esp, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-search me-2"></i>Filtrar</button>
                        <a href="equipes.php" class="btn btn-outline-secondary flex-fill"><i class="fas fa-rotate-left me-2"></i>Limpar</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="row g-4">
            <?php if (empty($equipes)): ?>
            <div class="col-12">
                <div class="text-center text-muted py-5 border border-dashed rounded-4">Nenhuma equipe encontrada.</div>
            </div>
            <?php else: ?>
            <?php foreach ($equipes as $equipe): ?>
            <?php $membros = $controller->getMembros((int) $equipe['id']); ?>
            <div class="col-xl-4 col-lg-6">
                <div class="team-card h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($equipe['nome'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <div class="tag"><i class="fas fa-star"></i><?php echo htmlspecialchars($equipe['especialidade'] ?? 'Sem especialidade', ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold small text-muted">Capacidade</div>
                            <div class="h5 mb-0"><?php echo (int) ($equipe['capacidade_eventos'] ?? 0); ?></div>
                        </div>
                    </div>
                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($equipe['descricao'] ?? 'Sem descricao registrada.', ENT_QUOTES, 'UTF-8'); ?></p>
                    <div class="mb-3">
                        <div class="fw-semibold mb-2">Membros (<?php echo count($membros); ?>)</div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (empty($membros)): ?>
                            <span class="text-muted small">Nenhum membro associado.</span>
                            <?php else: ?>
                            <?php foreach ($membros as $membro): ?>
                            <span class="member-badge">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($membro['nome_completo'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php if ($canManageEquipes): ?>
                                <button type="button" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#removeMembroModal"
                                    data-equipe="<?php echo (int) $equipe['id']; ?>"
                                    data-equipe-nome="<?php echo htmlspecialchars($equipe['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-usuario="<?php echo (int) $membro['usuario_id']; ?>"
                                    data-usuario-nome="<?php echo htmlspecialchars($membro['nome_completo'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </span>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <?php if ($canManageEquipes): ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editEquipeModal"
                                data-id="<?php echo (int) $equipe['id']; ?>"
                                data-nome="<?php echo htmlspecialchars($equipe['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-especialidade="<?php echo htmlspecialchars($equipe['especialidade'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-capacidade="<?php echo (int) ($equipe['capacidade_eventos'] ?? 0); ?>"
                                data-descricao="<?php echo htmlspecialchars($equipe['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-edit me-1"></i>Editar
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addMembroModal"
                                data-id="<?php echo (int) $equipe['id']; ?>"
                                data-nome="<?php echo htmlspecialchars($equipe['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-user-plus me-1"></i>Membro
                            </button>
                        </div>
                        <?php else: ?>
                        <span class="text-muted small">Visualizacao</span>
                        <?php endif; ?>
                        <?php if ($canRemoverEquipe): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteEquipeModal"
                            data-id="<?php echo (int) $equipe['id']; ?>"
                            data-nome="<?php echo htmlspecialchars($equipe['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-end mt-4">
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo htmlspecialchars('equipes.php?' . http_build_query(array_merge($_GET, ['page' => $i])), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </main>
    <?php if ($canManageEquipes): ?>
    <div class="modal fade" id="createEquipeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-people-line me-2 text-primary"></i>Nova equipe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="createNome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="createNome" name="nome" required>
                        </div>
                        <div class="col-md-6">
                            <label for="createEspecialidade" class="form-label">Especialidade</label>
                            <input type="text" class="form-control" id="createEspecialidade" name="especialidade" list="especialidadeList" placeholder="Multidisciplinar">
                        </div>
                        <div class="col-md-4">
                            <label for="createCapacidade" class="form-label">Capacidade simultanea</label>
                            <input type="number" class="form-control" id="createCapacidade" name="capacidade_eventos" min="1" value="1">
                        </div>
                        <div class="col-md-12">
                            <label for="createDescricao" class="form-label">Descricao</label>
                            <textarea class="form-control" id="createDescricao" name="descricao" rows="3" placeholder="Resumo das atividades da equipe"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editEquipeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editEquipeId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen-to-square me-2 text-primary"></i>Editar equipe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editNome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="editNome" name="nome" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editEspecialidade" class="form-label">Especialidade</label>
                            <input type="text" class="form-control" id="editEspecialidade" name="especialidade" list="especialidadeList">
                        </div>
                        <div class="col-md-4">
                            <label for="editCapacidade" class="form-label">Capacidade simultanea</label>
                            <input type="number" class="form-control" id="editCapacidade" name="capacidade_eventos" min="1" value="1">
                        </div>
                        <div class="col-md-12">
                            <label for="editDescricao" class="form-label">Descricao</label>
                            <textarea class="form-control" id="editDescricao" name="descricao" rows="3"></textarea>
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

    <div class="modal fade" id="addMembroModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post">
                <input type="hidden" name="action" value="add_member">
                <input type="hidden" name="equipe_id" id="addEquipeId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i>Adicionar membro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Selecione um usuario para associar a equipe <strong id="addEquipeNome"></strong>.</p>
                    <div class="mb-3">
                        <label for="addUsuario" class="form-label">Usuario</label>
                        <select class="form-select" id="addUsuario" name="usuario_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?php echo (int) $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nome_completo'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="removeMembroModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post">
                <input type="hidden" name="action" value="remove_member">
                <input type="hidden" name="equipe_id" id="removeEquipeId">
                <input type="hidden" name="usuario_id" id="removeUsuarioId">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-user-minus me-2"></i>Remover membro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Remover <strong id="removeUsuarioNome"></strong> da equipe <strong id="removeEquipeNome"></strong>?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Remover</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($canRemoverEquipe): ?>
    <div class="modal fade" id="deleteEquipeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteEquipeId">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-triangle-exclamation me-2"></i>Remover equipe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Deseja remover a equipe <strong id="deleteEquipeNome"></strong>? Todos os vinculos serao apagados.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Remover</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <datalist id="especialidadeList">
        <?php foreach ($especialidadesDisponiveis as $esp): ?>
        <option value="<?php echo htmlspecialchars($esp, ENT_QUOTES, 'UTF-8'); ?>"></option>
        <?php endforeach; ?>
    </datalist>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = document.getElementById('editEquipeModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) { return; }
                    editModal.querySelector('#editEquipeId').value = button.getAttribute('data-id') || '';
                    editModal.querySelector('#editNome').value = button.getAttribute('data-nome') || '';
                    editModal.querySelector('#editEspecialidade').value = button.getAttribute('data-especialidade') || '';
                    editModal.querySelector('#editCapacidade').value = button.getAttribute('data-capacidade') || 1;
                    editModal.querySelector('#editDescricao').value = button.getAttribute('data-descricao') || '';
                });
            }

            var addModal = document.getElementById('addMembroModal');
            if (addModal) {
                addModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) { return; }
                    addModal.querySelector('#addEquipeId').value = button.getAttribute('data-id') || '';
                    addModal.querySelector('#addEquipeNome').textContent = button.getAttribute('data-nome') || '';
                    var select = addModal.querySelector('#addUsuario');
                    if (select) { select.selectedIndex = 0; }
                });
            }

            var removeModal = document.getElementById('removeMembroModal');
            if (removeModal) {
                removeModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) { return; }
                    removeModal.querySelector('#removeEquipeId').value = button.getAttribute('data-equipe') || '';
                    removeModal.querySelector('#removeEquipeNome').textContent = button.getAttribute('data-equipe-nome') || '';
                    removeModal.querySelector('#removeUsuarioId').value = button.getAttribute('data-usuario') || '';
                    removeModal.querySelector('#removeUsuarioNome').textContent = button.getAttribute('data-usuario-nome') || '';
                });
            }

            var deleteModal = document.getElementById('deleteEquipeModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) { return; }
                    deleteModal.querySelector('#deleteEquipeId').value = button.getAttribute('data-id') || '';
                    deleteModal.querySelector('#deleteEquipeNome').textContent = button.getAttribute('data-nome') || '';
                });
            }
        });
    </script>
</body>
</html>