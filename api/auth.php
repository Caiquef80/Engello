<?php
/**
 * Rota Direta da API: Autenticação
 * Chamada diretamente sem roteamento calculado complexo
 */

require_once __DIR__ . '/../app/controllers/AutenticacaoController.php';

$controller = new AutenticacaoController();
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$acao   = $_GET['acao'] ?? '';

// Suporte para OPTIONS (Preflight CORS)
if ($metodo === 'OPTIONS') {
    http_response_code(200);
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit;
}

if ($metodo === 'POST') {
    if ($acao === 'logout') {
        $controller->logout();
    } else {
        $controller->login();
    }
} elseif ($metodo === 'GET') {
    if ($acao === 'logout') {
        $controller->logout();
    } else {
        $controller->me();
    }
} else {
    jsonResposta(false, 'Método HTTP não permitido.', null, 405);
}
