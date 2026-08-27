USE softline;

DELIMITER $
CREATE PROCEDURE select_produtos (
    IN p_id_usuario int
) BEGIN 
SELECT id, nome, codigo, valor FROM produtos WHERE id_usuario = p_id_usuario;
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE select_produto (
    IN p_id int,
    IN p_id_usuario int
) BEGIN 
SELECT id, nome, codigo, descricao, codigo_de_barras, valor, peso_bruto, peso_liquido FROM produtos WHERE id_usuario = p_id_usuario AND id = p_id;
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE inserir_produto (
    IN p_id_usuario int,
    IN p_nome VARCHAR(100),
    IN p_codigo int,
    IN p_descricao VARCHAR(60),
    IN p_codigo_de_barras CHAR(14),
    IN p_valor DECIMAL(10,2 ),
    IN p_peso_bruto DECIMAL(10,3 ),
    IN p_peso_liquido DECIMAL(10,3 ) 
) BEGIN 
INSERT INTO produtos (id_usuario, nome, codigo, descricao, codigo_de_barras, valor, peso_bruto, peso_liquido) 
VALUES (p_id_usuario, p_nome, p_codigo, p_descricao, p_codigo_de_barras, p_valor, p_peso_bruto, p_peso_liquido ); 
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE atualizar_produto (
    IN p_id int,
    IN p_id_usuario int,
    IN p_nome VARCHAR(100),
    IN p_codigo int,
    IN p_descricao VARCHAR(60),
    IN p_codigo_de_barras CHAR(14),
    IN p_valor DECIMAL(10,2 ),
    IN p_peso_bruto DECIMAL(10,3 ),
    IN p_peso_liquido DECIMAL(10,3 ) 
) BEGIN 
UPDATE produtos 
SET nome = COALESCE(p_nome, nome), codigo = COALESCE(p_codigo, codigo), descricao = COALESCE(p_descricao, descricao), codigo_de_barras = COALESCE(p_codigo_de_barras, codigo_de_barras), 
valor = COALESCE(p_valor, valor), peso_bruto = COALESCE(p_peso_bruto, peso_bruto), peso_liquido = COALESCE(p_peso_liquido, peso_liquido)  
WHERE (id = p_id AND id_usuario = p_id_usuario); 
END $  
DELIMITER ;

DELIMITER $
CREATE PROCEDURE deletar_produto (
    IN p_id_usuario int,  
    IN p_id int
) BEGIN 
DELETE FROM produtos WHERE (id = p_id AND id_usuario = p_id_usuario) ; 
END $  
DELIMITER ;

