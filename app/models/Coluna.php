<?php

declare(strict_types=1);

namespace App\Models;

use JsonSerializable;

class Coluna implements JsonSerializable
{
    public function __construct(
        private ?int $id = null,
        private string $titulo = '',
        private int $ordem = 0,
        private ?int $idQuadro = null
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getOrdem(): int { return $this->ordem; }
    public function getIdQuadro(): ?int { return $this->idQuadro; }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'ordem' => $this->ordem,
            'id_quadro' => $this->idQuadro
        ];
    }
}
