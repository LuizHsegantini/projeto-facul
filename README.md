# *MagicKids Eventos*

## Sistema de gestão de eventos infantis

## 📝 **Requisitos para Rodar**

🧾 Para executar o sistema de gestão de eventos infantis *MagicKids Eventos*, são **necessários**:

- ![Visual Studio Code](https://img.shields.io/badge/Visual%20Studio%20Code-0078d7.svg?style=for-the-badge&logo=visual-studio-code&logoColor=white)  Editor de código-fonte gratuito e de código aberto da Microsoft.

- ![XAMPP](https://img.shields.io/badge/Xampp-F37623?style=for-the-badge&logo=xampp&logoColor=white) Necessário para criar um servidor web local no seu computador. 

- ![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white) Linguagem de programação utilizada.

- **HeidiSQL**: Ferramenta para gerenciamento, desenvolvimento e administração de bancos de dados.

### Você pode **baixar por aqui**: [VS CODE](https://code.visualstudio.com/) , [XAMPP](https://www.apachefriends.org/pt_br/index.html) , [PHP](https://www.php.net/) e [HeidiSQL](https://www.heidisql.com/download.php)


## Como Executar

Repositório:


 ```bash
  https://github.com/LuizHsegantini/projeto-facul.git
 ````

1. Faça o download do **Zip** na aba Code do repositório

2. Procure o *Disco Local (C:)* ➝ xampp ➝ htdocs ➝ crie uma pasta.

3. Com a pasta criada, **extraia** os arquivos nela.

4. Abra a pasta do projeto no *Visual Studio Code*.

5. Copie o banco de dados **“banco.sql”** ➝ abra o **HeidSQL** ➝ *crie uma nova seção no HeidiSQL* ➝ na aba **Consulta** *cole* os códigos e *execute*.

6. O próximo passo é digitar no navegador: “localhost/nomedapasta/login.php”

7. Siga o menu interativo de login para iniciar a sessão.


_______________________________________________________________________________________________________________________________________



### Check List de Erros solucionados

- Na tela de check in/check out se você tentar mudar o evento, ele dá uma bugada e não sai do que foi selecionado primeiro.
- Na tela de perfil, os icones de estatísticas estavam girando. Mas o erro foi corrigido. 

# MagicKids Eventos - Sistema de Gestão

Sistema completo para gerenciamento de eventos infantis, desenvolvido para facilitar o cadastro, organização e controle de atividades para crianças.

## Índice

- [Sobre o Sistema](#sobre-o-sistema)
- [Funcionalidades Principais](#funcionalidades-principais)
- [Como Acessar](#como-acessar)
- [Cadastro de Crianças](#cadastro-de-crianças)
- [Módulos do Sistema](#módulos-do-sistema)
- [Perfis e Permissões](#perfis-e-permissões)
- [Recursos Avançados](#recursos-avançados)
- [Estrutura do Sistema](#estrutura-do-sistema)
- [Instalação](#instalação)
- [Suporte](#suporte)

## Sobre o Sistema

O MagicKids Eventos é uma plataforma web desenvolvida para empresas e organizadores de eventos infantis, oferecendo controle completo sobre cadastros, eventos, equipes e relatórios.

## Funcionalidades Principais

### 1. Cadastro de Crianças (Funcionalidade Principal)

**Formulário Completo com Validações Automáticas:**
- **Dados Pessoais**: Nome completo, data de nascimento, sexo
- **Validação de Idade**: Aceita apenas crianças entre 1 e 12 anos
- **Cálculo Automático**: Idade calculada automaticamente pela data de nascimento
- **Informações de Saúde**:
  - Alergias a alimentos (ex: amendoim, leite, ovos)
  - Alergias a medicamentos
  - Restrições alimentares (vegetariano, intolerância a lactose, glúten)
  - Observações de saúde (hiperativo, usa óculos, diabetes)

**Dados do Responsável:**
- Nome completo do responsável
- Grau de parentesco (Pai, Mãe, Avô, Avó, Tio, Tia, Tutor Legal)
- Telefone principal com formatação automática (XX) XXXXX-XXXX
- Telefone alternativo
- Endereço completo
- CPF com validação usando algoritmo MOD 11
- E-mail (opcional)

**Contato de Emergência:**
- Nome completo para emergências
- Telefone de emergência
- Grau de parentesco
- Autorização para retirada da criança

**Validações em Tempo Real:**
- CPF: Validação matemática completa
- Telefone: Formatação automática durante a digitação
- Data: Verificação de idade mínima/máxima
- Campos obrigatórios: Indicação visual de erros

### 2. Sistema de Login e Autenticação

**Página de Login (login.php):**
- Interface limpa e intuitiva
- Validação de credenciais
- Usuários de demonstração pré-configurados
- Limpeza automática de sessões problemáticas
- Funcionalidade mostrar/ocultar senha

**Usuários de Demonstração:**
- **Administrador**: admin / 123456
- **Coordenador**: gerente / 123456  
- **Animador**: colaborador / 123456

**Recursos de Segurança:**
- Hash MD5 para senhas
- Controle de sessões
- Logs de login/logout
- Prevenção contra múltiplos submits
- Limpeza automática de storage

## Módulos do Sistema

### Dashboard Executivo (dashboard_eventos.php)
- **Estatísticas em Tempo Real**:
  - Total de eventos cadastrados
  - Eventos ativos no momento
  - Total de crianças inscritas
  - Check-ins realizados hoje
  - Equipes ativas
  - Número de funcionários
- **Cards Interativos**: Animações e efeitos visuais
- **Próximos Eventos**: Lista cronológica
- **Aniversariantes do Mês**: Destaque especial
- **Ações Rápidas**: Acesso direto às funcionalidades mais usadas
- **Auto-refresh**: Atualização automática a cada 5 minutos

### Gestão de Crianças (criancas.php)
- **Lista Completa**: Visualização em cards com avatares
- **Filtros Avançados**:
  - Busca por nome ou responsável
  - Filtro por idade (mínima e máxima)
  - Filtro por sexo
  - Status (ativo/inativo/todos)
- **Informações Visuais**:
  - Indicador de aniversário hoje
  - Alertas de alergia em vermelho
  - Status de ativo/inativo
- **Paginação**: 15 registros por página
- **Ações**: Visualizar detalhes, editar, alterar status, excluir

### Gestão de Eventos (eventos.php)
- **Criação de Eventos com Validação AJAX**:
  - Tipos: Festa de Aniversário, Workshop, Acampamento, Gincana, Teatro, Esportes, Arte e Pintura, Culinária, Dança
  - Validação de datas (não permite eventos no passado)
  - Validação de faixa etária
  - Cálculo automático de data fim baseado na duração
- **Lista de Eventos**: Cards com informações completas
- **Status**: Planejado, Em Andamento, Concluído, Cancelado
- **Capacidade**: Barra de progresso visual das inscrições
- **Ações Rápidas**: Ver detalhes, check-in direto

### Check-in Digital (checkin.php)
- **Seleção de Evento**: Lista de eventos ativos
- **Interface de Check-in**:
  - Cards visuais para cada criança
  - Avatares com iniciais
  - Destaque para alergias
  - Status em tempo real (Confirmado, Check-in, Check-out)
- **Informações Completas**:
  - Dados da criança e responsável
  - Horário de check-in e check-out
  - Funcionário responsável pelo processo
- **Busca Rápida**: Modal para encontrar criança específica
- **Auto-refresh**: Atualização automática a cada 30 segundos

### Gestão de Atividades (atividades.php)
- **Tipos de Atividade**: Recreação, Alimentação, Oficina, Show, Brincadeira, Cuidados, Limpeza, Setup
- **Controle de Status**: Pendente, Em Execução, Concluída
- **Atribuição de Responsáveis**: Vinculação com funcionários
- **Controle de Datas**:
  - Data de início
  - Data fim prevista
  - Data fim real
- **Validações**: Datas não podem ser inconsistentes
- **Filtros**: Por status, evento, responsável

### Gestão de Equipes (equipes.php)
- **Especialidades Predefinidas**: Animação, Recreação, Culinária, Segurança, Limpeza, Arte, Música, Teatro, Esportes, Multidisciplinar
- **Capacidade de Eventos**: Quantos eventos simultâneos a equipe suporta
- **Gerenciamento de Membros**:
  - Adicionar funcionários às equipes
  - Remover membros
  - Visualizar histórico de participação
- **Estatísticas**: Total de membros, média por equipe

### Gestão de Funcionários (funcionarios.php)
- **Cadastro Completo**:
  - Dados pessoais com validação de CPF
  - Cargos personalizáveis
  - Perfis de acesso (Administrador, Coordenador, Animador, Monitor, Auxiliar)
- **Controle de Acesso**: Criação e edição de credenciais
- **Histórico**: Data de criação e última atualização
- **Estatísticas**: Resumo por perfil, novos cadastros

### Sistema de Relatórios (relatorios.php)
- **Resumo Geral**: Estatísticas consolidadas do sistema
- **Gráficos e Distribuições**:
  - Eventos por status
  - Atividades por status
  - Distribuição de equipes
  - Participação das crianças
- **Top 5**: Crianças mais participativas
- **Atividades Pendentes**: Lista de tarefas em aberto
- **Eventos Próximos**: Cronograma futuro
- **Auto-refresh**: Atualização automática
- **Funcionalidade de Export**: PDF, Excel, CSV (preparado para implementação)

### Logs do Sistema (logs.php)
- **Auditoria Completa**: Registro de todas as ações
- **Filtros Avançados**:
  - Por usuário
  - Por ação
  - Por tabela afetada
  - Por período
- **Informações Detalhadas**:
  - Dados anteriores e novos (em JSON)
  - IP do usuário
  - Timestamp preciso
- **Limpeza Automática**: Remoção de logs antigos (30, 90, 365 dias)
- **Export**: CSV com dados completos
- **Estatísticas**: Top usuários e ações mais frequentes

### Perfil do Usuário (profile.php)
- **Informações Pessoais**: Edição de dados do usuário logado
- **Alteração de Senha**: Com validação de senha atual
- **Estatísticas Pessoais**:
  - Logs de atividade
  - Equipes participando
  - Atividades atribuídas
  - Eventos coordenados
- **Histórico de Atividades**: Últimas ações realizadas
- **Participação em Equipes**: Lista com datas de entrada

## Perfis e Permissões

### Administrador
- **Acesso Total**: Todas as funcionalidades
- **Exclusivo**:
  - Gestão de funcionários
  - Logs do sistema
  - Exclusão de registros
  - Limpeza de dados
- **Dashboard**: Estatísticas completas

### Coordenador
- **Gestão Operacional**: Eventos, crianças, atividades, equipes
- **Relatórios**: Acesso completo a análises
- **Limitações**: Não gerencia funcionários nem logs
- **Check-in**: Controle total de presença

### Animador/Monitor
- **Execução**: Atividades e eventos
- **Cadastros**: Crianças e check-in
- **Visualização**: Dados limitados ao necessário
- **Dashboard**: Informações básicas

### Auxiliar
- **Básico**: Apenas check-in e visualização de crianças
- **Acesso Mínimo**: Dashboard simplificado
- **Sem Edição**: Apenas consulta

## Recursos Avançados

### Validações e Formatações
- **CPF**: Algoritmo MOD 11 completo
- **Telefone**: Formatação automática durante digitação
- **Idade**: Cálculo automático e validação de faixa etária
- **Datas**: Validação de consistência entre datas

### Interface e Experiência
- **Design Responsivo**: Funciona em desktop, tablet e mobile
- **Animações**: Efeitos visuais em cards e botões
- **Feedback Visual**: Indicadores de carregamento e sucesso
- **Auto-complete**: Campos com sugestões
- **Tooltips**: Ajuda contextual

### Segurança
- **Autenticação**: Sistema de login com sessões
- **Autorização**: Controle granular de permissões
- **Auditoria**: Logs detalhados de todas as ações
- **Validação**: Sanitização de dados de entrada
- **CSRF Protection**: Proteção contra ataques

### Performance
- **Paginação**: Carregamento otimizado de listas
- **Cache**: Sessões e dados temporários
- **AJAX**: Carregamento assíncrono quando necessário
- **Auto-refresh**: Atualização inteligente de dados

## Estrutura do Sistema

```
MagicKids/
├── assets/
│   ├── css/ (10 arquivos de estilo)
│   └── js/ (JavaScript específico)
├── config/
│   └── database.php (Configuração PDO)
├── controllers/ (8 controllers)
│   ├── AtividadesController.php
│   ├── CriancasController.php
│   ├── EquipesController.php
│   ├── EventosController.php
│   ├── FuncionariosController.php
│   ├── LogsController.php
│   ├── ProfileController.php
│   └── RelatoriosController.php
├── includes/
│   ├── auth.php (Autenticação)
│   └── LogService.php (Logs)
├── models/ (Páginas principais)
│   ├── dashboard_eventos.php
│   ├── criancas.php
│   ├── eventos.php
│   ├── checkin.php
│   ├── atividades.php
│   ├── equipes.php
│   ├── funcionarios.php
│   ├── relatorios.php
│   ├── logs.php
│   └── profile.php
├── cadastro_crianca.php (PRINCIPAL)
├── login.php (ACESSO)
└── README.md
```

### Banco de Dados
- **Tabelas Principais**: usuarios, criancas_cadastro, eventos, atividades, equipes
- **Relacionamentos**: Chaves estrangeiras bem definidas
- **Logs**: Tabela logs_sistema para auditoria
- **Índices**: Otimização de consultas

## Instalação

1. **Requisitos**:
   - PHP 7.4+
   - MySQL 5.7+
   - Apache/Nginx
   - Navegador moderno

2. **Configuração**:
   - Configure `config/database.php`
   - Importe o schema do banco
   - Configure permissões de diretório
   - Teste as credenciais de demonstração

3. **Primeira Execução**:
   - Acesse `login.php`
   - Use admin/123456
   - Configure novos usuários
   - Teste o cadastro de criança

## Recursos Técnicos Específicos

### JavaScript/Frontend
- **Bootstrap 5.3.2**: Framework CSS responsivo
- **Font Awesome 6.4.0**: Ícones completos
- **Validação em Tempo Real**: CPF, telefone, datas
- **Máscaras de Input**: Formatação automática
- **Modais**: Interface para ações complexas
- **AJAX**: Requisições assíncronas

### PHP/Backend
- **PDO**: Conexão segura com banco de dados
- **MVC Pattern**: Separação clara de responsabilidades
- **Sanitização**: Proteção contra XSS e SQL Injection
- **Sessions**: Gerenciamento seguro de estado
- **Error Handling**: Tratamento robusto de erros

### Base de Dados
- **MySQL**: Banco relacional otimizado
- **Triggers**: Automação de logs
- **Views**: Consultas otimizadas
- **Procedures**: Operações complexas

## Suporte

- **Documentação**: Código comentado e estruturado
- **Logs**: Sistema completo de auditoria
- **Backup**: Recomendado backup diário
- **Manutenção**: Limpeza automática de logs antigos

---

**MagicKids Eventos** - Sistema desenvolvido para proporcionar gestão completa e segura de eventos infantis.
