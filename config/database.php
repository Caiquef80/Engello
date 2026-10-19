<?php
/**
 * Configuração de Conexão com Banco de Dados MySQL
 * Compatível com XAMPP, WAMP, cPanel e InfinityFree
 */

function getDatabaseConnection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    // Configurações - Altere aqui com os dados da sua hospedagem (ex: InfinityFree)
    $host     = getenv('DB_HOST') ?: 'localhost';
    $dbname   = getenv('DB_NAME') ?: 'engellonovo';
    $user     = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $port     = getenv('DB_PORT') ?: '3306';
    $charset  = 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Previne SQL Injection no nível de driver
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci"
    ];

    try {
        $pdo = new PDO($dsn, $user, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log seguro do erro sem vazar credenciais
        error_log("Erro de Conexão MySQL: " . $e->getMessage());
        
        // Se for requisição de API, responde em JSON limpo
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Não foi possível conectar ao banco de dados MySQL. Verifique as credenciais no arquivo config/database.php.',
                'detalhe' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        die("<div style='font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;border-radius:8px;max-width:600px;margin:40px auto;border:1px solid #f87171;'>
            <h3 style='margin-top:0;'>Erro de Conexão ao MySQL</h3>
            <p>Não foi possível conectar ao banco de dados. Configure as credenciais em <strong>config/database.php</strong> e importe o arquivo <strong>database.sql</strong>.</p>
            <small style='color:#7f1d1d;'>Detalhe: " . htmlspecialchars($e->getMessage()) . "</small>
        </div>");
    }
}
