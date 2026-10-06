-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 18/11/2025 às 02:30
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
-- Banco de dados: `nexus network`
--

DELIMITER $$
--
-- Funções
--
CREATE DEFINER=`root`@`localhost` FUNCTION `calcular_distancia` (`lat1` DECIMAL(10,8), `lon1` DECIMAL(11,8), `lat2` DECIMAL(10,8), `lon2` DECIMAL(11,8)) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE R DECIMAL(10,2) DEFAULT 6371; -- Raio da Terra em km
    DECLARE dLat DECIMAL(10,8);
    DECLARE dLon DECIMAL(11,8);
    DECLARE a DECIMAL(20,10);
    DECLARE c DECIMAL(20,10);
    DECLARE distancia DECIMAL(10,2);
    
    SET dLat = RADIANS(lat2 - lat1);
    SET dLon = RADIANS(lon2 - lon1);
    
    SET a = SIN(dLat/2) * SIN(dLat/2) + 
            COS(RADIANS(lat1)) * COS(RADIANS(lat2)) * 
            SIN(dLon/2) * SIN(dLon/2);
    
    SET c = 2 * ATAN2(SQRT(a), SQRT(1-a));
    SET distancia = R * c;
    
    RETURN distancia;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacao`
--

CREATE TABLE `avaliacao` (
  `avl_codigo` int(11) NOT NULL,
  `cli_codigo` int(11) DEFAULT NULL,
  `pres_codigo` int(11) DEFAULT NULL,
  `avl_data` timestamp NOT NULL DEFAULT current_timestamp(),
  `avl_nota` int(1) NOT NULL CHECK (`avl_nota` between 1 and 5),
  `avl_comentario` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `avaliacao`
--

INSERT INTO `avaliacao` (`avl_codigo`, `cli_codigo`, `pres_codigo`, `avl_data`, `avl_nota`, `avl_comentario`) VALUES
(2, 4, 3, '2025-11-08 02:54:04', 5, 'Ótimo Profissional!'),
(4, 4, 6, '2025-11-15 15:20:32', 5, 'Ótimo Profissional, indico para todos!'),
(5, 12, 7, '2025-11-16 17:30:00', 5, 'Excelente professora! A Mariana explica matemática com muita paciência e meu filho melhorou muito as notas. Super recomendo!'),
(6, 13, 8, '2025-11-16 19:45:00', 5, 'Carlos é muito competente! Fez minhas unhas com capricho e um acabamento impecável. Pontual e atencioso!'),
(7, 11, 9, '2025-11-17 13:20:00', 4, 'A Beatriz animou a festa da minha empresa do começo ao fim! Repertório excelente e muito profissional. Recomendo!');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chat`
--

CREATE TABLE `chat` (
  `chat_codigo` int(11) NOT NULL,
  `cli_codigo` int(11) DEFAULT NULL,
  `pres_codigo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chat`
--

INSERT INTO `chat` (`chat_codigo`, `cli_codigo`, `pres_codigo`) VALUES
(3, 4, 3),
(4, 12, 7),
(5, 13, 8),
(7, 12, 8);

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `cli_codigo` int(11) NOT NULL,
  `cli_nome` varchar(90) NOT NULL,
  `cli_datanasc` date NOT NULL,
  `cli_genero` varchar(20) NOT NULL,
  `cli_email` varchar(50) NOT NULL,
  `cli_telefone` varchar(15) NOT NULL,
  `cli_estado` varchar(2) NOT NULL,
  `cli_cidade` varchar(25) NOT NULL,
  `cli_latitude` decimal(10,8) DEFAULT NULL,
  `cli_longitude` decimal(11,8) DEFAULT NULL,
  `cli_senha` varchar(255) DEFAULT NULL,
  `cli_cpf` varchar(11) NOT NULL,
  `cli_google_id` varchar(255) DEFAULT NULL,
  `cli_data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `cli_foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`cli_codigo`, `cli_nome`, `cli_datanasc`, `cli_genero`, `cli_email`, `cli_telefone`, `cli_estado`, `cli_cidade`, `cli_latitude`, `cli_longitude`, `cli_senha`, `cli_cpf`, `cli_google_id`, `cli_data_cadastro`, `cli_foto`) VALUES
(4, 'Gustavo Castro', '2007-10-10', 'Masculino', 'gustabiro1@gmail.com', '3333', 'RN', 'Natal', -5.80539800, -35.20809050, '$2y$10$6u3U2Y6bNrIVrKiEjn7HFeK/3Zd/2nEZwts8o.k22v1k4VU9Xnshe', '', NULL, '2025-08-18 20:48:44', 'uploads/fotos/69192fdbbd8c2_1763258331.png'),
(10,'Fernando Santos Caetano', '2001-06-05', 'Masculino', 'fernando.caetano@gmail.com', '(44) 1234-5678', 'AP', 'Amapá', NULL, NULL, '$2y$10$Elo/IJN/oNi48f7/Ds1yK.tMRaNPBkwMMH7r7pjhsaw.B0Lty7nw.', '59101267094', NULL, '2025-10-15 18:54:28', 'uploads/fotos/691bc8eef104e_1763428590.jpg'),
(11, 'André Melo', '1998-03-12', 'Masculino', 'Andre123$@gmail.com', '18991652334', 'RJ', 'Campos dos Goytacazes', -21.75460000, -41.32420000, '$2y$10$Elo/IJN/oNi48f7/Ds1yK.tMRaNPBkwMMH7r7pjhsaw.B0Lty7nw.', '85811035020', NULL, '2025-10-27 21:39:10', 'uploads/fotos/69028049459a8_1761771593.jpg'),
(12, 'Rafael Mendes Almeida', '1996-05-18', 'Masculino', 'rafael.mendes@hotmail.com', '(11) 96543-2109', 'SP', 'São Paulo', -23.55052000, -46.63330800, '$2y$10$Elo/IJN/oNi48f7/Ds1yK.tMRaNPBkwMMH7r7pjhsaw.B0Lty7nw.', '65432198704', NULL, '2025-11-18 01:08:18', 'uploads/fotos/691bcbaad9531_1763429290.jpg'),
(13, 'Juliana Ferreira Costa', '1994-09-30', 'Feminino', 'ju.ferreira.costa@gmail.com', '(21) 95432-1098', 'RJ', 'Rio de Janeiro', -22.90684700, -43.17289600, '$2y$10$Elo/IJN/oNi48f7/Ds1yK.tMRaNPBkwMMH7r7pjhsaw.B0Lty7nw.', '98765432105', NULL, '2025-11-18 01:08:18', 'uploads/fotos/691bcb1ec3a4d_1763429150.jpg');

-- --------------------------------------------------------

--
-- Estrutura para tabela `contatos`
--

CREATE TABLE `contatos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `assunto` varchar(50) NOT NULL,
  `mensagem` text NOT NULL,
  `data_envio` datetime NOT NULL,
  `status` enum('novo','lido','respondido') DEFAULT 'novo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `contatos`
--

INSERT INTO `contatos` (`id`, `nome`, `email`, `telefone`, `assunto`, `mensagem`, `data_envio`, `status`) VALUES
(1, 'Joao Pedro', 'joao.santos@gmail.com', '(18) 99916-51232', 'problema', 'Ocorreu um problema no meu cadastro.', '2025-11-17 15:01:41', 'novo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `geocode_cache`
--

CREATE TABLE `geocode_cache` (
  `cache_id` int(11) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `data_cache` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `geocode_cache`
--

INSERT INTO `geocode_cache` (`cache_id`, `cidade`, `estado`, `latitude`, `longitude`, `data_cache`) VALUES
(1, 'Bela Vista', 'MS', -22.10732700, -56.53173440, '2025-10-17 00:03:05'),
(2, 'Natal', 'RN', -5.80539800, -35.20809050, '2025-11-08 02:42:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens`
--

CREATE TABLE `mensagens` (
  `msg_id` int(11) NOT NULL,
  `chat_codigo` int(11) NOT NULL,
  `remetente_id` int(11) NOT NULL,
  `remetente_tipo` enum('cliente','prestador') NOT NULL,
  `conteudo` text DEFAULT NULL,
  `tipo_mensagem` enum('texto','arquivo') DEFAULT 'texto',
  `arquivo_nome` varchar(255) DEFAULT NULL,
  `arquivo_url` varchar(500) DEFAULT NULL,
  `arquivo_tamanho` int(11) DEFAULT NULL,
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `lida` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `mensagens`
--

INSERT INTO `mensagens` (`msg_id`, `chat_codigo`, `remetente_id`, `remetente_tipo`, `conteudo`, `tipo_mensagem`, `arquivo_nome`, `arquivo_url`, `arquivo_tamanho`, `data_envio`, `lida`) VALUES
(4, 3, 4, 'cliente', 'Olá João, Gostaria de fazer um site com você!', 'texto', NULL, NULL, NULL, '2025-11-15 15:42:04', 1),
(5, 3, 3, 'prestador', 'Olá Gustavo, sobre o que seria o site?', 'texto', NULL, NULL, NULL, '2025-11-15 15:43:33', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `prestadores`
--

CREATE TABLE `prestadores` (
  `pres_codigo` int(11) NOT NULL,
  `pres_nome` varchar(90) NOT NULL,
  `pres_datanasc` date NOT NULL,
  `pres_genero` varchar(20) NOT NULL,
  `pres_profissao` varchar(60) NOT NULL,
  `pres_descricao` varchar(255) NOT NULL,
  `pres_email` varchar(50) NOT NULL,
  `pres_telefone` varchar(15) NOT NULL,
  `pres_estado` varchar(2) NOT NULL,
  `pres_cidade` varchar(25) NOT NULL,
  `pres_latitude` decimal(10,8) DEFAULT NULL,
  `pres_longitude` decimal(11,8) DEFAULT NULL,
  `pres_senha` varchar(255) DEFAULT NULL,
  `prestador_cpf` varchar(11) NOT NULL,
  `pres_foto` varchar(255) DEFAULT NULL,
  `pres_precomin` decimal(10,2) NOT NULL,
  `pres_precomax` decimal(10,2) NOT NULL,
  `pres_google_id` varchar(255) DEFAULT NULL,
  `pres_data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `prestadores`
--

INSERT INTO `prestadores` (`pres_codigo`, `pres_nome`, `pres_datanasc`, `pres_genero`, `pres_profissao`, `pres_descricao`, `pres_email`, `pres_telefone`, `pres_estado`, `pres_cidade`, `pres_latitude`, `pres_longitude`, `pres_senha`, `prestador_cpf`, `pres_foto`, `pres_precomin`, `pres_precomax`, `pres_google_id`, `pres_data_cadastro`) VALUES
(3,'João Souza', '2006-10-10', 'Masculino', 'Programador', 'Programador apaixonado por tecnologia, cria soluções eficientes com lógica e criatividade. Sempre busca aprender novas linguagens e aprimorar habilidades para desenvolver códigos limpos e funcionais.', 'gustabiro1@gmail.com', '(18) 99999-9999', 'SP', 'São Paulo', -23.55052000, -46.63330800, '$2y$10$6D.u1jyzvyKTtQuePWlZ1e753eGdyaY7kPAJHqiabgkY89uCeL4we', '11144477735', 'uploads/fotos/691930000bdbf_1763258368.png', 40.00, 1000.00, NULL, '2025-08-18 20:48:44'),
(6,'Pires Sá', '1998-02-23', 'Masculino', 'Programador', 'Atuo na área a 5 anos, diploma em curso técnico em desenvolvimento de sistemas', 'joao.pires@gmail.com', '(43)18997655', 'MG', 'Aguanil', -20.94245330, -45.39271520, '$2y$10$Elo/IJN/oNi48f7/Ds1yK.tMRaNPBkwMMH7r7pjhsaw.B0Lty7nw.', '66274373098', 'uploads/fotos/68ffebfeacfb5_1761602558.png', 100.00, 2500.00, NULL, '2025-10-27 22:02:38'),
(7, 'Mariana Costa', '1995-03-15', 'Feminino', 'Professora', 'Professora de Matemática e Física com 8 anos de experiência. Especializada em aulas particulares para ensino fundamental, médio e preparação para vestibulares. Metodologia dinâmica e personalizada.', 'mariana.costa.prof@gmail.com', '(11) 98765-4321', 'SP', 'São Paulo', -23.55052000, -46.63330800, '$2y$10$Elo/IJN/oNi48f7/Ds1yK.tMRaNPBkwMMH7r7pjhsaw.B0Lty7nw.', '45678912301', 'uploads/fotos/691bc9ef55f46_1763428847.jpg', 60.00, 150.00, NULL, '2025-11-18 01:08:18'),
(8, 'Carlos Henrique Oliveira', '1988-07-22', 'Masculino', 'Manicure','Manicure profissional com 15 anos de experiência. Especializado em unhas decoradas, alongamento, blindagem e tratamentos. Atendimento personalizado com produtos de qualidade.', 'carlos.manicure@outlook.com', '(21) 97654-3210', 'RJ', 'Rio de Janeiro', -22.90684700, -43.17289600, '$2y$10$Elo/IJN/oNi48f7/Ds1yK.tMRaNPBkwMMH7r7pjhsaw.B0Lty7nw.', '78945612302', 'uploads/fotos/691bca5c0ba33_1763428956.jpg', 30.00, 120.00, NULL, '2025-11-18 01:08:18'),
(9, 'Beatriz Santos Silva', '1992-11-08', 'Feminino', 'DJ','DJ profissional especializada em festas, casamentos e eventos corporativos. Repertório diversificado e equipamento de alta qualidade. Mais de 200 eventos realizados com sucesso.', 'bia.dj@gmail.com', '(31) 99876-5432', 'MG', 'Belo Horizonte', -19.91686800, -43.93450800, '$2y$10$Elo/IJN/oNi48f7/Ds1yK.tMRaNPBkwMMH7r7pjhsaw.B0Lty7nw.', '32165498703', 'uploads/fotos/691bca8e2cf5e_1763429006.webp', 500.00, 3000.00, NULL, '2025-11-18 01:08:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `reset_codigos`
--

CREATE TABLE `reset_codigos` (
  `pres_codigo` int(11) NOT NULL,
  `pres_email` varchar(255) DEFAULT NULL,
  `codigo` varchar(6) DEFAULT NULL,
  `expiracao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `reset_codigos_clientes`
--

CREATE TABLE `reset_codigos_clientes` (
  `cli_codigo` int(11) NOT NULL,
  `cli_email` varchar(255) DEFAULT NULL,
  `codigo` varchar(6) DEFAULT NULL,
  `expiracao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD PRIMARY KEY (`avl_codigo`),
  ADD KEY `cli_codigo` (`cli_codigo`),
  ADD KEY `pres_codigo` (`pres_codigo`),
  ADD KEY `idx_avaliacao_prestador` (`pres_codigo`),
  ADD KEY `idx_avaliacao_cliente` (`cli_codigo`),
  ADD KEY `idx_avaliacao_data` (`avl_data`);

--
-- Índices de tabela `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`chat_codigo`),
  ADD KEY `cli_codigo` (`cli_codigo`),
  ADD KEY `pres_codigo` (`pres_codigo`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`cli_codigo`),
  ADD UNIQUE KEY `cli_google_id` (`cli_google_id`),
  ADD KEY `idx_localizacao_cliente` (`cli_latitude`,`cli_longitude`);

--
-- Índices de tabela `contatos`
--
ALTER TABLE `contatos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data` (`data_envio`);

--
-- Índices de tabela `geocode_cache`
--
ALTER TABLE `geocode_cache`
  ADD PRIMARY KEY (`cache_id`),
  ADD UNIQUE KEY `unique_location` (`cidade`,`estado`);

--
-- Índices de tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `idx_chat_codigo` (`chat_codigo`),
  ADD KEY `idx_data_envio` (`data_envio`),
  ADD KEY `idx_lida` (`lida`);

--
-- Índices de tabela `prestadores`
--
ALTER TABLE `prestadores`
  ADD PRIMARY KEY (`pres_codigo`),
  ADD UNIQUE KEY `pres_google_id` (`pres_google_id`),
  ADD KEY `idx_localizacao` (`pres_latitude`,`pres_longitude`);
ALTER TABLE `prestadores` ADD FULLTEXT KEY `idx_busca` (`pres_nome`,`pres_profissao`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  MODIFY `avl_codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `chat`
--
ALTER TABLE `chat`
  MODIFY `chat_codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `cli_codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `contatos`
--
ALTER TABLE `contatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `geocode_cache`
--
ALTER TABLE `geocode_cache`
  MODIFY `cache_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `prestadores`
--
ALTER TABLE `prestadores`
  MODIFY `pres_codigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD CONSTRAINT `avaliacao_ibfk_1` FOREIGN KEY (`cli_codigo`) REFERENCES `clientes` (`cli_codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `avaliacao_ibfk_2` FOREIGN KEY (`pres_codigo`) REFERENCES `prestadores` (`pres_codigo`) ON DELETE CASCADE;

--
-- Restrições para tabelas `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`cli_codigo`) REFERENCES `clientes` (`cli_codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`pres_codigo`) REFERENCES `prestadores` (`pres_codigo`) ON DELETE CASCADE;

--
-- Restrições para tabelas `mensagens`
--
ALTER TABLE `mensagens`
  ADD CONSTRAINT `mensagens_ibfk_1` FOREIGN KEY (`chat_codigo`) REFERENCES `chat` (`chat_codigo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
