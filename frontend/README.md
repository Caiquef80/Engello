# Engello — Frontend

Frontend tradicional com PHP + HTML + CSS + JavaScript, separado por responsabilidade e conectado à API REST real.

## Abrir

Com Apache/XAMPP, acesse:

`http://localhost/Engello/frontend/`

A API deste pacote está em:

`http://localhost/Engello/public/`

Se o projeto estiver configurado com outro caminho, altere somente:

`frontend/assets/js/config/api-config.js`

## Organização

- `pages/login.php` — tela de login.
- `pages/dashboard.php` — quadro principal.
- `assets/css/` — estilos separados por página/global.
- `assets/js/api.js` — única camada de comunicação HTTP.
- `assets/js/auth.js` — sessão e proteção das páginas.
- `assets/js/login.js` — somente comportamento do login.
- `assets/js/dashboard/` — estado, renderização, utilitários e ações do quadro.
- `assets/js/dashboard.js` — inicialização/orquestração.
- `config/routes.md` — mapa das rotas realmente usadas.

## Importante

Os mocks foram retirados do fluxo. O dashboard carrega:

1. usuário autenticado;
2. quadros;
3. colunas do primeiro quadro;
4. tarefas reais;
5. usuários, quando o usuário logado possui permissão.

A movimentação de uma tarefa usa `PUT /api/tarefas/{id}`, porque o backend atual não possui uma rota `PATCH /coluna`.

## Backend

Foram feitos apenas ajustes pequenos necessários para a integração:

- modelos passaram a serializar corretamente para JSON;
- foi adicionada `GET /api/me`;
- o roteador aceita o caminho `/Engello/public/api/...`;
- corrigido um erro de sintaxe em `UsuarioService.php`.
