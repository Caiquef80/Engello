<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use PDO;

class UsuarioRepository
{
    private PDO $database;

    public function __construct()
    {
        $this->database = Database::getConnection();
    }

    public function listarTodos(): array
    {
        $statement = $this->database->query(
            'SELECT * FROM "USUARIO" ORDER BY "id"'
        );

        return $statement->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT * FROM "USUARIO" WHERE "id" = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function buscarPorUuid(string $uuid): ?array
    {
        $statement = $this->database->prepare(
            'SELECT * FROM "USUARIO" WHERE "uuid" = :uuid'
        );
        $statement->execute(['uuid' => $uuid]);

        return $statement->fetch() ?: null;
    }

    public function buscarPorEmail(string $email): ?array
    {
        $statement = $this->database->prepare(
            'SELECT * FROM "USUARIO" WHERE "email" = :email'
        );
        $statement->execute(['email' => $email]);

        return $statement->fetch() ?: null;
    }

    public function criar(string $nome, string $email, string $senha, string $nivel): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO "USUARIO" ("nome", "email", "senha", "nivel")
             VALUES (:nome, :email, :senha, :nivel)
             RETURNING "id"'
        );

        $statement->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'nivel' => $nivel
        ]);

        return (int) $statement->fetchColumn();
    }

    public function excluir(int $id): bool
    {
        $statement = $this->database->prepare(
            'DELETE FROM "USUARIO" WHERE "id" = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
