USE softline;

DELIMITER $
CREATE PROCEDURE select_usuario (
    IN p_id int
) BEGIN 
SELECT id, email, nome FROM usuarios WHERE id = p_id;
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE login_usuario (
    IN p_email VARCHAR(100)
) BEGIN 
SELECT id, email, nome, password FROM usuarios WHERE email = p_email;
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE inserir_usuario (
    IN p_email VARCHAR(100),
    IN p_nome VARCHAR(60),
    IN p_password VARCHAR(255)
) BEGIN 
INSERT INTO usuarios (email, nome, password) 
VALUES (p_email, p_nome, p_password); 
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE atualizar_usuario (
    IN p_id int,
    IN p_email VARCHAR(100),
    IN p_nome VARCHAR(60),
    IN p_password VARCHAR(255)
) BEGIN 
UPDATE usuarios 
SET email = COALESCE(p_email, email), nome = COALESCE(p_nome, nome), 
password = COALESCE(p_password, password)  
WHERE id = p_id; 
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE deletar_usuario (
    IN p_id int
) BEGIN 
DELETE FROM usuarios WHERE id = p_id; 
END $  
DELIMITER ;