# 📋 Engello - Sistema de Gestão de Tarefas Corporativa (Reformulado)

Sistema de gerenciamento colaborativo de tarefas estilo Trello/Kanban, especialmente reformulado para operação simples, direta e segura em servidores compartilhados como **InfinityFree**, **cPanel**, **XAMPP** e **WAMP**.

---

## 🎯 Filosofia e Regras de Negócio do Projeto

1. **Gestão Aberta e Colaborativa:**  
   Diferente do Trello tradicional onde tarefas podem ser restritas a um único dono, no **Engello todos os funcionários cadastrados podem visualizar, criar, editar, mover e excluir tarefas de todos**. Qualquer membro da equipe pode colaborar em qualquer demanda da empresa.

2. **Tela Exclusiva para Administrador (Criação de Usuários):**  
   Apenas usuários com perfil `admin` têm acesso ao painel de cadastro e gerenciamento de colaboradores (`frontend/admin-usuarios.php`).

3. **Bloqueio Estrito de URLs Não Autorizadas:**  
   Se um usuário não autenticado ou um funcionário comum tentar acessar uma URL protegida (ou tentar disparar ações de criação/exclusão de usuários via API), ele é **imediatamente bloqueado** (com redirecionamento seguro e código HTTP `401` ou `403 Forbidden`).

---

## 🛠️ Mudanças da Reformulação Técnica

- **Eliminação Total do Composer & Namespaces Complexos:**  
  Não é necessário rodar `composer install` ou `composer dump-autoload`. Todos os arquivos utilizam `require_once` nativo do PHP.
- **Rotas Diretas na API:**  
  Adeus aos erros de cálculo de rotas no servidor! As chamadas são feitas diretamente para os endpoints:
  - `api/auth.php` (login, logout, verificação de sessão)
  - `api/usuarios.php` (listagem, criação restrita a ADM, remoção)
  - `api/quadros.php` (gestão dos quadros)
  - `api/colunas.php` (gestão e ordenação de colunas)
  - `api/tarefas.php` (criação, edição, movimentação e exclusão colaborativa de tarefas)
  - `api/subtarefas.php` (checklist interno dos cards: adicionar itens, alternar status e excluir)
- **Sistema de Subtarefas (Checklist nos Cards):**  
  Cada tarefa possui suporte a itens individuais com barra de progresso dinâmica percentual. Qualquer funcionário pode adicionar, marcar ou remover itens.
- **Migração para MySQL:**  
  Compatível com MariaDB/MySQL 5.7+ e 8.0+, InnoDB com integridade referencial (`ON DELETE CASCADE` e `ON DELETE SET NULL`) e charset `utf8mb4`.
- **Arquitetura MVC Padrão:**  
  - `app/models/` (Usuario, Quadro, Coluna, Tarefa)
  - `app/repositories/` (Consultas SQL seguras com PDO e Prepared Statements contra SQL Injection)
  - `app/services/` (Regras de negócio e sanitização de dados)
  - `app/controllers/` (Controle de requisições e respostas JSON)
  - `app/helpers/` (AuthMiddleware, Sanitizer, Response)
  - `config/` (Conexão PDO limpa e configurável)
  - `frontend/` (Interface moderna, responsiva e dinâmica)

---

## 🔑 Credenciais Padrão (Ambiente de Testes)

| Tipo de Usuário | E-mail | Senha | Acesso |
|---|---|---|---|
| **👑 Administrador** | `admin@engello.com` | `admin123` | Acesso total + Painel de Gestão de Usuários |
| **👤 Funcionário** | `joao.silva@empresa.com` | `user123` | Kanban completo (cria/edita/move qualquer tarefa) |
| **👤 Funcionário** | `maria.santos@empresa.com` | `user123` | Kanban completo |
| **👤 Funcionário** | `carlos.eduardo@empresa.com` | `user123` | Kanban completo |

---

## 🚀 Como Subir no InfinityFree (Passo a Passo)

1. **Acesse o painel do InfinityFree:**
   - Faça login na sua conta e entre no painel de controle da sua hospedagem.
2. **Crie a Base de Dados MySQL:**
   - Vá em **MySQL Databases**.
   - Crie um novo banco (ex: `epiz_XXXXXX_engello`). Anote o **Host MySQL** (ex: `sql123.infinityfree.com`), **Usuário** e **Senha**.
3. **Importe o Script SQL:**
   - Abra o **phpMyAdmin** pelo painel.
   - Selecione a base de dados criada.
   - Clique na aba **Importar**, selecione o arquivo `database.sql` incluído nesta pasta e clique em **Executar**.
4. **Configure a Conexão no PHP:**
   - Abra o arquivo `config/database.php` no bloco de notas ou editor:
     ```php
     $host     = 'sql123.infinityfree.com'; // Seu host no InfinityFree
     $dbname   = 'epiz_XXXXXX_engello';     // Nome do seu banco
     $user     = 'epiz_XXXXXX';             // Seu usuário do banco
     $password = 'sua_senha_aqui';          // Sua senha do banco
     ```
5. **Envie os Arquivos:**
   - Conecte via FileZilla (FTP) ou use o Gerenciador de Arquivos Online.
   - Envie todo o conteúdo desta pasta para dentro da pasta `htdocs`.
6. **Pronto!**  
   - Acesse o seu domínio (ex: `http://seunome.infinityfreeapp.com`). Ele abrirá imediatamente a tela de login.

---

## 💻 Como Rodar no XAMPP Localmente

1. Copie a pasta `Engello` para `C:\xampp\htdocs\engello`.
2. Inicie o **Apache** e o **MySQL** no XAMPP Control Panel.
3. Abra `http://localhost/phpmyadmin/` no navegador.
4. Crie um banco chamado `engello`.
5. Importe o arquivo `database.sql`.
6. Acesse `http://localhost/engello/` e utilize o sistema!
