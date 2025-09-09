-- ======================================================
-- BANCO DE CAMPVIA
-- ======================================================

-- DROP DATABASE IF EXISTS turismo;
CREATE DATABASE campvia;
USE campvia;

-- ======================================================
-- 1. Usuários
-- ======================================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    foto VARCHAR(255),
    cargo VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    sobrenome VARCHAR(100),
    apelido VARCHAR(100),
    telefone VARCHAR(20),
    endereco VARCHAR(255)
);

-- ======================================================
-- 2. Postagens
-- ======================================================
CREATE TABLE postagens (
    id_postagem INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('geral', 'passeio', 'roteiro') NOT NULL,
    capa VARCHAR(255),
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- ======================================================
-- 3. Categorias
-- ======================================================
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) UNIQUE NOT NULL
);

-- ======================================================
-- 4. Passeios (especialização de Postagem)
-- ======================================================
CREATE TABLE passeios (
    id_postagem INT PRIMARY KEY,
    localizacao VARCHAR(255),
    hora_funcionamento VARCHAR(100),
    contato VARCHAR(100),
    fotos TEXT,
    FOREIGN KEY (id_postagem) REFERENCES postagens(id_postagem) ON DELETE CASCADE
);

-- ======================================================
-- 5. Roteiros (especialização de Postagem)
-- ======================================================
CREATE TABLE roteiros (
    id_postagem INT PRIMARY KEY,
    nome_roteiro VARCHAR(150) NOT NULL,
    FOREIGN KEY (id_postagem) REFERENCES postagens(id_postagem) ON DELETE CASCADE
);

-- ======================================================
-- 6. Comentários
-- ======================================================
CREATE TABLE comentarios (
    id_comentario INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_postagem INT NOT NULL,
    texto TEXT NOT NULL,
    data_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_postagem) REFERENCES postagens(id_postagem)
);

-- ======================================================
-- 7. Inscrições
-- ======================================================
CREATE TABLE inscricoes (
    id_inscricao INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_postagem INT NOT NULL,
    data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_usuario, id_postagem),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_postagem) REFERENCES postagens(id_postagem)
);

-- ======================================================
-- 8. Likes
-- ======================================================
CREATE TABLE likes (
    id_like INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_postagem INT NOT NULL,
    data_like TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_usuario, id_postagem),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_postagem) REFERENCES postagens(id_postagem)
);

-- ======================================================
-- 9. Salvos
-- ======================================================
CREATE TABLE salvos (
    id_salvo INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_postagem INT NOT NULL,
    data_salvo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_usuario, id_postagem),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_postagem) REFERENCES postagens(id_postagem)
);

-- ======================================================
-- 10. Certificados
-- ======================================================
CREATE TABLE certificados (
    id_certificado INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_roteiro INT NOT NULL,
    data_emissao DATE NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_roteiro) REFERENCES roteiros(id_postagem)
);

-- ======================================================
-- 11. Postagem_Categoria (N:N)
-- ======================================================
CREATE TABLE postagem_categoria (
    id_postagem INT NOT NULL,
    id_categoria INT NOT NULL,
    PRIMARY KEY (id_postagem, id_categoria),
    FOREIGN KEY (id_postagem) REFERENCES postagens(id_postagem) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE CASCADE
);

-- ======================================================
-- 12. Passeio_Roteiro (N:N com atributos)
-- ======================================================
CREATE TABLE passeio_roteiro (
    id_passeio INT NOT NULL,
    id_roteiro INT NOT NULL,
    ordem INT,
    dia INT,
    observacoes TEXT,
    PRIMARY KEY (id_passeio, id_roteiro),
    FOREIGN KEY (id_passeio) REFERENCES passeios(id_postagem) ON DELETE CASCADE,
    FOREIGN KEY (id_roteiro) REFERENCES roteiros(id_postagem) ON DELETE CASCADE
);
