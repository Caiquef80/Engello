<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ColunaService;
use App\Support\JsonResponse;
use Throwable;

class ColunaController
{
    public function __construct(
        private ColunaService $service = new ColunaService()
    ) {
    }

    public function listarPorQuadro(int $idQuadro): void
    {
        try {
            JsonResponse::send($this->service->listarPorQuadro($idQuadro));
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => 'Erro interno.'], 500);
        }
    }

    public function criar(array $dados): void
    {
        try {
            JsonResponse::send(
                $this->service->criar(
                    (string) ($dados['titulo'] ?? ''),
                    (int) ($dados['ordem'] ?? 0),
                    (int) ($dados['id_quadro'] ?? 0)
                ),
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
                    (string) ($dados['titulo'] ?? ''),
                    (int) ($dados['ordem'] ?? 0)
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
