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

-- Copiando estrutura para tabela sistema_projetos.equipes
CREATE TABLE IF NOT EXISTS `equipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.equipes: ~5 rows (aproximadamente)
INSERT INTO `equipes` (`id`, `nome`, `descricao`, `data_criacao`, `data_atualizacao`) VALUES
	(1, 'Desenvolvimento Frontend', 'Equipe responsável pelo desenvolvimento da interface do usuário.', '2025-09-16 22:21:40', '2025-09-17 00:48:49'),
	(2, 'Desenvolvimento Backend', 'Equipe responsável pela lógica de negócio e APIs', '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(3, 'DevOps', 'Equipe responsável pela infraestrutura e deploy', '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(4, 'UX/UI Design', 'Equipe responsável pela experiência e interface do usuário', '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(5, 'Quality Assurance', 'Equipe responsável pelos testes e qualidade do software', '2025-09-16 22:21:40', '2025-09-16 22:21:40');

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.equipe_membros: ~10 rows (aproximadamente)
INSERT INTO `equipe_membros` (`id`, `equipe_id`, `usuario_id`, `data_entrada`) VALUES
	(1, 1, 3, '2025-09-16 22:21:40'),
	(2, 1, 4, '2025-09-16 22:21:40'),
	(4, 2, 5, '2025-09-16 22:21:40'),
	(5, 3, 4, '2025-09-16 22:21:40'),
	(6, 4, 5, '2025-09-16 22:21:40'),
	(7, 5, 3, '2025-09-16 22:21:40'),
	(8, 5, 4, '2025-09-16 22:21:40'),
	(11, 1, 5, '2025-09-16 23:15:16'),
	(12, 1, 1, '2025-09-16 23:18:22'),
	(18, 2, 1, '2025-09-17 01:09:59');

-- Copiando estrutura para tabela sistema_projetos.logs_sistema
CREATE TABLE IF NOT EXISTS `logs_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `tabela_afetada` varchar(100) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `logs_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.logs_sistema: ~19 rows (aproximadamente)
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
	(58, 1, 'Logout realizado', NULL, NULL, NULL, NULL, '::1', '2025-09-17 01:16:54');

-- Copiando estrutura para tabela sistema_projetos.projetos
CREATE TABLE IF NOT EXISTS `projetos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_termino_prevista` date DEFAULT NULL,
  `status` enum('planejado','em_andamento','concluido','cancelado') DEFAULT 'planejado',
  `gerente_id` int(11) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `gerente_id` (`gerente_id`),
  CONSTRAINT `projetos_ibfk_1` FOREIGN KEY (`gerente_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.projetos: ~5 rows (aproximadamente)
INSERT INTO `projetos` (`id`, `nome`, `descricao`, `data_inicio`, `data_termino_prevista`, `status`, `gerente_id`, `data_criacao`, `data_atualizacao`) VALUES
	(1, 'Sistema de E-commerce', 'Desenvolvimento de plataforma de vendas online completa 1\r\n', '2024-01-15', '2024-06-30', 'em_andamento', 2, '2025-09-16 22:21:40', '2025-09-16 22:39:54'),
	(2, 'App Mobile Corporativo', 'Aplicativo móvel para gestão interna da empresa', '2024-02-01', '2024-05-15', 'em_andamento', 2, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(3, 'Migração para Cloud', 'Migração da infraestrutura atual para AWS', '2024-03-01', '2024-08-30', 'planejado', 2, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(4, 'Sistema de CRM', 'Customer Relationship Management personalizado', '2023-11-01', '2024-02-28', 'concluido', 2, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(5, 'Portal do Cliente', 'Portal web para atendimento ao cliente', '2024-01-10', '2024-04-10', 'cancelado', 2, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(7, 'Teste', 'caadad', '2023-05-15', '2025-09-27', 'em_andamento', 2, '2025-09-17 01:07:57', '2025-09-17 01:08:20');

-- Copiando estrutura para tabela sistema_projetos.projeto_equipes
CREATE TABLE IF NOT EXISTS `projeto_equipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projeto_id` int(11) NOT NULL,
  `equipe_id` int(11) NOT NULL,
  `data_atribuicao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`projeto_id`,`equipe_id`),
  KEY `equipe_id` (`equipe_id`),
  CONSTRAINT `projeto_equipes_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projeto_equipes_ibfk_2` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.projeto_equipes: ~12 rows (aproximadamente)
INSERT INTO `projeto_equipes` (`id`, `projeto_id`, `equipe_id`, `data_atribuicao`) VALUES
	(2, 1, 2, '2025-09-16 22:21:40'),
	(3, 1, 4, '2025-09-16 22:21:40'),
	(4, 1, 5, '2025-09-16 22:21:40'),
	(5, 2, 1, '2025-09-16 22:21:40'),
	(6, 2, 2, '2025-09-16 22:21:40'),
	(7, 2, 4, '2025-09-16 22:21:40'),
	(8, 3, 3, '2025-09-16 22:21:40'),
	(9, 4, 2, '2025-09-16 22:21:40'),
	(10, 4, 5, '2025-09-16 22:21:40'),
	(11, 5, 1, '2025-09-16 22:21:40'),
	(12, 5, 4, '2025-09-16 22:21:40'),
	(13, 1, 3, '2025-09-16 22:47:34'),
	(15, 7, 2, '2025-09-17 01:08:45');

-- Copiando estrutura para tabela sistema_projetos.tarefas
CREATE TABLE IF NOT EXISTS `tarefas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `projeto_id` int(11) NOT NULL,
  `responsavel_id` int(11) DEFAULT NULL,
  `status` enum('pendente','em_execucao','concluida') DEFAULT 'pendente',
  `data_inicio` date DEFAULT NULL,
  `data_fim_prevista` date DEFAULT NULL,
  `data_fim_real` date DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `projeto_id` (`projeto_id`),
  KEY `responsavel_id` (`responsavel_id`),
  CONSTRAINT `tarefas_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tarefas_ibfk_2` FOREIGN KEY (`responsavel_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.tarefas: ~10 rows (aproximadamente)
INSERT INTO `tarefas` (`id`, `titulo`, `descricao`, `projeto_id`, `responsavel_id`, `status`, `data_inicio`, `data_fim_prevista`, `data_fim_real`, `data_criacao`, `data_atualizacao`) VALUES
	(1, 'Configurar ambiente de desenvolvimento', 'Preparar ambiente local e repositório', 1, 3, 'em_execucao', '2024-01-15', '2024-01-30', NULL, '2025-09-16 22:21:40', '2025-09-17 01:12:33'),
	(2, 'Desenvolver tela de login', 'Criar interface de autenticação', 1, 3, 'concluida', '2024-01-21', '2024-01-25', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(3, 'Implementar API de produtos', 'Criar endpoints para gestão de produtos', 1, 4, 'em_execucao', '2024-01-26', '2024-02-05', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(4, 'Design da homepage', 'Criar layout da página inicial', 1, 5, 'em_execucao', '2024-01-22', '2024-02-01', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(5, 'Testes de integração', 'Executar testes entre componentes', 1, 3, 'pendente', '2024-02-06', '2024-02-10', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(6, 'Prototipagem do app', 'Criar protótipo navegável', 2, 5, 'concluida', '2024-02-01', '2024-02-10', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(8, 'Desenvolver navegação', 'Implementar sistema de rotas', 2, 4, 'pendente', '2024-02-16', '2024-02-25', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(9, 'Análise de custos AWS', 'Levantamento de custos da migração', 3, 4, 'pendente', '2024-03-01', '2024-03-10', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(10, 'Plano de migração', 'Documentar estratégia de migração', 3, 4, 'pendente', '2024-03-11', '2024-03-20', NULL, '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(11, 'adad', 'adad', 3, 2, 'concluida', '2025-09-17', '2025-10-02', NULL, '2025-09-16 23:03:41', '2025-09-16 23:03:41'),
	(12, 'Teste 1', 'Teste', 7, 2, 'em_execucao', '2025-09-16', '2025-09-19', NULL, '2025-09-17 01:09:11', '2025-09-17 01:09:11');

-- Copiando estrutura para tabela sistema_projetos.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `login` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('administrador','gerente','colaborador') NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela sistema_projetos.usuarios: ~5 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome_completo`, `cpf`, `email`, `cargo`, `login`, `senha`, `perfil`, `data_criacao`, `data_atualizacao`) VALUES
	(1, 'Administrador do Sistema', '000.000.000-00', 'admin@techcorp.com', 'Administrador', 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'administrador', '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(2, 'João Silva Santos', '111.111.111-11', 'joao@techcorp.com', 'Gerente de Projetos', 'gerente', 'e10adc3949ba59abbe56e057f20f883e', 'gerente', '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(3, 'Maria Oliveira Costa', '222.222.222-22', 'maria@techcorp.com', 'Desenvolvedora Senior', 'colaborador', 'e10adc3949ba59abbe56e057f20f883e', 'colaborador', '2025-09-16 22:21:40', '2025-09-16 22:21:40'),
	(4, 'Pedro Almeida Lima', '333.333.333-33', 'pedro@techcorp.com', 'Analista de Sistemas', 'pedro', 'e10adc3949ba59abbe56e057f20f883e', 'gerente', '2025-09-16 22:21:40', '2025-09-16 22:38:36'),
	(5, 'Ana Carolina Ferreira', '444.444.444-44', 'ana@techcorp.com', 'Designer UX/UI', 'ana', 'e10adc3949ba59abbe56e057f20f883e', 'colaborador', '2025-09-16 22:21:40', '2025-09-16 22:21:40');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
