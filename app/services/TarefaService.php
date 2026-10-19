<?php
/**
 * Service Tarefa
 * Regra de negócio central: Todos os funcionários cadastrados têm permissão
 * completa para visualizar, criar, atualizar, mover e excluir tarefas de todos.
 */

require_once __DIR__ . '/../repositories/TarefaRepository.php';
require_once __DIR__ . '/../helpers/Sanitizer.php';

class TarefaService {
    private $tarefaRepo;

    public function __construct() {
        $this->tarefaRepo = new TarefaRepository();
    }

    public function listarPorQuadro($quadroId) {
        return $this->tarefaRepo->listarPorQuadro((int)$quadroId);
    }

    public function buscarPorId($id) {
        $tarefa = $this->tarefaRepo->buscarPorId((int)$id);
        if (!$tarefa) {
            return ['sucesso' => false, 'mensagem' => 'Tarefa não encontrada.'];
        }
        return ['sucesso' => true, 'dados' => $tarefa];
    }

    public function criar(array $dados, $usuarioId) {
        $quadroId = Sanitizer::inteiro($dados['quadro_id'] ?? 1);
        $colunaId = Sanitizer::inteiro($dados['coluna_id'] ?? 0);
        $titulo   = Sanitizer::texto($dados['titulo'] ?? '', 200);
        $descricao = Sanitizer::texto($dados['descricao'] ?? '', 2000);
        $prioridade = Sanitizer::enum($dados['prioridade'] ?? 'media', ['baixa', 'media', 'alta', 'urgente'], 'media');
        $dataEntrega = Sanitizer::data($dados['data_entrega'] ?? null);
        $responsavelId = Sanitizer::inteiro($dados['responsavel_id'] ?? 0);

        if ($colunaId <= 0) {
            return ['sucesso' => false, 'mensagem' => 'Coluna de destino inválida.'];
        }

        if (empty($titulo)) {
            return ['sucesso' => false, 'mensagem' => 'O título da tarefa é obrigatório.'];
        }

        $novoId = $this->tarefaRepo->criar([
            'quadro_id'      => $quadroId,
            'coluna_id'      => $colunaId,
            'titulo'         => $titulo,
            'descricao'      => $descricao,
            'prioridade'     => $prioridade,
            'data_entrega'   => $dataEntrega,
            'criado_por'     => (int)$usuarioId,
            'responsavel_id' => $responsavelId > 0 ? $responsavelId : null
        ]);

        $tarefaCriada = $this->tarefaRepo->buscarPorId($novoId);

        return [
            'sucesso' => true,
            'mensagem' => 'Tarefa criada com sucesso!',
            'dados' => $tarefaCriada
        ];
    }

    public function atualizar($id, array $dados) {
        $id = (int)$id;
        $tarefaExistente = $this->tarefaRepo->buscarPorId($id);
        if (!$tarefaExistente) {
            return ['sucesso' => false, 'mensagem' => 'Tarefa não encontrada.'];
        }

        $titulo        = Sanitizer::texto($dados['titulo'] ?? $tarefaExistente['titulo'], 200);
        $descricao     = Sanitizer::texto($dados['descricao'] ?? $tarefaExistente['descricao'], 2000);
        $prioridade    = Sanitizer::enum($dados['prioridade'] ?? $tarefaExistente['prioridade'], ['baixa', 'media', 'alta', 'urgente'], 'media');
        $dataEntrega   = isset($dados['data_entrega']) ? Sanitizer::data($dados['data_entrega']) : $tarefaExistente['data_entrega'];
        $responsavelId = isset($dados['responsavel_id']) ? Sanitizer::inteiro($dados['responsavel_id']) : $tarefaExistente['responsavel_id'];

        if (empty($titulo)) {
            return ['sucesso' => false, 'mensagem' => 'O título da tarefa não pode ser vazio.'];
        }

        $this->tarefaRepo->atualizar($id, [
            'titulo'         => $titulo,
            'descricao'      => $descricao,
            'prioridade'     => $prioridade,
            'data_entrega'   => $dataEntrega,
            'responsavel_id' => $responsavelId > 0 ? $responsavelId : null
        ]);

        $tarefaAtualizada = $this->tarefaRepo->buscarPorId($id);

        return [
            'sucesso' => true,
            'mensagem' => 'Tarefa atualizada com sucesso!',
            'dados' => $tarefaAtualizada
        ];
    }

    public function mover($id, $colunaId, $novaOrdem = null) {
        $id = (int)$id;
        $colunaId = Sanitizer::inteiro($colunaId);

        if ($id <= 0 || $colunaId <= 0) {
            return ['sucesso' => false, 'mensagem' => 'Identificadores de tarefa ou coluna inválidos.'];
        }

        $this->tarefaRepo->mover($id, $colunaId, $novaOrdem !== null ? (int)$novaOrdem : null);

        return [
            'sucesso' => true,
            'mensagem' => 'Tarefa movida com sucesso!'
        ];
    }

    public function deletar($id) {
        $id = (int)$id;
        $tarefa = $this->tarefaRepo->buscarPorId($id);
        if (!$tarefa) {
            return ['sucesso' => false, 'mensagem' => 'Tarefa não encontrada para exclusão.'];
        }

        $this->tarefaRepo->deletar($id);
        return [
            'sucesso' => true,
            'mensagem' => 'Tarefa excluída com sucesso!'
        ];
    }
}
