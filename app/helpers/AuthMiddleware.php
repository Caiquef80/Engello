<?php
/**
 * Middleware de Autenticação e Autorização
 * Bloqueia URLs e requisições não autorizadas
 */

require_once __DIR__ . '/Response.php';

class AuthMiddleware {
    public static function iniciarSessao() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurações de cookies seguros
            session_set_cookie_params([
                'lifetime' => 86400, // 24 horas
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public static function obterUsuarioLogado() {
        self::iniciarSessao();
        if (isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
            return $_SESSION['usuario'];
        }
        return null;
    }

    /**
     * Valida se o usuário está logado (para chamadas de API)
     */
    public static function exigirAutenticacaoApi() {
        $usuario = self::obterUsuarioLogado();
        if (!$usuario) {
            jsonResposta(false, 'Acesso não autorizado. Por favor, faça login para continuar.', null, 401);
        }
        return $usuario;
    }

    /**
     * Valida se o usuário é Administrador (para chamadas de API)
     */
    public static function exigirAdminApi() {
        $usuario = self::exigirAutenticacaoApi();
        if (($usuario['role'] ?? '') !== 'admin') {
            jsonResposta(false, 'Acesso negado (403). Somente administradores têm permissão para realizar esta ação.', null, 403);
        }
        return $usuario;
    }

    /**
     * Valida se o usuário está logado (para páginas HTML / PHP)
     */
    public static function exigirAutenticacaoPagina($caminhoLogin = 'login.php') {
        $usuario = self::obterUsuarioLogado();
        if (!$usuario) {
            header("Location: " . $caminhoLogin . "?erro=" . urlencode('Faça login para acessar o sistema.'));
            exit;
        }
        return $usuario;
    }

    /**
     * Valida se o usuário é Administrador (para páginas HTML / PHP)
     */
    public static function exigirAdminPagina($caminhoDashboard = 'dashboard.php') {
        $usuario = self::exigirAutenticacaoPagina('login.php');
        if (($usuario['role'] ?? '') !== 'admin') {
            header("Location: " . $caminhoDashboard . "?erro=" . urlencode('Acesso restrito: Você não tem permissão de Administrador para acessar a tela de gestão de usuários.'));
            exit;
        }
        return $usuario;
    }
}
