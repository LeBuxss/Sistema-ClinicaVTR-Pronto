-- O framework comeca sem tabelas da aplicacao.
-- Use: php console.php scaffold:crud Nome campo:string

CREATE TABLE IF NOT EXISTS `clinicavtr` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `campo` VARCHAR(255) NULL,
    `nome` VARCHAR(255) NULL,
    `cpf` VARCHAR(14) NULL,
    `email` VARCHAR(255) NULL,
    `senha` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cadastro` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `especie` VARCHAR(255) NULL,
    `tutor` VARCHAR(255) NULL,
    `veterinario` VARCHAR(255) NULL,
    `procedimento` VARCHAR(255) NULL,
    `finalizada` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `consulta` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NULL,
    `especie` VARCHAR(255) NULL,
    `raca` VARCHAR(120) NULL,
    `datanascimente` DATE NULL,
    `data_consulta` DATE NULL,
    `sexo` VARCHAR(255) NULL,
    `peso` DECIMAL(12,2) NULL,
    `tutor` VARCHAR(255) NULL,
    `telefone_tutor` VARCHAR(30) NULL,
    `queixa_principal` VARCHAR(500) NULL,
    `observacoes_clinicas` MEDIUMTEXT NULL,
    `horario_disponivel` TIME NULL,
    `veterinario_disponivel` VARCHAR(255) NULL,
    `finalizada` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `fichavacinal` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NULL,
    `vacinaaplica` VARCHAR(255) NULL,
    `lote` VARCHAR(255) NULL,
    `dataaplicacao` DATE NULL,
    `dataretorno` DATE NULL,
    `veterinarioresponsalve` VARCHAR(255) NULL,
    `finalizada` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
