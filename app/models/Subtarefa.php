<?php
/**
 * Model Subtarefa (Item de Checklist)
 */

class Subtarefa {
    public $id;
    public $tarefa_id;
    public $titulo;
    public $concluida;
    public $ordem;
    public $criado_em;

    public function __construct(array $dados = []) {
        $this->id        = $dados['id'] ?? null;
        $this->tarefa_id = $dados['tarefa_id'] ?? null;
        $this->titulo    = $dados['titulo'] ?? '';
        $this->concluida = !empty($dados['concluida']) ? 1 : 0;
        $this->ordem     = isset($dados['ordem']) ? (int)$dados['ordem'] : 0;
        $this->criado_em = $dados['criado_em'] ?? null;
    }

    public function toArray() {
        return [
            'id'        => (int)$this->id,
            'tarefa_id' => (int)$this->tarefa_id,
            'titulo'    => $this->titulo,
            'concluida' => (int)$this->concluida,
            'ordem'     => (int)$this->ordem,
            'criado_em' => $this->criado_em
        ];
    }
}
