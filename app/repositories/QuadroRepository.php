<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use PDO;

class QuadroRepository
{
    private PDO $database;

    public function __construct()
    {
        $this->database = Database::getConnection();
    }

    public function listarTodos(): array
    {
        return $this->database
            ->query('SELECT * FROM "QUADRO_TAREFAS" ORDER BY "id"')
            ->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT * FROM "QUADRO_TAREFAS" WHERE "id" = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function criar(string $titulo, int $idUsuario): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO "QUADRO_TAREFAS" ("titulo", "id_usuario")
             VALUES (:titulo, :id_usuario)
             RETURNING "id"'
        );
        $statement->execute([
            'titulo' => $titulo,
            'id_usuario' => $idUsuario
        ]);

        return (int) $statement->fetchColumn();
    }

    public function atualizar(int $id, string $titulo): bool
    {
        $statement = $this->database->prepare(
            'UPDATE "QUADRO_TAREFAS" SET "titulo" = :titulo WHERE "id" = :id'
        );
        $statement->execute(['id' => $id, 'titulo' => $titulo]);

        return $statement->rowCount() > 0;
    }

    public function excluir(int $id): bool
    {
        $statement = $this->database->prepare(
            'DELETE FROM "QUADRO_TAREFAS" WHERE "id" = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
