<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Coluna;
use App\Repositories\ColunaRepository;
use InvalidArgumentException;

class ColunaService
{
    public function __construct(
        private ColunaRepository $repository = new ColunaRepository()
    ) {
    }

    public function listarPorQuadro(int $idQuadro): array
    {
        return array_map(
            fn(array $dados) => $this->transformar($dados),
            $this->repository->listarPorQuadro($idQuadro)
        );
    }

    public function criar(string $titulo, int $ordem, int $idQuadro): Coluna
    {
        if (trim($titulo) === '') {
            throw new InvalidArgumentException('Título da coluna é obrigatório.');
        }

        $id = $this->repository->criar(trim($titulo), $ordem, $idQuadro);
        $dados = $this->repository->buscarPorId($id);

        return $this->transformar($dados);
    }

    public function atualizar(int $id, string $titulo, int $ordem): bool
    {
        if (trim($titulo) === '') {
            throw new InvalidArgumentException('Título da coluna é obrigatório.');
        }

        return $this->repository->atualizar($id, trim($titulo), $ordem);
    }

    public function excluir(int $id): bool
    {
        return $this->repository->excluir($id);
    }

    private function transformar(array $dados): Coluna
    {
        return new Coluna(
            (int) $dados['id'],
            $dados['titulo'],
            (int) $dados['ordem'],
            (int) $dados['id_quadro']
        );
    }
}
