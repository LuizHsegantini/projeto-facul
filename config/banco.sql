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
	(1, 1, 'Login realizado', NULL, NULL, NULL, NULL, '167.250.235.131', '2025-09-18 02:11:56');

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
