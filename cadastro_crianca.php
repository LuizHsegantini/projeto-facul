<?php
// cadastro_crianca.php - Formulário para cadastro de crianças em eventos
session_start();

// Configuração do banco de dados
require_once 'config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Processar dados da criança
        $nome_completo = $_POST['nome_completo'];
        $data_nascimento = $_POST['data_nascimento'];
        $sexo = $_POST['sexo'];
        $alergia_alimentos = $_POST['alergia_alimentos'] ?? '';
        $alergia_medicamentos = $_POST['alergia_medicamentos'] ?? '';
        $restricoes_alimentares = $_POST['restricoes_alimentares'] ?? '';
        $observacoes_saude = $_POST['observacoes_saude'] ?? '';
        
        // Dados do responsável
        $nome_responsavel = $_POST['nome_responsavel'];
        $grau_parentesco = $_POST['grau_parentesco'];
        $telefone_principal = $_POST['telefone_principal'];
        $telefone_alternativo = $_POST['telefone_alternativo'] ?? '';
        $endereco_completo = $_POST['endereco_completo'];
        $documento_rg_cpf = $_POST['documento_rg_cpf'];
        $email_responsavel = $_POST['email_responsavel'] ?? '';
        
        // Contato de emergência
        $nome_emergencia = $_POST['nome_emergencia'];
        $telefone_emergencia = $_POST['telefone_emergencia'];
        $grau_parentesco_emergencia = $_POST['grau_parentesco_emergencia'];
        $autorizacao_retirada = $_POST['autorizacao_retirada'];
        
        // Calcular idade automaticamente
        $idade = date_diff(date_create($data_nascimento), date_create('today'))->y;
        
        $query = "INSERT INTO criancas_cadastro (
            nome_completo, data_nascimento, idade, sexo, 
            alergia_alimentos, alergia_medicamentos, restricoes_alimentares, observacoes_saude,
            nome_responsavel, grau_parentesco, telefone_principal, telefone_alternativo, 
            endereco_completo, documento_rg_cpf, email_responsavel,
            nome_emergencia, telefone_emergencia, grau_parentesco_emergencia, autorizacao_retirada
        ) VALUES (
            :nome_completo, :data_nascimento, :idade, :sexo,
            :alergia_alimentos, :alergia_medicamentos, :restricoes_alimentares, :observacoes_saude,
            :nome_responsavel, :grau_parentesco, :telefone_principal, :telefone_alternativo,
            :endereco_completo, :documento_rg_cpf, :email_responsavel,
            :nome_emergencia, :telefone_emergencia, :grau_parentesco_emergencia, :autorizacao_retirada
        )";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nome_completo', $nome_completo);
        $stmt->bindParam(':data_nascimento', $data_nascimento);
        $stmt->bindParam(':idade', $idade);
        $stmt->bindParam(':sexo', $sexo);
        $stmt->bindParam(':alergia_alimentos', $alergia_alimentos);
        $stmt->bindParam(':alergia_medicamentos', $alergia_medicamentos);
        $stmt->bindParam(':restricoes_alimentares', $restricoes_alimentares);
        $stmt->bindParam(':observacoes_saude', $observacoes_saude);
        $stmt->bindParam(':nome_responsavel', $nome_responsavel);
        $stmt->bindParam(':grau_parentesco', $grau_parentesco);
        $stmt->bindParam(':telefone_principal', $telefone_principal);
        $stmt->bindParam(':telefone_alternativo', $telefone_alternativo);
        $stmt->bindParam(':endereco_completo', $endereco_completo);
        $stmt->bindParam(':documento_rg_cpf', $documento_rg_cpf);
        $stmt->bindParam(':email_responsavel', $email_responsavel);
        $stmt->bindParam(':nome_emergencia', $nome_emergencia);
        $stmt->bindParam(':telefone_emergencia', $telefone_emergencia);
        $stmt->bindParam(':grau_parentesco_emergencia', $grau_parentesco_emergencia);
        $stmt->bindParam(':autorizacao_retirada', $autorizacao_retirada);
        
        if ($stmt->execute()) {
            $message = 'Cadastro realizado com sucesso! A criança foi registrada para os eventos.';
            $messageType = 'success';
            
            // Limpar formulário após sucesso
            $_POST = array();
        } else {
            $message = 'Erro ao realizar o cadastro. Tente novamente.';
            $messageType = 'danger';
        }
        
    } catch (Exception $e) {
        $message = 'Erro no sistema: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Crianças - Eventos Infantis</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b9d;
            --secondary-color: #ffc93c;
            --success-color: #10b981;
            --info-color: #06bcf4;
        }
        
        body {
            background: linear-gradient(135deg, #ff6b9d 0%, #ffc93c 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 10px;
        }
        
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .form-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .form-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .form-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        .form-body {
            padding: 2rem;
        }
        
        .section-title {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: bold;
            font-size: 1.2rem;
            margin: 2rem 0 1rem 0;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }
        
        .section-title:first-child {
            margin-top: 0;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 12px 16px;
            font-size: 16px; /* Evita zoom no iOS */
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 157, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: bold;
            font-size: 18px;
            width: 100%;
            margin-top: 2rem;
            transition: transform 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 107, 157, 0.3);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 2rem;
        }
        
        .floating-icons {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .floating-icon {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
            color: white;
        }
        
        .floating-icon:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .floating-icon:nth-child(2) { top: 20%; right: 15%; animation-delay: 2s; }
        .floating-icon:nth-child(3) { bottom: 30%; left: 20%; animation-delay: 4s; }
        .floating-icon:nth-child(4) { bottom: 20%; right: 10%; animation-delay: 1s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .required {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            body {
                padding: 5px;
            }
            
            .form-body {
                padding: 1.5rem;
            }
            
            .form-header {
                padding: 1.5rem;
            }
            
            .form-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Icons -->
    <div class="floating-icons">
        <i class="fas fa-birthday-cake fa-3x floating-icon"></i>
        <i class="fas fa-child fa-4x floating-icon"></i>
        <i class="fas fa-heart fa-2x floating-icon"></i>
        <i class="fas fa-star fa-3x floating-icon"></i>
    </div>

    <div class="form-container">
        <div class="form-header">
            <i class="fas fa-child fa-3x mb-3"></i>
            <h1>Cadastro de Crianças</h1>
            <p>Sistema de Eventos Infantis - MagicKids</p>
        </div>
        
        <div class="form-body">
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" id="kidsForm">
                <!-- Dados da Criança -->
                <div class="section-title">
                    <i class="fas fa-child me-2"></i>
                    Dados da Criança
                </div>
                
                <div class="row g-3">
                    <div class="col-12">
                        <label for="nome_completo" class="form-label">Nome Completo <span class="required">*</span></label>
                        <input type="text" class="form-control" id="nome_completo" name="nome_completo" required
                               value="<?php echo htmlspecialchars($_POST['nome_completo'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="data_nascimento" class="form-label">Data de Nascimento <span class="required">*</span></label>
                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required
                               value="<?php echo htmlspecialchars($_POST['data_nascimento'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="sexo" class="form-label">Sexo <span class="required">*</span></label>
                        <select class="form-select" id="sexo" name="sexo" required>
                            <option value="">Selecione...</option>
                            <option value="Masculino" <?php echo ($_POST['sexo'] ?? '') === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                            <option value="Feminino" <?php echo ($_POST['sexo'] ?? '') === 'Feminino' ? 'selected' : ''; ?>>Feminino</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <label for="alergia_alimentos" class="form-label">Alergia a Alimentos</label>
                        <input type="text" class="form-control" id="alergia_alimentos" name="alergia_alimentos"
                               placeholder="Ex: amendoim, leite, ovos..."
                               value="<?php echo htmlspecialchars($_POST['alergia_alimentos'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-12">
                        <label for="alergia_medicamentos" class="form-label">Alergia a Medicamentos</label>
                        <input type="text" class="form-control" id="alergia_medicamentos" name="alergia_medicamentos"
                               placeholder="Especifique os medicamentos"
                               value="<?php echo htmlspecialchars($_POST['alergia_medicamentos'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-12">
                        <label for="restricoes_alimentares" class="form-label">Restrições Alimentares</label>
                        <input type="text" class="form-control" id="restricoes_alimentares" name="restricoes_alimentares"
                               placeholder="Ex: vegetariano, intolerância a lactose, glúten..."
                               value="<?php echo htmlspecialchars($_POST['restricoes_alimentares'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-12">
                        <label for="observacoes_saude" class="form-label">Observações de Saúde</label>
                        <textarea class="form-control" id="observacoes_saude" name="observacoes_saude" rows="3"
                                  placeholder="Ex: Hiperativo, usa óculos, diabetes..."><?php echo htmlspecialchars($_POST['observacoes_saude'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Dados dos Responsáveis -->
                <div class="section-title">
                    <i class="fas fa-users me-2"></i>
                    Dados do Responsável
                </div>
                
                <div class="row g-3">
                    <div class="col-12">
                        <label for="nome_responsavel" class="form-label">Nome do Responsável <span class="required">*</span></label>
                        <input type="text" class="form-control" id="nome_responsavel" name="nome_responsavel" required
                               value="<?php echo htmlspecialchars($_POST['nome_responsavel'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="grau_parentesco" class="form-label">Grau de Parentesco <span class="required">*</span></label>
                        <select class="form-select" id="grau_parentesco" name="grau_parentesco" required>
                            <option value="">Selecione...</option>
                            <option value="Pai" <?php echo ($_POST['grau_parentesco'] ?? '') === 'Pai' ? 'selected' : ''; ?>>Pai</option>
                            <option value="Mãe" <?php echo ($_POST['grau_parentesco'] ?? '') === 'Mãe' ? 'selected' : ''; ?>>Mãe</option>
                            <option value="Avô" <?php echo ($_POST['grau_parentesco'] ?? '') === 'Avô' ? 'selected' : ''; ?>>Avô</option>
                            <option value="Avó" <?php echo ($_POST['grau_parentesco'] ?? '') === 'Avó' ? 'selected' : ''; ?>>Avó</option>
                            <option value="Tio" <?php echo ($_POST['grau_parentesco'] ?? '') === 'Tio' ? 'selected' : ''; ?>>Tio</option>
                            <option value="Tia" <?php echo ($_POST['grau_parentesco'] ?? '') === 'Tia' ? 'selected' : ''; ?>>Tia</option>
                            <option value="Tutor Legal" <?php echo ($_POST['grau_parentesco'] ?? '') === 'Tutor Legal' ? 'selected' : ''; ?>>Tutor Legal</option>
                            <option value="Outro" <?php echo ($_POST['grau_parentesco'] ?? '') === 'Outro' ? 'selected' : ''; ?>>Outro</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="telefone_principal" class="form-label">Telefone Principal <span class="required">*</span></label>
                        <input type="tel" class="form-control" id="telefone_principal" name="telefone_principal" required
                               placeholder="(11) 99999-9999"
                               value="<?php echo htmlspecialchars($_POST['telefone_principal'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-12">
                        <label for="telefone_alternativo" class="form-label">Telefone Alternativo</label>
                        <input type="tel" class="form-control" id="telefone_alternativo" name="telefone_alternativo"
                               placeholder="(11) 99999-9999"
                               value="<?php echo htmlspecialchars($_POST['telefone_alternativo'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-12">
                        <label for="endereco_completo" class="form-label">Endereço Completo <span class="required">*</span></label>
                        <textarea class="form-control" id="endereco_completo" name="endereco_completo" rows="2" required
                                  placeholder="Rua, número, bairro, cidade - CEP"><?php echo htmlspecialchars($_POST['endereco_completo'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="documento_rg_cpf" class="form-label">RG/CPF <span class="required">*</span></label>
                        <input type="text" class="form-control" id="documento_rg_cpf" name="documento_rg_cpf" required
                               placeholder="000.000.000-00 ou 00.000.000-0"
                               value="<?php echo htmlspecialchars($_POST['documento_rg_cpf'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email_responsavel" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email_responsavel" name="email_responsavel"
                               placeholder="email@exemplo.com"
                               value="<?php echo htmlspecialchars($_POST['email_responsavel'] ?? ''); ?>">
                    </div>
                </div>
                
                <!-- Contato de Emergência -->
                <div class="section-title">
                    <i class="fas fa-phone-alt me-2"></i>
                    Contato de Emergência
                </div>
                
                <div class="row g-3">
                    <div class="col-12">
                        <label for="nome_emergencia" class="form-label">Nome Completo <span class="required">*</span></label>
                        <input type="text" class="form-control" id="nome_emergencia" name="nome_emergencia" required
                               value="<?php echo htmlspecialchars($_POST['nome_emergencia'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="telefone_emergencia" class="form-label">Telefone <span class="required">*</span></label>
                        <input type="tel" class="form-control" id="telefone_emergencia" name="telefone_emergencia" required
                               placeholder="(11) 99999-9999"
                               value="<?php echo htmlspecialchars($_POST['telefone_emergencia'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="grau_parentesco_emergencia" class="form-label">Grau de Parentesco <span class="required">*</span></label>
                        <input type="text" class="form-control" id="grau_parentesco_emergencia" name="grau_parentesco_emergencia" required
                               placeholder="Ex: Tio, Avó, Amigo da família..."
                               value="<?php echo htmlspecialchars($_POST['grau_parentesco_emergencia'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-12">
                        <label for="autorizacao_retirada" class="form-label">Autorização para Retirada <span class="required">*</span></label>
                        <select class="form-select" id="autorizacao_retirada" name="autorizacao_retirada" required>
                            <option value="">Selecione...</option>
                            <option value="Sim" <?php echo ($_POST['autorizacao_retirada'] ?? '') === 'Sim' ? 'selected' : ''; ?>>Sim - Autorizo a retirada da criança</option>
                            <option value="Não" <?php echo ($_POST['autorizacao_retirada'] ?? '') === 'Não' ? 'selected' : ''; ?>>Não - Apenas eu posso retirar</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-heart me-2"></i>
                    Cadastrar Criança
                </button>
            </form>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Máscara para telefone
        function formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d+)/, '($1) $2');
            }
            input.value = value;
        }
        
        // Aplicar máscara aos campos de telefone
        document.getElementById('telefone_principal').addEventListener('input', function() {
            formatPhone(this);
        });
        
        document.getElementById('telefone_alternativo').addEventListener('input', function() {
            formatPhone(this);
        });
        
        document.getElementById('telefone_emergencia').addEventListener('input', function() {
            formatPhone(this);
        });
        
        // Validar idade mínima e máxima
        document.getElementById('data_nascimento').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            
            if (age > 12) {
                alert('Este sistema é destinado a crianças até 12 anos.');
                this.value = '';
            } else if (age < 1) {
                alert('A criança deve ter pelo menos 1 ano.');
                this.value = '';
            }
        });
        
        // Validação do formulário
        document.getElementById('kidsForm').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios marcados com *');
                return false;
            }
        });
    </script>
</body>
</html>