<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Usuario;
use App\Services\QuadroService;
use App\Support\JsonResponse;
use Throwable;

class QuadroController
{
    public function __construct(
        private QuadroService $service = new QuadroService()
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

    public function criar(array $dados, Usuario $logado): void
    {
        try {
            JsonResponse::send(
                $this->service->criar((string) ($dados['titulo'] ?? ''), $logado),
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
                'atualizado' => $this->service->atualizar(
                    $id,
                    (string) ($dados['titulo'] ?? '')
                )
            ]);
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => $e->getMessage()], 400);
        }
    }

    public function excluir(int $id): void
    {
        try {
            JsonResponse::send([
                'excluido' => $this->service->excluir($id)
            ]);
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => 'Erro interno.'], 500);
        }
    }
}
