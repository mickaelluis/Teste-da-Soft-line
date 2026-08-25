USE softline;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    nome VARCHAR(60) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario int NOT NULL,
    nome VARCHAR(100) NOT NULL,
    codigo int NOT NULL,
    descricao VARCHAR(60) NOT NULL,
    codigo_de_barras CHAR(14) NOT NULL,
    valor DECIMAL(10,2 ) NOT NULL,
    peso_bruto DECIMAL(10,3 ) NOT NULL,
    peso_liquido DECIMAL(10,3 ) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,   
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario int NOT NULL,
    codigo int NOT NULL,
    nome VARCHAR(60) NOT NULL,
    fantasia VARCHAR(100) NOT NULL,
    documento VARCHAR(14) NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS endereco (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_clientes int NOT NULL,
    cep CHAR(8) NOT NULL,
    estado CHAR(2) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    bairro VARCHAR(100) NOT NULL,
    rua VARCHAR(100) NOT NULL,
    numero VARCHAR(10) NOT NULL,
    FOREIGN KEY (id_clientes) REFERENCES clientes(id) ON DELETE CASCADE
); 