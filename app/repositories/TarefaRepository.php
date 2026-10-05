<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use PDO;

class TarefaRepository
{
    private PDO $database;

    public function __construct()
    {
        $this->database = Database::getConnection();
    }

    public function listarTodos(): array
    {
        return $this->database
            ->query('SELECT * FROM "TAREFA" ORDER BY "id"')
            ->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT * FROM "TAREFA" WHERE "id" = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function criar(
        string $descricao,
        string $prioridade,
        string $dataInicio,
        ?string $prazo,
        int $idColuna,
        int $idResponsavel,
        int $idCriador
    ): int {
        $statement = $this->database->prepare(
            'INSERT INTO "TAREFA"
                ("descricao", "prioridade", "data_inicio", "prazo",
                 "id_coluna", "id_responsavel", "id_criador")
             VALUES
                (:descricao, :prioridade, :data_inicio, :prazo,
                 :id_coluna, :id_responsavel, :id_criador)
             RETURNING "id"'
        );

        $statement->execute([
            'descricao' => $descricao,
            'prioridade' => $prioridade,
            'data_inicio' => $dataInicio,
            'prazo' => $prazo,
            'id_coluna' => $idColuna,
            'id_responsavel' => $idResponsavel,
            'id_criador' => $idCriador
        ]);

        return (int) $statement->fetchColumn();
    }

    public function atualizar(
        int $id,
        string $descricao,
        string $prioridade,
        ?string $prazo,
        int $idColuna,
        int $idResponsavel
    ): bool {
        $statement = $this->database->prepare(
            'UPDATE "TAREFA"
             SET
                "descricao" = :descricao,
                "prioridade" = :prioridade,
                "prazo" = :prazo,
                "id_coluna" = :id_coluna,
                "id_responsavel" = :id_responsavel,
                "data_atualizacao" = CURRENT_TIMESTAMP
             WHERE "id" = :id'
        );

        $statement->execute([
            'id' => $id,
            'descricao' => $descricao,
            'prioridade' => $prioridade,
            'prazo' => $prazo,
            'id_coluna' => $idColuna,
            'id_responsavel' => $idResponsavel
        ]);

        return $statement->rowCount() > 0;
    }

    public function excluirSeCriador(int $id, int $idCriador): bool
    {
        $statement = $this->database->prepare(
            'DELETE FROM "TAREFA"
             WHERE "id" = :id AND "id_criador" = :id_criador'
        );
        $statement->execute([
            'id' => $id,
            'id_criador' => $idCriador
        ]);

        return $statement->rowCount() > 0;
    }
}
