# Engello — backend básico

Backend inicial para um quadro de tarefas estilo Trello.

## Arquitetura

```text
app/
├── config/
├── controllers/
├── models/
├── repositories/
├── services/
└── support/

routes/
public/
```

Fluxo esperado:

```text
Route
  ↓
Controller
  ↓
Service
  ↓
Repository
  ↓
Database
```

## Regras de negócio

### Usuários

- Somente `admin` pode criar usuário.
- Somente `admin` pode excluir usuário.
- Um admin não pode excluir a própria conta.
- Usuários comuns não conseguem executar operações administrativas.
- O UUID é usado como identificador externo do usuário.

### Tarefas

- Todos os usuários autenticados podem visualizar todas as tarefas.
- Todos os usuários autenticados podem editar qualquer tarefa.
- Somente o usuário que criou uma tarefa pode excluí-la.
- Ao criar uma tarefa, o backend grava o usuário autenticado como `id_criador`.

### Outras entidades

Quadros e colunas têm CRUD básico para servir como base de estudo. As regras específicas podem ser refinadas depois.

## Ponto importante sobre o banco

O projeto original enviado possui `TAREFA.id_responsavel`, mas não apresenta `TAREFA.id_criador`.

Como a regra solicitada é:

> somente quem criou a tarefa pode apagar a tarefa

não é correto usar `id_responsavel` como se fosse `id_criador`, porque qualquer usuário pode editar a tarefa e mudar o responsável.

Portanto, o código deste pacote pressupõe:

```sql
ALTER TABLE "TAREFA"
ADD COLUMN "id_criador" INT NOT NULL
REFERENCES "USUARIO"("id");
```

A criação da tarefa grava `id_criador` e a exclusão verifica:

```text
id_criador == usuário autenticado
```

Você pode analisar essa parte e decidir depois como quer adaptar ao seu banco.

## Autenticação

Para deixar o backend pequeno e didático, o exemplo usa o header:

```text
X-User-UUID: uuid-do-usuario
```

Isso é apenas uma estrutura inicial para a autorização. Para uma versão final, substitua por sessão ou JWT.

O `login` já verifica email + senha, mas ainda não emite token.

## Dependências

```bash
composer dump-autoload
```

## Observação

As regras de autorização ficam nos Services, e não somente nas rotas/frontend. Isso evita que alguém simplesmente ignore o frontend e tente chamar a API diretamente.
