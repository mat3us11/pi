CREATE DATABASE IF NOT EXISTS `campIvia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `campIvia`;


CREATE TABLE usuarios (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    foto_perfil varchar(255) DEFAULT NULL,
    endereco varchar(255) DEFAULT NULL,
    apelido varchar(100) DEFAULT NULL,
    preferencia_nome_apelido enum('nome','apelido') NOT NULL DEFAULT 'nome',
    nivel enum('usuario','admin') NOT NULL DEFAULT 'usuario'
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Roteiros
CREATE TABLE roteiros (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    usuario_id INT REFERENCES usuarioS(id) ON DELETE CASCADE,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    duracao INT,
    data_comeco DATE,
    data_fim DATE,
    destino VARCHAR(150),
    custo DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Dias do roteiro
CREATE TABLE dias_roteiro (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    roteiro_id INT REFERENCES roteiros(id) ON DELETE CASCADE,
    dia_numero INT NOT NULL,
    nome VARCHAR(150),
    descricao TEXT,
    date DATE
);

-- 4. Passeios
CREATE TABLE passeios (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    local VARCHAR(150),
    duracao_hora INT,
    preco DECIMAL(10,2) DEFAULT 0,
    dias_abertos VARCHAR(255), 
    incluido VARCHAR(255),
    levar VARCHAR(255), 
    categorias VARCHAR(100), 
    capa VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Relação Dia do Roteiro ↔ Passeios
CREATE TABLE dia_roteiro_passeios (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    dia_roteiro_id INT REFERENCES dias_roteiro(id) ON DELETE CASCADE,
    passeio_id INT REFERENCES passeios(id) ON DELETE CASCADE,
    inicio TIME,
    observacoes TEXT
);

-- -- 6. Avaliações de Passeios
-- CREATE TABLE tour_reviews (
--     id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
--     user_id UUID REFERENCES users(id) ON DELETE SET NULL,
--     tour_id UUID REFERENCES tours(id) ON DELETE CASCADE,
--     rating INT CHECK (rating BETWEEN 1 AND 5),
--     comment TEXT,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- 7. Tags
CREATE TABLE tags (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL
);

-- 7.1. Tags de Passeios
CREATE TABLE passeios_tags (
    passeio_id INT REFERENCES passeios(id) ON DELETE CASCADE,
    tag_id INT REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (passeio_id, tag_id)
);

-- 7.2. Tags de Roteiros
CREATE TABLE roteiros_tags (
    roteiro_id INT REFERENCES roteiros(id) ON DELETE CASCADE,
    tag_id INT REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (roteiro_id, tag_id)
);