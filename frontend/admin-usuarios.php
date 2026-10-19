<?php
require_once __DIR__ . '/../app/helpers/AuthMiddleware.php';
require_once __DIR__ . '/../app/services/UsuarioService.php';

// BLOQUEIO DE ACESSO: Apenas Administrador pode acessar esta tela!
// Se um funcionário comum tentar acessar diretamente esta URL, é redirecionado imediatamente com mensagem de bloqueio.
$adminLogado = AuthMiddleware::exigirAdminPagina('dashboard.php');

$usuarioService = new UsuarioService();
$usuarios = $usuarioService->listar();

$mensagem = '';
$tipoMensagem = '';

// Tratamento de criação de usuário direto via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? 'criar';

    if ($acao === 'criar') {
        $resultado = $usuarioService->criar([
            'nome'  => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'senha' => $_POST['senha'] ?? '',
            'cargo' => $_POST['cargo'] ?? '',
            'role'  => $_POST['role'] ?? 'funcionario',
            'ativo' => isset($_POST['ativo']) ? 1 : 0
        ]);

        $mensagem = $resultado['mensagem'];
        $tipoMensagem = $resultado['sucesso'] ? 'sucesso' : 'erro';
        if ($resultado['sucesso']) {
            $usuarios = $usuarioService->listar(); // Atualiza lista
        }
    } elseif ($acao === 'deletar') {
        $idDeletar = (int)($_POST['id'] ?? 0);
        $resultado = $usuarioService->deletar($idDeletar, $adminLogado['id']);
        $mensagem = $resultado['mensagem'];
        $tipoMensagem = $resultado['sucesso'] ? 'sucesso' : 'erro';
        if ($resultado['sucesso']) {
            $usuarios = $usuarioService->listar();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Usuários (Área ADM) | Engello</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard-body">
    <!-- BARRA SUPERIOR -->
    <header class="top-nav">
        <div class="nav-brand">
            <span class="brand-icon">👑</span>
            <div class="brand-text">
                <h1>Painel Administrativo</h1>
                <span>Gestão de Acesso & Colaboradores</span>
            </div>
        </div>

        <div class="nav-actions">
            <a href="dashboard.php" class="btn btn-outline btn-sm">
                ← Voltar ao Quadro Kanban
            </a>

            <div class="user-badge">
                <div class="user-avatar">
                    <?= htmlspecialchars(strtoupper(substr($adminLogado['nome'], 0, 1))) ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?= htmlspecialchars($adminLogado['nome']) ?></span>
                    <span class="user-role role-admin">ADMINISTRADOR</span>
                </div>
            </div>

            <a href="../api/auth.php?acao=logout" class="btn btn-outline btn-sm">
                Sair
            </a>
        </div>
    </header>

    <main class="admin-container">
        <!-- NOTIFICAÇÕES -->
        <?php if (!empty($mensagem)): ?>
            <div class="banner-alert <?= $tipoMensagem === 'sucesso' ? 'alert-success' : 'alert-danger' ?>">
                <span><?= $tipoMensagem === 'sucesso' ? '✅' : '⚠️' ?></span>
                <span><?= htmlspecialchars($mensagem) ?></span>
            </div>
        <?php endif; ?>

        <div class="admin-grid">
            <!-- COLUNA ESQUERDA: FORMULÁRIO DE CRIAÇÃO -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>➕ Cadastrar Novo Colaborador</h3>
                    <p>Crie o acesso para novos funcionários da empresa</p>
                </div>

                <form method="POST" action="admin-usuarios.php" class="admin-form" id="formNovoUsuario">
                    <input type="hidden" name="acao" value="criar">

                    <div class="form-group">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" required placeholder="Ex: Lucas Ferreira">
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail Corporativo *</label>
                        <input type="email" id="email" name="email" required placeholder="lucas.ferreira@empresa.com">
                    </div>

                    <div class="form-group">
                        <label for="cargo">Cargo / Setor *</label>
                        <input type="text" id="cargo" name="cargo" required placeholder="Ex: Analista Financeiro, Suporte">
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha Inicial (mínimo 6 dígitos) *</label>
                        <input type="password" id="senha" name="senha" required minlength="6" placeholder="••••••••">
                    </div>

                    <div class="form-group">
                        <label for="role">Perfil de Acesso no Sistema *</label>
                        <select id="role" name="role" required>
                            <option value="funcionario" selected>👤 Funcionário (Acesso ao Kanban e Tarefas)</option>
                            <option value="admin">👑 Administrador (Acesso total + Criação de Usuários)</option>
                        </select>
                    </div>

                    <div class="form-group-checkbox">
                        <label>
                            <input type="checkbox" name="ativo" value="1" checked>
                            Conta Ativa (permite login imediato)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Cadastrar Usuário
                    </button>
                </form>
            </div>

            <!-- COLUNA DIREITA: LISTAGEM DE USUÁRIOS -->
            <div class="admin-card">
                <div class="admin-card-header flex-between">
                    <div>
                        <h3>👥 Colaboradores Cadastrados (<?= count($usuarios) ?>)</h3>
                        <p>Gerenciamento de credenciais e permissões ativas</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Nome & E-mail</th>
                                <th>Cargo</th>
                                <th>Perfil</th>
                                <th>Status</th>
                                <th style="text-align: right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td>
                                        <div class="user-row-info">
                                            <strong><?= htmlspecialchars($u['nome']) ?></strong>
                                            <span class="user-row-email"><?= htmlspecialchars($u['email']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($u['cargo']) ?></td>
                                    <td>
                                        <span class="badge-role <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-func' ?>">
                                            <?= $u['role'] === 'admin' ? '👑 ADM' : '👤 Funcionário' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status <?= $u['ativo'] ? 'status-active' : 'status-inactive' ?>">
                                            <?= $u['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <?php if ((int)$u['id'] !== (int)$adminLogado['id']): ?>
                                            <form method="POST" action="admin-usuarios.php" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir o usuário <?= htmlspecialchars(addslashes($u['nome'])) ?>?');">
                                                <input type="hidden" name="acao" value="deletar">
                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="btn-delete" title="Excluir Usuário">
                                                    🗑️
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge-self">Você</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/admin.js"></script>
</body>
</html>
