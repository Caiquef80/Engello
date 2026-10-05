(() => {
  'use strict';

  const palette = [
    ['#f5f6f8', '#6b7280', '#e5e7eb', '#374151'],
    ['#eff6ff', '#3b82f6', '#dbeafe', '#1d4ed8'],
    ['#faf5ff', '#a855f7', '#ede9fe', '#7c3aed'],
    ['#f0fdf4', '#10b981', '#d1fae5', '#065f46']
  ];

  const priorities = {
    baixa: ['#f0fdf4', '#10b981', 'Baixa'],
    media: ['#fffbeb', '#f59e0b', 'Média'],
    alta: ['#fef2f2', '#ef4444', 'Alta'],
    urgente: ['#fef2f2', '#dc2626', 'Urgente']
  };

  window.EngelloDashboardUtils = {
    columnStyle(index) {
      const p = palette[index % palette.length];
      return { bg: p[0], dot: p[1], badge: p[2], badgeText: p[3] };
    },

    priority(value) {
      return priorities[value] || priorities.media;
    },

    normalizeBoard(board) {
      return {
        id: Number(board.id),
        title: board.titulo ?? `Quadro ${board.id}`,
        userId: board.id_usuario
      };
    },

    normalizeColumn(column, index) {
      return {
        id: Number(column.id),
        title: column.titulo ?? `Coluna ${column.id}`,
        order: Number(column.ordem ?? index),
        ...this.columnStyle(index)
      };
    },

    normalizeTask(task) {
      const description = task.descricao ?? '';
      return {
        id: Number(task.id),
        title: description.split('\n')[0] || `Tarefa ${task.id}`,
        description,
        priority: task.prioridade ?? 'media',
        dueDate: task.prazo ?? null,
        column: Number(task.id_coluna),
        assigneeId: task.id_responsavel ? Number(task.id_responsavel) : null,
        creatorId: task.id_criador ? Number(task.id_criador) : null
      };
    },

    normalizeUser(user) {
      return {
        id: Number(user.id),
        uuid: user.uuid,
        name: user.nome ?? 'Usuário',
        email: user.email ?? '',
        role: user.nivel ?? 'usuario'
      };
    },

    initials(name = '') {
      return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(part => part[0]?.toUpperCase() || '')
        .join('') || '?';
    },

    formatShortDate(date) {
      if (!date) return '';
      const parsed = new Date(`${date}T12:00:00`);
      return Number.isNaN(parsed.getTime())
        ? ''
        : parsed.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' }).replace('.', '');
    },

    formatLongDate(date) {
      if (!date) return '';
      const parsed = new Date(`${date}T12:00:00`);
      return Number.isNaN(parsed.getTime())
        ? ''
        : parsed.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' });
    },

    escapeHtml(value = '') {
      return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }
  };
})();
