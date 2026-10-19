<?php
/**
 * Helper para envio padronizado de respostas JSON
 */

function jsonResposta($sucesso, $mensagem, $dados = null, $codigoStatus = 200) {
    // Limpar buffers anteriores para evitar caracteres invisíveis
    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code($codigoStatus);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

    $resposta = [
        'sucesso' => $sucesso,
        'mensagem' => $mensagem,
    ];

    if ($dados !== null) {
        $resposta['dados'] = $dados;
    }

    echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function obterDadosRequisicao() {
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    if ($metodo === 'GET') {
        return $_GET;
    }

    // Leitura do payload JSON (php://input)
    $inputRaw = file_get_contents('php://input');
    if (!empty($inputRaw)) {
        $json = json_decode($inputRaw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }
    }

    // Fallback para form-data tradicional
    return $_POST;
}
