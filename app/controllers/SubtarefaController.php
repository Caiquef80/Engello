<?php
/**
 * Controller Subtarefa
 * Gerenciamento de itens do checklist do card
 */

require_once __DIR__ . '/../services/SubtarefaService.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class SubtarefaController {
    private $subtarefaService;

    public function __construct() {
        $this->subtarefaService = new SubtarefaService();
    }

    public function listar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $tarefaId = $_GET['tarefa_id'] ?? 0;
        $itens = $this->subtarefaService->listarPorTarefa((int)$tarefaId);
        jsonResposta(true, 'Itens do checklist recuperados.', $itens);
    }

    public function criar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $resultado = $this->subtarefaService->criar($dados);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem'], $resultado['dados'], 201);
    }

    public function alternar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);
        $resultado = $this->subtarefaService->alternarStatus((int)$id);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem'], $resultado['dados'] ?? null);
    }

    public function atualizar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);
        $resultado = $this->subtarefaService->atualizar((int)$id, $dados);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem']);
    }

    public function deletar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);
        $resultado = $this->subtarefaService->deletar((int)$id);
        jsonResposta(true, $resultado['mensagem']);
    }
}
