<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use PDO;

class ColunaRepository
{
    private PDO $database;

    public function __construct()
    {
        $this->database = Database::getConnection();
    }

    public function listarPorQuadro(int $idQuadro): array
    {
        $statement = $this->database->prepare(
            'SELECT * FROM "COLUNA"
             WHERE "id_quadro" = :id_quadro
             ORDER BY "ordem"'
        );
        $statement->execute(['id_quadro' => $idQuadro]);

        return $statement->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT * FROM "COLUNA" WHERE "id" = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function criar(string $titulo, int $ordem, int $idQuadro): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO "COLUNA" ("titulo", "ordem", "id_quadro")
             VALUES (:titulo, :ordem, :id_quadro)
             RETURNING "id"'
        );
        $statement->execute([
            'titulo' => $titulo,
            'ordem' => $ordem,
            'id_quadro' => $idQuadro
        ]);

        return (int) $statement->fetchColumn();
    }

    public function atualizar(int $id, string $titulo, int $ordem): bool
    {
        $statement = $this->database->prepare(
            'UPDATE "COLUNA"
             SET "titulo" = :titulo, "ordem" = :ordem
             WHERE "id" = :id'
        );
        $statement->execute([
            'id' => $id,
            'titulo' => $titulo,
            'ordem' => $ordem
        ]);

        return $statement->rowCount() > 0;
    }

    public function excluir(int $id): bool
    {
        $statement = $this->database->prepare(
            'DELETE FROM "COLUNA" WHERE "id" = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
