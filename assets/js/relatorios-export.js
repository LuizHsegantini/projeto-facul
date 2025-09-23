// relatorios-export.js - Funcionalidades de exportação de relatórios
class RelatoriosExporter {
    constructor() {
        this.initializeExportHandlers();
    }

    initializeExportHandlers() {
        // Handler para exportação PDF
        document.addEventListener('click', (e) => {
            if (e.target.closest('[href*="export=pdf"]')) {
                e.preventDefault();
                this.exportToPDF();
            }
        });

        // Handler para exportação Excel
        document.addEventListener('click', (e) => {
            if (e.target.closest('[href*="export=excel"]')) {
                e.preventDefault();
                this.exportToExcel();
            }
        });

        // Handler para exportação CSV
        document.addEventListener('click', (e) => {
            if (e.target.closest('[href*="export=csv"]')) {
                e.preventDefault();
                this.exportToCSV();
            }
        });
    }

    async exportToPDF() {
        try {
            this.showExportProgress('Gerando PDF...');
            
            // Verificar se html2pdf está disponível
            if (typeof html2pdf === 'undefined') {
                // Carregar html2pdf dinamicamente
                await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js');
            }
            
            // Preparar elemento para exportação
            const element = this.preparePDFContent();
            
            const opt = {
                margin: [0.5, 0.5, 0.5, 0.5],
                filename: `relatorio_magickids_${this.getCurrentDateTime()}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2,
                    useCORS: true,
                    letterRendering: true,
                    allowTaint: true
                },
                jsPDF: { 
                    unit: 'in', 
                    format: 'a4', 
                    orientation: 'portrait' 
                }
            };

            await html2pdf().set(opt).from(element).save();
            
            this.hideExportProgress();
            this.showSuccessMessage('PDF exportado com sucesso!');
            
        } catch (error) {
            console.error('Erro ao exportar PDF:', error);
            this.hideExportProgress();
            this.showErrorMessage('Erro ao exportar PDF. Verifique se as bibliotecas estão carregadas.');
        }
    }

    preparePDFContent() {
        // Clonar o conteúdo principal
        const originalContent = document.querySelector('.main-content');
        const clonedContent = originalContent.cloneNode(true);
        
        // Remover elementos que não devem aparecer no PDF
        const elementsToRemove = clonedContent.querySelectorAll('.dropdown, .btn, .floating-shapes');
        elementsToRemove.forEach(el => el.remove());
        
        // Ajustar estilos para PDF
        clonedContent.style.marginLeft = '0';
        clonedContent.style.padding = '20px';
        clonedContent.style.background = 'white';
        
        // Ajustar header para PDF
        const header = clonedContent.querySelector('.header-bar');
        if (header) {
            header.style.pageBreakAfter = 'avoid';
            header.innerHTML = `
                <div style="text-align: center; padding: 20px 0;">
                    <h1 style="color: #ff6b9d; margin-bottom: 10px;">MagicKids Eventos - Relatórios</h1>
                    <p style="color: #666; margin: 0;">Panorama consolidado sobre eventos, equipes e operações</p>
                    <p style="color: #999; margin: 0; font-size: 12px;">Gerado em: ${new Date().toLocaleString('pt-BR')}</p>
                </div>
            `;
        }
        
        // Ajustar cards para melhor visualização no PDF
        const cards = clonedContent.querySelectorAll('.stat-card, .mini-card');
        cards.forEach(card => {
            card.style.boxShadow = 'none';
            card.style.border = '1px solid #ddd';
            card.style.pageBreakInside = 'avoid';
            card.style.marginBottom = '15px';
        });
        
        // Ajustar tabelas
        const tables = clonedContent.querySelectorAll('table');
        tables.forEach(table => {
            table.style.fontSize = '12px';
            table.style.pageBreakInside = 'avoid';
        });
        
        return clonedContent;
    }

    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async exportToExcel() {
        try {
            this.showExportProgress('Gerando Excel...');
            
            // Verificar se XLSX está disponível
            if (typeof XLSX === 'undefined') {
                await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js');
            }
            
            const reportData = this.collectReportData();
            
            // Criar workbook
            const wb = XLSX.utils.book_new();
            
            // Sheet Resumo Geral
            if (Object.keys(reportData.resumoGeral).length > 0) {
                const resumoData = Object.entries(reportData.resumoGeral).map(([key, value]) => ({
                    'Métrica': key,
                    'Valor': value
                }));
                const resumoSheet = XLSX.utils.json_to_sheet(resumoData);
                XLSX.utils.book_append_sheet(wb, resumoSheet, 'Resumo Geral');
            }

            // Sheet Eventos Status
            if (Object.keys(reportData.eventosStatus).length > 0) {
                const eventosData = Object.entries(reportData.eventosStatus).map(([key, value]) => ({
                    'Status': key,
                    'Quantidade': value
                }));
                const eventosSheet = XLSX.utils.json_to_sheet(eventosData);
                XLSX.utils.book_append_sheet(wb, eventosSheet, 'Eventos Status');
            }

            // Sheet Atividades Status
            if (Object.keys(reportData.atividadesStatus).length > 0) {
                const atividadesData = Object.entries(reportData.atividadesStatus).map(([key, value]) => ({
                    'Status': key,
                    'Quantidade': value
                }));
                const atividadesSheet = XLSX.utils.json_to_sheet(atividadesData);
                XLSX.utils.book_append_sheet(wb, atividadesSheet, 'Atividades Status');
            }

            // Sheet Equipes
            if (reportData.equipesDistribuicao.length > 0) {
                const equipesSheet = XLSX.utils.json_to_sheet(reportData.equipesDistribuicao);
                XLSX.utils.book_append_sheet(wb, equipesSheet, 'Equipes');
            }

            // Sheet Participação das Crianças
            if (reportData.participacaoCriancas.length > 0) {
                const participacaoSheet = XLSX.utils.json_to_sheet(reportData.participacaoCriancas);
                XLSX.utils.book_append_sheet(wb, participacaoSheet, 'Participação');
            }

            // Sheet Atividades Pendentes
            if (reportData.atividadesPendentes.length > 0) {
                const atividadesPendentesSheet = XLSX.utils.json_to_sheet(reportData.atividadesPendentes);
                XLSX.utils.book_append_sheet(wb, atividadesPendentesSheet, 'Atividades Pendentes');
            }

            // Sheet Eventos Próximos
            if (reportData.eventosProximos.length > 0) {
                const eventosProximosSheet = XLSX.utils.json_to_sheet(reportData.eventosProximos);
                XLSX.utils.book_append_sheet(wb, eventosProximosSheet, 'Eventos Próximos');
            }

            // Sheet Logs Recentes
            if (reportData.logsRecentes.length > 0) {
                const logsSheet = XLSX.utils.json_to_sheet(reportData.logsRecentes);
                XLSX.utils.book_append_sheet(wb, logsSheet, 'Logs Recentes');
            }
            
            // Download
            const filename = `relatorio_magickids_${this.getCurrentDateTime()}.xlsx`;
            XLSX.writeFile(wb, filename);
            
            this.hideExportProgress();
            this.showSuccessMessage('Excel exportado com sucesso!');
            
        } catch (error) {
            console.error('Erro ao exportar Excel:', error);
            this.hideExportProgress();
            this.showErrorMessage('Erro ao exportar Excel. Verifique se as bibliotecas estão carregadas.');
        }
    }

    async exportToCSV() {
        try {
            this.showExportProgress('Gerando CSV...');
            
            const reportData = this.collectReportData();
            
            await this.delay(1000);
            
            const csv = this.convertToCSV(reportData);
            this.downloadCSV(csv, `relatorio_magickids_${this.getCurrentDateTime()}.csv`);
            
            this.hideExportProgress();
            this.showSuccessMessage('CSV exportado com sucesso!');
            
        } catch (error) {
            console.error('Erro ao exportar CSV:', error);
            this.showErrorMessage('Erro ao exportar CSV. Tente novamente.');
        }
    }

    collectReportData() {
        const data = {
            resumoGeral: {},
            eventosStatus: {},
            atividadesStatus: {},
            equipesDistribuicao: [],
            participacaoCriancas: [],
            atividadesPendentes: [],
            eventosProximos: [],
            logsRecentes: []
        };

        // Coletar estatísticas gerais
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            const label = card.querySelector('.stat-label')?.textContent.trim();
            const number = card.querySelector('.stat-number')?.textContent.trim();
            if (label && number) {
                data.resumoGeral[label] = number;
            }
        });

        // Coletar dados das tabelas
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            
            const tableData = rows.map(row => {
                const cells = Array.from(row.querySelectorAll('td'));
                const rowData = {};
                cells.forEach((cell, index) => {
                    if (headers[index]) {
                        rowData[headers[index]] = cell.textContent.trim();
                    }
                });
                return rowData;
            });

            // Identificar qual tabela pelos cabeçalhos
            if (headers.includes('Equipe')) {
                data.equipesDistribuicao = tableData;
            } else if (headers.includes('Nome') && headers.includes('Eventos')) {
                data.participacaoCriancas = tableData;
            }
        });

        // Coletar dados dos status
        document.querySelectorAll('.list-group').forEach(list => {
            const items = Array.from(list.querySelectorAll('.list-group-item'));
            const statusData = {};
            items.forEach(item => {
                const text = item.querySelector('span:first-child')?.textContent.trim();
                const count = item.querySelector('.badge')?.textContent.trim();
                if (text && count) {
                    statusData[text] = count;
                }
            });

            // Determinar se é eventos ou atividades pelo contexto do card pai
            const parentCard = list.closest('.card');
            const cardTitle = parentCard?.querySelector('h5')?.textContent.toLowerCase();
            
            if (cardTitle?.includes('evento')) {
                data.eventosStatus = statusData;
            } else if (cardTitle?.includes('atividade')) {
                data.atividadesStatus = statusData;
            }
        });

        // Coletar atividades pendentes
        document.querySelectorAll('.activity-item').forEach(item => {
            const titulo = item.querySelector('.fw-semibold')?.textContent.trim();
            const evento = item.querySelectorAll('.text-muted')[0]?.textContent.replace('Evento: ', '').trim();
            const responsavel = item.querySelectorAll('.text-muted')[1]?.textContent.replace('Responsável: ', '').trim();
            const prazo = item.querySelectorAll('.text-muted')[2]?.textContent.replace('Fim previsto: ', '').trim();
            
            if (titulo) {
                data.atividadesPendentes.push({
                    titulo,
                    evento,
                    responsavel,
                    prazo
                });
            }
        });

        // Coletar eventos próximos
        document.querySelectorAll('.timeline-item').forEach(item => {
            const nome = item.querySelector('.fw-semibold')?.textContent.trim();
            const data_inicio = item.querySelectorAll('.text-muted')[0]?.textContent.replace('Início: ', '').trim();
            const status = item.querySelector('.badge')?.textContent.trim();
            
            if (nome) {
                data.eventosProximos.push({
                    nome,
                    data_inicio,
                    status
                });
            }
        });

        // Coletar logs recentes
        document.querySelectorAll('.log-item').forEach(item => {
            const acao = item.querySelector('.fw-semibold')?.textContent.trim();
            const usuario = item.querySelectorAll('.text-muted')[0]?.textContent.replace('Usuário: ', '').trim();
            const tabela = item.querySelectorAll('.text-muted')[1]?.textContent.replace('Tabela: ', '').trim();
            const data_criacao = item.querySelectorAll('.text-muted')[2]?.textContent.replace('Data: ', '').trim();
            
            if (acao) {
                data.logsRecentes.push({
                    acao,
                    usuario,
                    tabela,
                    data_criacao
                });
            }
        });

        return data;
    }

    getCurrentDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        return `${year}-${month}-${day}_${hours}-${minutes}-${seconds}`;
    }

    convertToCSV(data) {
        let csv = '';
        
        // Cabeçalho do relatório
        csv += `"RELATÓRIO MAGICKIDS EVENTOS"\n`;
        csv += `"Gerado em: ${new Date().toLocaleString('pt-BR')}"\n`;
        csv += '\n';
        
        // Resumo geral
        if (Object.keys(data.resumoGeral).length > 0) {
            csv += '"RESUMO GERAL"\n';
            csv += '"Métrica","Valor"\n';
            Object.entries(data.resumoGeral).forEach(([key, value]) => {
                csv += `"${key}","${value}"\n`;
            });
            csv += '\n';
        }

        // Status dos eventos
        if (Object.keys(data.eventosStatus).length > 0) {
            csv += '"STATUS DOS EVENTOS"\n';
            csv += '"Status","Quantidade"\n';
            Object.entries(data.eventosStatus).forEach(([key, value]) => {
                csv += `"${key}","${value}"\n`;
            });
            csv += '\n';
        }

        // Status das atividades
        if (Object.keys(data.atividadesStatus).length > 0) {
            csv += '"STATUS DAS ATIVIDADES"\n';
            csv += '"Status","Quantidade"\n';
            Object.entries(data.atividadesStatus).forEach(([key, value]) => {
                csv += `"${key}","${value}"\n`;
            });
            csv += '\n';
        }

        // Equipes
        if (data.equipesDistribuicao.length > 0) {
            csv += '"EQUIPES"\n';
            const headers = Object.keys(data.equipesDistribuicao[0]);
            csv += headers.map(h => `"${h}"`).join(',') + '\n';
            data.equipesDistribuicao.forEach(row => {
                csv += headers.map(h => `"${row[h] || ''}"`).join(',') + '\n';
            });
            csv += '\n';
        }

        // Participação das crianças
        if (data.participacaoCriancas.length > 0) {
            csv += '"PARTICIPAÇÃO DAS CRIANÇAS"\n';
            const headers = Object.keys(data.participacaoCriancas[0]);
            csv += headers.map(h => `"${h}"`).join(',') + '\n';
            data.participacaoCriancas.forEach(row => {
                csv += headers.map(h => `"${row[h] || ''}"`).join(',') + '\n';
            });
            csv += '\n';
        }

        // Atividades pendentes
        if (data.atividadesPendentes.length > 0) {
            csv += '"ATIVIDADES PENDENTES"\n';
            csv += '"Título","Evento","Responsável","Prazo"\n';
            data.atividadesPendentes.forEach(item => {
                csv += `"${item.titulo}","${item.evento}","${item.responsavel}","${item.prazo}"\n`;
            });
            csv += '\n';
        }

        // Eventos próximos
        if (data.eventosProximos.length > 0) {
            csv += '"EVENTOS PRÓXIMOS"\n';
            csv += '"Nome","Data Início","Status"\n';
            data.eventosProximos.forEach(item => {
                csv += `"${item.nome}","${item.data_inicio}","${item.status}"\n`;
            });
            csv += '\n';
        }

        // Logs recentes
        if (data.logsRecentes.length > 0) {
            csv += '"LOGS RECENTES"\n';
            csv += '"Ação","Usuário","Tabela","Data"\n';
            data.logsRecentes.forEach(item => {
                csv += `"${item.acao}","${item.usuario}","${item.tabela}","${item.data_criacao}"\n`;
            });
        }

        return csv;
    }

    downloadCSV(csv, filename) {
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    showExportProgress(message) {
        // Criar modal de progresso
        const modal = document.createElement('div');
        modal.id = 'exportProgressModal';
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <h5>${message}</h5>
                        <p class="text-muted">Aguarde enquanto processamos o relatório...</p>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    hideExportProgress() {
        const modal = document.getElementById('exportProgressModal');
        if (modal) {
            modal.remove();
        }
    }

    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    showErrorMessage(message) {
        this.showToast(message, 'danger');
    }

    showToast(message, type = 'info') {
        // Criar container de toasts se não existir
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Inicializar e mostrar toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remover toast após ser ocultado
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    new RelatoriosExporter();
});