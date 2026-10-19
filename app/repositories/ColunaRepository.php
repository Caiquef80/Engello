<?php
/**
 * Repository Coluna
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Coluna.php';

class ColunaRepository {
    private $db;

    public function __construct() {
        $this->db = getDatabaseConnection();
    }

    public function listarPorQuadro($quadroId) {
        $sql = "SELECT * FROM colunas WHERE quadro_id = :quadro_id ORDER BY ordem ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':quadro_id', (int)$quadroId, PDO::PARAM_INT);
        $stmt->execute();
        $colunas = [];
        while ($linha = $stmt->fetch()) {
            $colunas[] = (new Coluna($linha))->toArray();
        }
        return $colunas;
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM colunas WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $linha = $stmt->fetch();
        return $linha ? new Coluna($linha) : null;
    }

    public function criar(array $dados) {
        // Obter próxima ordem
        $sqlOrdem = "SELECT COALESCE(MAX(ordem), 0) + 1 AS proxima FROM colunas WHERE quadro_id = :quadro_id";
        $stmtOrdem = $this->db->prepare($sqlOrdem);
        $stmtOrdem->bindValue(':quadro_id', (int)$dados['quadro_id'], PDO::PARAM_INT);
        $stmtOrdem->execute();
        $proxima = $stmtOrdem->fetch()['proxima'] ?? 1;

        $sql = "INSERT INTO colunas (quadro_id, titulo, ordem, cor) VALUES (:quadro_id, :titulo, :ordem, :cor)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':quadro_id', (int)$dados['quadro_id'], PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $dados['titulo'], PDO::PARAM_STR);
        $stmt->bindValue(':ordem', (int)($dados['ordem'] ?? $proxima), PDO::PARAM_INT);
        $stmt->bindValue(':cor', $dados['cor'] ?? '#3b82f6', PDO::PARAM_STR);
        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    public function atualizar($id, array $dados) {
        $sql = "UPDATE colunas SET titulo = :titulo, cor = :cor WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $dados['titulo'], PDO::PARAM_STR);
        $stmt->bindValue(':cor', $dados['cor'] ?? '#3b82f6', PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function atualizarOrdem($id, $novaOrdem) {
        $sql = "UPDATE colunas SET ordem = :ordem WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':ordem', (int)$novaOrdem, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deletar($id) {
        $sql = "DELETE FROM colunas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
