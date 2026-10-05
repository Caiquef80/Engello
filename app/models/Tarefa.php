<?php

declare(strict_types=1);

namespace App\Models;

use JsonSerializable;

class Tarefa implements JsonSerializable
{
    public function __construct(
        private ?int $id = null,
        private string $descricao = '',
        private ?string $prioridade = null,
        private ?string $dataInicio = null,
        private ?string $dataAtualizacao = null,
        private ?string $prazo = null,
        private ?int $idColuna = null,
        private ?int $idResponsavel = null,
        private ?int $idCriador = null
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getDescricao(): string { return $this->descricao; }
    public function getPrioridade(): ?string { return $this->prioridade; }
    public function getDataInicio(): ?string { return $this->dataInicio; }
    public function getDataAtualizacao(): ?string { return $this->dataAtualizacao; }
    public function getPrazo(): ?string { return $this->prazo; }
    public function getIdColuna(): ?int { return $this->idColuna; }
    public function getIdResponsavel(): ?int { return $this->idResponsavel; }
    public function getIdCriador(): ?int { return $this->idCriador; }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'prioridade' => $this->prioridade,
            'data_inicio' => $this->dataInicio,
            'data_atualizacao' => $this->dataAtualizacao,
            'prazo' => $this->prazo,
            'id_coluna' => $this->idColuna,
            'id_responsavel' => $this->idResponsavel,
            'id_criador' => $this->idCriador
        ];
    }
}
