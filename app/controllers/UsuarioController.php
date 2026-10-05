<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Usuario;
use App\Services\UsuarioService;
use App\Support\JsonResponse;
use DomainException;
use Throwable;

class UsuarioController
{
    public function __construct(
        private UsuarioService $service = new UsuarioService()
    ) {
    }

    public function listar(Usuario $logado): void
    {
        try {
            $usuarios = $this->service->listarTodos($logado);
            JsonResponse::send($usuarios);
        } catch (DomainException $e) {
            JsonResponse::send(['mensagem' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => 'Erro interno.'], 500);
        }
    }

    public function criar(array $dados, Usuario $logado): void
    {
        try {
            $usuario = $this->service->criar($dados, $logado);
            JsonResponse::send($usuario, 201);
        } catch (DomainException $e) {
            JsonResponse::send(['mensagem' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => $e->getMessage()], 400);
        }
    }

    public function excluir(string $uuid, Usuario $logado): void
    {
        try {
            $excluido = $this->service->excluir($uuid, $logado);

            JsonResponse::send(
                $excluido
                    ? ['mensagem' => 'Usuário excluído.']
                    : ['mensagem' => 'Usuário não encontrado.'],
                $excluido ? 200 : 404
            );
        } catch (DomainException $e) {
            JsonResponse::send(['mensagem' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            JsonResponse::send(['mensagem' => 'Erro interno.'], 500);
        }
    }
}
