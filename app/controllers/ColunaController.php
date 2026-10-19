<?php
/**
 * Controller Coluna
 */

require_once __DIR__ . '/../services/ColunaService.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class ColunaController {
    private $colunaService;

    public function __construct() {
        $this->colunaService = new ColunaService();
    }

    public function listar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $quadroId = $_GET['quadro_id'] ?? 1;
        $colunas = $this->colunaService->listarPorQuadro((int)$quadroId);
        jsonResposta(true, 'Colunas obtidas com sucesso.', $colunas);
    }

    public function criar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $resultado = $this->colunaService->criar($dados);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem'], $resultado['dados'], 201);
    }

    public function atualizar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);
        $resultado = $this->colunaService->atualizar($id, $dados);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem']);
    }

    public function reordenar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? 0;
        $ordem = $dados['ordem'] ?? 0;
        $resultado = $this->colunaService->reordenar($id, $ordem);
        jsonResposta(true, $resultado['mensagem']);
    }

    public function deletar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);
        $resultado = $this->colunaService->deletar($id);
        jsonResposta(true, $resultado['mensagem']);
    }
}
