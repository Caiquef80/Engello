<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Usuario;
use App\Repositories\UsuarioRepository;
use DomainException;

class AutenticacaoService
{
    public function __construct(
        private UsuarioRepository $repository = new UsuarioRepository()
    ) {
    }

    public function autenticar(string $email, string $senha): Usuario
    {
        $dados = $this->repository->buscarPorEmail($email);

        if (!$dados || !password_verify($senha, $dados['senha'])) {
            throw new DomainException('Email ou senha inválidos.');
        }

        return $this->transformar($dados);
    }

    public function usuarioAtual(string $uuid): Usuario
    {
        $dados = $this->repository->buscarPorUuid($uuid);

        if (!$dados) {
            throw new DomainException('Usuário autenticado não encontrado.');
        }

        return $this->transformar($dados);
    }

    private function transformar(array $dados): Usuario
    {
        return new Usuario(
            (int) $dados['id'],
            $dados['uuid'],
            $dados['nome'],
            $dados['email'],
            $dados['senha'],
            $dados['nivel']
        );
    }
}
