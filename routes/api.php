<?php

declare(strict_types=1);

namespace App\Routes;

use App\Controllers\AutenticacaoController;
use App\Controllers\ColunaController;
use App\Controllers\QuadroController;
use App\Controllers\TarefaController;
use App\Controllers\UsuarioController;
use App\Repositories\UsuarioRepository;
use App\Services\AutenticacaoService;
use App\Support\JsonResponse;

/*
 * Roteador mínimo para estudo.
 *
 * Para produção, você pode substituir por um roteador dedicado.
 * A regra de autorização deve continuar nos Services.
 */

function body(): array
{
    $json = file_get_contents('php://input');
    $dados = json_decode($json ?: '{}', true);

    return is_array($dados) ? $dados : [];
}

function usuarioAutenticado(): \App\Models\Usuario
{
    $uuid = $_SERVER['HTTP_X_USER_UUID'] ?? '';

    if ($uuid === '') {
        JsonResponse::send(['mensagem' => 'Usuário não autenticado.'], 401);
        exit;
    }

    $service = new AutenticacaoService(new UsuarioRepository());

    try {
        return $service->usuarioAtual($uuid);
    } catch (\DomainException $e) {
        JsonResponse::send(['mensagem' => $e->getMessage()], 401);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$path = $basePath !== '' && $basePath !== '/' && str_starts_with($requestPath, $basePath)
    ? substr($requestPath, strlen($basePath))
    : $requestPath;
$path = '/' . ltrim($path, '/');

try {
    if ($path === '/api/login' && $method === 'POST') {
        (new AutenticacaoController())->login(body());
        exit;
    }

    $usuario = usuarioAutenticado();

    if ($path === '/api/me' && $method === 'GET') {
        JsonResponse::send([
            'id' => $usuario->getId(),
            'uuid' => $usuario->getUuid(),
            'nome' => $usuario->getNome(),
            'email' => $usuario->getEmail(),
            'nivel' => $usuario->getNivel()
        ]);
        exit;
    }

    if ($path === '/api/usuarios' && $method === 'GET') {
        (new UsuarioController())->listar($usuario);
        exit;
    }

    if ($path === '/api/usuarios' && $method === 'POST') {
        (new UsuarioController())->criar(body(), $usuario);
        exit;
    }

    if (preg_match('#^/api/usuarios/([^/]+)$#', $path, $matches) && $method === 'DELETE') {
        (new UsuarioController())->excluir($matches[1], $usuario);
        exit;
    }

    if ($path === '/api/quadros' && $method === 'GET') {
        (new QuadroController())->listar();
        exit;
    }

    if ($path === '/api/quadros' && $method === 'POST') {
        (new QuadroController())->criar(body(), $usuario);
        exit;
    }

    if (preg_match('#^/api/quadros/(\d+)$#', $path, $matches) && $method === 'PUT') {
        (new QuadroController())->atualizar((int) $matches[1], body());
        exit;
    }

    if (preg_match('#^/api/quadros/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        (new QuadroController())->excluir((int) $matches[1]);
        exit;
    }

    if ($path === '/api/tarefas' && $method === 'GET') {
        (new TarefaController())->listar();
        exit;
    }

    if ($path === '/api/tarefas' && $method === 'POST') {
        (new TarefaController())->criar(body(), $usuario);
        exit;
    }

    if (preg_match('#^/api/tarefas/(\d+)$#', $path, $matches) && $method === 'GET') {
        (new TarefaController())->buscar((int) $matches[1]);
        exit;
    }

    if (preg_match('#^/api/tarefas/(\d+)$#', $path, $matches) && $method === 'PUT') {
        (new TarefaController())->atualizar((int) $matches[1], body());
        exit;
    }

    if (preg_match('#^/api/tarefas/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        (new TarefaController())->excluir((int) $matches[1], $usuario);
        exit;
    }

    if (preg_match('#^/api/quadros/(\d+)/colunas$#', $path, $matches) && $method === 'GET') {
        (new ColunaController())->listarPorQuadro((int) $matches[1]);
        exit;
    }

    if ($path === '/api/colunas' && $method === 'POST') {
        (new ColunaController())->criar(body());
        exit;
    }

    if (preg_match('#^/api/colunas/(\d+)$#', $path, $matches) && $method === 'PUT') {
        (new ColunaController())->atualizar((int) $matches[1], body());
        exit;
    }

    if (preg_match('#^/api/colunas/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        (new ColunaController())->excluir((int) $matches[1]);
        exit;
    }

    JsonResponse::send(['mensagem' => 'Rota não encontrada.'], 404);

} catch (\Throwable $e) {
    JsonResponse::send(['mensagem' => 'Erro interno do servidor.'], 500);
}
