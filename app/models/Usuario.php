<?php

declare(strict_types=1);

namespace App\Models;

use JsonSerializable;

class Usuario implements JsonSerializable
{
    public function __construct(
        private ?int $id = null,
        private ?string $uuid = null,
        private string $nome = '',
        private string $email = '',
        private string $senha = '',
        private string $nivel = 'usuario'
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getUuid(): ?string { return $this->uuid; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): string { return $this->email; }
    public function getSenha(): string { return $this->senha; }
    public function getNivel(): string { return $this->nivel; }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'nome' => $this->nome,
            'email' => $this->email,
            'nivel' => $this->nivel
        ];
    }
}
