DELIMITER $
CREATE PROCEDURE select_clientes (
    IN p_id_usuario int
) BEGIN 
SELECT id,codigo, nome FROM clientes WHERE id_usuario = p_id_usuario;
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE select_cliente (
    IN p_id int,
    IN p_id_usuario int
) BEGIN 
SELECT id,codigo, nome, fantasia, documento FROM clientes WHERE id_usuario = p_id_usuario AND id = p_id;
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE select_cliente_documento (
    IN p_id_usuario int,
    IN p_documento VARCHAR(14)
) BEGIN 
SELECT id, codigo, nome, fantasia, documento FROM clientes WHERE id_usuario = p_id_usuario AND documento = p_documento;
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE inserir_clientes (
    IN p_id_usuario int,  
    IN p_codigo int,
    IN p_nome VARCHAR(60),
    IN p_fantasia VARCHAR(100),
    IN p_documento VARCHAR(14)
) BEGIN 
INSERT INTO clientes (id_usuario, codigo, nome, fantasia, documento) 
VALUES (p_id_usuario, p_codigo, p_nome, p_fantasia, p_documento);
SELECT LAST_INSERT_ID() AS id;
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE atualizar_clientes (
    IN p_id int,
    IN p_id_usuario int,  
    IN p_codigo int,
    IN p_nome VARCHAR(60),
    IN p_fantasia VARCHAR(100),
    IN p_documento VARCHAR(14)
) BEGIN 
UPDATE clientes 
SET codigo = COALESCE(p_codigo, codigo), nome = COALESCE(p_nome, nome), fantasia = COALESCE(p_fantasia, fantasia), 
documento = COALESCE(p_documento, documento)
WHERE (id = p_id AND id_usuario = p_id_usuario); 
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE deletar_clientes (
    IN p_id_cliente int,  
    IN p_id int
) BEGIN 
DELETE FROM clientes WHERE (id = p_id AND id_usuario = p_id_usuario); 
END $  
DELIMITER ;