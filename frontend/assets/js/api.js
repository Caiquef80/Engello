(() => {
  'use strict';

  const baseUrl = (window.ENGELLO_CONFIG?.API_BASE_URL || '/api').replace(/\/$/, '');
  const sessionKey = 'engello-session';

  const routes = {
    login: '/api/login',
    me: '/api/me',
    usuarios: '/api/usuarios',
    usuario: uuid => `/api/usuarios/${encodeURIComponent(uuid)}`,
    quadros: '/api/quadros',
    quadro: id => `/api/quadros/${encodeURIComponent(id)}`,
    colunas: quadroId => `/api/quadros/${encodeURIComponent(quadroId)}/colunas`,
    colunasRoot: '/api/colunas',
    coluna: id => `/api/colunas/${encodeURIComponent(id)}`,
    tarefas: '/api/tarefas',
    tarefa: id => `/api/tarefas/${encodeURIComponent(id)}`
  };

  function getSession() {
    try {
      return JSON.parse(localStorage.getItem(sessionKey) || 'null');
    } catch {
      return null;
    }
  }

  function setSession(usuario) {
    localStorage.setItem(sessionKey, JSON.stringify(usuario));
  }

  function clearSession() {
    localStorage.removeItem(sessionKey);
  }

  async function request(path, options = {}) {
    const session = getSession();
    const headers = {
      Accept: 'application/json',
      ...(options.body !== undefined ? { 'Content-Type': 'application/json' } : {}),
      ...(options.headers || {})
    };

    if (session?.uuid) {
      headers['X-User-UUID'] = session.uuid;
    }

    const response = await fetch(`${baseUrl}${path}`, {
      ...options,
      headers
    });

    const contentType = response.headers.get('content-type') || '';
    const data = contentType.includes('application/json')
      ? await response.json()
      : await response.text();

    if (!response.ok) {
      if (response.status === 401) {
        clearSession();
      }

      const message =
        typeof data === 'object' && data?.mensagem
          ? data.mensagem
          : typeof data === 'object' && data?.message
            ? data.message
            : `Erro HTTP ${response.status}.`;

      throw new Error(message);
    }

    return data;
  }

  function login(email, senha) {
    return request(routes.login, {
      method: 'POST',
      body: JSON.stringify({ email, senha })
    }).then(result => {
      if (!result?.usuario?.uuid) {
        throw new Error('A API não retornou o usuário autenticado.');
      }

      setSession(result.usuario);
      return result.usuario;
    });
  }

  async function me() {
    const usuario = await request(routes.me);
    if (usuario?.uuid) setSession(usuario);
    return usuario;
  }

  function logoutLocal() {
    clearSession();
  }

  window.FlowBoardAPI = Object.freeze({
    baseUrl,
    routes,
    request,
    getSession,
    setSession,
    clearSession,
    login,
    me,
    logoutLocal,

    listUsers: () => request(routes.usuarios),

    listBoards: () => request(routes.quadros),
    createBoard: data => request(routes.quadros, {
      method: 'POST',
      body: JSON.stringify(data)
    }),
    updateBoard: (id, data) => request(routes.quadro(id), {
      method: 'PUT',
      body: JSON.stringify(data)
    }),
    deleteBoard: id => request(routes.quadro(id), { method: 'DELETE' }),

    listColumns: boardId => request(routes.colunas(boardId)),
    createColumn: data => request(routes.colunasRoot, {
      method: 'POST',
      body: JSON.stringify(data)
    }),
    updateColumn: (id, data) => request(routes.coluna(id), {
      method: 'PUT',
      body: JSON.stringify(data)
    }),
    deleteColumn: id => request(routes.coluna(id), { method: 'DELETE' }),

    listTasks: () => request(routes.tarefas),
    getTask: id => request(routes.tarefa(id)),
    createTask: data => request(routes.tarefas, {
      method: 'POST',
      body: JSON.stringify(data)
    }),
    updateTask: (id, data) => request(routes.tarefa(id), {
      method: 'PUT',
      body: JSON.stringify(data)
    }),
    deleteTask: id => request(routes.tarefa(id), { method: 'DELETE' })
  });
})();
