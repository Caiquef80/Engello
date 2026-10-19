<?php
/**
 * Model Usuario
 * Sem dependência de composer ou namespaces complexos
 */

class Usuario {
    public $id;
    public $nome;
    public $email;
    public $senha;
    public $cargo;
    public $role;
    public $ativo;
    public $criado_em;
    public $atualizado_em;

    public function __construct(array $dados = []) {
        $this->id            = $dados['id'] ?? null;
        $this->nome          = $dados['nome'] ?? '';
        $this->email         = $dados['email'] ?? '';
        $this->senha         = $dados['senha'] ?? '';
        $this->cargo         = $dados['cargo'] ?? 'Colaborador';
        $this->role          = $dados['role'] ?? 'funcionario';
        $this->ativo         = isset($dados['ativo']) ? (int)$dados['ativo'] : 1;
        $this->criado_em     = $dados['criado_em'] ?? null;
        $this->atualizado_em = $dados['atualizado_em'] ?? null;
    }

    public function toArraySeguro() {
        return [
            'id'            => (int)$this->id,
            'nome'          => $this->nome,
            'email'         => $this->email,
            'cargo'         => $this->cargo,
            'role'          => $this->role,
            'ativo'         => (int)$this->ativo,
            'criado_em'     => $this->criado_em,
            'atualizado_em' => $this->atualizado_em
        ];
    }
}
