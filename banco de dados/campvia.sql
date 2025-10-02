-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02/10/2025 às 01:43
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `campvia`
--
CREATE DATABASE IF NOT EXISTS `campvia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `campvia`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `passeios`
--

CREATE TABLE `passeios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `descricao` text NOT NULL,
  `categorias` varchar(255) DEFAULT NULL,
  `localidade` varchar(255) NOT NULL,
  `capa` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `passeios`
--

INSERT INTO `passeios` (`id`, `usuario_id`, `nome`, `descricao`, `categorias`, `localidade`, `capa`, `criado_em`) VALUES
(1, 24, 'asasassas', 'asasasasas', 'cultural', 'Americana, São Paulo, Brasil', '../uploads/geralcapa_24_1758654005.jfif', '2025-09-23 19:00:05'),
(2, 24, 'adawsdawdwadwadsdwa', 'awdaxsddgfbdfgfd', 'cultural', 'Americana, São Paulo, Brasil', '../uploads/geralcapa_24_1758654024.jfif', '2025-09-23 19:00:24');

-- --------------------------------------------------------

--
-- Estrutura para tabela `poi_images_cache`
--

CREATE TABLE `poi_images_cache` (
  `id` int(11) NOT NULL,
  `poi_key` varchar(255) NOT NULL,
  `image_url` text NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `rota`
--

CREATE TABLE `rota` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `descricao` text NOT NULL,
  `categorias` varchar(255) DEFAULT NULL,
  `ponto_partida` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `paradas` text DEFAULT NULL,
  `capa` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `rota`
--

INSERT INTO `rota` (`id`, `usuario_id`, `nome`, `descricao`, `categorias`, `ponto_partida`, `destino`, `paradas`, `capa`, `criado_em`) VALUES
(22, 9, 'Rota do mcqueen', 'Mcqueen lanches', 'aventura,gastronomica,citytour', 'Rua Ismênia Maria Dos Santos, Tatuí - São Paulo, 18280-160, Brasil', 'Sorocaba, São Paulo, Brasil', '[\"\\\"Sorocaba é do Senhor Jesus Cristo\\\"\",\"14-bis\",\"Adega Simao\",\"Bar\",\"Bar do Luizinho\",\"Be Brave Coffee and Tea II\",\"Bioliquido Escola de Natação\"]', '../uploads/geral/capa_9_1759361763.png', '2025-10-01 23:23:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `senha` varchar(200) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `apelido` varchar(100) DEFAULT NULL,
  `preferencia_nome_apelido` enum('nome','apelido') NOT NULL DEFAULT 'nome',
  `nivel` enum('usuario','admin') NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id`, `nome`, `email`, `senha`, `foto_perfil`, `endereco`, `apelido`, `preferencia_nome_apelido`, `nivel`) VALUES
(9, 'Mateus Azevedo', 'mateuschapad33@gmail.com', '', 'uploads/foto_9_1757983590.png', 'Rua Ismênia Maria dos Santos', 'Suetam', 'nome', 'admin'),
(10, 'Mateus Azevedo', 'mateusazevedo11.exe@gmail.com', '', 'https://lh3.googleusercontent.com/a/ACg8ocIx4oujtytswR-b2Ts1p01qzFb3o25T0wJMecsqXzKpBqG4DR4=s96-c', '', '', 'nome', 'usuario'),
(11, 'PEdrinho matador', 'mateus.azevedop5@gmail.com', '', 'uploads/foto_11_1752540121.png', NULL, NULL, 'nome', 'usuario'),
(12, 'Teste 333', 'aaa@gmail.com', '$2y$10$LiFdljExmm1Twd9sneyPtOaaIGgDqaK7hqPKtJAONiLbl6wXbc/wq', 'uploads/foto_12_1752623476.webp', '', '', 'nome', 'usuario'),
(13, 'Suetam Azevedo', 'suetamzinho11.exe@gmail.com', '', 'uploads/foto_13_1752351799.png', NULL, NULL, 'nome', 'usuario'),
(14, 'Teste', 'teste@gmail.com', '$2y$10$H8qE25dPYrm2/oLZhH1F1./zdgULMxZfwi3j36RYDdssDze1j.0GG', NULL, NULL, NULL, 'nome', 'usuario'),
(15, 'Mateus Azevedo', 'teste12@gmail.com', '$2y$10$WE5CWSBvDyojDazAC.KbheZQ00D8xUoLINdNpjqnEyfkAp7iUx/bu', NULL, NULL, NULL, 'nome', 'usuario'),
(16, 'mateus azevedo', 'teste3@gmail.com', '$2y$10$ZBzzxqbtvDoPwrUJqaGnP.OF9pS1Jv94cFuudFfHOMfvv3JRhXiCC', NULL, NULL, NULL, 'nome', 'admin'),
(17, 'Isabelle', 'isabelleplati01@gmail.com', '', 'https://lh3.googleusercontent.com/a/ACg8ocJHrLXw10R7DBv1jJ-XrKy_JwtJh-h_448I2Mxota_vZ-4N-A=s96-c', '', '', 'nome', 'usuario'),
(18, 'Tese Usuarios', 'Teste12345@gmail.com', '$2y$10$OVfaXDYKDvJmxl8SvVI2Hu5xpNczcjjdRykomyby03IkWWk9Oq7jC', NULL, NULL, NULL, 'nome', 'usuario'),
(19, 'Teste teste tesrw', 'testefoto@gmail.com', '$2y$10$jSq.JSN0MOsy8cyCdQZiw.mlUDVSLwVEKJJRCpVNgPTd2gSYap6ei', 'uploads/foto_19_1758067196.png', '', '', 'nome', 'usuario'),
(20, 'nome', 'nome@gmail.com', '$2y$10$LWIDO6YH05IfMWT/E.OAMuxzpqmdkRSRNeMMHD/WZBXNxQcgROL..', NULL, NULL, NULL, 'nome', 'usuario'),
(21, 'abc', 'abc2@gmail.com', '$2y$10$8syJIh8/fPX1xN94FyjazuywSPJUzpSZPZuVdo0GgP2P9h2tklNnu', NULL, NULL, NULL, 'nome', 'usuario'),
(22, 'admin', 'admin@gmail.com', 'admin123', NULL, NULL, NULL, 'nome', 'admin'),
(23, 'admin', 'admin@gmail.com', 'admin123', NULL, NULL, NULL, 'nome', 'admin'),
(24, 'admin', 'admin1@gmail.com', '$2y$10$UL/YVDXn7bLJa336XEBTIenbfVDbsY7cch5pxmytT0pDsPnqbfXHm', '../uploads/perfilfoto_24_1758650065.jpg', '', '', 'nome', 'admin'),
(25, 'taskup', 'taskup.gestao01@gmail.com', '', 'https://lh3.googleusercontent.com/a/ACg8ocIFeYITXJ4nRSRAbnHYHNGaeQNNm6VPK1_9vL8M7c-SkYme9g=s96-c', NULL, NULL, 'nome', '');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `passeios`
--
ALTER TABLE `passeios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `poi_images_cache`
--
ALTER TABLE `poi_images_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_poi_key` (`poi_key`);

--
-- Índices de tabela `rota`
--
ALTER TABLE `rota`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `passeios`
--
ALTER TABLE `passeios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `poi_images_cache`
--
ALTER TABLE `poi_images_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `rota`
--
ALTER TABLE `rota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `passeios`
--
ALTER TABLE `passeios`
  ADD CONSTRAINT `passeios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`);

--
-- Restrições para tabelas `rota`
--
ALTER TABLE `rota`
  ADD CONSTRAINT `rota_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
