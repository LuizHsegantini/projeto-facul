DELIMITER $$

-- ===================================
-- TABELA: criancas_cadastro
-- ===================================

DROP TRIGGER IF EXISTS `trg_criancas_cadastro_before_insert`$$
CREATE TRIGGER `trg_criancas_cadastro_before_insert`
BEFORE INSERT ON `criancas_cadastro`
FOR EACH ROW
BEGIN
    IF NEW.usr_inclusao IS NULL THEN
        SET NEW.usr_inclusao = IFNULL(@session_user_name, CURRENT_USER());
    END IF;
    IF NEW.data_cadastro IS NULL THEN
        SET NEW.data_cadastro = NOW();
    END IF;
END$$

DROP TRIGGER IF EXISTS `trg_criancas_cadastro_before_update`$$
CREATE TRIGGER `trg_criancas_cadastro_before_update`
BEFORE UPDATE ON `criancas_cadastro`
FOR EACH ROW
BEGIN
    SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
    SET NEW.data_atualizacao = NOW();
END$$

DROP TRIGGER IF EXISTS `trg_criancas_cadastro_after_insert`$$
CREATE TRIGGER `trg_criancas_cadastro_after_insert`
AFTER INSERT ON `criancas_cadastro`
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (
        usuario_id, acao, tabela_afetada, registro_id,
        dados_anteriores, dados_novos, ip_address
    ) VALUES (
        IFNULL(@session_user_id, NULL),
        'Inserção de registro',
        'criancas_cadastro',
        NEW.id,
        NULL,
        JSON_OBJECT(
            'id', NEW.id,
            'nome_completo', NEW.nome_completo,
            'data_nascimento', NEW.data_nascimento,
            'idade', NEW.idade,
            'sexo', NEW.sexo,
            'alergia_alimentos', NEW.alergia_alimentos,
            'alergia_medicamentos', NEW.alergia_medicamentos,
            'restricoes_alimentares', NEW.restricoes_alimentares,
            'observacoes_saude', NEW.observacoes_saude,
            'nome_responsavel', NEW.nome_responsavel,
            'grau_parentesco', NEW.grau_parentesco,
            'telefone_principal', NEW.telefone_principal,
            'telefone_alternativo', NEW.telefone_alternativo,
            'endereco_completo', NEW.endereco_completo,
            'documento_rg_cpf', NEW.documento_rg_cpf,
            'email_responsavel', NEW.email_responsavel,
            'nome_emergencia', NEW.nome_emergencia,
            'telefone_emergencia', NEW.telefone_emergencia,
            'grau_parentesco_emergencia', NEW.grau_parentesco_emergencia,
            'autorizacao_retirada', NEW.autorizacao_retirada,
            'data_cadastro', NEW.data_cadastro,
            'usr_cadastro', NEW.usr_cadastro,
            'ativo', NEW.ativo,
            'usr_inclusao', NEW.usr_inclusao
        ),
        IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
    );
END$$

DROP TRIGGER IF EXISTS `trg_criancas_cadastro_after_update`$$
CREATE TRIGGER `trg_criancas_cadastro_after_update`
AFTER UPDATE ON `criancas_cadastro`
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (
        usuario_id, acao, tabela_afetada, registro_id,
        dados_anteriores, dados_novos, ip_address
    ) VALUES (
        IFNULL(@session_user_id, NULL),
        'Atualização de registro',
        'criancas_cadastro',
        OLD.id,
        JSON_OBJECT(
            'id', OLD.id,
            'nome_completo', OLD.nome_completo,
            'data_nascimento', OLD.data_nascimento,
            'idade', OLD.idade,
            'sexo', OLD.sexo,
            'alergia_alimentos', OLD.alergia_alimentos,
            'alergia_medicamentos', OLD.alergia_medicamentos,
            'restricoes_alimentares', OLD.restricoes_alimentares,
            'observacoes_saude', OLD.observacoes_saude,
            'nome_responsavel', OLD.nome_responsavel,
            'grau_parentesco', OLD.grau_parentesco,
            'telefone_principal', OLD.telefone_principal,
            'telefone_alternativo', OLD.telefone_alternativo,
            'endereco_completo', OLD.endereco_completo,
            'documento_rg_cpf', OLD.documento_rg_cpf,
            'email_responsavel', OLD.email_responsavel,
            'nome_emergencia', OLD.nome_emergencia,
            'telefone_emergencia', OLD.telefone_emergencia,
            'grau_parentesco_emergencia', OLD.grau_parentesco_emergencia,
            'autorizacao_retirada', OLD.autorizacao_retirada,
            'data_cadastro', OLD.data_cadastro,
            'usr_cadastro', OLD.usr_cadastro,
            'ativo', OLD.ativo,
            'usr_inclusao', OLD.usr_inclusao
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'nome_completo', NEW.nome_completo,
            'data_nascimento', NEW.data_nascimento,
            'idade', NEW.idade,
            'sexo', NEW.sexo,
            'alergia_alimentos', NEW.alergia_alimentos,
            'alergia_medicamentos', NEW.alergia_medicamentos,
            'restricoes_alimentares', NEW.restricoes_alimentares,
            'observacoes_saude', NEW.observacoes_saude,
            'nome_responsavel', NEW.nome_responsavel,
            'grau_parentesco', NEW.grau_parentesco,
            'telefone_principal', NEW.telefone_principal,
            'telefone_alternativo', NEW.telefone_alternativo,
            'endereco_completo', NEW.endereco_completo,
            'documento_rg_cpf', NEW.documento_rg_cpf,
            'email_responsavel', NEW.email_responsavel,
            'nome_emergencia', NEW.nome_emergencia,
            'telefone_emergencia', NEW.telefone_emergencia,
            'grau_parentesco_emergencia', NEW.grau_parentesco_emergencia,
            'autorizacao_retirada', NEW.autorizacao_retirada,
            'data_cadastro', NEW.data_cadastro,
            'usr_cadastro', NEW.usr_cadastro,
            'ativo', NEW.ativo,
            'usr_inclusao', NEW.usr_inclusao
        ),
        IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
    );
END$$

DROP TRIGGER IF EXISTS `trg_criancas_cadastro_after_delete`$$
CREATE TRIGGER `trg_criancas_cadastro_after_delete`
AFTER DELETE ON `criancas_cadastro`
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (
        usuario_id, acao, tabela_afetada, registro_id,
        dados_anteriores, dados_novos, ip_address
    ) VALUES (
        IFNULL(@session_user_id, NULL),
        'Exclusão de registro',
        'criancas_cadastro',
        OLD.id,
        JSON_OBJECT(
            'id', OLD.id,
            'nome_completo', OLD.nome_completo,
            'data_nascimento', OLD.data_nascimento,
            'idade', OLD.idade,
            'sexo', OLD.sexo,
            'alergia_alimentos', OLD.alergia_alimentos,
            'alergia_medicamentos', OLD.alergia_medicamentos,
            'restricoes_alimentares', OLD.restricoes_alimentares,
            'observacoes_saude', OLD.observacoes_saude,
            'nome_responsavel', OLD.nome_responsavel,
            'grau_parentesco', OLD.grau_parentesco,
            'telefone_principal', OLD.telefone_principal,
            'telefone_alternativo', OLD.telefone_alternativo,
            'endereco_completo', OLD.endereco_completo,
            'documento_rg_cpf', OLD.documento_rg_cpf,
            'email_responsavel', OLD.email_responsavel,
            'nome_emergencia', OLD.nome_emergencia,
            'telefone_emergencia', OLD.telefone_emergencia,
            'grau_parentesco_emergencia', OLD.grau_parentesco_emergencia,
            'autorizacao_retirada', OLD.autorizacao_retirada,
            'data_cadastro', OLD.data_cadastro,
            'usr_cadastro', OLD.usr_cadastro,
            'ativo', OLD.ativo,
            'usr_inclusao', OLD.usr_inclusao
        ),
        NULL,
        IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
    );
END$$

DELIMITER ;

DELIMITER $$

-- BEFORE UPDATE
DROP TRIGGER IF EXISTS `trg_equipe_membros_before_update`$$
CREATE TRIGGER `trg_equipe_membros_before_update`
BEFORE UPDATE ON `equipe_membros`
FOR EACH ROW
BEGIN
  SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
END$$

-- AFTER UPDATE
DROP TRIGGER IF EXISTS `trg_equipe_membros_after_update`$$
CREATE TRIGGER `trg_equipe_membros_after_update`
AFTER UPDATE ON `equipe_membros`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Atualização de registro',
    'equipe_membros',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'equipe_id', OLD.equipe_id,
      'usuario_id', OLD.usuario_id,
      'data_entrada', OLD.data_entrada,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao
    ),
    JSON_OBJECT(
      'id', NEW.id,
      'equipe_id', NEW.equipe_id,
      'usuario_id', NEW.usuario_id,
      'data_entrada', NEW.data_entrada,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER INSERT
DROP TRIGGER IF EXISTS `trg_equipe_membros_after_insert`$$
CREATE TRIGGER `trg_equipe_membros_after_insert`
AFTER INSERT ON `equipe_membros`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Inserção de registro',
    'equipe_membros',
    NEW.id,
    NULL,
    JSON_OBJECT(
      'id', NEW.id,
      'equipe_id', NEW.equipe_id,
      'usuario_id', NEW.usuario_id,
      'data_entrada', NEW.data_entrada,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER DELETE
DROP TRIGGER IF EXISTS `trg_equipe_membros_after_delete`$$
CREATE TRIGGER `trg_equipe_membros_after_delete`
AFTER DELETE ON `equipe_membros`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Exclusão de registro',
    'equipe_membros',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'equipe_id', OLD.equipe_id,
      'usuario_id', OLD.usuario_id,
      'data_entrada', OLD.data_entrada,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao
    ),
    NULL,
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

DELIMITER ;

DELIMITER $$

-- BEFORE UPDATE
DROP TRIGGER IF EXISTS `trg_eventos_before_update`$$
CREATE TRIGGER `trg_eventos_before_update`
BEFORE UPDATE ON `eventos`
FOR EACH ROW
BEGIN
  SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
  SET NEW.data_atualizacao = NOW();
END$$

-- AFTER UPDATE
DROP TRIGGER IF EXISTS `trg_eventos_after_update`$$
CREATE TRIGGER `trg_eventos_after_update`
AFTER UPDATE ON `eventos`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Atualização de registro',
    'eventos',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'nome', OLD.nome,
      'tipo_evento', OLD.tipo_evento,
      'faixa_etaria_min', OLD.faixa_etaria_min,
      'faixa_etaria_max', OLD.faixa_etaria_max,
      'capacidade_maxima', OLD.capacidade_maxima,
      'local_evento', OLD.local_evento,
      'duracao_horas', OLD.duracao_horas,
      'descricao', OLD.descricao,
      'data_inicio', OLD.data_inicio,
      'data_fim_evento', OLD.data_fim_evento,
      'status', OLD.status,
      'coordenador_id', OLD.coordenador_id,
      'data_criacao', OLD.data_criacao,
      'data_atualizacao', OLD.data_atualizacao,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao
    ),
    JSON_OBJECT(
      'id', NEW.id,
      'nome', NEW.nome,
      'tipo_evento', NEW.tipo_evento,
      'faixa_etaria_min', NEW.faixa_etaria_min,
      'faixa_etaria_max', NEW.faixa_etaria_max,
      'capacidade_maxima', NEW.capacidade_maxima,
      'local_evento', NEW.local_evento,
      'duracao_horas', NEW.duracao_horas,
      'descricao', NEW.descricao,
      'data_inicio', NEW.data_inicio,
      'data_fim_evento', NEW.data_fim_evento,
      'status', NEW.status,
      'coordenador_id', NEW.coordenador_id,
      'data_criacao', NEW.data_criacao,
      'data_atualizacao', NEW.data_atualizacao,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER INSERT
DROP TRIGGER IF EXISTS `trg_eventos_after_insert`$$
CREATE TRIGGER `trg_eventos_after_insert`
AFTER INSERT ON `eventos`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Inserção de registro',
    'eventos',
    NEW.id,
    NULL,
    JSON_OBJECT(
      'id', NEW.id,
      'nome', NEW.nome,
      'tipo_evento', NEW.tipo_evento,
      'faixa_etaria_min', NEW.faixa_etaria_min,
      'faixa_etaria_max', NEW.faixa_etaria_max,
      'capacidade_maxima', NEW.capacidade_maxima,
      'local_evento', NEW.local_evento,
      'duracao_horas', NEW.duracao_horas,
      'descricao', NEW.descricao,
      'data_inicio', NEW.data_inicio,
      'data_fim_evento', NEW.data_fim_evento,
      'status', NEW.status,
      'coordenador_id', NEW.coordenador_id,
      'data_criacao', NEW.data_criacao,
      'data_atualizacao', NEW.data_atualizacao,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER DELETE
DROP TRIGGER IF EXISTS `trg_eventos_after_delete`$$
CREATE TRIGGER `trg_eventos_after_delete`
AFTER DELETE ON `eventos`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Exclusão de registro',
    'eventos',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'nome', OLD.nome,
      'tipo_evento', OLD.tipo_evento,
      'faixa_etaria_min', OLD.faixa_etaria_min,
      'faixa_etaria_max', OLD.faixa_etaria_max,
      'capacidade_maxima', OLD.capacidade_maxima,
      'local_evento', OLD.local_evento,
      'duracao_horas', OLD.duracao_horas,
      'descricao', OLD.descricao,
      'data_inicio', OLD.data_inicio,
      'data_fim_evento', OLD.data_fim_evento,
      'status', OLD.status,
      'coordenador_id', OLD.coordenador_id,
      'data_criacao', OLD.data_criacao,
      'data_atualizacao', OLD.data_atualizacao,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao
    ),
    NULL,
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

DELIMITER ;


DELIMITER $$

-- BEFORE UPDATE
DROP TRIGGER IF EXISTS `trg_evento_criancas_before_update`$$
CREATE TRIGGER `trg_evento_criancas_before_update`
BEFORE UPDATE ON `evento_criancas`
FOR EACH ROW
BEGIN
  SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
  SET NEW.data_atualizacao = NOW();
END$$

-- AFTER UPDATE
DROP TRIGGER IF EXISTS `trg_evento_criancas_after_update`$$
CREATE TRIGGER `trg_evento_criancas_after_update`
AFTER UPDATE ON `evento_criancas`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Atualização de registro',
    'evento_criancas',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'evento_id', OLD.evento_id,
      'crianca_id', OLD.crianca_id,
      'status_participacao', OLD.status_participacao,
      'data_inscricao', OLD.data_inscricao,
      'data_checkin', OLD.data_checkin,
      'data_checkout', OLD.data_checkout,
      'observacoes', OLD.observacoes,
      'usuario_checkin', OLD.usuario_checkin,
      'usuario_checkout', OLD.usuario_checkout,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao,
      'data_inclusao', OLD.data_inclusao,
      'data_atualizacao', OLD.data_atualizacao
    ),
    JSON_OBJECT(
      'id', NEW.id,
      'evento_id', NEW.evento_id,
      'crianca_id', NEW.crianca_id,
      'status_participacao', NEW.status_participacao,
      'data_inscricao', NEW.data_inscricao,
      'data_checkin', NEW.data_checkin,
      'data_checkout', NEW.data_checkout,
      'observacoes', NEW.observacoes,
      'usuario_checkin', NEW.usuario_checkin,
      'usuario_checkout', NEW.usuario_checkout,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao,
      'data_inclusao', NEW.data_inclusao,
      'data_atualizacao', NEW.data_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER INSERT
DROP TRIGGER IF EXISTS `trg_evento_criancas_after_insert`$$
CREATE TRIGGER `trg_evento_criancas_after_insert`
AFTER INSERT ON `evento_criancas`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Inserção de registro',
    'evento_criancas',
    NEW.id,
    NULL,
    JSON_OBJECT(
      'id', NEW.id,
      'evento_id', NEW.evento_id,
      'crianca_id', NEW.crianca_id,
      'status_participacao', NEW.status_participacao,
      'data_inscricao', NEW.data_inscricao,
      'data_checkin', NEW.data_checkin,
      'data_checkout', NEW.data_checkout,
      'observacoes', NEW.observacoes,
      'usuario_checkin', NEW.usuario_checkin,
      'usuario_checkout', NEW.usuario_checkout,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao,
      'data_inclusao', NEW.data_inclusao,
      'data_atualizacao', NEW.data_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER DELETE
DROP TRIGGER IF EXISTS `trg_evento_criancas_after_delete`$$
CREATE TRIGGER `trg_evento_criancas_after_delete`
AFTER DELETE ON `evento_criancas`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Exclusão de registro',
    'evento_criancas',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'evento_id', OLD.evento_id,
      'crianca_id', OLD.crianca_id,
      'status_participacao', OLD.status_participacao,
      'data_inscricao', OLD.data_inscricao,
      'data_checkin', OLD.data_checkin,
      'data_checkout', OLD.data_checkout,
      'observacoes', OLD.observacoes,
      'usuario_checkin', OLD.usuario_checkin,
      'usuario_checkout', OLD.usuario_checkout,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao,
      'data_inclusao', OLD.data_inclusao,
      'data_atualizacao', OLD.data_atualizacao
    ),
    NULL,
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

DELIMITER ;


DELIMITER $$

-- BEFORE UPDATE
DROP TRIGGER IF EXISTS `trg_evento_equipes_before_update`$$
CREATE TRIGGER `trg_evento_equipes_before_update`
BEFORE UPDATE ON `evento_equipes`
FOR EACH ROW
BEGIN
  SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
  SET NEW.data_atualizacao = NOW();
END$$

-- AFTER UPDATE
DROP TRIGGER IF EXISTS `trg_evento_equipes_after_update`$$
CREATE TRIGGER `trg_evento_equipes_after_update`
AFTER UPDATE ON `evento_equipes`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Atualização de registro',
    'evento_equipes',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'evento_id', OLD.evento_id,
      'equipe_id', OLD.equipe_id,
      'data_atribuicao', OLD.data_atribuicao,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao,
      'data_inclusao', OLD.data_inclusao,
      'data_atualizacao', OLD.data_atualizacao
    ),
    JSON_OBJECT(
      'id', NEW.id,
      'evento_id', NEW.evento_id,
      'equipe_id', NEW.equipe_id,
      'data_atribuicao', NEW.data_atribuicao,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao,
      'data_inclusao', NEW.data_inclusao,
      'data_atualizacao', NEW.data_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER INSERT
DROP TRIGGER IF EXISTS `trg_evento_equipes_after_insert`$$
CREATE TRIGGER `trg_evento_equipes_after_insert`
AFTER INSERT ON `evento_equipes`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Inserção de registro',
    'evento_equipes',
    NEW.id,
    NULL,
    JSON_OBJECT(
      'id', NEW.id,
      'evento_id', NEW.evento_id,
      'equipe_id', NEW.equipe_id,
      'data_atribuicao', NEW.data_atribuicao,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao,
      'data_inclusao', NEW.data_inclusao,
      'data_atualizacao', NEW.data_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER DELETE
DROP TRIGGER IF EXISTS `trg_evento_equipes_after_delete`$$
CREATE TRIGGER `trg_evento_equipes_after_delete`
AFTER DELETE ON `evento_equipes`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Exclusão de registro',
    'evento_equipes',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'evento_id', OLD.evento_id,
      'equipe_id', OLD.equipe_id,
      'data_atribuicao', OLD.data_atribuicao,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao,
      'data_inclusao', OLD.data_inclusao,
      'data_atualizacao', OLD.data_atualizacao
    ),
    NULL,
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

DELIMITER ;

DELIMITER $$

-- BEFORE UPDATE
DROP TRIGGER IF EXISTS `trg_tarefas_before_update`$$
CREATE TRIGGER `trg_tarefas_before_update`
BEFORE UPDATE ON `tarefas`
FOR EACH ROW
BEGIN
  SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
  SET NEW.data_atualizacao = NOW();
END$$

-- AFTER UPDATE
DROP TRIGGER IF EXISTS `trg_tarefas_after_update`$$
CREATE TRIGGER `trg_tarefas_after_update`
AFTER UPDATE ON `tarefas`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Atualização de registro',
    'tarefas',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'titulo', OLD.titulo,
      'tipo_atividade', OLD.tipo_atividade,
      'descricao', OLD.descricao,
      'material_necessario', OLD.material_necessario,
      'publico_alvo', OLD.publico_alvo,
      'evento_id', OLD.evento_id,
      'responsavel_id', OLD.responsavel_id,
      'status', OLD.status,
      'data_inicio', OLD.data_inicio,
      'data_fim_prevista', OLD.data_fim_prevista,
      'data_fim_real', OLD.data_fim_real,
      'data_criacao', OLD.data_criacao,
      'data_atualizacao', OLD.data_atualizacao,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao
    ),
    JSON_OBJECT(
      'id', NEW.id,
      'titulo', NEW.titulo,
      'tipo_atividade', NEW.tipo_atividade,
      'descricao', NEW.descricao,
      'material_necessario', NEW.material_necessario,
      'publico_alvo', NEW.publico_alvo,
      'evento_id', NEW.evento_id,
      'responsavel_id', NEW.responsavel_id,
      'status', NEW.status,
      'data_inicio', NEW.data_inicio,
      'data_fim_prevista', NEW.data_fim_prevista,
      'data_fim_real', NEW.data_fim_real,
      'data_criacao', NEW.data_criacao,
      'data_atualizacao', NEW.data_atualizacao,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER INSERT
DROP TRIGGER IF EXISTS `trg_tarefas_after_insert`$$
CREATE TRIGGER `trg_tarefas_after_insert`
AFTER INSERT ON `tarefas`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Inserção de registro',
    'tarefas',
    NEW.id,
    NULL,
    JSON_OBJECT(
      'id', NEW.id,
      'titulo', NEW.titulo,
      'tipo_atividade', NEW.tipo_atividade,
      'descricao', NEW.descricao,
      'material_necessario', NEW.material_necessario,
      'publico_alvo', NEW.publico_alvo,
      'evento_id', NEW.evento_id,
      'responsavel_id', NEW.responsavel_id,
      'status', NEW.status,
      'data_inicio', NEW.data_inicio,
      'data_fim_prevista', NEW.data_fim_prevista,
      'data_fim_real', NEW.data_fim_real,
      'data_criacao', NEW.data_criacao,
      'data_atualizacao', NEW.data_atualizacao,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER DELETE
DROP TRIGGER IF EXISTS `trg_tarefas_after_delete`$$
CREATE TRIGGER `trg_tarefas_after_delete`
AFTER DELETE ON `tarefas`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Exclusão de registro',
    'tarefas',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'titulo', OLD.titulo,
      'tipo_atividade', OLD.tipo_atividade,
      'descricao', OLD.descricao,
      'material_necessario', OLD.material_necessario,
      'publico_alvo', OLD.publico_alvo,
      'evento_id', OLD.evento_id,
      'responsavel_id', OLD.responsavel_id,
      'status', OLD.status,
      'data_inicio', OLD.data_inicio,
      'data_fim_prevista', OLD.data_fim_prevista,
      'data_fim_real', OLD.data_fim_real,
      'data_criacao', OLD.data_criacao,
      'data_atualizacao', OLD.data_atualizacao,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao
    ),
    NULL,
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

DELIMITER ;

DELIMITER $$

-- BEFORE UPDATE
DROP TRIGGER IF EXISTS `trg_usuarios_before_update`$$
CREATE TRIGGER `trg_usuarios_before_update`
BEFORE UPDATE ON `usuarios`
FOR EACH ROW
BEGIN
  SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
  SET NEW.data_atualizacao = NOW();
END$$

-- AFTER UPDATE
DROP TRIGGER IF EXISTS `trg_usuarios_after_update`$$
CREATE TRIGGER `trg_usuarios_after_update`
AFTER UPDATE ON `usuarios`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Atualização de registro',
    'usuarios',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'nome_completo', OLD.nome_completo,
      'cpf', OLD.cpf,
      'email', OLD.email,
      'cargo', OLD.cargo,
      'login', OLD.login,
      'senha', OLD.senha,
      'perfil', OLD.perfil,
      'data_criacao', OLD.data_criacao,
      'data_atualizacao', OLD.data_atualizacao,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao
    ),
    JSON_OBJECT(
      'id', NEW.id,
      'nome_completo', NEW.nome_completo,
      'cpf', NEW.cpf,
      'email', NEW.email,
      'cargo', NEW.cargo,
      'login', NEW.login,
      'senha', NEW.senha,
      'perfil', NEW.perfil,
      'data_criacao', NEW.data_criacao,
      'data_atualizacao', NEW.data_atualizacao,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER INSERT
DROP TRIGGER IF EXISTS `trg_usuarios_after_insert`$$
CREATE TRIGGER `trg_usuarios_after_insert`
AFTER INSERT ON `usuarios`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Inserção de registro',
    'usuarios',
    NEW.id,
    NULL,
    JSON_OBJECT(
      'id', NEW.id,
      'nome_completo', NEW.nome_completo,
      'cpf', NEW.cpf,
      'email', NEW.email,
      'cargo', NEW.cargo,
      'login', NEW.login,
      'senha', NEW.senha,
      'perfil', NEW.perfil,
      'data_criacao', NEW.data_criacao,
      'data_atualizacao', NEW.data_atualizacao,
      'usr_inclusao', NEW.usr_inclusao,
      'usr_atualizacao', NEW.usr_atualizacao
    ),
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

-- AFTER DELETE
DROP TRIGGER IF EXISTS `trg_usuarios_after_delete`$$
CREATE TRIGGER `trg_usuarios_after_delete`
AFTER DELETE ON `usuarios`
FOR EACH ROW
BEGIN
  INSERT INTO logs_sistema (
    usuario_id, acao, tabela_afetada, registro_id,
    dados_anteriores, dados_novos, ip_address
  ) VALUES (
    IFNULL(@session_user_id, NULL),
    'Exclusão de registro',
    'usuarios',
    OLD.id,
    JSON_OBJECT(
      'id', OLD.id,
      'nome_completo', OLD.nome_completo,
      'cpf', OLD.cpf,
      'email', OLD.email,
      'cargo', OLD.cargo,
      'login', OLD.login,
      'senha', OLD.senha,
      'perfil', OLD.perfil,
      'data_criacao', OLD.data_criacao,
      'data_atualizacao', OLD.data_atualizacao,
      'usr_inclusao', OLD.usr_inclusao,
      'usr_atualizacao', OLD.usr_atualizacao
    ),
    NULL,
    IFNULL(@session_ip, SUBSTRING_INDEX(USER(), '@', 1))
  );
END$$

DELIMITER ;




DELIMITER $$

-- ================================
-- TABELA: criancas_cadastro
-- ================================
DROP TRIGGER IF EXISTS trg_criancas_cadastro_before_insert$$
CREATE TRIGGER trg_criancas_cadastro_before_insert
BEFORE INSERT ON criancas_cadastro
FOR EACH ROW
BEGIN
    IF NEW.usr_inclusao IS NULL THEN
        SET NEW.usr_inclusao = IFNULL(@session_user_name, CURRENT_USER());
    END IF;
    IF NEW.data_cadastro IS NULL THEN
        SET NEW.data_cadastro = NOW();
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_criancas_cadastro_before_update$$
CREATE TRIGGER trg_criancas_cadastro_before_update
BEFORE UPDATE ON criancas_cadastro
FOR EACH ROW
BEGIN
    SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
    SET NEW.data_atualizacao = NOW();
END$$

-- ================================
-- TABELA: equipes
-- ================================
DROP TRIGGER IF EXISTS trg_equipes_before_insert$$
CREATE TRIGGER trg_equipes_before_insert
BEFORE INSERT ON equipes
FOR EACH ROW
BEGIN
    IF NEW.usr_inclusao IS NULL THEN
        SET NEW.usr_inclusao = IFNULL(@session_user_name, CURRENT_USER());
    END IF;
    IF NEW.data_criacao IS NULL THEN
        SET NEW.data_criacao = NOW();
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_equipes_before_update$$
CREATE TRIGGER trg_equipes_before_update
BEFORE UPDATE ON equipes
FOR EACH ROW
BEGIN
    SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
    SET NEW.data_atualizacao = NOW();
END$$

-- ================================
-- TABELA: equipe_membros
-- ================================
DROP TRIGGER IF EXISTS trg_equipe_membros_before_insert$$
CREATE TRIGGER trg_equipe_membros_before_insert
BEFORE INSERT ON equipe_membros
FOR EACH ROW
BEGIN
    IF NEW.usr_inclusao IS NULL THEN
        SET NEW.usr_inclusao = IFNULL(@session_user_name, CURRENT_USER());
    END IF;
    IF NEW.data_inclusao IS NULL THEN
        SET NEW.data_inclusao = NOW();
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_equipe_membros_before_update$$
CREATE TRIGGER trg_equipe_membros_before_update
BEFORE UPDATE ON equipe_membros
FOR EACH ROW
BEGIN
    SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
    SET NEW.data_atualizacao = NOW();
END$$

-- ================================
-- TABELA: eventos
-- ================================
DROP TRIGGER IF EXISTS trg_eventos_before_insert$$
CREATE TRIGGER trg_eventos_before_insert
BEFORE INSERT ON eventos
FOR EACH ROW
BEGIN
    IF NEW.usr_inclusao IS NULL THEN
        SET NEW.usr_inclusao = IFNULL(@session_user_name, CURRENT_USER());
    END IF;
    IF NEW.data_criacao IS NULL THEN
        SET NEW.data_criacao = NOW();
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_eventos_before_update$$
CREATE TRIGGER trg_eventos_before_update
BEFORE UPDATE ON eventos
FOR EACH ROW
BEGIN
    SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
    SET NEW.data_atualizacao = NOW();
END$$

-- ================================
-- TABELA: evento_criancas
-- ================================
DROP TRIGGER IF EXISTS trg_evento_criancas_before_insert$$
CREATE TRIGGER trg_evento_criancas_before_insert
BEFORE INSERT ON evento_criancas
FOR EACH ROW
BEGIN
    IF NEW.usr_inclusao IS NULL THEN
        SET NEW.usr_inclusao = IFNULL(@session_user_name, CURRENT_USER());
    END IF;
    IF NEW.data_inclusao IS NULL THEN
        SET NEW.data_inclusao = NOW();
    END IF;
    IF NEW.data_atualizacao IS NULL THEN
        SET NEW.data_atualizacao = NOW();
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_evento_criancas_before_update$$
CREATE TRIGGER trg_evento_criancas_before_update
BEFORE UPDATE ON evento_criancas
FOR EACH ROW
BEGIN
    SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
    SET NEW.data_atualizacao = NOW();
END$$

-- ================================
-- TABELA: evento_equipes
-- ================================
DROP TRIGGER IF EXISTS trg_evento_equipes_before_insert$$
CREATE TRIGGER trg_evento_equipes_before_insert
BEFORE INSERT ON evento_equipes
FOR EACH ROW
BEGIN
    IF NEW.usr_inclusao IS NULL THEN
        SET NEW.usr_inclusao = IFNULL(@session_user_name, CURRENT_USER());
    END IF;
    IF NEW.data_inclusao IS NULL THEN
        SET NEW.data_inclusao = NOW();
    END IF;
    IF NEW.data_atualizacao IS NULL THEN
        SET NEW.data_atualizacao = NOW();
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_evento_equipes_before_update$$
CREATE TRIGGER trg_evento_equipes_before_update
BEFORE UPDATE ON evento_equipes
FOR EACH ROW
BEGIN
    SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
    SET NEW.data_atualizacao = NOW();
END$$

-- ================================
-- TABELA: tarefas
-- ================================
DROP TRIGGER IF EXISTS trg_tarefas_before_insert$$
CREATE TRIGGER trg_tarefas_before_insert
BEFORE INSERT ON tarefas
FOR EACH ROW
BEGIN
    IF NEW.usr_inclusao IS NULL THEN
        SET NEW.usr_inclusao = IFNULL(@session_user_name, CURRENT_USER());
    END IF;
    IF NEW.data_criacao IS NULL THEN
        SET NEW.data_criacao = NOW();
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_tarefas_before_update$$
CREATE TRIGGER trg_tarefas_before_update
BEFORE UPDATE ON tarefas
FOR EACH ROW
BEGIN
    SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
    SET NEW.data_atualizacao = NOW();
END$$

-- ================================
-- TABELA: usuarios
-- ================================
DROP TRIGGER IF EXISTS trg_usuarios_before_insert$$
CREATE TRIGGER trg_usuarios_before_insert
BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.usr_inclusao IS NULL THEN
        SET NEW.usr_inclusao = IFNULL(@session_user_name, CURRENT_USER());
    END IF;
    IF NEW.data_criacao IS NULL THEN
        SET NEW.data_criacao = NOW();
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_usuarios_before_update$$
CREATE TRIGGER trg_usuarios_before_update
BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN
    SET NEW.usr_atualizacao = IFNULL(@session_user_name, CURRENT_USER());
    SET NEW.data_atualizacao = NOW();
END$$

DELIMITER ;
