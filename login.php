<?php
// login.php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Se já estiver logado, redirecionar para dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        if (login($username, $password)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Usuário ou senha incorretos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestão</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 2px solid #e2e8f0;
            border-right: none;
            background: #f8fafc;
        }
        
        .input-group .form-control {
            border-radius: 0 10px 10px 0;
            border-left: none;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: var(--primary-color);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .demo-users {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        
        .demo-users small {
            color: #64748b;
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
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-project-diagram fa-4x shape"></i>
        <i class="fas fa-tasks fa-3x shape"></i>
        <i class="fas fa-users fa-4x shape"></i>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-building"></i>
                <h3 class="mb-0">TechCorp Solutions</h3>
                <p class="mb-0 opacity-75">Sistema de Gestão de Projetos</p>
            </div>
            
            <div class="login-body">
                <h4 class="text-center mb-4">Faça seu Login</h4>
                
                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                   placeholder="Digite seu usuário" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Digite sua senha" required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login text-white">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Entrar no Sistema
                        </button>
                    </div>
                </form>
                
                <!-- Usuários de Demonstração -->
                <div class="demo-users">
                    <h6 class="text-center mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Usuários de Demonstração
                    </h6>
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <small class="d-block fw-bold">Admin</small>
                            <small class="text-muted">admin</small><br>
                            <small class="text-muted">123456</small>
                        </div>
                        <div class="col-4">
                            <small class="d-block fw-bold">Gerente</small>
                            <small class="text-muted">gerente</small><br>
                            <small class="text-muted">123456</small>
                        </div>
                        <div class="col-4">
                            <small class="d-block fw-bold">Colaborador</small>
                            <small class="text-muted">colaborador</small><br>
                            <small class="text-muted">123456</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Foco automático no campo de usuário
        document.getElementById('username').focus();
        
        // Animação suave nos campos de entrada
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
        
        // Efeito de clique nos usuários demo
        document.querySelectorAll('.demo-users .col-4').forEach(userDemo => {
            userDemo.addEventListener('click', function() {
                const username = this.querySelector('.text-muted').textContent;
                document.getElementById('username').value = username;
                document.getElementById('password').value = '123456';
                document.getElementById('password').focus();
            });
            
            userDemo.style.cursor = 'pointer';
            userDemo.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#e2e8f0';
                this.style.borderRadius = '8px';
            });
            
            userDemo.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'transparent';
            });
        });
    </script>
</body>
</html>