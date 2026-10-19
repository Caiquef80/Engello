<?php
/**
 * Service Subtarefa (Checklist do Card)
 * Colaboração aberta: Todos os colaboradores podem adicionar e marcar itens
 */

require_once __DIR__ . '/../repositories/SubtarefaRepository.php';
require_once __DIR__ . '/../helpers/Sanitizer.php';

class SubtarefaService {
    private $subtarefaRepo;

    public function __construct() {
        $this->subtarefaRepo = new SubtarefaRepository();
    }

    public function listarPorTarefa($tarefaId) {
        $id = Sanitizer::inteiro($tarefaId);
        return $this->subtarefaRepo->listarPorTarefa($id);
    }

    public function criar(array $dados) {
        $tarefaId = Sanitizer::inteiro($dados['tarefa_id'] ?? 0);
        $titulo   = Sanitizer::texto($dados['titulo'] ?? '', 255);

        if ($tarefaId <= 0) {
            return ['sucesso' => false, 'mensagem' => 'ID da tarefa inválido.'];
        }

        if (empty($titulo)) {
            return ['sucesso' => false, 'mensagem' => 'O título do item não pode ser vazio.'];
        }

        $novoId = $this->subtarefaRepo->criar([
            'tarefa_id' => $tarefaId,
            'titulo'    => $titulo,
            'concluida' => 0
        ]);

        $itemCriado = $this->subtarefaRepo->buscarPorId($novoId);

        return [
            'sucesso' => true,
            'mensagem' => 'Item adicionado ao checklist!',
            'dados' => $itemCriado ? $itemCriado->toArray() : null
        ];
    }

    public function alternarStatus($id) {
        $id = Sanitizer::inteiro($id);
        $item = $this->subtarefaRepo->buscarPorId($id);
        if (!$item) {
            return ['sucesso' => false, 'mensagem' => 'Item não encontrado.'];
        }

        $this->subtarefaRepo->alternarStatus($id);
        $itemAtualizado = $this->subtarefaRepo->buscarPorId($id);

        return [
            'sucesso' => true,
            'mensagem' => 'Status do checklist atualizado!',
            'dados' => $itemAtualizado ? $itemAtualizado->toArray() : null
        ];
    }

    public function atualizar($id, array $dados) {
        $id = Sanitizer::inteiro($id);
        $item = $this->subtarefaRepo->buscarPorId($id);
        if (!$item) {
            return ['sucesso' => false, 'mensagem' => 'Item não encontrado.'];
        }

        $titulo = Sanitizer::texto($dados['titulo'] ?? $item->titulo, 255);
        $concluida = isset($dados['concluida']) ? (int)$dados['concluida'] : $item->concluida;

        if (empty($titulo)) {
            return ['sucesso' => false, 'mensagem' => 'O título do item não pode ser vazio.'];
        }

        $this->subtarefaRepo->atualizar($id, [
            'titulo' => $titulo,
            'concluida' => $concluida
        ]);

        return ['sucesso' => true, 'mensagem' => 'Item atualizado com sucesso!'];
    }

    public function deletar($id) {
        $id = Sanitizer::inteiro($id);
        $this->subtarefaRepo->deletar($id);
        return ['sucesso' => true, 'mensagem' => 'Item removido do checklist com sucesso!'];
    }
}
