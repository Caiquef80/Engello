<?php
require_once __DIR__ . '/../app/helpers/AuthMiddleware.php';
require_once __DIR__ . '/../app/services/AutenticacaoService.php';

$usuario = AuthMiddleware::obterUsuarioLogado();
if ($usuario) {
    header("Location: dashboard.php");
    exit;
}

$erro = $_GET['erro'] ?? '';
$sucesso = $_GET['sucesso'] ?? '';

// Tratamento direto de POST caso JavaScript esteja desabilitado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authService = new AutenticacaoService();
    $resultado = $authService->login($_POST['email'] ?? '', $_POST['senha'] ?? '');
    if ($resultado['sucesso']) {
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = $resultado['mensagem'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Engello - Gestão Corporativa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-box">
                <span class="logo-icon">📋</span>
            </div>
            <h2>Engello</h2>
            <p>Gerenciamento Colaborativo de Tarefas da Empresa</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger">
                <span>⚠️</span>
                <span><?= htmlspecialchars($erro) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="alert alert-success">
                <span>✅</span>
                <span><?= htmlspecialchars($sucesso) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="login-form" id="loginForm">
            <div class="form-group">
                <label for="email">E-mail Corporativo</label>
                <input type="email" id="email" name="email" required placeholder="seu.nome@empresa.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="senha">Senha de Acesso</label>
                <input type="password" id="senha" name="senha" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="btnEntrar">
                Entrar no Sistema
            </button>
        </form>

        <div class="quick-credentials">
            <p class="quick-title">Credenciais de Teste / Demonstração:</p>
            <div class="quick-buttons">
                <button type="button" class="btn-demo" onclick="preencher('admin@engello.com', 'admin123')">
                    👑 Administrador (admin123)
                </button>
                <button type="button" class="btn-demo" onclick="preencher('joao.silva@empresa.com', 'user123')">
                    👤 Funcionário (user123)
                </button>
            </div>
        </div>

        <div class="login-footer">
            <small>Engello - Gestão Transparente & Integrada</small>
        </div>
    </div>

    <script>
        function preencher(email, senha) {
            document.getElementById('email').value = email;
            document.getElementById('senha').value = senha;
        }
    </script>
</body>
</html>
