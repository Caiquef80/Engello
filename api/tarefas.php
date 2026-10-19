<?php
/**
 * Rota Direta da API: Tarefas (Kanban)
 * Filosofia do Projeto: Todos os funcionários cadastrados podem ler,
 * criar, atualizar, mover e excluir tarefas de todos os colaboradores.
 */

require_once __DIR__ . '/../app/controllers/TarefaController.php';

$controller = new TarefaController();
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
        if (isset($_GET['id'])) {
            $controller->buscar();
        } else {
            $controller->listar();
        }
        break;

    case 'POST':
        if ($acao === 'mover') {
            $controller->mover();
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
