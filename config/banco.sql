-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS sistema_projetos;
USE sistema_projetos;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    cargo VARCHAR(100),
    login VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('administrador', 'gerente', 'colaborador') NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de projetos
CREATE TABLE IF NOT EXISTS projetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    data_inicio DATE,
    data_termino_prevista DATE,
    status ENUM('planejado', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'planejado',
    gerente_id INT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gerente_id) REFERENCES usuarios(id)
);

-- Tabela de tarefas
CREATE TABLE IF NOT EXISTS tarefas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    projeto_id INT NOT NULL,
    responsavel_id INT,
    status ENUM('pendente', 'em_execucao', 'concluida') DEFAULT 'pendente',
    data_inicio DATE,
    data_fim_prevista DATE,
    data_fim_real DATE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
);

-- Tabela de equipes
CREATE TABLE IF NOT EXISTS equipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de membros das equipes
CREATE TABLE IF NOT EXISTS equipe_membros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipe_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_entrada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipe_id) REFERENCES equipes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_member (equipe_id, usuario_id)
);

-- Tabela de relacionamento entre projetos e equipes
CREATE TABLE IF NOT EXISTS projeto_equipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    equipe_id INT NOT NULL,
    data_atribuicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (equipe_id) REFERENCES equipes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (projeto_id, equipe_id)
);

-- Tabela de logs do sistema
CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(255) NOT NULL,
    tabela_afetada VARCHAR(100),
    registro_id INT,
    dados_anteriores JSON,
    dados_novos JSON,
    ip_address VARCHAR(45),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Inserir usuários de teste
INSERT INTO usuarios (nome_completo, cpf, email, cargo, login, senha, perfil) VALUES
('Administrador do Sistema', '000.000.000-00', 'admin@techcorp.com', 'Administrador', 'admin', MD5('123456'), 'administrador'),
('João Silva Santos', '111.111.111-11', 'joao@techcorp.com', 'Gerente de Projetos', 'gerente', MD5('123456'), 'gerente'),
('Maria Oliveira Costa', '222.222.222-22', 'maria@techcorp.com', 'Desenvolvedora Senior', 'colaborador', MD5('123456'), 'colaborador'),
('Pedro Almeida Lima', '333.333.333-33', 'pedro@techcorp.com', 'Analista de Sistemas', 'pedro', MD5('123456'), 'colaborador'),
('Ana Carolina Ferreira', '444.444.444-44', 'ana@techcorp.com', 'Designer UX/UI', 'ana', MD5('123456'), 'colaborador');

-- Inserir projetos de exemplo
INSERT INTO projetos (nome, descricao, data_inicio, data_termino_prevista, status, gerente_id) VALUES
('Sistema de E-commerce', 'Desenvolvimento de plataforma de vendas online completa', '2024-01-15', '2024-06-30', 'em_andamento', 2),
('App Mobile Corporativo', 'Aplicativo móvel para gestão interna da empresa', '2024-02-01', '2024-05-15', 'em_andamento', 2),
('Migração para Cloud', 'Migração da infraestrutura atual para AWS', '2024-03-01', '2024-08-30', 'planejado', 2),
('Sistema de CRM', 'Customer Relationship Management personalizado', '2023-11-01', '2024-02-28', 'concluido', 2),
('Portal do Cliente', 'Portal web para atendimento ao cliente', '2024-01-10', '2024-04-10', 'cancelado', 2);

-- Inserir equipes
INSERT INTO equipes (nome, descricao) VALUES
('Desenvolvimento Frontend', 'Equipe responsável pelo desenvolvimento da interface do usuário'),
('Desenvolvimento Backend', 'Equipe responsável pela lógica de negócio e APIs'),
('DevOps', 'Equipe responsável pela infraestrutura e deploy'),
('UX/UI Design', 'Equipe responsável pela experiência e interface do usuário'),
('Quality Assurance', 'Equipe responsável pelos testes e qualidade do software');

-- Inserir membros nas equipes
INSERT INTO equipe_membros (equipe_id, usuario_id) VALUES
(1, 3), (1, 4),  -- Frontend: Maria, Pedro
(2, 3), (2, 5),  -- Backend: Maria, Ana
(3, 4),          -- DevOps: Pedro
(4, 5),          -- UX/UI: Ana
(5, 3), (5, 4);  -- QA: Maria, Pedro

-- Inserir relacionamento projeto-equipe
INSERT INTO projeto_equipes (projeto_id, equipe_id) VALUES
(1, 1), (1, 2), (1, 4), (1, 5),  -- E-commerce: Frontend, Backend, UX/UI, QA
(2, 1), (2, 2), (2, 4),          -- App Mobile: Frontend, Backend, UX/UI
(3, 3),                          -- Cloud: DevOps
(4, 2), (4, 5),                  -- CRM: Backend, QA
(5, 1), (5, 4);                  -- Portal: Frontend, UX/UI

-- Inserir tarefas de exemplo
INSERT INTO tarefas (titulo, descricao, projeto_id, responsavel_id, status, data_inicio, data_fim_prevista) VALUES
('Configurar ambiente de desenvolvimento', 'Preparar ambiente local e repositório', 1, 3, 'concluida', '2024-01-15', '2024-01-20'),
('Desenvolver tela de login', 'Criar interface de autenticação', 1, 3, 'concluida', '2024-01-21', '2024-01-25'),
('Implementar API de produtos', 'Criar endpoints para gestão de produtos', 1, 4, 'em_execucao', '2024-01-26', '2024-02-05'),
('Design da homepage', 'Criar layout da página inicial', 1, 5, 'em_execucao', '2024-01-22', '2024-02-01'),
('Testes de integração', 'Executar testes entre componentes', 1, 3, 'pendente', '2024-02-06', '2024-02-10'),

('Prototipagem do app', 'Criar protótipo navegável', 2, 5, 'concluida', '2024-02-01', '2024-02-10'),
('Configurar React Native', 'Setup do projeto mobile', 2, 3, 'em_execucao', '2024-02-11', '2024-02-15'),
('Desenvolver navegação', 'Implementar sistema de rotas', 2, 4, 'pendente', '2024-02-16', '2024-02-25'),

('Análise de custos AWS', 'Levantamento de custos da migração', 3, 4, 'pendente', '2024-03-01', '2024-03-10'),
('Plano de migração', 'Documentar estratégia de migração', 3, 4, 'pendente', '2024-03-11', '2024-03-20');

-- Inserir alguns logs de exemplo
INSERT INTO logs_sistema (usuario_id, acao, ip_address) VALUES
(1, 'Login realizado', '192.168.1.100'),
(2, 'Login realizado', '192.168.1.101'),
(3, 'Login realizado', '192.168.1.102');