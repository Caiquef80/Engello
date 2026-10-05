<?php

declare(strict_types=1);

namespace App\Services;

use App\utils\PasswordValidator;
use App\utils\EmailValidator;
use App\Models\Usuario;
use App\Repositories\UsuarioRepository;
use DomainException;
use InvalidArgumentException;
use RuntimeException;

class UsuarioService
{
    public function __construct(
        private UsuarioRepository $repository = new UsuarioRepository()
    ) {
    }

    public function listarTodos(Usuario $logado): array
    {
        $this->exigirAdmin($logado);

        return array_map(
            fn(array $dados) => $this->transformar($dados),
            $this->repository->listarTodos()
        );
    }

    public function buscarPorUuid(string $uuid): ?Usuario
    {
        $dados = $this->repository->buscarPorUuid($uuid);

        return $dados ? $this->transformar($dados) : null;
    }

    public function criar(array $dados, Usuario $logado): Usuario
    {
        $this->exigirAdmin($logado);

        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = trim((string) ($dados['email'] ?? ''));
        $senha = (string) ($dados['senha'] ?? '');
        $nivel = (string) ($dados['nivel'] ?? 'usuario');

        if ($nome === '' || $email === '' || $senha === '') {
            throw new InvalidArgumentException('Nome, email e senha são obrigatórios.');
        }
        
        if(!EmailValidator::validate($email)){
            throw new InvalidArgumentException('Email inválido!');
            
        }

        if(!PasswordValidator::validate($senha)){
            throw new  InvalidArgumentException("Senha inválida!");
            
        }

        if (!in_array($nivel, ['admin', 'usuario'], true)) {
            throw new InvalidArgumentException('Nível de usuário inválido.');
        }


        if ($this->repository->buscarPorEmail($email)) {
            throw new InvalidArgumentException('Email já cadastrado.');
        }

        $id = $this->repository->criar(
            $nome,
            $email,
            password_hash($senha, PASSWORD_DEFAULT),
            $nivel
        );

        $usuario = $this->repository->buscarPorId($id);

        if (!$usuario) {
            throw new RuntimeException('Usuário criado, mas não foi possível recuperá-lo.');
        }

        return $this->transformar($usuario);
    }

    public function excluir(string $uuid, Usuario $logado): bool
    {
        $this->exigirAdmin($logado);

        $usuario = $this->repository->buscarPorUuid($uuid);

        if (!$usuario) {
            return false;
        }

        if ((int) $usuario['id'] === $logado->getId()) {
            throw new DomainException('O administrador não pode excluir a própria conta.');
        }

        return $this->repository->excluir((int) $usuario['id']);
    }

    private function exigirAdmin(Usuario $logado): void
    {
        if ($logado->getNivel() !== 'admin') {
            throw new DomainException('Acesso negado.');
        }
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
