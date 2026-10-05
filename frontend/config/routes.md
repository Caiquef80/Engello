# Engello — integração frontend/API

O frontend agora usa somente a API real. Não há mais `mock.js` sendo carregado pelas páginas.

## API usada pelo frontend

| Método | Rota | Uso |
|---|---|---|
| POST | `/api/login` | Autenticação |
| GET | `/api/me` | Usuário autenticado |
| GET | `/api/usuarios` | Usuários (admin) |
| POST | `/api/usuarios` | Criar usuário (admin) |
| DELETE | `/api/usuarios/{uuid}` | Excluir usuário (admin) |
| GET | `/api/quadros` | Listar quadros |
| POST | `/api/quadros` | Criar quadro |
| PUT | `/api/quadros/{id}` | Editar quadro |
| DELETE | `/api/quadros/{id}` | Excluir quadro |
| GET | `/api/quadros/{id}/colunas` | Listar colunas |
| POST | `/api/colunas` | Criar coluna |
| PUT | `/api/colunas/{id}` | Editar coluna |
| DELETE | `/api/colunas/{id}` | Excluir coluna |
| GET | `/api/tarefas` | Listar tarefas |
| GET | `/api/tarefas/{id}` | Buscar tarefa |
| POST | `/api/tarefas` | Criar tarefa |
| PUT | `/api/tarefas/{id}` | Editar/mover tarefa |
| DELETE | `/api/tarefas/{id}` | Excluir tarefa |

## Autenticação atual

O backend ainda não usa JWT. Depois do login, a API devolve o `uuid` do usuário. O frontend guarda apenas os dados necessários da sessão no `localStorage` e envia:

`X-User-UUID: <uuid>`

em cada chamada autenticada.

Isso deixa a integração compatível com o backend atual sem criar uma autenticação paralela no frontend.

## Estrutura

```text
frontend/
├── assets/
│   ├── css/
│   │   ├── global.css
│   │   ├── login.css
│   │   └── dashboard.css
│   └── js/
│       ├── config/
│       │   └── api-config.js
│       ├── dashboard/
│       │   ├── state.js
│       │   ├── utils.js
│       │   ├── render.js
│       │   └── actions.js
│       ├── api.js
│       ├── auth.js
│       ├── login.js
│       └── theme.js
├── config/
│   └── routes.md
├── pages/
│   ├── login.php
│   └── dashboard.php
└── index.php
```

`dashboard.js` ficou apenas como orquestrador. Renderização, estado, utilitários e ações estão separados para facilitar manutenção futura.
