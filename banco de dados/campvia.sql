-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 06/10/2025 às 22:37
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
-- Estrutura para tabela `cidade`
--

CREATE TABLE `cidade` (
  `id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `estado` char(2) NOT NULL DEFAULT 'SP',
  `slug` varchar(140) NOT NULL,
  `descricao` text DEFAULT NULL,
  `capa` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cidade`
--

INSERT INTO `cidade` (`id`, `nome`, `estado`, `slug`, `descricao`, `capa`, `criado_em`) VALUES
(1, 'Iperó', 'SP', 'ipero', '', '../uploads/cidades/capa_cidade_1_1759775042.webp', '2025-10-01 23:46:11'),
(2, 'Capela do Alto', 'SP', 'capela-do-alto', '', '../uploads/cidades/capa_cidade_2_1759774714.jpg', '2025-10-01 23:46:11'),
(3, 'Boituva', 'SP', 'boituva', 'Boituva é uma cidade muito bonita', '../uploads/cidades/capa_cidade_3_1759675411.jpg', '2025-10-01 23:46:11'),
(4, 'Cerquilho', 'SP', 'cerquilho', '', '../uploads/cidades/capa_cidade_4_1759774803.jpg', '2025-10-01 23:46:11'),
(5, 'Araçoiaba da Serra', 'SP', 'aracoiaba-da-serra', '', '../uploads/cidades/capa_cidade_5_1759774969.jpg', '2025-10-01 23:46:11'),
(6, 'Tietê', 'SP', 'tiete', '', '../uploads/cidades/capa_cidade_6_1759774403.jpg', '2025-10-01 23:46:11'),
(7, 'Itapetininga', 'SP', 'itapetininga', '', '../uploads/cidades/capa_cidade_7_1759775009.jpg', '2025-10-01 23:46:11'),
(8, 'Laranjal Paulista', 'SP', 'laranjal-paulista', '', '../uploads/cidades/capa_cidade_8_1759774569.jpg', '2025-10-01 23:46:11'),
(9, 'Porto Feliz', 'SP', 'porto-feliz', '', '../uploads/cidades/capa_cidade_9_1759774765.jpg', '2025-10-01 23:46:11'),
(10, 'Conchas', 'SP', 'conchas', '', '../uploads/cidades/capa_cidade_10_1759774881.jpg', '2025-10-01 23:46:11'),
(11, 'Salto de Pirapora', 'SP', 'salto-de-pirapora', '', '../uploads/cidades/capa_cidade_11_1759774480.jpg', '2025-10-01 23:46:11'),
(12, 'Sorocaba', 'SP', 'sorocaba', '', '../uploads/cidades/capa_cidade_12_1759774630.jpg', '2025-10-01 23:46:11'),
(13, 'Votorantim', 'SP', 'votorantim', '', '../uploads/cidades/capa_cidade_13_1759774431.jpg', '2025-10-01 23:46:11'),
(14, 'Pilar do Sul', 'SP', 'pilar-do-sul', '', '../uploads/cidades/capa_cidade_14_1759774329.jpg', '2025-10-01 23:46:11'),
(15, 'Capivari', 'SP', 'capivari', '', '../uploads/cidades/capa_cidade_15_1759774680.jpg', '2025-10-01 23:46:11'),
(16, 'Porangaba', 'SP', 'porangaba', '', '../uploads/cidades/capa_cidade_16_1759774932.jpg', '2025-10-01 23:46:11'),
(17, 'Tatuí', 'SP', 'tatui', '', '../uploads/cidades/capa_cidade_17_1759774529.jpg', '2025-10-01 23:46:11');

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
  `cidade_id` int(11) DEFAULT NULL,
  `paradas` text DEFAULT NULL,
  `capa` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `rota`
--

INSERT INTO `rota` (`id`, `usuario_id`, `nome`, `descricao`, `categorias`, `ponto_partida`, `destino`, `cidade_id`, `paradas`, `capa`, `criado_em`) VALUES
(32, 9, 'Rota da Música e da Gastronomia: Cesário Lange a Tatuí (2 dias)', 'Descubra o interior de São Paulo em um roteiro focado na gastronomia regional e na rica tradição musical de Tatuí. Começando por Cesário Lange, a jornada culmina em Tatuí, a Capital da Música, com experiências culinárias autênticas e imersão na cultura local.', 'citytour', 'Cesário Lange, SP', 'Tatuí, SP', NULL, '[{\"nome\":\"Cesário Lange - Santuário Nossa Senhora da Piedade\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Fazenda Histórica em Cesário Lange (opcional)\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Tatuí - Conservatório Dramático e Musical Dr. Carlos de Campos\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Museu Histórico Paulo Setúbal (Tatuí)\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Praça da Matriz (Tatuí)\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Mercado Municipal (Tatuí) - Degustação de produtos locais\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Restaurantes e Cafés tradicionais de Tatuí - Almoço e lanche com pratos típicos\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Jantar com música ao vivo em Tatuí - Experiência gastronômica e cultural\",\"lat\":null,\"lon\":null,\"image_url\":null}]', '../uploads/geral/capa_9_1759502734.png', '2025-10-03 14:45:34'),
(33, 17, 'Semana Gastronômica em Boituva', 'Um roteiro de 7 dias explorando as delícias gastronômicas de Boituva e arredores, com foco em experiências autênticas e sabores regionais.', '', 'Boituva, São Paulo, Brasil (ponto de encontro a definir)', 'Boituva, São Paulo, Brasil', NULL, '[{\"nome\":\"Boituva - Sabores da Roça e Doces Caseiros\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Iperó - Almoço em Restaurante com Vista Panorâmica\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Tietê - Degustação de Cachaças Artesanais\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Cerquilho - Jantar com Pratos Típicos Italianos\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Laranjal Paulista - Visita a Produtores Locais e Compras de Produtos Frescos\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Pereiras - Festival Gastronômico (se houver evento)\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Boituva - Churrasco de Despedida com Culinária Local\",\"lat\":null,\"lon\":null,\"image_url\":null}]', '../uploads/geral/capa_17_1759511200.jpg', '2025-10-03 17:06:40'),
(35, 9, 'Roteiro de 7 dias em Sorocaba e arredores', 'Explore a riqueza cultural, histórica e natural de Sorocaba e cidades vizinhas em uma semana. Este roteiro inclui passeios pela cidade, ecoturismo e experiências gastronômicas.', 'história, natureza, cultura, gastronomia', 'A definir (depende do ponto de partida do viajante)', 'Sorocaba, SP', NULL, '[{\"nome\":\"Centro Histórico de Sorocaba\",\"lat\":0,\"lon\":0,\"image_url\":\"\"},{\"nome\":\"Mercado Municipal de Sorocaba\",\"lat\":0,\"lon\":0,\"image_url\":\"\"},{\"nome\":\"Parque Zoológico Municipal Quinzinho de Barros\",\"lat\":0,\"lon\":0,\"image_url\":\"\"},{\"nome\":\"Jardim Botânico Irmãos Villas-Bôas\",\"lat\":0,\"lon\":0,\"image_url\":\"\"},{\"nome\":\"Estrada do Vinho (São Roque)\",\"lat\":0,\"lon\":0,\"image_url\":\"\"},{\"nome\":\"Ski Mountain Park (São Roque)\",\"lat\":0,\"lon\":0,\"image_url\":\"\"},{\"nome\":\"Itapeva Ecoresort (Itapeva)\",\"lat\":0,\"lon\":0,\"image_url\":\"\"},{\"nome\":\"Oficina Cultural Grande Otelo\",\"lat\":0,\"lon\":0,\"image_url\":\"\"},{\"nome\":\"Shopping Iguatemi Esplanada\",\"lat\":0,\"lon\":0,\"image_url\":\"\"}]', NULL, '2025-10-04 16:45:43'),
(36, 9, 'Roteiro de 2 Semanas para Viver Bem em Sorocaba', 'Um roteiro relaxante de duas semanas em Sorocaba, focado em aproveitar a tranquilidade da cidade, sua gastronomia, cultura e natureza, com tempo para se ambientar e desfrutar de cada experiência.', 'natureza, gastronomia, cultura, relaxamento', 'A definir, dependendo da sua origem', 'Sorocaba, SP', NULL, '[{\"nome\":\"Chegada em Sorocaba e Acomodação\",\"image_url\":\"\"},{\"nome\":\"Parque das Águas\",\"image_url\":\"\"},{\"nome\":\"Mosteiro de São Bento\",\"image_url\":\"\"},{\"nome\":\"Mercado Municipal\",\"image_url\":\"\"},{\"nome\":\"Jardim Botânico Irmãos Villas-Bôas\",\"image_url\":\"\"},{\"nome\":\"Museu Histórico Sorocabano\",\"image_url\":\"\"},{\"nome\":\"Tour Gastronômico\",\"image_url\":\"\"},{\"nome\":\"Dia Livre para Relaxamento\",\"image_url\":\"\"},{\"nome\":\"Fazenda Histórica (opcional)\",\"image_url\":\"\"},{\"nome\":\"Despedida e Partida\",\"image_url\":\"\"}]', NULL, '2025-10-04 16:48:52'),
(37, 11, 'Sabores de Porangaba: Um Bate e Volta Gastronômico', 'Explore Porangaba em um bate e volta delicioso, focado em seus melhores restaurantes e produtores locais. Prepare-se para experimentar pratos típicos, queijos artesanais e doces caseiros, descobrindo os sabores autênticos desta charmosa cidade do interior paulista.', 'gastronomica', 'A definir pelo viajante (sugerido: São Paulo ou cidades vizinhas)', 'Porangaba, SP', NULL, '[{\"nome\":\"Restaurante da Ponte\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Queijaria Artesanal do Seu Zé\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Doceria da Vovó\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Empório Raízes da Terra\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Sorveteria Sabor do Campo\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Feira do Produtor Rural (aos sábados)\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Cachaçaria Alambique da Serra\",\"lat\":null,\"lon\":null,\"image_url\":null},{\"nome\":\"Cafeteria Aroma e Sabor\",\"lat\":null,\"lon\":null,\"image_url\":null}]', '../uploads/geral/capa_11_1759718057.jpg', '2025-10-06 02:33:52');

-- --------------------------------------------------------

--
-- Estrutura para tabela `rota_inscricao`
--

CREATE TABLE `rota_inscricao` (
  `id` int(11) NOT NULL,
  `rota_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(11, 'PEdrinho matador', 'mateus.azevedop5@gmail.com', '', '../uploads/perfilfoto_11_1759775411.webp', '', '', 'nome', 'admin'),
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
-- Índices de tabela `cidade`
--
ALTER TABLE `cidade`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_slug` (`slug`),
  ADD KEY `idx_nome_estado` (`nome`,`estado`);

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
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_rota_cidade` (`cidade_id`);

--
-- Índices de tabela `rota_inscricao`
--
ALTER TABLE `rota_inscricao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_rota_usuario` (`rota_id`,`usuario_id`),
  ADD KEY `idx_inscricao_usuario` (`usuario_id`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cidade`
--
ALTER TABLE `cidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `rota_inscricao`
--
ALTER TABLE `rota_inscricao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `fk_rota_cidade` FOREIGN KEY (`cidade_id`) REFERENCES `cidade` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rota_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `rota_inscricao`
--
ALTER TABLE `rota_inscricao`
  ADD CONSTRAINT `fk_inscricao_rota` FOREIGN KEY (`rota_id`) REFERENCES `rota` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inscricao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
