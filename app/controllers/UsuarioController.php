<?php
/**
 * Controller Usuario
 * Proteção de Acesso: Apenas Administrador pode criar, editar ou excluir usuários
 */

require_once __DIR__ . '/../services/UsuarioService.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class UsuarioController {
    private $usuarioService;

    public function __construct() {
        $this->usuarioService = new UsuarioService();
    }

    /**
     * Listar usuários (disponível para todos os funcionários para atribuir responsáveis às tarefas)
     */
    public function listar() {
        AuthMiddleware::exigirAutenticacaoApi();
        $usuarios = $this->usuarioService->listar();
        jsonResposta(true, 'Lista de usuários recuperada com sucesso.', $usuarios);
    }

    /**
     * Criar novo usuário (RESTRITO: SOMENTE ADMINISTRADOR)
     */
    public function criar() {
        AuthMiddleware::exigirAdminApi(); // Bloqueia e retorna 403 se não for ADM
        $dados = obterDadosRequisicao();
        $resultado = $this->usuarioService->criar($dados);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem'], $resultado['dados'] ?? null, 201);
    }

    /**
     * Atualizar usuário (RESTRITO: SOMENTE ADMINISTRADOR)
     */
    public function atualizar() {
        AuthMiddleware::exigirAdminApi(); // Bloqueia e retorna 403 se não for ADM
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);

        if (!$id) {
            jsonResposta(false, 'ID do usuário não fornecido.', null, 400);
        }

        $resultado = $this->usuarioService->atualizar($id, $dados);
        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem']);
    }

    /**
     * Deletar usuário (RESTRITO: SOMENTE ADMINISTRADOR)
     */
    public function deletar() {
        $adminLogado = AuthMiddleware::exigirAdminApi();
        $dados = obterDadosRequisicao();
        $id = $dados['id'] ?? ($_GET['id'] ?? 0);

        if (!$id) {
            jsonResposta(false, 'ID do usuário não fornecido para exclusão.', null, 400);
        }

        $resultado = $this->usuarioService->deletar($id, $adminLogado['id']);
        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 400);
        }

        jsonResposta(true, $resultado['mensagem']);
    }
}
