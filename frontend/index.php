<?php
/**
 * Ponto de entrada do Frontend
 * Redireciona de acordo com o status de autenticação
 */

require_once __DIR__ . '/../app/helpers/AuthMiddleware.php';

$usuario = AuthMiddleware::obterUsuarioLogado();

if ($usuario) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
