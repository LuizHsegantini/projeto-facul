<?php
// logout.php

// Iniciar sessão apenas se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se chamado via AJAX para efetuar o logout
if (isset($_POST['confirm_logout']) && $_POST['confirm_logout'] === 'true') {
    // Registrar log de logout se estiver logado
    if (isset($_SESSION['user_id'])) {
        require_once 'includes/auth.php';
        
        // Registrar a ação de logout
        logSystemAction($_SESSION['user_id'], 'Logout realizado');
    }

    // Destruir todas as variáveis de sessão
    session_unset();

    // Destruir a sessão
    session_destroy();

    // Retornar resposta JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'redirect' => 'login.php?logout=1']);
    exit();
}

// Se não há sessão ativa, usar dados padrão para demonstração
if (!isset($_SESSION['user_id'])) {
    $currentUser = [
        'nome' => 'Usuário Visitante',
        'perfil' => 'visitante'
    ];
} else {
    $currentUser = [
        'nome' => $_SESSION['user_name'] ?? 'Usuário',
        'perfil' => $_SESSION['user_profile'] ?? ''
    ];
}

$currentUser = [
    'nome' => $_SESSION['user_name'] ?? 'Usuário',
    'perfil' => $_SESSION['user_profile'] ?? ''
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Sistema de Gestão</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
            --danger-color: #ef4444;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logout-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .logout-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
        }
        
        .btn-logout {
            background: var(--danger-color);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        }
        
        .btn-cancel {
            background: #6b7280;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #4b5563;
            color: white;
        }
        
        .logout-progress {
            display: none;
        }
        
        .progress {
            height: 8px;
            border-radius: 10px;
            background: #f1f5f9;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .logout-success {
            display: none;
        }
        
        .success-icon {
            font-size: 4rem;
            color: var(--success-color);
            animation: bounceIn 0.6s ease-out;
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }
        
        @keyframes fadeOut {
            to { opacity: 0; transform: scale(0.9); }
        }
        
        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 4s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
            50% { transform: translateY(-20px) rotate(180deg); opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Floating Particles -->
    <div class="floating-particles">
        <div class="particle" style="left: 10%; top: 20%; width: 6px; height: 6px; animation-delay: 0s;"></div>
        <div class="particle" style="left: 20%; top: 80%; width: 8px; height: 8px; animation-delay: 1s;"></div>
        <div class="particle" style="left: 80%; top: 40%; width: 4px; height: 4px; animation-delay: 2s;"></div>
        <div class="particle" style="left: 70%; top: 70%; width: 10px; height: 10px; animation-delay: 1.5s;"></div>
        <div class="particle" style="left: 90%; top: 10%; width: 5px; height: 5px; animation-delay: 0.5s;"></div>
    </div>

    <div class="logout-container">
        <!-- Confirmação inicial -->
        <div id="logout-confirmation">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            
            <h2 class="mb-3">Confirmar Logout</h2>
            <p class="text-muted mb-4">
                Olá, <strong><?php echo htmlspecialchars($currentUser['nome']); ?></strong>!<br>
                Tem certeza que deseja sair do sistema?
            </p>
            
            <div class="d-flex gap-3 justify-content-center">
                <button type="button" class="btn btn-logout" id="confirm-logout">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Sim, Sair
                </button>
                <button type="button" class="btn btn-cancel" onclick="window.history.back()">
                    <i class="fas fa-times me-2"></i>
                    Cancelar
                </button>
            </div>
        </div>

        <!-- Progresso do logout -->
        <div id="logout-progress" class="logout-progress">
            <div class="logout-icon">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            
            <h3 class="mb-3">Realizando Logout...</h3>
            <p class="text-muted mb-4">Aguarde enquanto finalizamos sua sessão</p>
            
            <div class="progress mb-3">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
            
            <div class="logout-steps">
                <small class="text-muted">
                    <span id="step-text">Salvando dados da sessão...</span>
                </small>
            </div>
        </div>

        <!-- Logout concluído -->
        <div id="logout-success" class="logout-success">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h3 class="mb-3 text-success">Logout Realizado!</h3>
            <p class="text-muted mb-4">
                Sua sessão foi finalizada com sucesso.<br>
                Redirecionando para a página de login...
            </p>
            
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('confirm-logout').addEventListener('click', function() {
            // Ocultar confirmação com animação
            const confirmation = document.getElementById('logout-confirmation');
            confirmation.classList.add('fade-out');
            
            setTimeout(() => {
                confirmation.style.display = 'none';
                
                // Mostrar progresso
                const progress = document.getElementById('logout-progress');
                progress.style.display = 'block';
                
                // Simular processo de logout com etapas
                simulateLogoutProcess();
            }, 500);
        });
        
        function simulateLogoutProcess() {
            const progressBar = document.querySelector('.progress-bar');
            const stepText = document.getElementById('step-text');
            const steps = [
                { text: 'Salvando dados da sessão...', progress: 20 },
                { text: 'Registrando atividade no log...', progress: 40 },
                { text: 'Limpando dados temporários...', progress: 60 },
                { text: 'Finalizando conexões...', progress: 80 },
                { text: 'Concluindo logout...', progress: 100 }
            ];
            
            let currentStep = 0;
            
            const interval = setInterval(() => {
                if (currentStep < steps.length) {
                    const step = steps[currentStep];
                    stepText.textContent = step.text;
                    progressBar.style.width = step.progress + '%';
                    currentStep++;
                } else {
                    clearInterval(interval);
                    
                    // Após completar o progresso, fazer logout real
                    setTimeout(() => {
                        performActualLogout();
                    }, 500);
                }
            }, 800);
        }
        
        function performActualLogout() {
            // Fazer requisição AJAX para efetuar logout
            fetch('logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'confirm_logout=true'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar sucesso
                    document.getElementById('logout-progress').style.display = 'none';
                    document.getElementById('logout-success').style.display = 'block';
                    
                    // Redirecionar após 2 segundos
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                } else {
                    alert('Erro ao fazer logout. Tentando novamente...');
                    window.location.href = 'login.php';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                // Em caso de erro, redirecionar diretamente
                window.location.href = 'login.php?logout=1';
            });
        }
        
        // Prevenir voltar durante o processo
        let logoutInProgress = false;
        document.getElementById('confirm-logout').addEventListener('click', function() {
            logoutInProgress = true;
        });
        
        window.addEventListener('beforeunload', function(e) {
            if (logoutInProgress) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>