<?php
// team_members.php - AJAX endpoint para gerenciar membros das equipes
require_once 'includes/auth.php';
require_once 'controllers/TeamsController.php';

// Verificar se o usuário está logado e tem permissão
requireLogin();

if (!hasPermission('gerente')) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Acesso negado.</div>';
    exit();
}

// Verificar se foi fornecido ID da equipe
if (!isset($_GET['team_id']) || !is_numeric($_GET['team_id'])) {
    echo '<div class="alert alert-danger">ID da equipe inválido.</div>';
    exit();
}

$team_id = (int)$_GET['team_id'];
$teamsController = new TeamsController();

try {
    $team = $teamsController->getById($team_id);
    if (!$team) {
        echo '<div class="alert alert-danger">Equipe não encontrada.</div>';
        exit();
    }
    
    $members = $teamsController->getTeamMembers($team_id);
    $availableUsers = $teamsController->getAvailableUsers($team_id);
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erro ao carregar dados: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}
?>

<style>
.member-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.member-card {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
    background: #fff;
}

.member-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-color: #667eea;
}

.available-user-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    transition: all 0.3s ease;
    background: #f8f9fa;
    cursor: pointer;
}

.available-user-card:hover {
    background: #e3f2fd;
    border-color: #2196f3;
    transform: translateX(5px);
}

.section-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}

.quick-add-btn {
    transition: all 0.3s ease;
}

.quick-add-btn:hover {
    transform: scale(1.1);
}

.stats-badge {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 2px dashed #dee2e6;
}

.divider {
    height: 2px;
    background: linear-gradient(90deg, transparent, #dee2e6, transparent);
    margin: 25px 0;
}
</style>

<div class="container-fluid">
    <!-- Header com estatísticas -->
    <div class="section-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1">
                    <i class="fas fa-users me-2"></i>
                    Gerenciar Membros - <?php echo htmlspecialchars($team['nome']); ?>
                </h5>
                <p class="mb-0 opacity-75">Adicione ou remova membros da equipe</p>
            </div>
            <div class="col-md-4 text-end">
                <span class="stats-badge">
                    <?php echo count($members); ?> membros ativos
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Membros Atuais -->
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-primary mb-0">
                    <i class="fas fa-user-check me-2"></i>
                    Membros Atuais (<?php echo count($members); ?>)
                </h6>
                <?php if (!empty($members)): ?>
                <small class="text-muted">Clique no × para remover</small>
                <?php endif; ?>
            </div>

            <?php if (empty($members)): ?>
                <div class="empty-state">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Nenhum membro na equipe</h6>
                    <p class="text-muted mb-0">Comece adicionando o primeiro membro!</p>
                </div>
            <?php else: ?>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($members as $index => $member): ?>
                    <div class="member-card">
                        <div class="d-flex align-items-center">
                            <div class="member-avatar me-3" 
                                 style="background: <?php echo ['#667eea', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997'][$index % 6]; ?>">
                                <?php echo strtoupper(substr($member['nome_completo'], 0, 2)); ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($member['nome_completo']); ?></h6>
                                        <p class="text-muted mb-1 small">
                                            <i class="fas fa-briefcase me-1"></i>
                                            <?php echo htmlspecialchars($member['cargo'] ?? 'Cargo não informado'); ?>
                                        </p>
                                        <div>
                                            <span class="badge bg-<?php echo $member['perfil'] === 'administrador' ? 'danger' : ($member['perfil'] === 'gerente' ? 'warning' : 'info'); ?>">
                                                <i class="fas fa-<?php echo $member['perfil'] === 'administrador' ? 'crown' : ($member['perfil'] === 'gerente' ? 'user-tie' : 'user'); ?> me-1"></i>
                                                <?php echo ucfirst($member['perfil']); ?>
                                            </span>
                                            <span class="badge bg-light text-dark ms-1">
                                                <i class="fas fa-envelope me-1"></i>
                                                <?php echo htmlspecialchars($member['email']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <form method="POST" action="teams.php" style="display: inline;">
                                        <input type="hidden" name="action" value="remove_member">
                                        <input type="hidden" name="team_id" value="<?php echo $team_id; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                onclick="return confirm('⚠️ Tem certeza que deseja remover <?php echo htmlspecialchars($member['nome_completo']); ?> da equipe?\n\nEsta ação irá:\n• Remover o usuário de todos os projetos desta equipe\n• Retirar suas atribuições de tarefas relacionadas')"
                                                title="Remover da equipe">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Divisor visual -->
        <div class="col-lg-1 d-none d-lg-block">
            <div style="height: 100%; width: 2px; background: linear-gradient(180deg, transparent, #dee2e6, transparent); margin: 0 auto;"></div>
        </div>
        <div class="d-lg-none">
            <div class="divider"></div>
        </div>

        <!-- Adicionar Novos Membros -->
        <div class="col-lg-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-success mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Adicionar Membro
                </h6>
                <?php if (!empty($availableUsers)): ?>
                <small class="text-muted"><?php echo count($availableUsers); ?> disponíveis</small>
                <?php endif; ?>
            </div>

            <?php if (empty($availableUsers)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h6 class="text-muted">Todos incluídos!</h6>
                    <p class="text-muted mb-0">Todos os usuários já estão nesta equipe</p>
                </div>
            <?php else: ?>
                <!-- Formulário de adição -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <form method="POST" action="teams.php" id="addMemberForm">
                            <input type="hidden" name="action" value="add_member">
                            <input type="hidden" name="team_id" value="<?php echo $team_id; ?>">
                            
                            <div class="mb-3">
                                <label for="user_id" class="form-label">
                                    <i class="fas fa-search me-1"></i>
                                    Selecionar Usuário
                                </label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">🔍 Escolha um usuário...</option>
                                    <?php foreach ($availableUsers as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['nome_completo']); ?>
                                        <?php if ($user['cargo']): ?>
                                        - <?php echo htmlspecialchars($user['cargo']); ?>
                                        <?php endif; ?>
                                        (<?php echo ucfirst($user['perfil']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-user-plus me-2"></i>Adicionar à Equipe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista visual de usuários disponíveis -->
                <h6 class="text-muted mb-3">
                    <i class="fas fa-list me-2"></i>
                    Usuários Disponíveis
                </h6>
                <div style="max-height: 350px; overflow-y: auto;">
                    <?php foreach ($availableUsers as $index => $user): ?>
                    <div class="available-user-card" 
                         onclick="quickAddMember(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nome_completo']); ?>')"
                         title="Clique para adicionar rapidamente">
                        <div class="d-flex align-items-center">
                            <div class="member-avatar me-3" 
                                 style="background: <?php echo ['#6c757d', '#17a2b8', '#fd7e14', '#e83e8c', '#6610f2'][$index % 5]; ?>; width: 35px; height: 35px; font-size: 0.8rem;">
                                <?php echo strtoupper(substr($user['nome_completo'], 0, 2)); ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 small"><?php echo htmlspecialchars($user['nome_completo']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($user['cargo'] ?? 'Cargo não informado'); ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?php echo $user['perfil'] === 'administrador' ? 'danger' : ($user['perfil'] === 'gerente' ? 'warning' : 'info'); ?> mb-1">
                                            <?php echo ucfirst($user['perfil']); ?>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-success quick-add-btn d-block mx-auto" 
                                                onclick="event.stopPropagation(); quickAddMember(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nome_completo']); ?>')"
                                                title="Adicionar rapidamente">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function quickAddMember(userId, userName) {
    if (confirm('✅ Adicionar ' + userName + ' à equipe?\n\nO usuário terá acesso aos projetos e tarefas desta equipe.')) {
        document.getElementById('user_id').value = userId;
        document.getElementById('addMemberForm').submit();
    }
}

// Adicionar funcionalidade de busca rápida
document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.getElementById('user_id');
    if (selectElement) {
        selectElement.addEventListener('change', function() {
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const userName = selectedOption.text.split(' - ')[0].split(' (')[0];
                
                // Highlight da seleção
                this.style.borderColor = '#28a745';
                this.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
            } else {
                this.style.borderColor = '';
                this.style.boxShadow = '';
            }
        });
    }
});

// Efeito visual para cards de usuários disponíveis
document.querySelectorAll('.available-user-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.borderColor = '#28a745';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.borderColor = '#e9ecef';
    });
});
</script>