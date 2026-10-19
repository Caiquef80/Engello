<?php
/**
 * Model Coluna
 */

class Coluna {
    public $id;
    public $quadro_id;
    public $titulo;
    public $ordem;
    public $cor;
    public $criado_em;

    public function __construct(array $dados = []) {
        $this->id        = $dados['id'] ?? null;
        $this->quadro_id = $dados['quadro_id'] ?? null;
        $this->titulo    = $dados['titulo'] ?? '';
        $this->ordem     = isset($dados['ordem']) ? (int)$dados['ordem'] : 0;
        $this->cor       = $dados['cor'] ?? '#3b82f6';
        $this->criado_em = $dados['criado_em'] ?? null;
    }

    public function toArray() {
        return [
            'id'        => (int)$this->id,
            'quadro_id' => (int)$this->quadro_id,
            'titulo'    => $this->titulo,
            'ordem'     => (int)$this->ordem,
            'cor'       => $this->cor,
            'criado_em' => $this->criado_em
        ];
    }
}
