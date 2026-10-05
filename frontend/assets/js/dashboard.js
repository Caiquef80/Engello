document.addEventListener('DOMContentLoaded', async () => {
  'use strict';

  const API = window.FlowBoardAPI;
  const state = window.EngelloDashboardState;
  const U = window.EngelloDashboardUtils;
  const render = window.EngelloDashboardRender;
  const actions = window.EngelloDashboardActions;

  const user = await window.EngelloAuth.requireAuthentication();
  if (!user) return;

  state.reset();
  state.currentUser = user;

  try {
    await loadDashboard();
    renderAll();
    actions.bindModal();
    bindHeader();
  } catch (error) {
    showFatalError(error);
  }

  async function loadDashboard() {
    const rawBoards = await API.listBoards();
    state.boards = Array.isArray(rawBoards)
      ? rawBoards.map(U.normalizeBoard)
      : [];

    state.currentBoard = state.boards[0] || null;

    if (!state.currentBoard) {
      state.columns = [];
      state.tasks = [];
      await loadUsers();
      return;
    }

    const [rawColumns, rawTasks] = await Promise.all([
      API.listColumns(state.currentBoard.id),
      API.listTasks()
    ]);

    state.columns = Array.isArray(rawColumns)
      ? rawColumns.map(U.normalizeColumn)
      : [];

    state.tasks = Array.isArray(rawTasks)
      ? rawTasks
          .map(U.normalizeTask)
          .filter(task => state.columns.some(column => column.id === task.column))
      : [];

    await loadUsers();
  }

  async function loadUsers() {
    try {
      const rawUsers = await API.listUsers();
      state.users = Array.isArray(rawUsers)
        ? rawUsers.map(U.normalizeUser)
        : [];
    } catch (error) {
      // A API restringe /usuarios a administradores. O quadro continua funcionando.
      if (!/acesso negado|403/i.test(error.message)) {
        console.warn('Não foi possível carregar usuários:', error.message);
      }
      state.users = [];
    }
  }

  function renderAll() {
    render.renderHeader();
    render.renderProgress();
    render.renderBoard();
    render.renderSidebar();
  }

  function bindHeader() {
    document.getElementById('logout-button')?.addEventListener('click', () => {
      window.EngelloAuth.logout();
    });

    document.getElementById('activity-toggle')?.addEventListener('click', () => {
      state.showActivity = !state.showActivity;
      document.getElementById('activity-toggle')
        .classList.toggle('active', state.showActivity);
      document.getElementById('activity-sidebar')
        .classList.toggle('hidden', !state.showActivity);
    });
  }

  function showFatalError(error) {
    console.error(error);
    document.getElementById('board').innerHTML = `
      <div class="empty-state">
        <h2>Não foi possível carregar o quadro</h2>
        <p>${U.escapeHtml(error.message || 'Erro desconhecido.')}</p>
        <button class="primary-button" id="reload-dashboard" type="button">Tentar novamente</button>
      </div>
    `;

    document.getElementById('reload-dashboard')
      ?.addEventListener('click', () => window.location.reload());
  }
});
