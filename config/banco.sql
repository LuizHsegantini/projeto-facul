-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.4.32-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para sistema_projetos
CREATE DATABASE IF NOT EXISTS `sistema_projetos` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `sistema_projetos`;

-- Copiando estrutura para tabela sistema_projetos.criancas_cadastro
CREATE TABLE IF NOT EXISTS `criancas_cadastro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) NOT NULL,
  `data_nascimento` date NOT NULL,
  `idade` int(3) NOT NULL,
  `sexo` enum('Masculino','Feminino') NOT NULL,
  `alergia_alimentos` text DEFAULT NULL,
  `alergia_medicamentos` text DEFAULT NULL,
  `restricoes_alimentares` text DEFAULT NULL,
  `observacoes_saude` text DEFAULT NULL,
  `nome_responsavel` varchar(255) NOT NULL,
  `grau_parentesco` varchar(50) NOT NULL,
  `telefone_principal` varchar(20) NOT NULL,
  `telefone_alternativo` varchar(20) DEFAULT NULL,
  `endereco_completo` text NOT NULL,
  `documento_rg_cpf` varchar(20) NOT NULL,
  `email_responsavel` varchar(255) DEFAULT NULL,
  `nome_emergencia` varchar(255) NOT NULL,
  `telefone_emergencia` varchar(20) NOT NULL,
  `grau_parentesco_emergencia` varchar(50) NOT NULL,
  `autorizacao_retirada` enum('Sim','Não') NOT NULL DEFAULT 'Não',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_nome` (`nome_completo`),
  KEY `idx_idade` (`idade`),
  KEY `idx_data_nascimento` (`data_nascimento`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.criancas_cadastro: ~5 rows (aproximadamente)
INSERT INTO `criancas_cadastro` (`id`, `nome_completo`, `data_nascimento`, `idade`, `sexo`, `alergia_alimentos`, `alergia_medicamentos`, `restricoes_alimentares`, `observacoes_saude`, `nome_responsavel`, `grau_parentesco`, `telefone_principal`, `telefone_alternativo`, `endereco_completo`, `documento_rg_cpf`, `email_responsavel`, `nome_emergencia`, `telefone_emergencia`, `grau_parentesco_emergencia`, `autorizacao_retirada`, `data_cadastro`, `data_atualizacao`, `ativo`) VALUES
	(1, 'Gabriel Silva Santos', '2018-03-15', 6, 'Masculino', 'Amendoim', NULL, 'Sem glúten', 'Criança ativa, gosta de futebol', 'Ana Silva Santos', 'Mãe', '(11) 98765-4321', NULL, 'Rua das Flores, 123 - Vila Esperança, São Paulo - SP - 05678-900', '123.456.789-00', NULL, 'Carlos Silva Santos', '(11) 99876-5432', 'Pai', 'Sim', '2025-09-17 23:39:20', '2025-09-17 23:39:20', 1),
	(2, 'Sofia Oliveira Costa', '2017-08-22', 7, 'Feminino', '', NULL, 'Vegetariana', 'Usa óculos, muito criativa', 'Mariana Oliveira Costa', 'Mãe', '(11) 94567-8901', NULL, 'Av. Paulista, 500 - Bela Vista, São Paulo - SP - 01310-100', '987.654.321-11', NULL, 'Roberto Costa Lima', '(11) 93456-7890', 'Avô', 'Sim', '2025-09-17 23:39:20', '2025-09-17 23:39:20', 1),
	(3, 'Lucas Ferreira Lima', '2019-01-10', 5, 'Masculino', 'Leite, Ovos', NULL, 'Intolerante à lactose', 'Alérgico, sempre levar medicação', 'Patricia Ferreira Lima', 'Mãe', '(11) 92345-6789', NULL, 'Rua do Sol, 789 - Jardim América, São Paulo - SP - 04567-123', '456.789.123-22', NULL, 'João Ferreira Santos', '(11) 91234-5678', 'Tio', 'Não', '2025-09-17 23:39:20', '2025-09-17 23:39:20', 1),
	(4, 'Luiz henrique Segantini', '2018-01-18', 7, 'Masculino', 'Leite, Ovo e Churrasco', 'não', 'não', 'Oculos', 'Luiza Prado Claro', 'Pai', '(98) 08319-83111', '(10) 21902-890192091', 'jdjadijaikdjiad', '538948303843', 'kjaidhjidh@gmail.com', 'qadadada', '(11) 21212-1212', '12adadad', 'Sim', '2025-09-17 23:41:41', '2025-09-17 23:41:41', 1),
	(6, 'adad', '2024-02-16', 1, 'Feminino', '', '', '', '', 'adad', 'Mãe', '(12) 1212-1212', '(12) 12112-121212', '121212', 'ada', 'adadad@gmail.com', 'adadadad', '(15) 45485-7487878', 'ada', 'Sim', '2025-09-18 01:57:34', '2025-09-18 01:58:27', 0);

-- Copiando estrutura para tabela sistema_projetos.equipes
CREATE TABLE IF NOT EXISTS `equipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `especialidade` enum('Animação','Recreação','Culinária','Segurança','Limpeza','Arte','Música','Teatro','Esportes','Multidisciplinar') DEFAULT 'Multidisciplinar',
  `capacidade_eventos` int(3) DEFAULT 1,
  `descricao` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.equipes: ~5 rows (aproximadamente)
INSERT INTO `equipes` (`id`, `nome`, `especialidade`, `capacidade_eventos`, `descricao`, `data_criacao`, `data_atualizacao`) VALUES
	(1, 'Equipe de Animação', 'Animação', 1, 'Responsável pela animação e entretenimento das crianças', '2025-09-16 22:21:40', '2025-09-17 23:39:20'),
	(2, 'Equipe de Recreação', 'Recreação', 1, 'Responsável pelas atividades recreativas e brincadeiras', '2025-09-16 22:21:40', '2025-09-17 23:39:20'),
	(3, 'Equipe de Segurança', 'Segurança', 1, 'Responsável pela segurança e bem-estar das crianças', '2025-09-16 22:21:40', '2025-09-17 23:39:20'),
	(4, 'Equipe de Arte e Criatividade', 'Arte', 5, 'Responsável por oficinas de arte e atividades criativas', '2025-09-16 22:21:40', '2025-09-18 02:00:41'),
	(5, 'Equipe de Culinária', 'Culinária', 1, 'Responsável pela alimentação e lanches das crianças', '2025-09-16 22:21:40', '2025-09-17 23:39:20');

-- Copiando estrutura para tabela sistema_projetos.equipe_membros
CREATE TABLE IF NOT EXISTS `equipe_membros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipe_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_entrada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_member` (`equipe_id`,`usuario_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `equipe_membros_ibfk_1` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipe_membros_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.equipe_membros: ~10 rows (aproximadamente)
INSERT INTO `equipe_membros` (`id`, `equipe_id`, `usuario_id`, `data_entrada`) VALUES
	(1, 1, 3, '2025-09-16 22:21:40'),
	(2, 1, 4, '2025-09-16 22:21:40'),
	(4, 2, 5, '2025-09-16 22:21:40'),
	(5, 3, 4, '2025-09-16 22:21:40'),
	(7, 5, 3, '2025-09-16 22:21:40'),
	(8, 5, 4, '2025-09-16 22:21:40'),
	(11, 1, 5, '2025-09-16 23:15:16'),
	(12, 1, 1, '2025-09-16 23:18:22'),
	(18, 2, 1, '2025-09-17 01:09:59'),
	(19, 4, 1, '2025-09-18 02:00:34');

-- Copiando estrutura para tabela sistema_projetos.eventos
CREATE TABLE IF NOT EXISTS `eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `tipo_evento` enum('Festa Infantil','Aniversário','Workshop','Acampamento','Gincana','Show Infantil','Outro') DEFAULT 'Festa Infantil',
  `faixa_etaria_min` int(3) DEFAULT 3,
  `faixa_etaria_max` int(3) DEFAULT 12,
  `capacidade_maxima` int(4) DEFAULT 50,
  `local_evento` text DEFAULT NULL,
  `duracao_horas` decimal(3,1) DEFAULT 4.0,
  `descricao` text DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim_evento` date DEFAULT NULL,
  `status` enum('planejado','em_andamento','concluido','cancelado') DEFAULT 'planejado',
  `coordenador_id` int(11) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `gerente_id` (`coordenador_id`),
  CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`coordenador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.eventos: ~4 rows (aproximadamente)
INSERT INTO `eventos` (`id`, `nome`, `tipo_evento`, `faixa_etaria_min`, `faixa_etaria_max`, `capacidade_maxima`, `local_evento`, `duracao_horas`, `descricao`, `data_inicio`, `data_fim_evento`, `status`, `coordenador_id`, `data_criacao`, `data_atualizacao`) VALUES
	(1, 'Festa Super-Heróis', 'Festa Infantil', 5, 10, 30, 'Salão de Festas - Shopping Center', 4.0, 'Festa temática com super-heróis para crianças de 5 a 10 anos', '2024-01-15', '2024-06-30', 'em_andamento', 2, '2025-09-16 22:21:40', '2025-09-17 23:39:20'),
	(2, 'Acampamento Aventura', 'Acampamento', 8, 12, 20, 'Sítio Natureza - Zona Rural', 48.0, 'Acampamento de fim de semana com atividades ao ar livre', '2024-02-01', '2024-05-15', 'em_andamento', 2, '2025-09-16 22:21:40', '2025-09-17 23:39:20'),
	(3, 'Workshop de Arte', 'Workshop', 6, 11, 15, 'Ateliê Kids - Centro Cultural', 3.0, 'Oficina de pintura e artesanato para desenvolvimento criativo', '2024-03-01', '0000-00-00', 'planejado', 2, '2025-09-16 22:21:40', '2025-09-18 00:06:13'),
	(8, 'Churrasco', '', 2, 15, 5, 'Salão principal', 5.0, 'Teste', '2025-09-19', '0000-00-00', 'planejado', 2, '2025-09-18 01:56:45', '2025-09-18 01:58:17');

-- Copiando estrutura para tabela sistema_projetos.evento_criancas
CREATE TABLE IF NOT EXISTS `evento_criancas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `crianca_id` int(11) NOT NULL,
  `status_participacao` enum('Inscrito','Confirmado','Check-in','Check-out','Cancelado') DEFAULT 'Inscrito',
  `data_inscricao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_checkin` timestamp NULL DEFAULT NULL,
  `data_checkout` timestamp NULL DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `usuario_checkin` int(11) DEFAULT NULL,
  `usuario_checkout` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_evento_crianca` (`evento_id`,`crianca_id`),
  KEY `evento_id` (`evento_id`),
  KEY `crianca_id` (`crianca_id`),
  KEY `usuario_checkin` (`usuario_checkin`),
  KEY `usuario_checkout` (`usuario_checkout`),
  CONSTRAINT `evento_criancas_checkin_fk` FOREIGN KEY (`usuario_checkin`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `evento_criancas_checkout_fk` FOREIGN KEY (`usuario_checkout`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `evento_criancas_crianca_fk` FOREIGN KEY (`crianca_id`) REFERENCES `criancas_cadastro` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evento_criancas_evento_fk` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.evento_criancas: ~7 rows (aproximadamente)
INSERT INTO `evento_criancas` (`id`, `evento_id`, `crianca_id`, `status_participacao`, `data_inscricao`, `data_checkin`, `data_checkout`, `observacoes`, `usuario_checkin`, `usuario_checkout`) VALUES
	(1, 1, 1, 'Confirmado', '2025-09-17 23:39:20', NULL, NULL, NULL, NULL, NULL),
	(2, 1, 2, 'Inscrito', '2025-09-17 23:39:20', NULL, NULL, NULL, NULL, NULL),
	(3, 2, 1, 'Inscrito', '2025-09-17 23:39:20', NULL, NULL, NULL, NULL, NULL),
	(4, 3, 2, 'Check-out', '2025-09-17 23:39:20', '2025-09-18 00:00:01', '2025-09-18 00:00:03', NULL, 1, 1),
	(5, 3, 3, 'Check-in', '2025-09-17 23:39:20', '2025-09-18 00:06:29', NULL, NULL, 1, NULL),
	(6, 3, 4, 'Check-in', '2025-09-18 00:17:28', '2025-09-18 00:46:58', NULL, '', 1, NULL),
	(7, 8, 6, 'Inscrito', '2025-09-18 01:57:44', NULL, NULL, '', NULL, NULL);

-- Copiando estrutura para tabela sistema_projetos.evento_equipes
CREATE TABLE IF NOT EXISTS `evento_equipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `equipe_id` int(11) NOT NULL,
  `data_atribuicao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`evento_id`,`equipe_id`),
  KEY `equipe_id` (`equipe_id`),
  CONSTRAINT `evento_equipes_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evento_equipes_ibfk_2` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.evento_equipes: ~8 rows (aproximadamente)
INSERT INTO `evento_equipes` (`id`, `evento_id`, `equipe_id`, `data_atribuicao`) VALUES
	(2, 1, 2, '2025-09-16 22:21:40'),
	(3, 1, 4, '2025-09-16 22:21:40'),
	(4, 1, 5, '2025-09-16 22:21:40'),
	(5, 2, 1, '2025-09-16 22:21:40'),
	(6, 2, 2, '2025-09-16 22:21:40'),
	(7, 2, 4, '2025-09-16 22:21:40'),
	(8, 3, 3, '2025-09-16 22:21:40'),
	(13, 1, 3, '2025-09-16 22:47:34');

-- Copiando estrutura para tabela sistema_projetos.historico_participacao
CREATE TABLE IF NOT EXISTS `historico_participacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `crianca_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `data_evento` date NOT NULL,
  `avaliacao_comportamento` enum('Excelente','Bom','Regular','Precisa Atenção') DEFAULT NULL,
  `observacoes_evento` text DEFAULT NULL,
  `fotos_autorizadas` tinyint(1) DEFAULT 1,
  `participou_atividades` text DEFAULT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `crianca_id` (`crianca_id`),
  KEY `evento_id` (`evento_id`),
  CONSTRAINT `historico_crianca_fk` FOREIGN KEY (`crianca_id`) REFERENCES `criancas_cadastro` (`id`) ON DELETE CASCADE,
  CONSTRAINT `historico_evento_fk` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.historico_participacao: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela sistema_projetos.logs_sistema
CREATE TABLE IF NOT EXISTS `logs_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(255) NOT NULL COMMENT 'Check-in criança, Check-out criança, Evento criado, Criança cadastrada, etc.',
  `tabela_afetada` varchar(100) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `logs_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.logs_sistema: ~97 rows (aproximadamente)
INSERT INTO `logs_sistema` (`id`, `usuario_id`, `acao`, `tabela_afetada`, `registro_id`, `dados_anteriores`, `dados_novos`, `ip_address`, `data_criacao`) VALUES
	(1, 1, 'Login realizado', NULL, NULL, NULL, NULL, '192.168.1.100', '2025-09-16 22:21:40'),
	(2, 2, 'Login realizado', NULL, NULL, NULL, NULL, '192.168.1.101', '2025-09-16 22:21:40'),
	(3, 3, 'Login realizado', NULL, NULL, NULL, NULL, '192.168.1.102', '2025-09-16 22:21:40'),
	(4, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-16 22:22:19'),
	(5, 1, 'Tarefa atualizada', 'tarefas', 1, '{"id":1,"titulo":"Configurar ambiente de desenvolvimento","descricao":"Preparar ambiente local e repositório","projeto_id":1,"responsavel_id":3,"status":"concluida","data_inicio":"2024-01-15","data_fim_prevista":"2024-01-20","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40","projeto_nome":"Sistema de E-commerce","responsavel_nome":"Maria Oliveira Costa"}', '{"id":"1","action":"update","titulo":"Configurar ambiente de desenvolvimento","descricao":"Preparar ambiente local e repositório","projeto_id":"1","responsavel_id":"3","status":"concluida","data_inicio":"2024-01-15","data_fim_prevista":"2024-01-20"}', '::1', '2025-09-16 22:50:33'),
	(6, 1, 'Tarefa atualizada', 'tarefas', 1, '{"id":1,"titulo":"Configurar ambiente de desenvolvimento","descricao":"Preparar ambiente local e repositório","projeto_id":1,"responsavel_id":3,"status":"concluida","data_inicio":"2024-01-15","data_fim_prevista":"2024-01-20","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40","projeto_nome":"Sistema de E-commerce","responsavel_nome":"Maria Oliveira Costa"}', '{"id":"1","action":"update","titulo":"Configurar ambiente de desenvolvimento","descricao":"Preparar ambiente local e repositório","projeto_id":"1","responsavel_id":"3","status":"concluida","data_inicio":"2024-01-15","data_fim_prevista":"2024-01-20"}', '::1', '2025-09-16 22:51:00'),
	(7, 1, 'Equipe criada', 'equipes', 6, NULL, '{"id":"","action":"create","nome":"kndkon","descricao":""}', '::1', '2025-09-16 22:52:41'),
	(8, 1, 'Equipe atualizada', 'equipes', 6, '{"id":6,"nome":"kndkon","descricao":"","data_criacao":"2025-09-16 19:52:41","data_atualizacao":"2025-09-16 19:52:41"}', '{"id":"6","action":"update","nome":"Faculdade","descricao":""}', '::1', '2025-09-16 22:52:52'),
	(9, 1, 'Equipe excluída', 'equipes', 6, '{"id":6,"nome":"Faculdade","descricao":"","data_criacao":"2025-09-16 19:52:41","data_atualizacao":"2025-09-16 19:52:52"}', NULL, '::1', '2025-09-16 22:52:56'),
	(10, 1, 'Tarefa excluída', 'tarefas', 7, '{"id":7,"titulo":"Configurar React Native","descricao":"Setup do projeto mobile","projeto_id":2,"responsavel_id":3,"status":"em_execucao","data_inicio":"2024-02-11","data_fim_prevista":"2024-02-15","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40","projeto_nome":"App Mobile Corporativo","responsavel_nome":"Maria Oliveira Costa"}', NULL, '::1', '2025-09-16 22:53:23'),
	(11, 1, 'Equipe atribuída ao projeto', 'projeto_equipes', NULL, NULL, '{"projeto_id":"1","equipe_id":"1"}', '::1', '2025-09-16 22:53:38'),
	(12, 1, 'Tarefa criada', 'tarefas', 11, NULL, '{"id":"","action":"create","titulo":"adad","descricao":"adad","projeto_id":"3","responsavel_id":"2","status":"concluida","data_inicio":"2025-09-17","data_fim_prevista":"2025-10-02"}', '::1', '2025-09-16 23:03:41'),
	(13, 1, 'Equipe removida do projeto', 'projeto_equipes', NULL, '{"projeto_id":"1","equipe_id":"1"}', NULL, '::1', '2025-09-16 23:04:07'),
	(14, 1, 'Membro adicionado à equipe', 'equipe_membros', NULL, NULL, '{"equipe_id":"1","usuario_id":"1"}', '::1', '2025-09-16 23:06:58'),
	(15, 1, 'Membro adicionado à equipe', 'equipe_membros', NULL, NULL, '{"equipe_id":"1","usuario_id":"2"}', '::1', '2025-09-16 23:13:36'),
	(16, 1, 'Membro adicionado à equipe', 'equipe_membros', NULL, NULL, '{"equipe_id":"1","usuario_id":"5"}', '::1', '2025-09-16 23:15:16'),
	(17, 1, 'Membro removido da equipe', 'equipe_membros', NULL, '{"equipe_id":"1","usuario_id":"1"}', NULL, '::1', '2025-09-16 23:15:20'),
	(18, 1, 'Membro adicionado à equipe', 'equipe_membros', NULL, NULL, '{"equipe_id":"1","usuario_id":"1"}', '::1', '2025-09-16 23:18:22'),
	(19, 1, 'Membro removido da equipe', 'equipe_membros', NULL, '{"equipe_id":"1","usuario_id":"2"}', NULL, '::1', '2025-09-16 23:33:07'),
	(20, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:04:01'),
	(21, 2, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:25:59'),
	(22, 2, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:26:02'),
	(23, 3, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:26:04'),
	(24, 3, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:27:34'),
	(25, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:27:37'),
	(26, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:27:40'),
	(27, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:29:16'),
	(28, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:29:19'),
	(29, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:31:52'),
	(30, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:31:55'),
	(31, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:32:39'),
	(32, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:32:42'),
	(33, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:34:23'),
	(34, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:34:35'),
	(35, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:36:33'),
	(36, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:36:37'),
	(37, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:39:14'),
	(38, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:39:18'),
	(39, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:41:11'),
	(40, 1, 'Logout realizado com timer de animação', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:47:49'),
	(41, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 00:48:35'),
	(42, 1, 'Equipe atualizada', 'equipes', 1, '{"id":1,"nome":"Desenvolvimento Frontend","descricao":"Equipe responsável pelo desenvolvimento da interface do usuário","data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40"}', '{"id":"1","action":"update","nome":"Desenvolvimento Frontend","descricao":"Equipe responsável pelo desenvolvimento da interface do usuário."}', '::1', '2025-09-17 00:48:49'),
	(43, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 01:01:11'),
	(44, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 01:06:57'),
	(45, 1, 'Projeto criado', 'projetos', 7, NULL, '{"id":"","action":"create","nome":"zkjnakjhncjk","descricao":"caadad","data_inicio":"2023-05-15","data_termino_prevista":"2025-09-17","status":"em_andamento","gerente_id":"2"}', '::1', '2025-09-17 01:07:57'),
	(46, 1, 'Projeto atualizado', 'projetos', 7, '{"id":7,"nome":"zkjnakjhncjk","descricao":"caadad","data_inicio":"2023-05-15","data_termino_prevista":"2025-09-17","status":"em_andamento","gerente_id":2,"data_criacao":"2025-09-16 22:07:57","data_atualizacao":"2025-09-16 22:07:57","gerente_nome":"João Silva Santos"}', '{"id":"7","action":"update","nome":"Teste","descricao":"caadad","data_inicio":"2023-05-15","data_termino_prevista":"2025-09-17","status":"em_andamento","gerente_id":"2"}', '::1', '2025-09-17 01:08:13'),
	(47, 1, 'Projeto atualizado', 'projetos', 7, '{"id":7,"nome":"Teste","descricao":"caadad","data_inicio":"2023-05-15","data_termino_prevista":"2025-09-17","status":"em_andamento","gerente_id":2,"data_criacao":"2025-09-16 22:07:57","data_atualizacao":"2025-09-16 22:08:13","gerente_nome":"João Silva Santos"}', '{"id":"7","action":"update","nome":"Teste","descricao":"caadad","data_inicio":"2023-05-15","data_termino_prevista":"2025-09-27","status":"em_andamento","gerente_id":"2"}', '::1', '2025-09-17 01:08:20'),
	(48, 1, 'Equipe atribuída ao projeto', 'projeto_equipes', NULL, NULL, '{"projeto_id":"7","equipe_id":"2"}', '::1', '2025-09-17 01:08:45'),
	(49, 1, 'Tarefa criada', 'tarefas', 12, NULL, '{"id":"","action":"create","titulo":"Teste 1","descricao":"Teste","projeto_id":"7","responsavel_id":"2","status":"em_execucao","data_inicio":"2025-09-16","data_fim_prevista":"2025-09-19"}', '::1', '2025-09-17 01:09:11'),
	(50, 1, 'Membro removido da equipe', 'equipe_membros', NULL, '{"equipe_id":"2","usuario_id":"3"}', NULL, '::1', '2025-09-17 01:09:53'),
	(51, 1, 'Membro adicionado à equipe', 'equipe_membros', NULL, NULL, '{"equipe_id":"2","usuario_id":"1"}', '::1', '2025-09-17 01:09:59'),
	(52, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 01:11:42'),
	(53, 3, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 01:11:49'),
	(54, 3, 'Status da tarefa atualizado', 'tarefas', 1, '{"id":1,"titulo":"Configurar ambiente de desenvolvimento","descricao":"Preparar ambiente local e repositório","projeto_id":1,"responsavel_id":3,"status":"concluida","data_inicio":"2024-01-15","data_fim_prevista":"2024-01-20","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40","projeto_nome":"Sistema de E-commerce","responsavel_nome":"Maria Oliveira Costa"}', '{"status":"em_execucao"}', '::1', '2025-09-17 01:12:25'),
	(55, 3, 'Tarefa atualizada', 'tarefas', 1, '{"id":1,"titulo":"Configurar ambiente de desenvolvimento","descricao":"Preparar ambiente local e repositório","projeto_id":1,"responsavel_id":3,"status":"em_execucao","data_inicio":"2024-01-15","data_fim_prevista":"2024-01-20","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 22:12:25","projeto_nome":"Sistema de E-commerce","responsavel_nome":"Maria Oliveira Costa"}', '{"id":"1","action":"update","titulo":"Configurar ambiente de desenvolvimento","descricao":"Preparar ambiente local e repositório","projeto_id":"1","responsavel_id":"3","status":"em_execucao","data_inicio":"2024-01-15","data_fim_prevista":"2024-01-30"}', '::1', '2025-09-17 01:12:33'),
	(56, 3, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 01:13:15'),
	(57, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 01:13:38'),
	(58, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 01:16:54'),
	(59, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 01:22:34'),
	(60, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 01:22:45'),
	(61, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 23:43:56'),
	(62, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 23:45:43'),
	(63, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 23:46:18'),
	(64, 1, 'Check-in realizado', 'evento_criancas', NULL, NULL, '{"evento_id":"3","crianca_id":"2"}', '::1', '2025-09-18 00:00:01'),
	(65, 1, 'Check-out realizado', 'evento_criancas', NULL, NULL, '{"evento_id":"3","crianca_id":"2"}', '::1', '2025-09-18 00:00:03'),
	(66, 1, 'Evento atualizado', 'eventos', 3, '{"id":3,"nome":"Workshop de Arte","tipo_evento":"Workshop","faixa_etaria_min":6,"faixa_etaria_max":11,"capacidade_maxima":15,"local_evento":"Ateli\\u00ea Kids - Centro Cultural","duracao_horas":"3.0","descricao":"Oficina de pintura e artesanato para desenvolvimento criativo","data_inicio":"2024-03-01","data_fim_evento":"2024-08-30","status":"planejado","coordenador_id":2,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-17 20:39:20","coordenador_nome":"Jo\\u00e3o Silva Santos"}', '{"action":"update","id":"3","nome":"Workshop de Arte","tipo_evento":"Workshop","coordenador_id":"2","descricao":"Oficina de pintura e artesanato para desenvolvimento criativo","data_inicio":"2024-03-01T00:00","duracao_horas":"3.0","faixa_etaria_min":"6","faixa_etaria_max":"11","capacidade_maxima":"15","local_evento":"Ateli\\u00ea Kids - Centro Cultural","status":"planejado","data_fim_evento":""}', '::1', '2025-09-18 00:06:13'),
	(67, 1, 'Check-in realizado', 'evento_criancas', NULL, NULL, '{"evento_id":"3","crianca_id":"3"}', '::1', '2025-09-18 00:06:29'),
	(68, 1, 'Criança adicionada ao evento', 'evento_criancas', NULL, NULL, '{"evento_id":"3","crianca_id":"4"}', '::1', '2025-09-18 00:17:28'),
	(69, 1, 'Evento excluído', 'eventos', 5, '{"id":5,"nome":"Portal do Cliente","tipo_evento":"Festa Infantil","faixa_etaria_min":3,"faixa_etaria_max":12,"capacidade_maxima":50,"local_evento":null,"duracao_horas":"4.0","descricao":"Portal web para atendimento ao cliente","data_inicio":"2024-01-10","data_fim_evento":"2024-04-10","status":"cancelado","coordenador_id":2,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40","coordenador_nome":"Jo\\u00e3o Silva Santos"}', NULL, '::1', '2025-09-18 00:17:41'),
	(70, 1, 'Evento excluído', 'eventos', 4, '{"id":4,"nome":"Sistema de CRM","tipo_evento":"Festa Infantil","faixa_etaria_min":3,"faixa_etaria_max":12,"capacidade_maxima":50,"local_evento":null,"duracao_horas":"4.0","descricao":"Customer Relationship Management personalizado","data_inicio":"2023-11-01","data_fim_evento":"2024-02-28","status":"concluido","coordenador_id":2,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40","coordenador_nome":"Jo\\u00e3o Silva Santos"}', NULL, '::1', '2025-09-18 00:17:45'),
	(71, 1, 'Evento excluído', 'eventos', 7, '{"id":7,"nome":"Teste","tipo_evento":"Festa Infantil","faixa_etaria_min":3,"faixa_etaria_max":12,"capacidade_maxima":50,"local_evento":null,"duracao_horas":"4.0","descricao":"caadad","data_inicio":"2023-05-15","data_fim_evento":"2025-09-27","status":"em_andamento","coordenador_id":2,"data_criacao":"2025-09-16 22:07:57","data_atualizacao":"2025-09-16 22:08:20","coordenador_nome":"Jo\\u00e3o Silva Santos"}', NULL, '::1', '2025-09-18 00:17:50'),
	(72, 1, 'Login realizado', NULL, NULL, NULL, NULL, '177.23.233.196', '2025-09-18 00:43:08'),
	(73, 1, 'Check-in realizado', 'evento_criancas', NULL, NULL, '{"evento_id":"3","crianca_id":"4"}', '::1', '2025-09-18 00:46:59'),
	(74, 1, 'Login realizado', NULL, NULL, NULL, NULL, '187.95.169.231', '2025-09-18 00:51:31'),
	(75, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-18 00:52:53'),
	(76, 3, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-18 00:53:38'),
	(77, 3, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-18 01:20:00'),
	(78, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-18 01:20:18'),
	(79, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-18 01:29:14'),
	(80, 1, 'Login realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-18 01:29:37'),
	(81, 1, 'Login realizado', NULL, NULL, NULL, NULL, '167.250.235.131', '2025-09-18 01:32:29'),
	(82, 1, 'Funcionario atualizado', 'usuarios', 1, '{"id":1,"nome_completo":"Administrador do Sistema","cpf":"000.000.000-00","email":"admin@techcorp.com","cargo":"Administrador do Sistema","login":"admin","perfil":"administrador"}', '{"id":1,"nome_completo":"Luiz H Segantini","cpf":"000.000.000-00","email":"admin@techcorp.com","cargo":"Administrador do Sistema","login":"admin","perfil":"administrador","senha_atualizada":false}', '::1', '2025-09-18 01:39:23'),
	(83, 1, 'Evento criado', 'eventos', 8, NULL, '{"action":"create","status":"planejado","nome":"Churrasco","tipo_evento":"Culin\\u00e1ria","coordenador_id":"1","descricao":"Teste","data_inicio":"2025-09-19T22:59","duracao_horas":"5","faixa_etaria_min":"2","faixa_etaria_max":"15","capacidade_maxima":"5","local_evento":"Sal\\u00e3o principal","data_fim_evento":""}', '::1', '2025-09-18 01:56:45'),
	(84, 1, 'Criança adicionada ao evento', 'evento_criancas', NULL, NULL, '{"evento_id":"8","crianca_id":"6"}', '::1', '2025-09-18 01:57:44'),
	(85, 1, 'Evento atualizado', 'eventos', 8, '{"id":8,"nome":"Churrasco","tipo_evento":"","faixa_etaria_min":2,"faixa_etaria_max":15,"capacidade_maxima":5,"local_evento":"Sal\\u00e3o principal","duracao_horas":"5.0","descricao":"Teste","data_inicio":"2025-09-19","data_fim_evento":"0000-00-00","status":"planejado","coordenador_id":1,"data_criacao":"2025-09-17 22:56:45","data_atualizacao":"2025-09-17 22:56:45","coordenador_nome":"Luiz H Segantini"}', '{"action":"update","id":"8","nome":"Churrasco","tipo_evento":"Festa de Anivers\\u00e1rio","coordenador_id":"2","descricao":"Teste","data_inicio":"2025-09-19T00:00","duracao_horas":"5.0","faixa_etaria_min":"2","faixa_etaria_max":"15","capacidade_maxima":"5","local_evento":"Sal\\u00e3o principal","status":"planejado","data_fim_evento":""}', '::1', '2025-09-18 01:58:17'),
	(86, 1, 'Atividade removida', 'tarefas', 3, '{"id":3,"titulo":"Implementar API de produtos","tipo_atividade":"Recreação","descricao":"Criar endpoints para gestão de produtos","material_necessario":null,"publico_alvo":null,"evento_id":1,"responsavel_id":4,"status":"em_execucao","data_inicio":"2024-01-26","data_fim_prevista":"2024-02-05","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40"}', NULL, '::1', '2025-09-18 01:59:08'),
	(87, 1, 'Status da atividade atualizado', 'tarefas', 4, '{"id":4,"titulo":"Design da homepage","tipo_atividade":"Recreação","descricao":"Criar layout da página inicial","material_necessario":null,"publico_alvo":null,"evento_id":1,"responsavel_id":5,"status":"em_execucao","data_inicio":"2024-01-22","data_fim_prevista":"2024-02-01","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40"}', '{"status":"pendente"}', '::1', '2025-09-18 01:59:12'),
	(88, 1, 'Atividade removida', 'tarefas', 4, '{"id":4,"titulo":"Design da homepage","tipo_atividade":"Recreação","descricao":"Criar layout da página inicial","material_necessario":null,"publico_alvo":null,"evento_id":1,"responsavel_id":5,"status":"pendente","data_inicio":"2024-01-22","data_fim_prevista":"2024-02-01","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-17 22:59:12"}', NULL, '::1', '2025-09-18 01:59:15'),
	(89, 1, 'Atividade removida', 'tarefas', 11, '{"id":11,"titulo":"adad","tipo_atividade":"Recreação","descricao":"adad","material_necessario":null,"publico_alvo":null,"evento_id":3,"responsavel_id":2,"status":"concluida","data_inicio":"2025-09-17","data_fim_prevista":"2025-10-02","data_fim_real":null,"data_criacao":"2025-09-16 20:03:41","data_atualizacao":"2025-09-16 20:03:41"}', NULL, '::1', '2025-09-18 01:59:16'),
	(90, 1, 'Atividade removida', 'tarefas', 1, '{"id":1,"titulo":"Preparar decoração temática","tipo_atividade":"Setup","descricao":"Montar cenário e decoração do tema super-heróis","material_necessario":"Balões, painéis, fantasias, adesivos","publico_alvo":"Crianças 5-10 anos","evento_id":1,"responsavel_id":3,"status":"em_execucao","data_inicio":"2024-01-15","data_fim_prevista":"2024-01-30","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-17 20:39:20"}', NULL, '::1', '2025-09-18 01:59:47'),
	(91, 1, 'Atividade removida', 'tarefas', 2, '{"id":2,"titulo":"Organizar gincana de heróis","tipo_atividade":"Recreação","descricao":"Coordenar brincadeiras e desafios temáticos","material_necessario":"Cordas, bolas, obstáculos, fantasias","publico_alvo":"Crianças 5-10 anos","evento_id":1,"responsavel_id":3,"status":"concluida","data_inicio":"2024-01-21","data_fim_prevista":"2024-01-25","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-17 20:39:20"}', NULL, '::1', '2025-09-18 01:59:49'),
	(92, 1, 'Atividade removida', 'tarefas', 8, '{"id":8,"titulo":"Desenvolver navegação","tipo_atividade":"Recreação","descricao":"Implementar sistema de rotas","material_necessario":null,"publico_alvo":null,"evento_id":2,"responsavel_id":4,"status":"pendente","data_inicio":"2024-02-16","data_fim_prevista":"2024-02-25","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40"}', NULL, '::1', '2025-09-18 01:59:52'),
	(93, 1, 'Atividade removida', 'tarefas', 6, '{"id":6,"titulo":"Prototipagem do app","tipo_atividade":"Recreação","descricao":"Criar protótipo navegável","material_necessario":null,"publico_alvo":null,"evento_id":2,"responsavel_id":5,"status":"concluida","data_inicio":"2024-02-01","data_fim_prevista":"2024-02-10","data_fim_real":null,"data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-16 19:21:40"}', NULL, '::1', '2025-09-18 01:59:58'),
	(94, 1, 'Membro adicionado a equipe', 'equipes', 4, NULL, '{"usuario_id":1}', '::1', '2025-09-18 02:00:34'),
	(95, 1, 'Equipe atualizada', 'equipes', 4, '{"id":4,"nome":"Equipe de Arte e Criatividade","especialidade":"Arte","capacidade_eventos":1,"descricao":"Responsável por oficinas de arte e atividades criativas","data_criacao":"2025-09-16 19:21:40","data_atualizacao":"2025-09-17 20:39:20","membros":[{"usuario_id":5,"data_entrada":"2025-09-16 19:21:40","nome_completo":"Ana Carolina Ferreira","perfil":"auxiliar"},{"usuario_id":1,"data_entrada":"2025-09-17 23:00:34","nome_completo":"Luiz H Segantini","perfil":"administrador"}]}', '{"nome":"Equipe de Arte e Criatividade","especialidade":"Arte","capacidade_eventos":5}', '::1', '2025-09-18 02:00:41'),
	(96, 1, 'Membro removido da equipe', 'equipes', 4, '{"usuario_id":5}', NULL, '::1', '2025-09-18 02:00:44'),
	(97, 1, 'Login realizado', NULL, NULL, NULL, NULL, '167.250.235.131', '2025-09-18 02:11:56');

-- Copiando estrutura para tabela sistema_projetos.tarefas
CREATE TABLE IF NOT EXISTS `tarefas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `tipo_atividade` enum('Recreação','Alimentação','Oficina','Show','Brincadeira','Cuidados','Limpeza','Setup','Outro') DEFAULT 'Recreação',
  `descricao` text DEFAULT NULL,
  `material_necessario` text DEFAULT NULL,
  `publico_alvo` varchar(100) DEFAULT NULL,
  `evento_id` int(11) NOT NULL,
  `responsavel_id` int(11) DEFAULT NULL,
  `status` enum('pendente','em_execucao','concluida') DEFAULT 'pendente',
  `data_inicio` date DEFAULT NULL,
  `data_fim_prevista` date DEFAULT NULL,
  `data_fim_real` date DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `projeto_id` (`evento_id`),
  KEY `responsavel_id` (`responsavel_id`),
  CONSTRAINT `tarefas_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tarefas_ibfk_2` FOREIGN KEY (`responsavel_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.tarefas: ~3 rows (aproximadamente)
INSERT INTO `tarefas` (`id`, `titulo`, `tipo_atividade`, `descricao`, `material_necessario`, `publico_alvo`, `evento_id`, `responsavel_id`, `status`, `data_inicio`, `data_fim_prevista`, `data_fim_real`, `data_criacao`, `data_atualizacao`) VALUES
	(5, 'Testes de integração', 'Recreação', 'Executar testes entre componentes', NULL, NULL, 1, 3, 'pendente', '2024-02-06', '2024-02-10', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(9, 'Análise de custos AWS', 'Recreação', 'Levantamento de custos da migração', NULL, NULL, 3, 4, 'pendente', '2024-03-01', '2024-03-10', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(10, 'Plano de migração', 'Recreação', 'Documentar estratégia de migração', NULL, NULL, 3, 4, 'pendente', '2024-03-11', '2024-03-20', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40');

-- Copiando estrutura para tabela sistema_projetos.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `login` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('administrador','coordenador','animador','monitor','auxiliar') NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.usuarios: ~6 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome_completo`, `cpf`, `email`, `cargo`, `login`, `senha`, `perfil`, `data_criacao`, `data_atualizacao`) VALUES
	(1, 'Luiz H Segantini', '000.000.000-00', 'admin@techcorp.com', 'Administrador do Sistema', 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'administrador', '2025-09-16 22:21:40', '2025-09-18 01:39:23'),
	(2, 'João Silva Santos', '111.111.111-11', 'joao@techcorp.com', 'Coordenador de Eventos', 'gerente', 'e10adc3949ba59abbe56e057f20f883e', 'coordenador', '2025-09-16 22:21:40', '2025-09-17 23:39:20'),
	(3, 'Maria Oliveira Costa', '222.222.222-22', 'maria@techcorp.com', 'Animador Infantil', 'colaborador', 'e10adc3949ba59abbe56e057f20f883e', 'animador', '2025-09-16 22:21:40', '2025-09-17 23:39:20'),
	(4, 'Pedro Almeida Lima', '333.333.333-33', 'pedro@techcorp.com', 'Monitor de Atividades', 'pedro', 'e10adc3949ba59abbe56e057f20f883e', 'monitor', '2025-09-16 22:21:40', '2025-09-17 23:39:20'),
	(5, 'Ana Carolina Ferreira', '444.444.444-44', 'ana@techcorp.com', 'Auxiliar de Eventos', 'ana', 'e10adc3949ba59abbe56e057f20f883e', 'auxiliar', '2025-09-16 22:21:40', '2025-09-17 23:39:20');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
