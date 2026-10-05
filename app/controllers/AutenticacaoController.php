<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AutenticacaoService;
use App\Support\JsonResponse;
use DomainException;
use Throwable;

class AutenticacaoController
{
    public function __construct(
        private AutenticacaoService $service = new AutenticacaoService()
    ) {
    }

    public function login(array $dados): void
    {
        try {
            $usuario = $this->service->autenticar(
                trim((string) ($dados['email'] ?? '')),
                (string) ($dados['senha'] ?? '')
            );

            JsonResponse::send([
                'usuario' => [
                    'uuid' => $usuario->getUuid(),
                    'nome' => $usuario->getNome(),
                    'email' => $usuario->getEmail(),
                    'nivel' => $usuario->getNivel()
                ]
            ]);
        } catch (DomainException $e) {
            JsonResponse::send(['mensagem' => $e->getMessage()], 401);
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => 'Erro interno.'], 500);
        }
    }
}
