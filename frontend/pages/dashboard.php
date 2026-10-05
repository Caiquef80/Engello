<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Engello · Projeto Principal</title>
  <link rel="stylesheet" href="../assets/css/global.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
  <div id="dashboard-app" class="dashboard-page">
    <header class="topbar">
      <div class="topbar-left">
        <a class="brand-inline" href="dashboard.html" aria-label="FlowBoard">
          <div class="logo-mark"></div>
          <span>Engello</span>
        </a>
        <span class="separator">·</span>
        <span class="project-name">Projeto Principal</span>
        <div id="progress-badge" class="progress-badge"></div>
      </div>

      <div class="topbar-right">
        <div id="online-users" class="online-users"></div>

        <button id="theme-toggle" class="toolbar-button" type="button" title="Alternar tema"></button>

        <button id="activity-toggle" class="toolbar-button active" type="button">
          <span class="activity-icon">⌁</span>
          <span class="optional-label">Atividade</span>
        </button>

        <div class="current-user">
          <div id="current-user-avatar" class="avatar"></div>
          <div class="current-user-info">
            <strong id="current-user-name"></strong>
            <small id="current-user-role"></small>
          </div>
          <button id="logout-button" class="icon-button" type="button" title="Sair">↪</button>
        </div>
      </div>
    </header>

    <div class="workspace">
      <main id="board" class="board-area"></main>
      <aside id="activity-sidebar" class="activity-sidebar"></aside>
    </div>
  </div>

  <div id="card-modal" class="modal-backdrop hidden">
    <div class="card-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <div id="modal-content"></div>
    </div>
  </div>

  <script src="../assets/js/config/api-config.js"></script>
  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/auth.js"></script>
  <script src="../assets/js/theme.js"></script>
  <script src="../assets/js/dashboard/state.js"></script>
  <script src="../assets/js/dashboard/utils.js"></script>
  <script src="../assets/js/dashboard/render.js"></script>
  <script src="../assets/js/dashboard/actions.js"></script>
  <script src="../assets/js/dashboard.js"></script>
</body>
</html>
