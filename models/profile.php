<?php
// profile.php - Perfil do usuário com dados dinâmicos
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
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-user-circle fa-6x shape"></i>
        <i class="fas fa-star fa-5x shape"></i>
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
                <a class="nav-link" href="dashboard_eventos.php">
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
            <li class="nav-item">
                <a class="nav-link active" href="profile.php">
                    <i class="fas fa-user me-2"></i>Perfil
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header-bar d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Meu Perfil</h2>
                <p class="text-muted mb-0">Gerencie suas informações pessoais e configurações</p>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-outline-secondary me-2" onclick="window.history.back()">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </button>
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
                    <div class="profile-header">
                        <div class="user-avatar">
                            <?php echo getInitials($userProfile['nome_completo']); ?>
                        </div>
                        <h3 class="mb-1"><?php echo htmlspecialchars($userProfile['nome_completo']); ?></h3>
                        <p class="mb-0">
                            <span class="badge <?php echo getPerfilBadgeClass($userProfile['perfil']); ?>">
                                <?php echo formatPerfil($userProfile['perfil']); ?>
                            </span>
                        </p>
                        <p class="mb-2 mt-2"><?php echo htmlspecialchars($userProfile['cargo'] ?? 'Cargo não informado'); ?></p>
                        <small>Membro desde: <?php echo date('d/m/Y', strtotime($userProfile['data_criacao'])); ?></small>
                    </div>
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
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $userStats['total_logs']; ?></div>
                            <div class="stat-label">Logs de Atividade</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $userStats['total_equipes']; ?></div>
                            <div class="stat-label">Equipes Participando</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $userStats['total_atividades']; ?></div>
                            <div class="stat-label">Atividades Atribuídas</div>
                        </div>
                        <div class="stat-card">
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
                                <form method="POST" id="profileForm">
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
                                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                                            <i class="fas fa-times me-2"></i>Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Salvar Alterações
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Security Tab -->
                            <div class="tab-pane fade" id="security-info" role="tabpanel">
                                <form method="POST" id="passwordForm">
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
                                            <i class="fas fa-times me-2"></i>Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-key me-2"></i>Alterar Senha
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Activity Tab -->
                            <div class="tab-pane fade" id="activity-info" role="tabpanel">
                                <h6 class="mb-3">Minhas Atividades Atribuídas</h6>
                                <?php if (empty($userActivities)): ?>
                                    <p class="text-muted">Nenhuma atividade atribuída no momento.</p>
                                <?php else: ?>
                                    <?php foreach ($userActivities as $atividade): ?>
                                    <div class="activity-item">
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
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <hr class="my-4">

                                <h6 class="mb-3">Logs Recentes</h6>
                                <?php if (empty($recentLogs)): ?>
                                    <p class="text-muted">Nenhuma atividade recente encontrada.</p>
                                <?php else: ?>
                                    <?php foreach ($recentLogs as $log): ?>
                                    <div class="recent-log">
                                        <div class="d-flex justify-content-between">
                                            <small><strong><?php echo htmlspecialchars($log['acao']); ?></strong></small>
                                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($log['data_criacao'])); ?></small>
                                        </div>
                                        <?php if ($log['tabela_afetada']): ?>
                                        <small class="text-muted">Tabela: <?php echo htmlspecialchars($log['tabela_afetada']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <div class="text-center mt-3">
                                    <a href="logs.php?user=<?php echo $userId; ?>" class="btn btn-outline-primary btn-sm">
                                        Ver Todos os Logs
                                    </a>
                                </div>
                            </div>

                            <!-- Teams Tab -->
                            <div class="tab-pane fade" id="teams-info" role="tabpanel">
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
                                                <div class="team-badge">
                                                    Membro desde: <?php echo date('d/m/Y', strtotime($equipe['data_entrada'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <div class="text-center mt-3">
                                    <a href="equipes.php" class="btn btn-outline-primary">
                                        <i class="fas fa-users me-2"></i>Ver Todas as Equipes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and submission
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
            submitBtn.disabled = true;
            
            // Allow form to submit normally - PHP will handle the processing
        });

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('As senhas não coincidem!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres!');
                return false;
            }
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Alterando...';
            submitBtn.disabled = true;
            
            // Allow form to submit normally - PHP will handle the processing
        });

        // Auto-generate avatar initials
        function updateAvatarInitials() {
            const nameField = document.getElementById('nome_completo');
            const avatar = document.querySelector('.user-avatar');
            
            nameField.addEventListener('input', function() {
                const name = this.value.trim();
                if (name) {
                    const initials = name.split(' ')
                        .map(n => n.charAt(0))
                        .join('')
                        .substring(0, 2)
                        .toUpperCase();
                    avatar.textContent = initials;
                }
            });
        }

        // Initialize avatar functionality
        updateAvatarInitials();

        // Add floating animation to stat cards
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            card.addEventListener('mouseenter', function() {
                this.style.animation = `float 2s ease-in-out infinite`;
                this.style.animationDelay = `${index * 0.2}s`;
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.animation = '';
            });
        });

        // Real-time form validation
        function validateForm() {
            const email = document.getElementById('email').value;
            const nome = document.getElementById('nome_completo').value;
            const cargo = document.getElementById('cargo').value;
            
            let isValid = true;
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('email').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('email').classList.remove('is-invalid');
                document.getElementById('email').classList.add('is-valid');
            }
            
            // Name validation
            if (nome.trim().length < 3) {
                document.getElementById('nome_completo').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('nome_completo').classList.remove('is-invalid');
                document.getElementById('nome_completo').classList.add('is-valid');
            }
            
            // Cargo validation
            if (cargo.trim().length === 0) {
                document.getElementById('cargo').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('cargo').classList.remove('is-invalid');
                document.getElementById('cargo').classList.add('is-valid');
            }
            
            return isValid;
        }
        
        // Add validation listeners
        ['nome_completo', 'email', 'cargo'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('blur', validateForm);
            }
        });
        
        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthIndicator = document.getElementById('password-strength') || createPasswordStrengthIndicator();
            
            let strength = 0;
            let strengthText = '';
            let strengthClass = '';
            
            if (password.length >= 6) strength += 1;
            if (password.match(/[a-z]/)) strength += 1;
            if (password.match(/[A-Z]/)) strength += 1;
            if (password.match(/[0-9]/)) strength += 1;
            if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
            
            switch(strength) {
                case 0:
                case 1:
                    strengthText = 'Muito fraca';
                    strengthClass = 'text-danger';
                    break;
                case 2:
                    strengthText = 'Fraca';
                    strengthClass = 'text-warning';
                    break;
                case 3:
                    strengthText = 'Média';
                    strengthClass = 'text-info';
                    break;
                case 4:
                    strengthText = 'Forte';
                    strengthClass = 'text-success';
                    break;
                case 5:
                    strengthText = 'Muito forte';
                    strengthClass = 'text-success fw-bold';
                    break;
            }
            
            strengthIndicator.textContent = `Força da senha: ${strengthText}`;
            strengthIndicator.className = `small ${strengthClass}`;
        });
        
        function createPasswordStrengthIndicator() {
            const indicator = document.createElement('div');
            indicator.id = 'password-strength';
            indicator.className = 'small text-muted mt-1';
            document.getElementById('new_password').parentNode.appendChild(indicator);
            return indicator;
        }
        
        // Tab switching with URL hash
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash;
            if (hash) {
                const tabButton = document.querySelector(`button[data-bs-target="${hash}"]`);
                if (tabButton) {
                    tabButton.click();
                }
            }
        });
        
        // Update URL hash when tab changes
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(button => {
            button.addEventListener('shown.bs.tab', function(e) {
                const targetHash = e.target.getAttribute('data-bs-target');
                window.history.replaceState(null, null, targetHash);
            });
        });
        
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-info)');
            alerts.forEach(alert => {
                if (alert.classList.contains('alert-success')) {
                    setTimeout(() => {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }, 5000);
                }
            });
        });

        // Confirmation dialogs for sensitive actions
        document.addEventListener('DOMContentLoaded', function() {
            const passwordForm = document.getElementById('passwordForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    if (!confirm('Tem certeza que deseja alterar sua senha?')) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
        
        // Loading states for forms
        function setLoadingState(form, isLoading) {
            const submitBtn = form.querySelector('button[type="submit"]');
            const inputs = form.querySelectorAll('input:not([disabled])');
            
            if (isLoading) {
                submitBtn.disabled = true;
                inputs.forEach(input => input.disabled = true);
            } else {
                submitBtn.disabled = false;
                inputs.forEach(input => input.disabled = false);
            }
        }
        
        // Enhanced form submission handling
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                setLoadingState(this, true);
            });
        });
        
        // Re-enable forms after page load (in case of validation errors)
        window.addEventListener('load', function() {
            document.querySelectorAll('form').forEach(form => {
                setLoadingState(form, false);
            });
        });
    </script>
</body>
</html>