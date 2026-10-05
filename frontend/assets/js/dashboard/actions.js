(() => {
  'use strict';

  const API = window.FlowBoardAPI;
  const state = window.EngelloDashboardState;
  const U = window.EngelloDashboardUtils;
  const render = window.EngelloDashboardRender;

  function findTask(id) {
    return state.tasks.find(task => task.id === Number(id));
  }

  async function addTask() {
    const textarea = document.querySelector('#board .new-card textarea');
    const description = textarea?.value.trim();

    if (!description || !state.newCardColumnId) return;

    try {
      const created = await API.createTask({
        descricao: description,
        prioridade: 'media',
        id_coluna: state.newCardColumnId
      });

      state.tasks.push(U.normalizeTask(created));
      state.activities.unshift({
        action: 'criou',
        target: `"${description}"`
      });
      state.activities = state.activities.slice(0, 10);
      state.newCardColumnId = null;

      render.renderProgress();
      render.renderBoard();
      render.renderSidebar();
    } catch (error) {
      alert(error.message);
    }
  }

  async function moveTask(id, columnId) {
    const task = findTask(id);
    if (!task || task.column === columnId) return;

    const oldColumn = task.column;

    try {
      await API.updateTask(task.id, {
        descricao: task.description,
        prioridade: task.priority,
        prazo: task.dueDate,
        id_coluna: columnId,
        id_responsavel: task.assigneeId || undefined
      });

      task.column = columnId;

      const targetColumn = state.columns.find(column => column.id === columnId);
      state.activities.unshift({
        action: 'moveu',
        target: `"${task.title}" → ${targetColumn?.title || 'nova coluna'}`
      });
      state.activities = state.activities.slice(0, 10);

      render.renderProgress();
      render.renderBoard();
      render.renderSidebar();
    } catch (error) {
      task.column = oldColumn;
      alert(error.message);
    }
  }

  async function closeTaskModal() {
    state.selectedTaskId = null;
    document.getElementById('card-modal').classList.add('hidden');
  }

  async function bindModal() {
    const modal = document.getElementById('card-modal');

    modal.addEventListener('click', event => {
      if (event.target === modal) closeTaskModal();
    });

    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') closeTaskModal();
    });

    modal.addEventListener('click', event => {
      const close = event.target.closest('#close-modal');
      if (close) {
        closeTaskModal();
        return;
      }

      const move = event.target.closest('[data-move]');
      if (move && state.selectedTaskId) {
        moveTask(state.selectedTaskId, Number(move.dataset.move));
        closeTaskModal();
      }
    });
  }

  window.EngelloDashboardActions = {
    addTask,
    moveTask,
    closeTaskModal,
    bindModal
  };
})();
