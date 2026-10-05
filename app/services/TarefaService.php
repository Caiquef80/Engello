<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tarefa;
use App\Models\Usuario;
use App\Repositories\TarefaRepository;
use DomainException;
use InvalidArgumentException;

class TarefaService
{
    public function __construct(
        private TarefaRepository $repository = new TarefaRepository()
    ) {
    }

    public function listarTodos(): array
    {
        return array_map(
            fn(array $dados) => $this->transformar($dados),
            $this->repository->listarTodos()
        );
    }

    public function buscarPorId(int $id): ?Tarefa
    {
        $dados = $this->repository->buscarPorId($id);

        return $dados ? $this->transformar($dados) : null;
    }

    public function criar(array $dados, Usuario $logado): Tarefa
    {
        $descricao = trim((string) ($dados['descricao'] ?? ''));
        $prioridade = (string) ($dados['prioridade'] ?? 'media');
        $dataInicio = (string) ($dados['data_inicio'] ?? date('Y-m-d H:i:s'));
        $prazo = $dados['prazo'] ?? null;
        $idColuna = (int) ($dados['id_coluna'] ?? 0);
        $idResponsavel = (int) ($dados['id_responsavel'] ?? $logado->getId());

        $this->validar($descricao, $prioridade, $idColuna, $idResponsavel);

        $id = $this->repository->criar(
            $descricao,
            $prioridade,
            $dataInicio,
            $prazo,
            $idColuna,
            $idResponsavel,
            (int) $logado->getId()
        );

        return $this->buscarPorId($id);
    }

    public function atualizar(int $id, array $dados): bool
    {
        $descricao = trim((string) ($dados['descricao'] ?? ''));
        $prioridade = (string) ($dados['prioridade'] ?? 'media');
        $prazo = $dados['prazo'] ?? null;
        $idColuna = (int) ($dados['id_coluna'] ?? 0);
        $idResponsavel = (int) ($dados['id_responsavel'] ?? 0);

        $this->validar($descricao, $prioridade, $idColuna, $idResponsavel);

        // Regra: qualquer usuário autenticado pode editar qualquer tarefa.
        return $this->repository->atualizar(
            $id,
            $descricao,
            $prioridade,
            $prazo,
            $idColuna,
            $idResponsavel
        );
    }

    public function excluir(int $id, Usuario $logado): bool
    {
        $tarefa = $this->repository->buscarPorId($id);

        if (!$tarefa) {
            return false;
        }

        $idCriador = (int) ($tarefa['id_criador'] ?? 0);

        if ($idCriador !== (int) $logado->getId()) {
            throw new DomainException(
                'Somente o usuário que criou a tarefa pode excluí-la.'
            );
        }

        return $this->repository->excluirSeCriador(
            $id,
            (int) $logado->getId()
        );
    }

    private function validar(
        string $descricao,
        string $prioridade,
        int $idColuna,
        int $idResponsavel
    ): void {
        if ($descricao === '') {
            throw new InvalidArgumentException('Descrição da tarefa é obrigatória.');
        }

        if (!in_array($prioridade, ['baixa', 'media', 'alta', 'urgente'], true)) {
            throw new InvalidArgumentException('Prioridade inválida.');
        }

        if ($idColuna <= 0 || $idResponsavel <= 0) {
            throw new InvalidArgumentException('Coluna e responsável são obrigatórios.');
        }
    }

    private function transformar(array $dados): Tarefa
    {
        return new Tarefa(
            (int) $dados['id'],
            $dados['descricao'],
            $dados['prioridade'] ?? null,
            $dados['data_inicio'] ?? null,
            $dados['data_atualizacao'] ?? null,
            $dados['prazo'] ?? null,
            isset($dados['id_coluna']) ? (int) $dados['id_coluna'] : null,
            isset($dados['id_responsavel']) ? (int) $dados['id_responsavel'] : null,
            isset($dados['id_criador']) ? (int) $dados['id_criador'] : null
        );
    }
}
