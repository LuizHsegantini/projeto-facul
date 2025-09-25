<?php
// profile.php - Perfil do usuário com dados dinâmicos - CORRIGIDO
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../controllers/ProfileController.php';

// Verificar se o usuário está logado
requireLogin();

$profileController = new ProfileController();
$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Processar ações do formulário
$message = '';
$messageType = '';

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $errors = $profileController->validateProfileData($_POST + ['user_id' => $userId]);
                if (empty($errors)) {
                    if ($profileController->updateProfile($userId, $_POST)) {
                        $message = 'Perfil atualizado com sucesso!';
                        $messageType = 'success';
                        // Recarregar dados do usuário
                        $currentUser = getCurrentUser();
                    } else {
                        $message = 'Erro ao atualizar perfil.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = implode('<br>', $errors);
                    $messageType = 'danger';
                }
                break;
                
            case 'change_password':
                $errors = $profileController->validatePassword($_POST['new_password'], $_POST['confirm_password']);
                if (empty($errors)) {
                    $result = $profileController->changePassword($userId, $_POST['current_password'], $_POST['new_password']);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'danger';
                } else {
                    $message = implode('<br>', $errors);
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Buscar dados do perfil
$userProfile = $profileController->getUserProfile($userId);
$userStats = $profileController->getUserStats($userId);
$userActivities = $profileController->getUserActivities($userId, 5);
$userTeams = $profileController->getUserTeams($userId);
$recentLogs = $profileController->getRecentLogs($userId, 5);

if (!$userProfile) {
    die('Erro: Perfil não encontrado.');
}

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

// Função para gerar iniciais do avatar
function getInitials($name) {
    $names = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($names, 0, 2) as $n) {
        $initials .= strtoupper(substr($n, 0, 1));
    }
    return $initials;
}

// Função para formatar perfil
function formatPerfil($perfil) {
    $perfis = [
        'administrador' => 'Administrador',
        'coordenador' => 'Coordenador',
        'animador' => 'Animador',
        'monitor' => 'Monitor',
        'auxiliar' => 'Auxiliar'
    ];
    return $perfis[$perfil] ?? ucfirst($perfil);
}

// Função para badge de perfil
function getPerfilBadgeClass($perfil) {
    $classes = [
        'administrador' => 'bg-danger',
        'coordenador' => 'bg-primary',
        'animador' => 'bg-success',
        'monitor' => 'bg-info',
        'auxiliar' => 'bg-warning'
    ];
    return $classes[$perfil] ?? 'bg-secondary';
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - MagicKids Eventos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard_eventos.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-user-circle fa-6x shape"></i>
        <i class="fas fa-star fa-5x shape"></i>
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
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header-bar d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1 welcome-text">Meu Perfil</h2>
                <p class="text-muted mb-0"><i class="fas fa-user me-2"></i>Gerencie suas informações pessoais e configurações</p>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-outline-secondary me-2" onclick="window.location.href='dashboard_eventos.php'">
                    <i class="fas fa-arrow-left me-2"></i>Voltar ao Dashboard
                </button>
                <div class="user-avatar">
                    <?php echo getInitials($currentUser['nome_completo']); ?>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Overview -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="user-avatar mb-3 mx-auto" style="width: 120px; height: 120px; font-size: 2.5rem;">
                            <?php echo getInitials($userProfile['nome_completo']); ?>
                        </div>
                        <h3 class="mb-1"><?php echo htmlspecialchars($userProfile['nome_completo']); ?></h3>
                        <p class="mb-2">
                            <span class="badge <?php echo getPerfilBadgeClass($userProfile['perfil']); ?>">
                                <?php echo formatPerfil($userProfile['perfil']); ?>
                            </span>
                        </p>
                        <p class="mb-2 mt-2 text-muted"><?php echo htmlspecialchars($userProfile['cargo'] ?? 'Cargo não informado'); ?></p>
                        <small class="text-muted">Membro desde: <?php echo date('d/m/Y', strtotime($userProfile['data_criacao'])); ?></small>
                    </div>
                    <hr>
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Informações de Contato</h6>
                        <div class="mb-2">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <small><?php echo htmlspecialchars($userProfile['email']); ?></small>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-id-card text-primary me-2"></i>
                            <small><?php echo htmlspecialchars($userProfile['cpf']); ?></small>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-user text-primary me-2"></i>
                            <small>Login: <?php echo htmlspecialchars($userProfile['login']); ?></small>
                        </div>
                        <?php if ($userStats['ultimo_login']): ?>
                        <div class="mb-2">
                            <i class="fas fa-calendar text-primary me-2"></i>
                            <small>Último acesso: <?php echo date('d/m/Y H:i', strtotime($userStats['ultimo_login'])); ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            Estatísticas Pessoais
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="stat-card primary mb-3">
                            <div class="stat-number"><?php echo $userStats['total_logs']; ?></div>
                            <div class="stat-label">Logs de Atividade</div>
                        </div>
                        <div class="stat-card success mb-3">
                            <div class="stat-number"><?php echo $userStats['total_equipes']; ?></div>
                            <div class="stat-label">Equipes Participando</div>
                        </div>
                        <div class="stat-card info mb-3">
                            <div class="stat-number"><?php echo $userStats['total_atividades']; ?></div>
                            <div class="stat-label">Atividades Atribuídas</div>
                        </div>
                        <div class="stat-card warning mb-0">
                            <div class="stat-number"><?php echo $userStats['total_eventos_coordenados']; ?></div>
                            <div class="stat-label">Eventos Coordenados</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-info" type="button" role="tab">
                                    <i class="fas fa-user me-2"></i>Informações Pessoais
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security-info" type="button" role="tab">
                                    <i class="fas fa-lock me-2"></i>Segurança
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity-info" type="button" role="tab">
                                    <i class="fas fa-history me-2"></i>Atividades
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teams-info" type="button" role="tab">
                                    <i class="fas fa-users me-2"></i>Equipes
                                </button>
                            </li>
                        </ul>

                        <!-- Tab content -->
                        <div class="tab-content" id="profileTabContent">
                            <!-- Profile Info Tab -->
                            <div class="tab-pane fade show active" id="profile-info" role="tabpanel">
                                <form method="POST" id="profileForm" class="mt-4">
                                    <input type="hidden" name="action" value="update_profile">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nome_completo" class="form-label">Nome Completo</label>
                                            <input type="text" class="form-control" id="nome_completo" name="nome_completo" 
                                                   value="<?php echo htmlspecialchars($userProfile['nome_completo']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($userProfile['email']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="cargo" class="form-label">Cargo</label>
                                            <input type="text" class="form-control" id="cargo" name="cargo" 
                                                   value="<?php echo htmlspecialchars($userProfile['cargo'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="perfil" class="form-label">Perfil</label>
                                            <input type="text" class="form-control" id="perfil" 
                                                   value="<?php echo formatPerfil($userProfile['perfil']); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="cpf" class="form-label">CPF</label>
                                            <input type="text" class="form-control" id="cpf" 
                                                   value="<?php echo htmlspecialchars($userProfile['cpf']); ?>" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="login" class="form-label">Login</label>
                                            <input type="text" class="form-control" id="login" 
                                                   value="<?php echo htmlspecialchars($userProfile['login']); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='dashboard_eventos.php'">
                                            <i class="fas fa-arrow-left me-2"></i>Voltar ao Dashboard
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Salvar Alterações
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Security Tab -->
                            <div class="tab-pane fade" id="security-info" role="tabpanel">
                                <form method="POST" id="passwordForm" class="mt-4">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Por segurança, confirme sua senha atual antes de definir uma nova.
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="current_password" class="form-label">Senha Atual</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="new_password" class="form-label">Nova Senha</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-lock me-1"></i>
                                            A senha deve ter pelo menos 6 caracteres.
                                        </small>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-outline-secondary" onclick="this.form.reset()">
                                            <i class="fas fa-times me-2"></i>Limpar Campos
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-key me-2"></i>Alterar Senha
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Activity Tab -->
                            <div class="tab-pane fade" id="activity-info" role="tabpanel">
                                <div class="mt-4">
                                    <h6 class="mb-3">Minhas Atividades Atribuídas</h6>
                                    <?php if (empty($userActivities)): ?>
                                        <p class="text-muted">Nenhuma atividade atribuída no momento.</p>
                                    <?php else: ?>
                                        <?php foreach ($userActivities as $atividade): ?>
                                        <div class="card mb-3" style="border-left: 4px solid var(--primary-color);">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($atividade['titulo']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($atividade['evento_nome'] ?? 'Evento não especificado'); ?></small>
                                                    </div>
                                                    <span class="badge bg-<?php echo $atividade['status'] === 'concluida' ? 'success' : ($atividade['status'] === 'em_execucao' ? 'primary' : 'warning'); ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $atividade['status'])); ?>
                                                    </span>
                                                </div>
                                                <?php if ($atividade['data_inicio'] || $atividade['data_fim_prevista']): ?>
                                                <p class="mb-1 mt-2">
                                                    <small>
                                                        <?php if ($atividade['data_inicio']): ?>
                                                            Início: <?php echo date('d/m/Y', strtotime($atividade['data_inicio'])); ?>
                                                        <?php endif; ?>
                                                        <?php if ($atividade['data_fim_prevista']): ?>
                                                            | Prazo: <?php echo date('d/m/Y', strtotime($atividade['data_fim_prevista'])); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <hr class="my-4">

                                    <h6 class="mb-3">Logs Recentes</h6>
                                    <?php if (empty($recentLogs)): ?>
                                        <p class="text-muted">Nenhuma atividade recente encontrada.</p>
                                    <?php else: ?>
                                        <?php foreach ($recentLogs as $log): ?>
                                        <div class="card mb-2" style="border-left: 3px solid var(--info-color);">
                                            <div class="card-body py-2">
                                                <div class="d-flex justify-content-between">
                                                    <small><strong><?php echo htmlspecialchars($log['acao']); ?></strong></small>
                                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($log['data_criacao'])); ?></small>
                                                </div>
                                                <?php if ($log['tabela_afetada']): ?>
                                                <small class="text-muted">Tabela: <?php echo htmlspecialchars($log['tabela_afetada']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if (hasUserPermission('logs')): ?>
                                    <div class="text-center mt-3">
                                        <a href="logs.php?user=<?php echo $userId; ?>" class="btn btn-outline-primary btn-sm">
                                            Ver Todos os Logs
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Teams Tab -->
                            <div class="tab-pane fade" id="teams-info" role="tabpanel">
                                <div class="mt-4">
                                    <h6 class="mb-3">Equipes que Participo</h6>
                                    
                                    <?php if (empty($userTeams)): ?>
                                        <p class="text-muted">Você não participa de nenhuma equipe no momento.</p>
                                    <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($userTeams as $equipe): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary">
                                                        <i class="fas fa-<?php 
                                                            $icons = [
                                                                'Animação' => 'gamepad',
                                                                'Recreação' => 'running',
                                                                'Culinária' => 'utensils',
                                                                'Segurança' => 'shield-alt',
                                                                'Limpeza' => 'broom',
                                                                'Arte' => 'palette',
                                                                'Música' => 'music',
                                                                'Teatro' => 'theater-masks',
                                                                'Esportes' => 'basketball-ball',
                                                                'Multidisciplinar' => 'users'
                                                            ];
                                                            echo $icons[$equipe['especialidade']] ?? 'users';
                                                        ?> me-2"></i>
                                                        <?php echo htmlspecialchars($equipe['nome']); ?>
                                                    </h6>
                                                    <p class="card-text">
                                                        <small class="text-muted">Especialidade: <?php echo htmlspecialchars($equipe['especialidade']); ?></small>
                                                    </p>
                                                    <span class="badge" style="background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white;">
                                                        Membro desde: <?php echo date('d/m/Y', strtotime($equipe['data_entrada'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (hasUserPermission('equipes')): ?>
                                    <div class="text-center mt-3">
                                        <a href="equipes.php" class="btn btn-outline-primary">
                                            <i class="fas fa-users me-2"></i>Ver Todas as Equipes
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Logout (usando o mesmo do dashboard) -->
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout.js"></script>
    <script>
        // Prevenir reset automático dos formulários
        document.addEventListener('DOMContentLoaded', function() {
            // Salvar valores originais
            const profileForm = document.getElementById('profileForm');
            const passwordForm = document.getElementById('passwordForm');
            
            // Desabilitar autocomplete para evitar reset automático
            if (profileForm) {
                profileForm.setAttribute('autocomplete', 'off');
                
                // Prevenir reset do formulário
                profileForm.addEventListener('reset', function(e) {
                    e.preventDefault();
                    return false;
                });
                
                // Garantir que dados sejam mantidos durante o envio
                profileForm.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
                    submitBtn.disabled = true;
                    
                    // Não prevenir o envio - deixar o PHP processar
                    return true;
                });
            }
            
            if (passwordForm) {
                passwordForm.setAttribute('autocomplete', 'off');
                
                // Validação de senha em tempo real
                const newPassword = document.getElementById('new_password');
                const confirmPassword = document.getElementById('confirm_password');
                
                function validatePasswords() {
                    if (newPassword.value && confirmPassword.value) {
                        if (newPassword.value === confirmPassword.value) {
                            confirmPassword.classList.remove('is-invalid');
                            confirmPassword.classList.add('is-valid');
                            return true;
                        } else {
                            confirmPassword.classList.remove('is-valid');
                            confirmPassword.classList.add('is-invalid');
                            return false;
                        }
                    }
                    return false;
                }
                
                if (confirmPassword) {
                    confirmPassword.addEventListener('input', validatePasswords);
                    newPassword.addEventListener('input', validatePasswords);
                }
                
                passwordForm.addEventListener('submit', function(e) {
                    const currentPassword = document.getElementById('current_password').value;
                    const newPass = newPassword.value;
                    const confirmPass = confirmPassword.value;
                    
                    if (!currentPassword) {
                        e.preventDefault();
                        alert('Por favor, informe sua senha atual');
                        return false;
                    }
                    
                    if (newPass !== confirmPass) {
                        e.preventDefault();
                        alert('As senhas não coincidem!');
                        return false;
                    }
                    
                    if (newPass.length < 6) {
                        e.preventDefault();
                        alert('A senha deve ter pelo menos 6 caracteres!');
                        return false;
                    }
                    
                    if (!confirm('Tem certeza que deseja alterar sua senha?')) {
                        e.preventDefault();
                        return false;
                    }
                    
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Alterando...';
                    submitBtn.disabled = true;
                    
                    return true;
                });
            }
        });
        
        // Auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-success');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    if (bsAlert) {
                        bsAlert.close();
                    }
                }, 5000);
            });
        });
        
        // Avatar update em tempo real
        document.getElementById('nome_completo').addEventListener('input', function() {
            const name = this.value.trim();
            const avatars = document.querySelectorAll('.user-avatar');
            
            if (name) {
                const initials = name.split(' ')
                    .map(n => n.charAt(0))
                    .join('')
                    .substring(0, 2)
                    .toUpperCase();
                
                avatars.forEach(avatar => {
                    if (!avatar.querySelector('img')) { // Se não tiver imagem
                        avatar.textContent = initials;
                    }
                });
            }
        });
        
        // Validação de email em tempo real
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (email) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
        
        // Validação de campo obrigatório
        document.getElementById('cargo').addEventListener('blur', function() {
            const cargo = this.value.trim();
            
            if (!cargo) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
        
        // Tab navigation com URL hash
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                const tabButton = document.querySelector(`button[data-bs-target="${hash}"]`);
                if (tabButton) {
                    const tab = new bootstrap.Tab(tabButton);
                    tab.show();
                }
            }
        });
        
        // Update URL hash quando trocar de tab
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(button => {
            button.addEventListener('shown.bs.tab', function(e) {
                const targetHash = e.target.getAttribute('data-bs-target');
                if (targetHash) {
                    window.history.replaceState(null, null, targetHash);
                }
            });
        });
        
        // Indicador de força da senha
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            let strengthText = document.getElementById('password-strength');
            
            if (!strengthText) {
                strengthText = document.createElement('div');
                strengthText.id = 'password-strength';
                strengthText.className = 'small mt-1';
                this.parentNode.appendChild(strengthText);
            }
            
            let strength = 0;
            let strengthLabel = '';
            let strengthClass = '';
            
            if (password.length >= 6) strength += 1;
            if (password.match(/[a-z]/)) strength += 1;
            if (password.match(/[A-Z]/)) strength += 1;
            if (password.match(/[0-9]/)) strength += 1;
            if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
            
            switch(strength) {
                case 0:
                case 1:
                    strengthLabel = 'Muito fraca';
                    strengthClass = 'text-danger';
                    break;
                case 2:
                    strengthLabel = 'Fraca';
                    strengthClass = 'text-warning';
                    break;
                case 3:
                    strengthLabel = 'Média';
                    strengthClass = 'text-info';
                    break;
                case 4:
                    strengthLabel = 'Forte';
                    strengthClass = 'text-success';
                    break;
                case 5:
                    strengthLabel = 'Muito forte';
                    strengthClass = 'text-success fw-bold';
                    break;
            }
            
            strengthText.textContent = password ? `Força da senha: ${strengthLabel}` : '';
            strengthText.className = `small mt-1 ${strengthClass}`;
        });
        
        // Hover effects nas stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>