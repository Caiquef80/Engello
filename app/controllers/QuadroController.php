<?php
/**
 * Controller Quadro
 */

require_once __DIR__ . '/../services/QuadroService.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class QuadroController {
    private $quadroService;

    public function __construct() {
        $this->quadroService = new QuadroService();
    }

    public function listar() {
        AuthMiddleware::exigirAutenticacaoApi();
        if (isset($_GET['pagina'])) {
            $pagina = (int)$_GET['pagina'];
            $limite = (int)($_GET['limite'] ?? 5);
            $busca  = trim((string)($_GET['busca'] ?? ''));
            $resultado = $this->quadroService->listarPaginado($pagina, $limite, $busca);
            jsonResposta(true, 'Quadros obtidos com sucesso.', $resultado);
        } else {
            $quadros = $this->quadroService->listar();
            jsonResposta(true, 'Quadros obtidos com sucesso.', $quadros);
        }
    }

    public function obter() {
        AuthMiddleware::exigirAutenticacaoApi();
        $id = $_GET['id'] ?? 1;
        $quadro = $this->quadroService->buscarPorId((int)$id);
        if (!$quadro) {
            jsonResposta(false, 'Quadro não encontrado.', null, 404);
        }
        jsonResposta(true, 'Quadro obtido com sucesso.', $quadro);
    }

    public function criar() {
        $usuario = AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $resultado = $this->quadroService->criar($dados, $usuario['id']);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem'], $resultado['dados'], 201);
    }

    public function atualizar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);
        $resultado = $this->quadroService->atualizar($id, $dados);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem']);
    }

    public function deletar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);
        $resultado = $this->quadroService->deletar($id);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem']);
    }
}
