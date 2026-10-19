<?php
/**
 * Controller Autenticação
 */

require_once __DIR__ . '/../services/AutenticacaoService.php';
require_once __DIR__ . '/../helpers/Response.php';

class AutenticacaoController {
    private $authService;

    public function __construct() {
        $this->authService = new AutenticacaoService();
    }

    public function login() {
        $dados = obterDadosRequisicao();
        $email = $dados['email'] ?? '';
        $senha = $dados['senha'] ?? '';

        $resultado = $this->authService->login($email, $senha);

        if (!$resultado['sucesso']) {
            jsonResposta(false, $resultado['mensagem'], null, 401);
        }

        jsonResposta(true, $resultado['mensagem'], $resultado['usuario']);
    }

    public function logout() {
        $resultado = $this->authService->logout();
        jsonResposta(true, $resultado['mensagem']);
    }

    public function me() {
        $usuario = $this->authService->obterUsuarioAtual();
        if (!$usuario) {
            jsonResposta(false, 'Nenhum usuário logado.', null, 401);
        }
        jsonResposta(true, 'Usuário autenticado.', $usuario);
    }
}
