<?php
/**
 * Model Quadro
 */

class Quadro {
    public $id;
    public $titulo;
    public $descricao;
    public $criado_por;
    public $criado_em;

    public function __construct(array $dados = []) {
        $this->id         = $dados['id'] ?? null;
        $this->titulo     = $dados['titulo'] ?? '';
        $this->descricao  = $dados['descricao'] ?? '';
        $this->criado_por = $dados['criado_por'] ?? null;
        $this->criado_em  = $dados['criado_em'] ?? null;
    }

    public function toArray() {
        return [
            'id'         => (int)$this->id,
            'titulo'     => $this->titulo,
            'descricao'  => $this->descricao,
            'criado_por' => $this->criado_por ? (int)$this->criado_por : null,
            'criado_em'  => $this->criado_em
        ];
    }
}
