<?php
/**
 * Controller Tarefa
 * Colaboração Aberta Corporativa: Qualquer funcionário autenticado
 * pode criar, visualizar, editar, alterar status e excluir tarefas.
 */

require_once __DIR__ . '/../services/TarefaService.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class TarefaController {
    private $tarefaService;

    public function __construct() {
        $this->tarefaService = new TarefaService();
    }

    public function listar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $quadroId = $_GET['quadro_id'] ?? 1;
        $tarefas = $this->tarefaService->listarPorQuadro((int)$quadroId);
        jsonResposta(true, 'Tarefas recuperadas com sucesso.', $tarefas);
    }

    public function buscar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $id = $_GET['id'] ?? 0;
        $resultado = $this->tarefaService->buscarPorId((int)$id);
        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 404);
        }
        jsonResposta(true, 'Tarefa recuperada.', $resultado['dados']);
    }

    public function criar() {
        $usuario = AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $resultado = $this->tarefaService->criar($dados, $usuario['id']);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem'], $resultado['dados'], 201);
    }

    public function atualizar() {
        AuthMiddleware::exigirAutenticacaoApi(); // Qualquer funcionário pode editar!
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);

        if (!$id) {
            jsonResposta(false, 'ID da tarefa não informado.', null, 400);
        }

        $resultado = $this->tarefaService->atualizar($id, $dados);
        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem'], $resultado['dados'] ?? null);
    }

    public function mover() {
        AuthMiddleware::exigirAutenticacaoApi(); // Qualquer funcionário pode mover entre colunas!
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? 0;
        $colunaId = $dados['coluna_id'] ?? 0;
        $ordem = isset($dados['ordem']) ? $dados['ordem'] : null;

        $resultado = $this->tarefaService->mover($id, $colunaId, $ordem);
        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem']);
    }

    public function deletar() {
        AuthMiddleware::exigirAutenticacaoApi(); // Qualquer funcionário pode excluir tarefas conforme regra da empresa!
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);

        if (!$id) {
            jsonResposta(false, 'ID da tarefa não informado para exclusão.', null, 400);
        }

        $resultado = $this->tarefaService->deletar($id);
        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem']);
    }
}
