// logout.js - Sistema de logout simplificado sem AJAX problemático

document.addEventListener('DOMContentLoaded', function() {
    // Elementos do DOM
    const logoutTrigger = document.getElementById('logout-trigger');
    const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
    const confirmLogoutBtn = document.getElementById('confirm-logout-btn');

    // Event listener para o trigger de logout
    if (logoutTrigger) {
        logoutTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            logoutModal.show();
        });
    }

    // Event listener para confirmação do logout
    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener('click', function() {
            // Desabilitar botão para evitar múltiplos cliques
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saindo...';
            
            // Fechar modal
            logoutModal.hide();
            
            // Criar um formulário invisível para fazer POST direto
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href; // Usar a página atual
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'logout';
            
            form.appendChild(actionInput);
            document.body.appendChild(form);
            
            // Limpar dados locais antes do logout
            clearLocalData();
            
            // Submeter o formulário para fazer logout via POST direto
            setTimeout(() => {
                form.submit();
            }, 500);
        });
    }

    // Função para limpar dados locais
    function clearLocalData() {
        try {
            // Limpar sessionStorage
            if (typeof(Storage) !== "undefined" && sessionStorage) {
                sessionStorage.clear();
            }
            
            // Limpar dados específicos do localStorage
            if (typeof(Storage) !== "undefined" && localStorage) {
                const keysToRemove = ['userPreferences', 'tempData', 'formDrafts', 'dashboardCache'];
                keysToRemove.forEach(key => {
                    localStorage.removeItem(key);
                });
            }
        } catch (e) {
            console.warn('Erro ao limpar dados locais:', e);
        }
    }

    // Adicionar efeitos visuais simples aos botões
    const allLogoutButtons = document.querySelectorAll('.btn-logout-confirm, .btn-cancel-logout');
    allLogoutButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.2)';
            }
        });
        
        btn.addEventListener('mouseleave', function() {
            if (!this.disabled) {
                this.style.transform = '';
                this.style.boxShadow = '';
            }
        });
    });

    // Gerenciar estado do modal
    if (document.getElementById('logoutModal')) {
        document.getElementById('logoutModal').addEventListener('hidden.bs.modal', function() {
            // Restaurar botão se modal foi fechado sem confirmar
            if (confirmLogoutBtn && confirmLogoutBtn.disabled) {
                confirmLogoutBtn.disabled = false;
                confirmLogoutBtn.innerHTML = '<i class="fas fa-sign-out-alt me-2"></i>Sim, Sair';
            }
        });
    }

    // Funcionalidade adicional: logout por inatividade simplificado
    let inactivityTimer;
    const INACTIVITY_TIME = 25 * 60 * 1000; // 25 minutos
    
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            if (confirm('Sua sessão expirará em breve devido à inatividade.\n\nDeseja continuar navegando?')) {
                resetInactivityTimer();
            } else {
                // Logout automático via formulário
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = window.location.href;
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'logout';
                
                form.appendChild(actionInput);
                document.body.appendChild(form);
                
                clearLocalData();
                form.submit();
            }
        }, INACTIVITY_TIME);
    }

    // Eventos que resetam o timer de inatividade
    const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    activityEvents.forEach(event => {
        document.addEventListener(event, resetInactivityTimer, true);
    });

    // Iniciar timer de inatividade
    resetInactivityTimer();

    // Melhorar experiência visual do modal
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            document.body.style.overflow = 'hidden';
            
            // Animação do ícone
            const icon = this.querySelector('.logout-icon-modal i');
            if (icon) {
                icon.style.transform = 'scale(0.8)';
                icon.style.opacity = '0';
                
                setTimeout(() => {
                    icon.style.transition = 'all 0.6s ease';
                    icon.style.transform = 'scale(1)';
                    icon.style.opacity = '1';
                }, 100);
            }
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            document.body.style.overflow = '';
        });
    }

    // Funcionalidade para detectar fechamento do navegador
    window.addEventListener('beforeunload', function(e) {
        // Apenas aviso, sem tentar logout via AJAX
        if (document.querySelector('#logoutModal.show')) {
            e.preventDefault();
            e.returnValue = 'O logout está em andamento. Tem certeza que deseja sair?';
            return 'O logout está em andamento. Tem certeza que deseja sair?';
        }
    });

    // Expor função de logout para uso externo
    window.forceLogout = function(reason = 'forced') {
        console.log('[Logout] Logout forçado iniciado:', reason);
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;
        form.style.display = 'none';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'logout';
        
        form.appendChild(actionInput);
        document.body.appendChild(form);
        
        clearLocalData();
        form.submit();
    };

    // Debug log
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.log('[Logout] Sistema de logout simplificado inicializado');
    }
});