<?php
// profile.php - Página de perfil do usuário
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'controllers/ProfileController.php';

// Verificar se o usuário está logado
requireLogin();

$profileController = new ProfileController();
$currentUser = getCurrentUser();
$userId = $currentUser['id'];

$message = '';
$messageType = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $data = [
                    'nome_completo' => trim($_POST['nome_completo']),
                    'email' => trim($_POST['email']),
                    'cargo' => trim($_POST['cargo']),
                    'user_id' => $userId
                ];
                
                $errors = $profileController->validateProfileData($data);
                
                if (empty($errors)) {
                    if ($profileController->updateProfile($userId, $data)) {
                        $message = "Perfil atualizado com sucesso!";
                        $messageType = "success";
                        // Recarregar dados do usuário
                        $currentUser = getCurrentUser();
                    } else {
                        $message = "Erro ao atualizar perfil. Tente novamente.";
                        $messageType = "danger";
                    }
                } else {
                    $message = implode("<br>", $errors);
                    $messageType = "danger";
                }
                break;
                
            case 'change_password':
                $currentPassword = $_POST['current_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];
                
                $errors = $profileController->validatePassword($newPassword, $confirmPassword);
                
                if (empty($errors)) {
                    $result = $profileController->changePassword($userId, $currentPassword, $newPassword);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'danger';
                } else {
                    $message = implode("<br>", $errors);
                    $messageType = "danger";
                }
                break;
        }
    }
}

// Buscar dados do perfil
$userProfile = $profileController->getUserProfile($userId);
$userStats = $profileController->getUserStats($userId);
$userActivities = $profileController->getUserActivities($userId);
$userTeams = $profileController->getUserTeams($userId);
$recentLogs = $profileController->getRecentLogs($userId);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - MagicKids Eventos</title>
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
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
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
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 2px 10px rgba(255, 107, 157, 0.1);
            margin-bottom: 1.5rem;
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
        .stat-card.info { border-left-color: var(--info-color); }
        
        .stat-number {
            font-size: 2rem;
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
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .profile-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .activity-timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0.5rem;
            width: 10px;
            height: 10px;
            background: var(--primary-color);
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 1px var(--primary-color);
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
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-user fa-6x shape"></i>
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
        </ul>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header-bar d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Meu Perfil</h2>
                <p class="text-muted mb-0">Gerencie suas informações pessoais e preferências</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="dashboard_eventos.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar ao Dashboard
                </a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php if ($messageType === 'success'): ?>
            <i class="fas fa-check-circle me-2"></i>
            <?php else: ?>
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php endif; ?>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Card -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($currentUser['nome_completo'], 0, 2)); ?>
                            <span class="profile-badge bg-<?php 
                                $badgeColors = [
                                    'administrador' => 'danger',
                                    'coordenador' => 'primary',
                                    'animador' => 'success',
                                    'monitor' => 'info',
                                    'auxiliar' => 'warning'
                                ];
                                echo $badgeColors[$currentUser['perfil']] ?? 'secondary';
                            ?>">
                                <?php echo ucfirst($currentUser['perfil']); ?>
                            </span>
                        </div>
                        
                        <h4 class="mb-1"><?php echo htmlspecialchars($currentUser['nome_completo']); ?></h4>
                        <p class="text-muted mb-1"><?php echo htmlspecialchars($currentUser['cargo']); ?></p>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-envelope me-1"></i>
                            <?php echo htmlspecialchars($currentUser['email']); ?>
                        </p>
                        
                        <div class="row text-center">
                            <div class="col">
                                <div class="border-end">
                                    <h5 class="mb-0"><?php echo date('d/m/Y', strtotime($userProfile['data_criacao'])); ?></h5>
                                    <small class="text-muted">Cadastro</small>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="mb-0">
                                    <?php 
                                    if ($userStats['ultimo_login']) {
                                        echo date('d/m/Y', strtotime($userStats['ultimo_login']));
                                    } else {
                                        echo 'Nunca';
                                    }
                                    ?>
                                </h5>
                                <small class="text-muted">Último Login</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="stat-card primary">
                            <div class="stat-number text-primary"><?php echo $userStats['total_atividades']; ?></div>
                            <div class="stat-label">Atividades</div>
                            <i class="fas fa-gamepad stat-icon"></i>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card info">
                            <div class="stat-number text-info"><?php echo $userStats['total_equipes']; ?></div>
                            <div class="stat-label">Equipes</div>
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card success">
                            <div class="stat-number text-success"><?php echo $userStats['total_eventos_coordenados']; ?></div>
                            <div class="stat-label">Eventos</div>
                            <i class="fas fa-calendar-star stat-icon"></i>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card warning">
                            <div class="stat-number text-warning"><?php echo $userStats['total_logs']; ?></div>
                            <div class="stat-label">Ações