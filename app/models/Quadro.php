<?php

declare(strict_types=1);

namespace App\Models;

use JsonSerializable;

class Quadro implements JsonSerializable
{
    public function __construct(
        private ?int $id = null,
        private string $titulo = '',
        private ?int $idUsuario = null
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getIdUsuario(): ?int { return $this->idUsuario; }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'id_usuario' => $this->idUsuario
        ];
    }
}
