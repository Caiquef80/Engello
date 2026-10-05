(() => {
  'use strict';

  const state = window.EngelloDashboardState;
  const U = window.EngelloDashboardUtils;

  function getUser(id) {
    return state.users.find(user => user.id === Number(id)) || null;
  }

  function renderHeader() {
    const user = state.currentUser;
    const avatar = document.getElementById('current-user-avatar');
    const name = document.getElementById('current-user-name');
    const role = document.getElementById('current-user-role');
    const project = document.querySelector('.project-name');

    const initials = U.initials(user?.nome || user?.name);
    avatar.textContent = initials;
    avatar.style.background = 'var(--brand-solid)';
    name.textContent = user?.nome || user?.name || 'Usuário';
    role.textContent = user?.nivel || user?.role || 'usuario';
    project.textContent = state.currentBoard?.title || 'Sem quadro';

    document.getElementById('online-users').innerHTML =
      `<span class="online-count">${state.users.length} membros cadastrados</span>`;
  }

  function renderProgress() {
    const completedColumnIds = state.columns
      .filter(column => /conclu|finaliz|feito|done/i.test(column.title))
      .map(column => column.id);

    const done = state.tasks.filter(task => completedColumnIds.includes(task.column)).length;
    const total = state.tasks.length;
    const pct = total ? Math.round(done / total * 100) : 0;

    document.getElementById('progress-badge').textContent =
      `✓ ${done}/${total} concluídos · ${pct}%`;
  }

  function renderBoard() {
    const board = document.getElementById('board');

    if (!state.columns.length) {
      board.innerHTML = `
        <div class="empty-state">
          <h2>Este quadro ainda não possui colunas</h2>
          <p>Crie uma coluna pela API para começar a organizar as tarefas.</p>
        </div>
      `;
      return;
    }

    board.innerHTML = `
      <div class="board-columns">
        ${state.columns.map(column => {
          const tasks = state.tasks.filter(task => task.column === column.id);
          const isNew = state.newCardColumnId === column.id;

          return `
            <section class="kanban-column" data-column="${column.id}"
              style="background:${column.bg}">
              <header class="column-header">
                <div class="column-title-wrap">
                  <span class="column-dot" style="background:${column.dot}"></span>
                  <span class="column-title">${U.escapeHtml(column.title)}</span>
                  <span class="column-count"
                    style="background:${column.badge};color:${column.badgeText}">${tasks.length}</span>
                </div>
                <button class="add-card-button" data-add="${column.id}"
                  title="Adicionar tarefa">+</button>
              </header>

              <div class="cards-list custom-scroll" data-drop="${column.id}">
                ${tasks.map(renderTask).join('')}
                ${isNew ? renderNewTask() : ''}
              </div>
            </section>
          `;
        }).join('')}
      </div>
    `;

    bindBoardEvents();
  }

  function renderTask(task) {
    const priority = U.priority(task.priority);
    const user = getUser(task.assigneeId);
    const overdue = task.dueDate && new Date(`${task.dueDate}T23:59:59`) < new Date();

    return `
      <article class="kanban-card" draggable="true" data-card="${task.id}">
        <p class="card-title">${U.escapeHtml(task.title)}</p>
        <div class="card-footer">
          <div class="card-meta">
            <span class="priority" style="background:${priority[0]};color:${priority[1]}">${priority[2]}</span>
            ${task.dueDate
              ? `<span class="due-date ${overdue ? 'overdue' : ''}">▣ ${U.formatShortDate(task.dueDate)}</span>`
              : ''}
          </div>
          ${user
            ? `<div class="assignee-list">
                 <div class="avatar" style="background:var(--brand-solid)" title="${U.escapeHtml(user.name)}">
                   ${U.initials(user.name)}
                 </div>
               </div>`
            : ''}
        </div>
      </article>
    `;
  }

  function renderNewTask() {
    return `
      <div class="new-card">
        <textarea rows="2" placeholder="Descrição da tarefa… (Enter para salvar)"></textarea>
        <div class="new-card-actions">
          <button class="new-card-save" data-save-new>Adicionar</button>
          <button class="new-card-cancel" data-cancel-new>Cancelar</button>
        </div>
      </div>
    `;
  }

  function renderSidebar() {
    const sidebar = document.getElementById('activity-sidebar');

    sidebar.innerHTML = `
      <div class="activity-header">
        <span style="color:var(--brand-text)">⌁</span>
        <strong>Atividade recente</strong>
      </div>
      <div class="activity-list custom-scroll">
        ${state.activities.length
          ? state.activities.map(activity => `
              <div class="activity-item">
                <div class="avatar" style="background:var(--brand-solid)">
                  ${U.initials(state.currentUser?.nome)}
                </div>
                <div>
                  <div class="activity-text">
                    <strong>${U.escapeHtml(state.currentUser?.nome || 'Você')}</strong>
                    ${U.escapeHtml(activity.action)}
                    <span class="target">${U.escapeHtml(activity.target)}</span>
                  </div>
                  <div class="activity-time">agora</div>
                </div>
              </div>
            `).join('')
          : `<div class="empty-activity">As atividades desta sessão aparecerão aqui.</div>`}
      </div>

      <div class="members">
        <p class="members-title">Membros do time</p>
        ${state.users.length
          ? state.users.map(user => `
              <div class="member">
                <div class="avatar-wrap">
                  <div class="avatar" style="background:var(--brand-solid)">
                    ${U.initials(user.name)}
                  </div>
                </div>
                <div class="member-info">
                  <strong>${U.escapeHtml(user.name)}</strong>
                  <small>${U.escapeHtml(user.role)}</small>
                </div>
                ${user.uuid === state.currentUser?.uuid ? '<span class="you-badge">você</span>' : ''}
              </div>
            `).join('')
          : `<div class="empty-activity">Lista de membros disponível apenas para administradores.</div>`}
      </div>
    `;
  }

  function renderModal(task) {
    const modal = document.getElementById('card-modal');
    const content = document.getElementById('modal-content');

    if (!task) {
      modal.classList.add('hidden');
      return;
    }

    const column = state.columns.find(item => item.id === task.column);
    const priority = U.priority(task.priority);
    const assignee = getUser(task.assigneeId);

    content.innerHTML = `
      <div class="modal-header">
        <div class="modal-top">
          <div class="modal-labels labels"></div>
          <button class="modal-close" id="close-modal" type="button">×</button>
        </div>
        <h2 class="modal-title" id="modal-title">${U.escapeHtml(task.title)}</h2>
        <span class="modal-status"
          style="background:${column?.badge || '#eee'};color:${column?.badgeText || '#333'}">
          <i style="width:6px;height:6px;border-radius:50%;background:${column?.dot || '#777'}"></i>
          ${U.escapeHtml(column?.title || 'Sem coluna')}
        </span>
      </div>

      <div class="modal-body">
        <div class="detail-block">
          <p class="detail-label">Descrição</p>
          <p class="detail-description">${U.escapeHtml(task.description)}</p>
        </div>

        <div class="detail-grid detail-block">
          <div>
            <p class="detail-label">Prioridade</p>
            <span class="priority" style="background:${priority[0]};color:${priority[1]}">${priority[2]}</span>
          </div>
          ${task.dueDate
            ? `<div><p class="detail-label">Prazo</p>
                <p class="detail-value">${U.formatLongDate(task.dueDate)}</p></div>`
            : ''}
        </div>

        <div class="detail-block">
          <p class="detail-label">Responsável</p>
          <div class="assignee-chips">
            ${assignee
              ? `<div class="assignee-chip">
                  <span class="avatar" style="background:var(--brand-solid)">${U.initials(assignee.name)}</span>
                  ${U.escapeHtml(assignee.name)}
                </div>`
              : '<span class="detail-value">Não informado</span>'}
          </div>
        </div>

        <div class="detail-block">
          <p class="detail-label">Mover para coluna</p>
          <div class="move-options">
            ${state.columns.filter(c => c.id !== task.column).map(c =>
              `<button class="move-button" data-move="${c.id}"
                style="background:${c.badge};color:${c.badgeText};border-color:${c.dot}30">
                <span style="width:7px;height:7px;border-radius:50%;background:${c.dot}"></span>
                ${U.escapeHtml(c.title)}
              </button>`
            ).join('')}
          </div>
        </div>
      </div>
    `;

    modal.classList.remove('hidden');
  }

  function bindBoardEvents() {
    const board = document.getElementById('board');

    board.querySelectorAll('[data-add]').forEach(button => {
      button.addEventListener('click', () => {
        state.newCardColumnId = Number(button.dataset.add);
        renderBoard();
        board.querySelector('.new-card textarea')?.focus();
      });
    });

    board.querySelectorAll('[data-card]').forEach(card => {
      card.addEventListener('click', event => {
        if (event.target.closest('button')) return;
        state.selectedTaskId = Number(card.dataset.card);
        renderModal(state.tasks.find(task => task.id === state.selectedTaskId));
      });

      card.addEventListener('dragstart', () => {
        state.draggingTaskId = Number(card.dataset.card);
        card.classList.add('dragging');
      });

      card.addEventListener('dragend', () => {
        state.draggingTaskId = null;
        card.classList.remove('dragging');
      });
    });

    board.querySelectorAll('[data-drop]').forEach(drop => {
      drop.addEventListener('dragover', event => {
        event.preventDefault();
        drop.closest('.kanban-column')?.classList.add('drag-over');
      });

      drop.addEventListener('dragleave', () => {
        drop.closest('.kanban-column')?.classList.remove('drag-over');
      });

      drop.addEventListener('drop', () => {
        if (state.draggingTaskId) {
          window.EngelloDashboardActions.moveTask(
            state.draggingTaskId,
            Number(drop.dataset.drop)
          );
        }
        drop.closest('.kanban-column')?.classList.remove('drag-over');
      });
    });

    board.querySelector('[data-save-new]')?.addEventListener(
      'click',
      window.EngelloDashboardActions.addTask
    );

    board.querySelector('[data-cancel-new]')?.addEventListener('click', () => {
      state.newCardColumnId = null;
      renderBoard();
    });

    board.querySelector('.new-card textarea')?.addEventListener('keydown', event => {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        window.EngelloDashboardActions.addTask();
      }

      if (event.key === 'Escape') {
        state.newCardColumnId = null;
        renderBoard();
      }
    });
  }

  window.EngelloDashboardRender = {
    renderHeader,
    renderProgress,
    renderBoard,
    renderSidebar,
    renderModal
  };
})();
