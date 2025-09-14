-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 14/09/2025
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
-- Estrutura para tabela `usuario`
--

DROP TABLE IF EXISTS `usuario`;
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
-- Dados para a tabela `usuario`
--

INSERT INTO `usuario` 
(`id`, `nome`, `email`, `senha`, `foto_perfil`, `endereco`, `apelido`, `preferencia_nome_apelido`, `nivel`) VALUES
(9, 'Mateus Azevedo', 'mateuschapad33@gmail.com', '', 'uploads/foto_9_1753299656.png', 'Rua Ismênia Maria dos Santos', 'Suetam', 'nome', 'admin'),
(10, 'Mateus Azevedo', 'mateusazevedo11.exe@gmail.com', '', 'https://lh3.googleusercontent.com/a/ACg8ocIx4oujtytswR-b2Ts1p01qzFb3o25T0wJMecsqXzKpBqG4DR4=s96-c', NULL, NULL, 'nome', 'usuario'),
(11, 'PEdrinho matador', 'mateus.azevedop5@gmail.com', '', 'uploads/foto_11_1752540121.png', NULL, NULL, 'nome', 'usuario'),
(12, 'akazinho ', 'aaa@gmail.com', '$2y$10$LiFdljExmm1Twd9sneyPtOaaIGgDqaK7hqPKtJAONiLbl6wXbc/wq', 'uploads/foto_12_1752623476.webp', NULL, NULL, 'nome', 'usuario'),
(13, 'Suetam Azevedo', 'suetamzinho11.exe@gmail.com', '', 'uploads/foto_13_1752351799.png', NULL, NULL, 'nome', 'usuario');

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
