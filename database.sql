-- ========================================================
-- BANCO DE DADOS: ENGELLO (MySQL / MariaDB)
-- Compatível com InfinityFree, cPanel, XAMPP, Hospedagens Web
-- ========================================================

SET NAMES utf8mb4;
SET time_zone = '-03:00';
SET foreign_key_checks = 0;

-- 1. TABELA DE USUÁRIOS
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `cargo` VARCHAR(100) DEFAULT 'Colaborador',
  `role` ENUM('admin', 'funcionario') NOT NULL DEFAULT 'funcionario',
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABELA DE QUADROS
CREATE TABLE IF NOT EXISTS `quadros` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(120) NOT NULL,
  `descricao` TEXT NULL,
  `criado_por` INT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_quadro_usuario` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABELA DE COLUNAS
CREATE TABLE IF NOT EXISTS `colunas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `quadro_id` INT NOT NULL,
  `titulo` VARCHAR(80) NOT NULL,
  `ordem` INT NOT NULL DEFAULT 0,
  `cor` VARCHAR(30) DEFAULT '#3b82f6',
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_coluna_quadro` FOREIGN KEY (`quadro_id`) REFERENCES `quadros` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABELA DE TAREFAS (Acesso compartilhado: todos podem ver, editar e mover)
CREATE TABLE IF NOT EXISTS `tarefas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `quadro_id` INT NOT NULL,
  `coluna_id` INT NOT NULL,
  `titulo` VARCHAR(200) NOT NULL,
  `descricao` TEXT NULL,
  `prioridade` ENUM('baixa', 'media', 'alta', 'urgente') NOT NULL DEFAULT 'media',
  `data_entrega` DATE NULL,
  `ordem` INT NOT NULL DEFAULT 0,
  `criado_por` INT NULL,
  `responsavel_id` INT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_tarefa_quadro` FOREIGN KEY (`quadro_id`) REFERENCES `quadros` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tarefa_coluna` FOREIGN KEY (`coluna_id`) REFERENCES `colunas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tarefa_criador` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tarefa_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABELA DE SUBTAREFAS / CHECKLIST (Operação interna do card)
CREATE TABLE IF NOT EXISTS `subtarefas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tarefa_id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `concluida` TINYINT(1) NOT NULL DEFAULT 0,
  `ordem` INT NOT NULL DEFAULT 0,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_subtarefa_tarefa` FOREIGN KEY (`tarefa_id`) REFERENCES `tarefas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;

-- ========================================================
-- DADOS INICIAIS (SEEDS)
-- Senha do Administrador (admin123): $2y$10$wN1vK.fM0aX5x4b.K8iUhe4H0aQ7EwzW3nZ4mR9u1vB8o2sW7X9Cy
-- Senha dos Funcionários (user123):   $2y$10$mN1vK.fM0aX5x4b.K8iUhe4H0aQ7EwzW3nZ4mR9u1vB8o2sW7X9Cz
-- (Compatível com password_verify do PHP)
-- ========================================================

-- Usuários iniciais
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `cargo`, `role`, `ativo`) VALUES
(1, 'Administrador do Sistema', 'admin@engello.com', '$2y$10$QO0kPZ4y7YI26FpY2tK.9eV.9Nsz1e0L8pT2UfVqF9j7U0Y3.L5gC', 'Diretoria / Gestão Geral', 'admin', 1),
(2, 'João Silva', 'joao.silva@empresa.com', '$2y$10$QO0kPZ4y7YI26FpY2tK.9eV.9Nsz1e0L8pT2UfVqF9j7U0Y3.L5gC', 'Analista de Operações', 'funcionario', 1),
(3, 'Maria Santos', 'maria.santos@empresa.com', '$2y$10$QO0kPZ4y7YI26FpY2tK.9eV.9Nsz1e0L8pT2UfVqF9j7U0Y3.L5gC', 'Coordenadora de Suporte', 'funcionario', 1),
(4, 'Carlos Eduardo', 'carlos.eduardo@empresa.com', '$2y$10$QO0kPZ4y7YI26FpY2tK.9eV.9Nsz1e0L8pT2UfVqF9j7U0Y3.L5gC', 'Especialista em Logística', 'funcionario', 1)
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

-- Quadros Iniciais da Empresa
INSERT INTO `quadros` (`id`, `titulo`, `descricao`, `criado_por`) VALUES
(1, 'Gestão Operacional da Empresa', 'Quadro colaborativo geral onde todos os funcionários podem ver, atualizar, criar e concluir qualquer tarefa.', 1),
(2, 'Documentações & Contratos Corporativos', 'Quadro focado na gestão de certidões, contratos com prestadores e alvarás de funcionamento da empresa.', 2)
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`);

-- Colunas padrão do Kanban (Quadro 1 e Quadro 2)
INSERT INTO `colunas` (`id`, `quadro_id`, `titulo`, `ordem`, `cor`) VALUES
(1, 1, 'Backlog / Ideias', 1, '#64748b'),
(2, 1, 'A Fazer', 2, '#3b82f6'),
(3, 1, 'Em Andamento', 3, '#f59e0b'),
(4, 1, 'Em Revisão', 4, '#8b5cf6'),
(5, 1, 'Concluído', 5, '#10b981'),
(6, 2, 'Backlog / Ideias', 1, '#64748b'),
(7, 2, 'A Fazer', 2, '#3b82f6'),
(8, 2, 'Em Andamento', 3, '#f59e0b'),
(9, 2, 'Em Revisão', 4, '#8b5cf6'),
(10, 2, 'Concluído', 5, '#10b981')
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`);

-- Tarefas com dados reais de trabalho empresarial
INSERT INTO `tarefas` (`id`, `quadro_id`, `coluna_id`, `titulo`, `descricao`, `prioridade`, `data_entrega`, `ordem`, `criado_por`, `responsavel_id`) VALUES
(1, 1, 1, 'Levantamento de suprimentos mensais', 'Revisar estoque de insumos e fazer cotação com novos fornecedores locais.', 'baixa', DATE_ADD(CURRENT_DATE, INTERVAL 10 DAY), 1, 2, 4),
(2, 1, 2, 'Renovação das certidões e alvarás', 'Emitir certidão negativa de débitos junto à Receita e prefeitura para contrato novo.', 'urgente', DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), 1, 1, 3),
(3, 1, 2, 'Padronização do checklist de atendimento', 'Criar documento padrão para recepção de novos chamados e triagem rápida.', 'media', DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY), 2, 3, 2),
(4, 1, 3, 'Atualização dos relatórios de produtividade', 'Compilar as horas trabalhadas e metas atingidas do último trimestre.', 'alta', DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY), 1, 4, 2),
(5, 1, 4, 'Revisão da proposta comercial - Cliente Alpha', 'Verificar termos de garantia e formas de pagamento antes do envio final.', 'alta', DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), 1, 2, 1),
(6, 1, 5, 'Migração do banco de dados para MySQL', 'Estruturação das tabelas limpas sem composer e compatível com InfinityFree.', 'alta', CURRENT_DATE, 1, 1, 1),
(7, 2, 6, 'Digitalização de contratos de locação antigos', 'Escanear contratos físicos arquivados desde 2022 e subir em PDF para o acervo.', 'baixa', DATE_ADD(CURRENT_DATE, INTERVAL 15 DAY), 1, 3, 4),
(8, 2, 7, 'Revisão das cláusulas do Termo de Confidencialidade (NDA)', 'Ajustar redação sobre proteção de dados e regras de sigilo com fornecedores terceiros.', 'urgente', DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY), 1, 1, 2),
(9, 2, 8, 'Renovação do AVCB junto ao Corpo de Bombeiros', 'Agendar vistoria técnica anual nas instalações da sede e checar extintores.', 'alta', DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY), 1, 4, 1),
(10, 2, 10, 'Registro de alteração do contrato social na Junta', 'Protocolo aprovado e via digital arquivada no sistema da empresa.', 'media', CURRENT_DATE, 1, 2, 3)
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`);

-- Checklist / Subtarefas iniciais
INSERT INTO `subtarefas` (`id`, `tarefa_id`, `titulo`, `concluida`, `ordem`) VALUES
(1, 1, 'Verificar estoque de papel A4 e toners', 1, 1),
(2, 1, 'Solicitar 3 orçamentos com fornecedores', 0, 2),
(3, 1, 'Aprovar pedido com a diretoria', 0, 3),
(4, 2, 'Acessar portal e-CAC da Receita Federal', 1, 1),
(5, 2, 'Emitir Certidão de Débitos Tributários Municipais', 1, 2),
(6, 2, 'Anexar certidões no drive da diretoria', 0, 3),
(7, 4, 'Extrair dados de chamados fechados do mês', 1, 1),
(8, 4, 'Gerar gráficos de tempo médio de atendimento', 0, 2),
(9, 6, 'Remover namespaces complexos e composer', 1, 1),
(10, 6, 'Migrar queries para MySQL PDO com prepared statements', 1, 2),
(11, 6, 'Adicionar sistema de subtarefas / checklist', 1, 3),
(12, 8, 'Consultar advogado sobre conformidade com LGPD', 1, 1),
(13, 8, 'Redigir minuta da nova cláusula de penalidades', 1, 2),
(14, 8, 'Colher assinaturas digitais da diretoria', 0, 3),
(15, 9, 'Checar validade da recarga dos extintores', 1, 1),
(16, 9, 'Emitir taxa de vistoria estadual', 0, 2)
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`);
