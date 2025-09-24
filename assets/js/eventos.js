/**
 * eventos.js - Script corrigido para resolver problemas de travamento
 */

// Variáveis globais
let currentEventData = null;
let currentCriancaData = null;
let isProcessing = false; // Flag para evitar múltiplas submissões

// Função para inicializar a página
document.addEventListener('DOMContentLoaded', function() {
    console.log('Página de eventos carregada');
    
    // Inicializar componentes
    initializeEventHandlers();
    initializeFormValidation();
    initializeModals();
    
    // Se houver ação de editar na URL, abrir modal automaticamente
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'edit' && document.getElementById('editEventoModal')) {
        setTimeout(() => {
            new bootstrap.Modal(document.getElementById('editEventoModal')).show();
        }, 500);
    }
});

// Inicializar event handlers
function initializeEventHandlers() {
    // Event handlers para forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });
    
    // Event handlers para botões de ação
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-action]')) {
            handleButtonClick(e);
        }
    });
    
    // Auto-refresh de alertas
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(alert => {
            if (alert.classList.contains('alert-success')) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 500);
            }
        });
    }, 5000);
}

// Handler principal para submissões de formulário
function handleFormSubmit(e) {
    // Verificar se já está processando
    if (isProcessing) {
        e.preventDefault();
        console.log('Formulário já está sendo processado');
        return false;
    }
    
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Validações específicas
    if (!validateForm(form)) {
        e.preventDefault();
        return false;
    }
    
    // Marcar como processando
    isProcessing = true;
    
    // Efeito visual no botão
    if (submitButton) {
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';
        submitButton.disabled = true;
        
        // Timeout de segurança
        setTimeout(() => {
            if (submitButton) {
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            }
            isProcessing = false;
        }, 10000);
    }
    
    console.log('Formulário submetido:', form.name || form.id);
}

// Validação de formulários
function validateForm(form) {
    const action = form.querySelector('input[name="action"]')?.value;
    
    if (action === 'create' || action === 'update') {
        return validateEventForm(form);
    }
    
    return true; // Para outros tipos de formulário
}

// Validação específica para formulários de evento
function validateEventForm(form) {
    const errors = [];
    
    // Validar campos obrigatórios
    const requiredFields = [
        { name: 'nome', label: 'Nome do evento' },
        { name: 'tipo_evento', label: 'Tipo de evento' },
        { name: 'data_inicio', label: 'Data de início' },
        { name: 'duracao_horas', label: 'Duração' },
        { name: 'faixa_etaria_min', label: 'Idade mínima' },
        { name: 'faixa_etaria_max', label: 'Idade máxima' },
        { name: 'capacidade_maxima', label: 'Capacidade máxima' },
        { name: 'local_evento', label: 'Local do evento' },
        { name: 'coordenador_id', label: 'Coordenador' }
    ];
    
    requiredFields.forEach(field => {
        const element = form.querySelector(`[name="${field.name}"]`);
        if (!element || !element.value.trim()) {
            errors.push(`${field.label} é obrigatório`);
            if (element) {
                element.classList.add('is-invalid');
            }
        } else if (element.classList.contains('is-invalid')) {
            element.classList.remove('is-invalid');
        }
    });
    
    // Validar faixa etária
    const idadeMin = parseInt(form.querySelector('[name="faixa_etaria_min"]')?.value);
    const idadeMax = parseInt(form.querySelector('[name="faixa_etaria_max"]')?.value);
    
    if (idadeMin && idadeMax && idadeMin > idadeMax) {
        errors.push('A idade mínima não pode ser maior que a idade máxima');
        form.querySelector('[name="faixa_etaria_min"]')?.classList.add('is-invalid');
        form.querySelector('[name="faixa_etaria_max"]')?.classList.add('is-invalid');
    }
    
    // Validar data
    const dataInicio = form.querySelector('[name="data_inicio"]')?.value;
    if (dataInicio) {
        const dataEvento = new Date(dataInicio);
        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);
        
        if (dataEvento < hoje) {
            // Apenas aviso, não bloqueia
            console.warn('Data do evento é no passado');
        }
    }
    
    // Validar duração
    const duracao = parseFloat(form.querySelector('[name="duracao_horas"]')?.value);
    if (duracao && (duracao <= 0 || duracao > 24)) {
        errors.push('Duração deve ser entre 1 e 24 horas');
        form.querySelector('[name="duracao_horas"]')?.classList.add('is-invalid');
    }
    
    // Validar capacidade
    const capacidade = parseInt(form.querySelector('[name="capacidade_maxima"]')?.value);
    if (capacidade && (capacidade <= 0 || capacidade > 1000)) {
        errors.push('Capacidade deve ser entre 1 e 1000 participantes');
        form.querySelector('[name="capacidade_maxima"]')?.classList.add('is-invalid');
    }
    
    // Mostrar erros
    if (errors.length > 0) {
        showAlert('Erro de Validação', errors.join('<br>'), 'danger');
        return false;
    }
    
    return true;
}

// Função para mostrar alertas
function showAlert(title, message, type = 'info') {
    // Remover alertas existentes
    document.querySelectorAll('.alert.alert-validation').forEach(alert => {
        alert.remove();
    });
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show alert-validation" role="alert">
            <i class="fas fa-${getIconForAlertType(type)} me-2"></i>
            <strong>${title}:</strong><br>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Inserir no topo do main-content
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Scroll para o topo
        mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Função auxiliar para ícones de alerta
function getIconForAlertType(type) {
    const icons = {
        'success': 'check-circle',
        'danger': 'exclamation-triangle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Handler para cliques em botões
function handleButtonClick(e) {
    const action = e.target.dataset.action;
    
    switch (action) {
        case 'edit-event':
            editEvent(e.target.dataset.eventId);
            break;
        case 'delete-event':
            deleteEvent(e.target.dataset.eventId, e.target.dataset.eventName);
            break;
        case 'add-crianca':
            addCrianca(e.target.dataset.eventoId);
            break;
        case 'remove-crianca':
            removeCrianca(e.target.dataset.eventoId, e.target.dataset.criancaId, e.target.dataset.criancaName);
            break;
    }
}

// Função para mostrar ações do evento
function showEventoActions(evento) {
    if (typeof evento === 'string') {
        try {
            evento = JSON.parse(evento);
        } catch (e) {
            console.error('Erro ao parsear dados do evento:', e);
            return;
        }
    }
    
    currentEventData = evento;
    document.getElementById('eventActionTitle').textContent = evento.nome;
    new bootstrap.Modal(document.getElementById('eventActionsModal')).show();
}

// Função para mostrar ações da criança
function showCriancaActions(eventoId, criancaId, criancaNome) {
    currentCriancaData = {
        eventoId: eventoId,
        criancaId: criancaId,
        nome: criancaNome
    };
    document.getElementById('criancaActionName').textContent = criancaNome;
    new bootstrap.Modal(document.getElementById('criancaActionsModal')).show();
}

// Ações do evento
function viewEventDetails() {
    if (currentEventData) {
        window.location.href = `eventos.php?id=${currentEventData.id}`;
    }
}

function goToCheckin() {
    if (currentEventData) {
        window.location.href = `checkin.php?evento=${currentEventData.id}`;
    }
}

function editCurrentEvent() {
    if (currentEventData) {
        bootstrap.Modal.getInstance(document.getElementById('eventActionsModal'))?.hide();
        window.location.href = `eventos.php?id=${currentEventData.id}&action=edit`;
    }
}

function deleteCurrentEvent() {
    if (currentEventData) {
        bootstrap.Modal.getInstance(document.getElementById('eventActionsModal'))?.hide();
        deleteEvento(currentEventData.id, currentEventData.nome);
    }
}

// Ações da criança
function viewCriancaDetails() {
    if (currentCriancaData) {
        window.location.href = `criancas.php?id=${currentCriancaData.criancaId}`;
    }
}

function viewCriancaEvents() {
    if (currentCriancaData) {
        window.location.href = `criancas.php?id=${currentCriancaData.criancaId}&tab=eventos`;
    }
}

function confirmRemoveCrianca() {
    if (currentCriancaData) {
        bootstrap.Modal.getInstance(document.getElementById('criancaActionsModal'))?.hide();
        removeCrianca(currentCriancaData.eventoId, currentCriancaData.criancaId, currentCriancaData.nome);
    }
}

// Função para deletar evento
function deleteEvento(id, nome) {
    if (!id || !nome) return;
    
    document.getElementById('deleteEventoId').value = id;
    document.getElementById('deleteEventoName').textContent = nome;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Função para remover criança
function removeCrianca(eventoId, criancaId, nomeCrianca) {
    if (!confirm(`Tem certeza que deseja remover ${nomeCrianca} deste evento?`)) {
        return;
    }
    
    if (isProcessing) {
        showAlert('Atenção', 'Aguarde o processamento da ação anterior', 'warning');
        return;
    }
    
    isProcessing = true;
    
    // Criar e submeter formulário
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="remove_crianca">
        <input type="hidden" name="evento_id" value="${eventoId}">
        <input type="hidden" name="crianca_id" value="${criancaId}">
    `;
    
    document.body.appendChild(form);
    form.submit();
}

// Inicializar modais
function initializeModals() {
    // Reset forms quando modais são fechados
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            const forms = modal.querySelectorAll('form');
            forms.forEach(form => {
                form.reset();
                // Remover classes de validação
                form.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
            });
            
            // Reset flags
            isProcessing = false;
        });
    });
    
    // Configurar modais de edição
    const editModal = document.getElementById('editEventoModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function() {
            // Pre-preencher dados se necessário
            console.log('Modal de edição aberto');
        });
    }
}

// Função para formatar telefone automaticamente
function setupPhoneFormatting() {
    document.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d+)/, '($1) $2');
            }
            e.target.value = value;
        });
    });
}

// Animações e efeitos visuais
function initializeAnimations() {
    // Efeitos hover nos cards
    document.querySelectorAll('.evento-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) translateX(8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    document.querySelectorAll('.crianca-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(8px)';
            this.style.boxShadow = '0 4px 15px rgba(255, 107, 157, 0.2)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
}

// Função para debug e logging
function debugForm(form) {
    console.group('Debug do Formulário');
    console.log('Form:', form);
    console.log('Action:', form.querySelector('[name="action"]')?.value);
    
    const formData = new FormData(form);
    console.log('Dados do formulário:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    console.groupEnd();
}

// Função para validação em tempo real
function setupRealTimeValidation() {
    // Validação de faixa etária
    const idadeMinInput = document.querySelector('[name="faixa_etaria_min"]');
    const idadeMaxInput = document.querySelector('[name="faixa_etaria_max"]');
    
    if (idadeMinInput && idadeMaxInput) {
        [idadeMinInput, idadeMaxInput].forEach(input => {
            input.addEventListener('blur', function() {
                const min = parseInt(idadeMinInput.value);
                const max = parseInt(idadeMaxInput.value);
                
                if (min && max && min > max) {
                    idadeMinInput.classList.add('is-invalid');
                    idadeMaxInput.classList.add('is-invalid');
                } else {
                    idadeMinInput.classList.remove('is-invalid');
                    idadeMaxInput.classList.remove('is-invalid');
                }
            });
        });
    }
    
    // Validação de capacidade
    const capacidadeInput = document.querySelector('[name="capacidade_maxima"]');
    if (capacidadeInput) {
        capacidadeInput.addEventListener('blur', function() {
            const valor = parseInt(this.value);
            if (valor && (valor <= 0 || valor > 1000)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }
    
    // Validação de duração
    const duracaoInput = document.querySelector('[name="duracao_horas"]');
    if (duracaoInput) {
        duracaoInput.addEventListener('blur', function() {
            const valor = parseFloat(this.value);
            if (valor && (valor <= 0 || valor > 24)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }
}

// Inicializar tudo quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    setupPhoneFormatting();
    initializeAnimations();
    setupRealTimeValidation();
});

// Função para prevenção de duplo clique
function preventDoubleClick(element) {
    if (element.classList.contains('processing')) {
        return false;
    }
    
    element.classList.add('processing');
    setTimeout(() => {
        element.classList.remove('processing');
    }, 3000);
    
    return true;
}

// Adicionar event listener para prevenção de duplo clique em todos os botões de submit
document.addEventListener('click', function(e) {
    if (e.target.type === 'submit' || e.target.closest('button[type="submit"]')) {
        const button = e.target.type === 'submit' ? e.target : e.target.closest('button[type="submit"]');
        if (!preventDoubleClick(button)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }
});

// CSS para elementos em processamento
const style = document.createElement('style');
style.textContent = `
    .processing {
        opacity: 0.7;
        pointer-events: none;
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
    }
    
    .alert-validation {
        margin-bottom: 1rem;
        max-width: 100%;
    }
`;
document.head.appendChild(style);

console.log('Script de eventos inicializado com sucesso');