<?php
// login.php - Versão corrigida sem loop de redirecionamento
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PRIMEIRO: Limpar qualquer sessão problemática ANTES de incluir auth.php
if (session_status() !== PHP_SESSION_NONE) {
    session_destroy();
}

// Iniciar sessão limpa
session_start();
$_SESSION = array();

require_once 'config/database.php';

// Função de login local (sem depender do auth.php problemático)
function loginDirect($username, $password) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM usuarios WHERE login = :login AND senha = MD5(:senha)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':login', $username);
        $stmt->bindParam(':senha', $password);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            // Limpar sessão e definir novas variáveis
            $_SESSION = array();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome_completo'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_profile'] = $user['perfil'];
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['user_cargo'] = $user['cargo'] ?? '';
            $_SESSION['last_activity'] = time();
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Erro na autenticação: " . $e->getMessage());
        return false;
    }
}

$error = '';
$success = '';

// Verificar mensagens da URL
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

if (isset($_GET['logout'])) {
    if ($_GET['logout'] === 'emergency') {
        $success = 'Logout de emergência realizado com sucesso.';
    } elseif ($_GET['logout'] === 'visual_success') {
        $success = 'Logout realizado com sucesso! Sistema limpo.';
    } elseif ($_GET['logout'] === 'error') {
        $error = 'Logout realizado mas com aviso: ' . ($_GET['msg'] ?? 'erro desconhecido');
    } else {
        $success = 'Logout realizado com sucesso.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        if (loginDirect($username, $password)) {
            // Redirecionar para dashboard após login bem-sucedido
            header('Location: dashboard_eventos.php');
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
    <title>Login - MagicKids Eventos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="fas fa-magic fa-4x shape"></i>
        <i class="fas fa-birthday-cake fa-3x shape"></i>
        <i class="fas fa-child fa-4x shape"></i>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-magic"></i>
                <h3>MagicKids Eventos</h3>
                <p>Sistema de Gestão de Eventos Infantis</p>
            </div>
            
            <div class="login-body">
                <h4>Faça seu Login</h4>
                
                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <!-- Botão de Limpeza de Emergência -->
                <?php if ($error && strpos($error, 'logado') !== false): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-tools me-2"></i>
                    <strong>Problema detectado!</strong> 
                    <a href="logout_emergency.php" class="btn btn-sm btn-warning ms-2">
                        <i class="fas fa-broom me-1"></i>Limpar Sistema
                    </a>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
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
                            <span class="input-group-text toggle-password" style="cursor: pointer;" title="Mostrar/Ocultar senha">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Entrar no Sistema
                        </button>
                    </div>
                </form>
                
                <!-- Usuários de Demonstração -->
                <div class="demo-users">
                    <h6>
                        <i class="fas fa-info-circle me-1"></i>
                        Usuários de Demonstração
                    </h6>
                    <div class="row">
                        <div class="col-4">
                            <span class="fw-bold">Admin</span>
                            <span class="text-muted">admin</span>
                            <span class="text-muted">123456</span>
                        </div>
                        <div class="col-4">
                            <span class="fw-bold">Coordenador</span>
                            <span class="text-muted">gerente</span>
                            <span class="text-muted">123456</span>
                        </div>
                        <div class="col-4">
                            <span class="fw-bold">Animador</span>
                            <span class="text-muted">colaborador</span>
                            <span class="text-muted">123456</span>
                        </div>
                    </div>
                </div>
                
                <!-- Informações de Sistema -->
                <div class="system-info">
                    <small class="text-muted d-block text-center">
                        <i class="fas fa-shield-alt me-1"></i>
                        Sistema com limpeza automática de sessões
                    </small>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Limpar qualquer storage problemático ao carregar
        try {
            if (typeof(Storage) !== "undefined") {
                // Limpar dados que podem estar causando conflito
                localStorage.removeItem('user_session');
                localStorage.removeItem('login_data');
                sessionStorage.clear();
            }
        } catch (e) {
            console.warn('Erro ao limpar storage:', e);
        }
        
        // Foco automático no campo de usuário
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Funcionalidade para mostrar/ocultar senha
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Efeito de clique nos usuários demo
        document.querySelectorAll('.demo-users .col-4').forEach(userDemo => {
            userDemo.addEventListener('click', function() {
                const spans = this.querySelectorAll('.text-muted');
                const username = spans[0].textContent;
                const password = spans[1].textContent;
                
                document.getElementById('username').value = username;
                document.getElementById('password').value = password;
                document.getElementById('password').focus();
                
                // Efeito visual de seleção
                this.style.background = 'rgba(255, 107, 157, 0.1)';
                setTimeout(() => {
                    this.style.background = '';
                }, 300);
            });
            
            userDemo.style.cursor = 'pointer';
            userDemo.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#e2e8f0';
                this.style.borderRadius = '8px';
                this.style.transform = 'scale(1.05)';
            });
            
            userDemo.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'transparent';
                this.style.transform = 'scale(1)';
            });
        });
        
        // Controle do formulário simplificado
        let formSubmitted = false;
        
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const submitBtn = this.querySelector('.btn-login');
            
            // Verificar se campos estão preenchidos
            if (!username || !password) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return false;
            }
            
            // Prevenir múltiplos submits
            if (formSubmitted) {
                e.preventDefault();
                return false;
            }
            
            // Marcar como submetido e alterar visual do botão
            formSubmitted = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Entrando...';
            submitBtn.disabled = true;
            
            // Permitir que o formulário seja enviado normalmente
            return true;
        });
        
        // Auto-limpar mensagens após 5 segundos
        const alertElements = document.querySelectorAll('.alert');
        alertElements.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
        
        // Limpar URL de parâmetros de erro
        if (window.location.search) {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('logout')) {
                // Manter logout message
                setTimeout(() => {
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                }, 3000);
            } else {
                // Limpar outros parâmetros imediatamente
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        }
    </script>
</body>
</html>