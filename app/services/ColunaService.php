<?php
/**
 * Service Coluna
 */

require_once __DIR__ . '/../repositories/ColunaRepository.php';
require_once __DIR__ . '/../helpers/Sanitizer.php';

class ColunaService {
    private $colunaRepo;

    public function __construct() {
        $this->colunaRepo = new ColunaRepository();
    }

    public function listarPorQuadro($quadroId) {
        return $this->colunaRepo->listarPorQuadro((int)$quadroId);
    }

    public function criar(array $dados) {
        $quadroId = Sanitizer::inteiro($dados['quadro_id'] ?? 0);
        $titulo   = Sanitizer::texto($dados['titulo'] ?? '', 80);
        $cor      = Sanitizer::texto($dados['cor'] ?? '#3b82f6', 30);

        if ($quadroId <= 0) {
            return ['sucesso' => false, 'mensagem' => 'ID do quadro inválido.'];
        }

        if (empty($titulo)) {
            return ['sucesso' => false, 'mensagem' => 'O título da coluna é obrigatório.'];
        }

        $novoId = $this->colunaRepo->criar([
            'quadro_id' => $quadroId,
            'titulo'    => $titulo,
            'cor'       => $cor
        ]);

        return [
            'sucesso' => true,
            'mensagem' => 'Coluna criada com sucesso!',
            'dados' => ['id' => $novoId, 'quadro_id' => $quadroId, 'titulo' => $titulo, 'cor' => $cor]
        ];
    }

    public function atualizar($id, array $dados) {
        $id     = (int)$id;
        $titulo = Sanitizer::texto($dados['titulo'] ?? '', 80);
        $cor    = Sanitizer::texto($dados['cor'] ?? '#3b82f6', 30);

        if (empty($titulo)) {
            return ['sucesso' => false, 'mensagem' => 'O título da coluna não pode ser vazio.'];
        }

        $this->colunaRepo->atualizar($id, [
            'titulo' => $titulo,
            'cor'    => $cor
        ]);

        return ['sucesso' => true, 'mensagem' => 'Coluna atualizada com sucesso!'];
    }

    public function reordenar($id, $ordem) {
        $this->colunaRepo->atualizarOrdem((int)$id, (int)$ordem);
        return ['sucesso' => true, 'mensagem' => 'Ordem da coluna atualizada.'];
    }

    public function deletar($id) {
        $this->colunaRepo->deletar((int)$id);
        return ['sucesso' => true, 'mensagem' => 'Coluna removida com sucesso.'];
    }
}
