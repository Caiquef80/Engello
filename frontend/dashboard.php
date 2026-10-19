<?php
require_once __DIR__ . '/../app/helpers/AuthMiddleware.php';
require_once __DIR__ . '/../app/services/QuadroService.php';
require_once __DIR__ . '/../app/services/ColunaService.php';
require_once __DIR__ . '/../app/services/TarefaService.php';
require_once __DIR__ . '/../app/services/UsuarioService.php';

// Bloqueia acesso não autorizado à URL
$usuario = AuthMiddleware::exigirAutenticacaoPagina('login.php');

$erro = $_GET['erro'] ?? '';
$sucesso = $_GET['sucesso'] ?? '';

// Carregar serviços
$quadroService = new QuadroService();
$colunaService = new ColunaService();
$tarefaService = new TarefaService();
$usuarioService = new UsuarioService();

// Carregar quadros paginados para a barra lateral (evita travar renderização com muitos quadros)
$paginaQuadros = max(1, (int)($_GET['p_quadros'] ?? 1));
$buscaQuadros = trim((string)($_GET['busca_quadro'] ?? ''));
$dadosQuadros = $quadroService->listarPaginado($paginaQuadros, 6, $buscaQuadros);
$quadros = $dadosQuadros['quadros'];
$totalQuadros = $dadosQuadros['total'];
$totalPaginasQuadros = $dadosQuadros['total_paginas'];

// Definir quadro ativo atual
$quadroId = (int)($_GET['quadro_id'] ?? ($quadros[0]['id'] ?? 1));
$quadroAtual = $quadroService->buscarPorId($quadroId);
if (!$quadroAtual && !empty($quadros)) {
    $quadroAtual = $quadros[0];
    $quadroId = (int)$quadroAtual['id'];
}

// Carregar colunas e tarefas do quadro ativo selecionado
$colunas = $colunaService->listarPorQuadro($quadroId);
$tarefas = $tarefaService->listarPorQuadro($quadroId);
$usuarios = $usuarioService->listar();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quadroAtual['titulo'] ?? 'Quadro') ?> | Engello</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard-body">
    <!-- BARRA SUPERIOR -->
    <header class="top-nav">
        <div class="nav-brand">
            <span class="brand-icon">📋</span>
            <div class="brand-text">
                <h1>Engello</h1>
                <span>Gestão Operacional Aberta</span>
            </div>
        </div>

        <div class="nav-actions">
            <!-- BOTÃO EXCLUSIVO PARA ADMINISTRADOR -->
            <?php if ($usuario['role'] === 'admin'): ?>
                <a href="admin-usuarios.php" class="btn btn-warning btn-sm">
                    👑 Gestão de Usuários (ADM)
                </a>
            <?php endif; ?>

            <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalNovaTarefa()">
                + Nova Tarefa
            </button>

            <!-- PERFIL DO USUÁRIO -->
            <div class="user-badge">
                <div class="user-avatar">
                    <?= htmlspecialchars(strtoupper(substr($usuario['nome'], 0, 1))) ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?= htmlspecialchars($usuario['nome']) ?></span>
                    <span class="user-role <?= $usuario['role'] === 'admin' ? 'role-admin' : 'role-func' ?>">
                        <?= $usuario['role'] === 'admin' ? 'ADMINISTRADOR' : 'COLABORADOR' ?>
                    </span>
                </div>
            </div>

            <a href="../api/auth.php?acao=logout" class="btn btn-outline btn-sm">
                Sair
            </a>
        </div>
    </header>

    <!-- AVISO DE PERMISSÃO / MENSAGENS -->
    <?php if (!empty($erro)): ?>
        <div class="banner-alert alert-danger">
            <span>⚠️</span>
            <span><?= htmlspecialchars($erro) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($sucesso)): ?>
        <div class="banner-alert alert-success">
            <span>✅</span>
            <span><?= htmlspecialchars($sucesso) ?></span>
        </div>
    <?php endif; ?>

    <!-- ESTRUTURA COM SIDEBAR DE QUADROS E ÁREA DO KANBAN -->
    <div class="dashboard-layout">
        <!-- SIDEBAR DE QUADROS COM PAGINAÇÃO -->
        <aside class="boards-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">
                    <span class="sidebar-icon">📑</span>
                    <h3>Quadros (<?= $totalQuadros ?>)</h3>
                </div>
                <button type="button" class="btn btn-primary btn-xs" onclick="abrirModalNovoQuadro()" title="Criar novo quadro">
                    + Novo
                </button>
            </div>

            <!-- BUSCA RÁPIDA DE QUADROS -->
            <form method="GET" action="dashboard.php" class="sidebar-search-form">
                <input type="hidden" name="quadro_id" value="<?= $quadroId ?>">
                <input type="text" name="busca_quadro" placeholder="Filtrar quadros..." value="<?= htmlspecialchars($buscaQuadros) ?>">
            </form>

            <!-- LISTAGEM PAGINADA DE QUADROS -->
            <div class="boards-list">
                <?php if (empty($quadros)): ?>
                    <div class="empty-boards-msg">Nenhum quadro encontrado.</div>
                <?php else: ?>
                    <?php foreach ($quadros as $q): ?>
                        <a href="dashboard.php?quadro_id=<?= $q['id'] ?><?= !empty($buscaQuadros) ? '&busca_quadro=' . urlencode($buscaQuadros) : '' ?>&p_quadros=<?= $paginaQuadros ?>" 
                           class="board-nav-item <?= (int)$q['id'] === (int)$quadroId ? 'active' : '' ?>">
                            <div class="board-nav-info">
                                <span class="board-bullet"></span>
                                <span class="board-nav-title"><?= htmlspecialchars($q['titulo']) ?></span>
                            </div>
                            <span class="board-nav-count" title="Tarefas neste quadro">
                                <?= (int)($q['total_tarefas'] ?? 0) ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- CONTROLES DE PAGINAÇÃO DOS QUADROS -->
            <?php if ($totalPaginasQuadros > 1): ?>
                <div class="sidebar-pagination">
                    <?php if ($paginaQuadros > 1): ?>
                        <a href="dashboard.php?quadro_id=<?= $quadroId ?>&p_quadros=<?= $paginaQuadros - 1 ?><?= !empty($buscaQuadros) ? '&busca_quadro=' . urlencode($buscaQuadros) : '' ?>" class="btn-page">
                            « Anterior
                        </a>
                    <?php else: ?>
                        <span class="btn-page disabled">« Anterior</span>
                    <?php endif; ?>

                    <span class="page-indicator"><?= $paginaQuadros ?> / <?= $totalPaginasQuadros ?></span>

                    <?php if ($paginaQuadros < $totalPaginasQuadros): ?>
                        <a href="dashboard.php?quadro_id=<?= $quadroId ?>&p_quadros=<?= $paginaQuadros + 1 ?><?= !empty($buscaQuadros) ? '&busca_quadro=' . urlencode($buscaQuadros) : '' ?>" class="btn-page">
                            Próximo »
                        </a>
                    <?php else: ?>
                        <span class="btn-page disabled">Próximo »</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </aside>

        <!-- CONTEÚDO PRINCIPAL DO KANBAN -->
        <main class="boards-main-content">
            <!-- CABEÇALHO DO QUADRO ATIVO -->
            <div class="active-board-header">
                <div>
                    <div class="active-board-tag">Quadro Operacional Ativo</div>
                    <h2 class="active-board-title"><?= htmlspecialchars($quadroAtual['titulo'] ?? 'Quadro') ?></h2>
                    <?php if (!empty($quadroAtual['descricao'])): ?>
                        <p class="active-board-desc"><?= htmlspecialchars($quadroAtual['descricao']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="active-board-actions">
                    <button type="button" class="btn btn-outline btn-sm" onclick="abrirModalEditarQuadro(<?= $quadroId ?>, '<?= htmlspecialchars(addslashes($quadroAtual['titulo'])) ?>', '<?= htmlspecialchars(addslashes($quadroAtual['descricao'] ?? '')) ?>')">
                        ✏️ Editar Quadro
                    </button>
                    <?php if ($totalQuadros > 1): ?>
                        <button type="button" class="btn btn-outline btn-sm text-danger" onclick="excluirQuadro(<?= $quadroId ?>, '<?= htmlspecialchars(addslashes($quadroAtual['titulo'])) ?>')">
                            🗑️ Excluir Quadro
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- BANNER DE REGRAS COLABORATIVAS -->
            <div class="banner-collaborative mb-3">
                <span class="banner-icon">💡</span>
                <div>
                    <strong>Ambiente Corporativo Integrado:</strong> Todas as tarefas deste quadro são compartilhadas com a equipe. 
                    Qualquer colaborador cadastrado pode criar, atualizar, mover e concluir atividades.
                </div>
            </div>

            <!-- ÁREA DAS COLUNAS KANBAN -->
            <div class="kanban-container">
                <div class="kanban-board" id="kanbanBoard">
            <?php foreach ($colunas as $coluna): ?>
                <div class="kanban-column" data-coluna-id="<?= $coluna['id'] ?>" ondragover="permitirSoltar(event)" ondrop="soltarTarefa(event, <?= $coluna['id'] ?>)">
                    <div class="column-header" style="border-top-color: <?= htmlspecialchars($coluna['cor']) ?>;">
                        <div class="column-title-group">
                            <h3 class="column-title"><?= htmlspecialchars($coluna['titulo']) ?></h3>
                            <?php 
                                $totalNestaColuna = count(array_filter($tarefas, function($t) use ($coluna) {
                                    return (int)$t['coluna_id'] === (int)$coluna['id'];
                                }));
                            ?>
                            <span class="column-badge"><?= $totalNestaColuna ?></span>
                        </div>
                        <button type="button" class="btn-icon" title="Adicionar nesta coluna" onclick="abrirModalNovaTarefa(<?= $coluna['id'] ?>)">+</button>
                    </div>

                    <div class="column-cards" id="coluna-cards-<?= $coluna['id'] ?>">
                        <?php foreach ($tarefas as $tarefa): ?>
                            <?php if ((int)$tarefa['coluna_id'] === (int)$coluna['id']): ?>
                                <div class="task-card" draggable="true" ondragstart="iniciarArrasto(event, <?= $tarefa['id'] ?>)" id="task-card-<?= $tarefa['id'] ?>" onclick="abrirModalEditarTarefa(<?= htmlspecialchars(json_encode($tarefa)) ?>)">
                                    <div class="card-tags">
                                        <span class="tag-priority priority-<?= htmlspecialchars($tarefa['prioridade']) ?>">
                                            <?= htmlspecialchars(ucfirst($tarefa['prioridade'])) ?>
                                        </span>
                                        <?php if (!empty($tarefa['data_entrega'])): ?>
                                            <span class="tag-date">
                                                📅 <?= date('d/m', strtotime($tarefa['data_entrega'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h4 class="card-title"><?= htmlspecialchars($tarefa['titulo']) ?></h4>
                                    
                                    <?php if (!empty($tarefa['descricao'])): ?>
                                        <p class="card-desc"><?= htmlspecialchars(mb_strimwidth($tarefa['descricao'], 0, 90, '...')) ?></p>
                                    <?php endif; ?>

                                    <?php if ((int)($tarefa['total_subtarefas'] ?? 0) > 0): ?>
                                        <?php 
                                            $total = (int)$tarefa['total_subtarefas'];
                                            $concluidas = (int)$tarefa['concluidas_subtarefas'];
                                            $pct = $total > 0 ? round(($concluidas / $total) * 100) : 0;
                                        ?>
                                        <div class="card-checklist-badge">
                                            <div class="checklist-meta">
                                                <span class="checklist-icon">☑️</span>
                                                <span class="checklist-text"><?= $concluidas ?>/<?= $total ?> subtarefas (<?= $pct ?>%)</span>
                                            </div>
                                            <div class="checklist-progress-bar">
                                                <div class="checklist-progress-fill" style="width: <?= $pct ?>%;"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="card-footer">
                                        <div class="card-assignee">
                                            <span class="avatar-mini">
                                                <?= htmlspecialchars(strtoupper(substr($tarefa['responsavel_nome'] ?? 'Equipe', 0, 1))) ?>
                                            </span>
                                            <span class="assignee-name"><?= htmlspecialchars($tarefa['responsavel_nome'] ?? 'Sem responsável') ?></span>
                                        </div>
                                        <span class="card-edit-hint" title="Clique para editar">✏️</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL: NOVA TAREFA -->
    <div id="modalNovaTarefa" class="modal-overlay hidden">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Criar Nova Tarefa</h3>
                <button type="button" class="btn-close" onclick="fecharModais()">×</button>
            </div>
            <form id="formNovaTarefa" onsubmit="salvarNovaTarefa(event)">
                <input type="hidden" id="nova_quadro_id" value="<?= $quadroId ?>">
                <div class="form-group">
                    <label for="nova_titulo">Título da Tarefa *</label>
                    <input type="text" id="nova_titulo" required placeholder="Ex: Fechar relatório de despesas">
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="nova_coluna_id">Coluna Inicial *</label>
                        <select id="nova_coluna_id" required>
                            <?php foreach ($colunas as $col): ?>
                                <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['titulo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group flex-1">
                        <label for="nova_prioridade">Prioridade</label>
                        <select id="nova_prioridade">
                            <option value="baixa">Baixa</option>
                            <option value="media" selected>Média</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="nova_responsavel_id">Atribuir a</label>
                        <select id="nova_responsavel_id">
                            <option value="">Qualquer Colaborador</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?> (<?= htmlspecialchars($u['cargo']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group flex-1">
                        <label for="nova_data_entrega">Data de Entrega</label>
                        <input type="date" id="nova_data_entrega">
                    </div>
                </div>

                <div class="form-group">
                    <label for="nova_descricao">Descrição Detalhada</label>
                    <textarea id="nova_descricao" rows="3" placeholder="Orientações e instruções para o time..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="fecharModais()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Tarefa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDITAR / EXCLUIR TAREFA (QUALQUER FUNCIONÁRIO PODE EDITAR/EXCLUIR) -->
    <div id="modalEditarTarefa" class="modal-overlay hidden">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Editar Tarefa Colaborativa</h3>
                <button type="button" class="btn-close" onclick="fecharModais()">×</button>
            </div>
            <form id="formEditarTarefa" onsubmit="salvarEdicaoTarefa(event)">
                <input type="hidden" id="edit_id">
                
                <div class="form-group">
                    <label for="edit_titulo">Título da Tarefa *</label>
                    <input type="text" id="edit_titulo" required>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="edit_coluna_id">Mover para Coluna</label>
                        <select id="edit_coluna_id">
                            <?php foreach ($colunas as $col): ?>
                                <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['titulo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group flex-1">
                        <label for="edit_prioridade">Prioridade</label>
                        <select id="edit_prioridade">
                            <option value="baixa">Baixa</option>
                            <option value="media">Média</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="edit_responsavel_id">Responsável</label>
                        <select id="edit_responsavel_id">
                            <option value="">Nenhum / Aberto</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?> (<?= htmlspecialchars($u['cargo']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group flex-1">
                        <label for="edit_data_entrega">Data de Entrega</label>
                        <input type="date" id="edit_data_entrega">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_descricao">Descrição</label>
                    <textarea id="edit_descricao" rows="3"></textarea>
                </div>

                <!-- SEÇÃO DO CHECKLIST / SUBTAREFAS -->
                <div class="checklist-section">
                    <div class="checklist-header">
                        <div class="checklist-title">
                            <span>☑️</span>
                            <h4>Checklist / Subtarefas Operacionais</h4>
                        </div>
                        <span id="checklist_progresso_label" class="checklist-progresso-badge">0/0 (0%)</span>
                    </div>

                    <div class="checklist-progress-bar mb-3">
                        <div id="checklist_progresso_barra" class="checklist-progress-fill" style="width: 0%;"></div>
                    </div>

                    <!-- Lista de itens dinâmicos do checklist -->
                    <div id="checklist_itens_container" class="checklist-itens-list">
                        <!-- Carregado via JS -->
                    </div>

                    <!-- Formulário para adicionar nova subtarefa -->
                    <div class="add-subtask-box">
                        <input type="text" id="novo_subtask_titulo" placeholder="Adicionar novo item de verificação..." onkeydown="if(event.key==='Enter'){event.preventDefault();adicionarSubtarefa();}">
                        <button type="button" class="btn btn-outline btn-sm" onclick="adicionarSubtarefa()">
                            + Adicionar
                        </button>
                    </div>
                </div>

                <div class="modal-footer flex-between">
                    <button type="button" class="btn btn-danger" onclick="excluirTarefa()">
                        🗑️ Excluir Tarefa
                    </button>
                    <div class="button-group">
                        <button type="button" class="btn btn-outline" onclick="fecharModais()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: NOVO QUADRO (SEPARAÇÃO DE ASSUNTOS DA EMPRESA) -->
    <div id="modalNovoQuadro" class="modal-overlay hidden">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Criar Novo Quadro Corporativo</h3>
                <button type="button" class="btn-close" onclick="fecharModais()">×</button>
            </div>
            <form id="formNovoQuadro" onsubmit="salvarNovoQuadro(event)">
                <div class="form-group">
                    <label for="novo_quadro_titulo">Título do Quadro *</label>
                    <input type="text" id="novo_quadro_titulo" required placeholder="Ex: Documentações Corporativas, Expansão, TI...">
                </div>

                <div class="form-group">
                    <label for="novo_quadro_descricao">Descrição do Assunto / Objetivo</label>
                    <textarea id="novo_quadro_descricao" rows="3" placeholder="Explique a finalidade deste quadro para a equipe da empresa..."></textarea>
                </div>

                <div class="alert alert-info" style="font-size: 0.8rem; padding: 0.6rem 0.8rem; margin-top: 0.5rem; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; border-radius: 6px;">
                    ℹ️ O novo quadro será criado automaticamente com as 5 colunas padrão (Backlog, A Fazer, Em Andamento, Em Revisão e Concluído).
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="fecharModais()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Quadro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDITAR QUADRO -->
    <div id="modalEditarQuadro" class="modal-overlay hidden">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Editar Título do Quadro</h3>
                <button type="button" class="btn-close" onclick="fecharModais()">×</button>
            </div>
            <form id="formEditarQuadro" onsubmit="salvarEdicaoQuadro(event)">
                <input type="hidden" id="edit_quadro_id">
                <div class="form-group">
                    <label for="edit_quadro_titulo">Título do Quadro *</label>
                    <input type="text" id="edit_quadro_titulo" required>
                </div>

                <div class="form-group">
                    <label for="edit_quadro_descricao">Descrição</label>
                    <textarea id="edit_quadro_descricao" rows="3"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="fecharModais()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
