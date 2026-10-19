<?php
/**
 * Model Tarefa
 * Tarefas são colaborativas: todos os funcionários podem visualizar, criar,
 * atualizar, mover e excluir qualquer tarefa.
 */

class Tarefa {
    public $id;
    public $quadro_id;
    public $coluna_id;
    public $titulo;
    public $descricao;
    public $prioridade;
    public $data_entrega;
    public $ordem;
    public $criado_por;
    public $responsavel_id;
    public $criado_em;
    public $atualizado_em;

    public function __construct(array $dados = []) {
        $this->id             = $dados['id'] ?? null;
        $this->quadro_id      = $dados['quadro_id'] ?? null;
        $this->coluna_id      = $dados['coluna_id'] ?? null;
        $this->titulo         = $dados['titulo'] ?? '';
        $this->descricao      = $dados['descricao'] ?? '';
        $this->prioridade     = $dados['prioridade'] ?? 'media';
        $this->data_entrega   = $dados['data_entrega'] ?? null;
        $this->ordem          = isset($dados['ordem']) ? (int)$dados['ordem'] : 0;
        $this->criado_por     = $dados['criado_por'] ?? null;
        $this->responsavel_id = $dados['responsavel_id'] ?? null;
        $this->criado_em      = $dados['criado_em'] ?? null;
        $this->atualizado_em  = $dados['atualizado_em'] ?? null;
    }

    public function toArray() {
        return [
            'id'             => (int)$this->id,
            'quadro_id'      => (int)$this->quadro_id,
            'coluna_id'      => (int)$this->coluna_id,
            'titulo'         => $this->titulo,
            'descricao'      => $this->descricao,
            'prioridade'     => $this->prioridade,
            'data_entrega'   => $this->data_entrega,
            'ordem'          => (int)$this->ordem,
            'criado_por'     => $this->criado_por ? (int)$this->criado_por : null,
            'responsavel_id' => $this->responsavel_id ? (int)$this->responsavel_id : null,
            'criado_em'      => $this->criado_em,
            'atualizado_em'  => $this->atualizado_em
        ];
    }
}
