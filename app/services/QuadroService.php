<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Quadro;
use App\Models\Usuario;
use App\Repositories\QuadroRepository;
use InvalidArgumentException;

class QuadroService
{
    public function __construct(
        private QuadroRepository $repository = new QuadroRepository()
    ) {
    }

    public function listarTodos(): array
    {
        return array_map(
            fn(array $dados) => new Quadro(
                (int) $dados['id'],
                $dados['titulo'],
                (int) $dados['id_usuario']
            ),
            $this->repository->listarTodos()
        );
    }

    public function criar(string $titulo, Usuario $logado): Quadro
    {
        $titulo = trim($titulo);

        if ($titulo === '') {
            throw new InvalidArgumentException('Título do quadro é obrigatório.');
        }

        $id = $this->repository->criar($titulo, $logado->getId());
        $dados = $this->repository->buscarPorId($id);

        return new Quadro(
            (int) $dados['id'],
            $dados['titulo'],
            (int) $dados['id_usuario']
        );
    }

    public function atualizar(int $id, string $titulo): bool
    {
        if (trim($titulo) === '') {
            throw new InvalidArgumentException('Título do quadro é obrigatório.');
        }

        return $this->repository->atualizar($id, trim($titulo));
    }

    public function excluir(int $id): bool
    {
        return $this->repository->excluir($id);
    }
}
