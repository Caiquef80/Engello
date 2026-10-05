<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Engello · Entrar</title>
  <link rel="stylesheet" href="../assets/css/global.css">
  <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
  <main class="login-page">
    <section class="login-brand">
      <div class="brand-decoration brand-decoration-top"></div>
      <div class="brand-decoration brand-decoration-bottom"></div>

      <div class="brand-logo">
        <div class="logo-mark" aria-hidden="true"></div>
        <span>Engello</span>
      </div>

      <div class="brand-hero">
        <div class="online-badge"><span></span><strong id="online-count">3</strong> membros online agora</div>
        <h1>Um quadro,<br><em>toda a equipe</em><br>em sintonia.</h1>
        <p>Acompanhe o progresso de todos em tempo real. Sem silos, sem surpresas — só clareza e colaboração.</p>
      </div>

      <div class="demo-accounts">
        <p class="demo-title">CONTAS DEMO — clique para preencher</p>
        <div id="demo-users"></div>
      </div>
    </section>

    <section class="login-form-panel">
      <button id="theme-toggle" class="theme-button" type="button" title="Alternar tema"></button>

      <div class="login-form-wrap">
        <div class="mobile-brand">
          <div class="logo-mark"></div>
          <span>Engello</span>
        </div>

        <div class="login-card">
          <div class="login-heading">
            <h2>Entrar na conta</h2>
            <p>Bem-vindo de volta. Insira suas credenciais abaixo.</p>
          </div>

          <form id="login-form" novalidate>
            <div class="field">
              <label for="email">E-mail</label>
              <input id="email" name="email" type="email" autocomplete="email" placeholder="nome@empresa.com" required>
            </div>

            <div class="field">
              <label for="password">Senha</label>
              <div class="password-wrap">
                <input id="password" name="password" type="password" autocomplete="current-password" placeholder="••••••••" required>
                <button id="toggle-password" type="button" class="password-toggle" aria-label="Mostrar senha">◉</button>
              </div>
            </div>

            <div id="login-error" class="form-error hidden" role="alert"></div>

            <button id="login-submit" class="primary-button" type="submit">Entrar</button>
          </form>
        </div>

        <p class="copyright">© 2026 Engello · Todos os direitos reservados</p>
      </div>
    </section>
  </main>

  <script src="../assets/js/config/api-config.js"></script>
  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/auth.js"></script>
  <script src="../assets/js/theme.js"></script>
  <script src="../assets/js/login.js"></script>
</body>
</html>
