<?php
/**
 * Rota Direta da API: Colunas do Kanban
 */

require_once __DIR__ . '/../app/controllers/ColunaController.php';

$controller = new ColunaController();
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$acao   = $_GET['acao'] ?? '';

if ($metodo === 'OPTIONS') {
    http_response_code(200);
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit;
}

switch ($metodo) {
    case 'GET':
        $controller->listar();
        break;

    case 'POST':
        if ($acao === 'reordenar') {
            $controller->reordenar();
        } elseif ($acao === 'atualizar') {
            $controller->atualizar();
        } elseif ($acao === 'deletar') {
            $controller->deletar();
        } else {
            $controller->criar();
        }
        break;

    case 'PUT':
        $controller->atualizar();
        break;

    case 'DELETE':
        $controller->deletar();
        break;

    default:
        jsonResposta(false, 'Método HTTP não permitido.', null, 405);
        break;
}
