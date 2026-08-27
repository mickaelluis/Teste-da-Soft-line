USE softline;

DELIMITER $
CREATE PROCEDURE select_enderecos (
    IN p_id_clientes int
) BEGIN 
SELECT id, cep, estado, cidade, bairro, rua, numero FROM endereco WHERE id_clientes = p_id_clientes;
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE select_endereco (
    IN p_id int,
    IN p_id_clientes int
) BEGIN 
SELECT id, cep, estado, cidade, bairro, rua, numero FROM endereco WHERE id = p_id AND id_clientes = p_id_clientes;
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE inserir_endereco (
    IN p_id_clientes int,
    IN p_cep CHAR(8),
    IN p_estado CHAR(2),
    IN p_cidade VARCHAR(100),
    IN p_bairro VARCHAR(100),
    IN p_rua VARCHAR(100),
    IN p_numero VARCHAR(10)
) BEGIN 
INSERT INTO endereco (id_clientes, cep, estado, cidade, bairro, rua, numero) 
VALUES (p_id_clientes, p_cep, p_estado, p_cidade, p_bairro, p_rua, p_numero); 
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE atualizar_endereco (
    IN p_id int,
    IN p_id_clientes int,
    IN p_cep CHAR(8),
    IN p_estado CHAR(2),
    IN p_cidade VARCHAR(100),
    IN p_bairro VARCHAR(100),
    IN p_rua VARCHAR(100),
    IN p_numero VARCHAR(10)
) BEGIN 
UPDATE endereco 
SET cep = COALESCE(p_cep, cep), estado = COALESCE(p_estado, estado), cidade = COALESCE(p_cidade, cidade), 
bairro = COALESCE(p_bairro, bairro), rua = COALESCE(p_rua, rua), numero = COALESCE(p_numero, numero)  
WHERE (id = p_id AND id_clientes = p_id_clientes); 
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE deletar_endereco (
    IN p_id_clientes int,
    IN p_id int
) BEGIN 
DELETE FROM endereco WHERE (id = p_id AND id_clientes = p_id_clientes); 
END $  
DELIMITER ;
