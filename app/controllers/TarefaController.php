<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Usuario;
use App\Services\TarefaService;
use App\Support\JsonResponse;
use DomainException;
use Throwable;

class TarefaController
{
    public function __construct(
        private TarefaService $service = new TarefaService()
    ) {
    }

    public function listar(): void
    {
        try {
            JsonResponse::send($this->service->listarTodos());
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => 'Erro interno.'], 500);
        }
    }

    public function buscar(int $id): void
    {
        try {
            $tarefa = $this->service->buscarPorId($id);

            JsonResponse::send(
                $tarefa ?? ['mensagem' => 'Tarefa não encontrada.'],
                $tarefa ? 200 : 404
            );
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => 'Erro interno.'], 500);
        }
    }

    public function criar(array $dados, Usuario $logado): void
    {
        try {
            JsonResponse::send(
                $this->service->criar($dados, $logado),
                201
            );
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => $e->getMessage()], 400);
        }
    }

    public function atualizar(int $id, array $dados): void
    {
        try {
            JsonResponse::send([
                'atualizado' => $this->service->atualizar($id, $dados)
            ]);
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => $e->getMessage()], 400);
        }
    }

    public function excluir(int $id, Usuario $logado): void
    {
        try {
            JsonResponse::send([
                'excluido' => $this->service->excluir($id, $logado)
            ]);
        } catch (DomainException $e) {
            JsonResponse::send(['mensagem' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => 'Erro interno.'], 500);
        }
    }
}
