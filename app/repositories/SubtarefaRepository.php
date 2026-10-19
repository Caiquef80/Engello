<?php
/**
 * Repository Subtarefa (Checklist)
 * Acesso com PDO Prepared Statements (Proteção contra SQL Injection)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Subtarefa.php';

class SubtarefaRepository {
    private $db;

    public function __construct() {
        $this->db = getDatabaseConnection();
    }

    public function listarPorTarefa($tarefaId) {
        $sql = "SELECT * FROM subtarefas WHERE tarefa_id = :tarefa_id ORDER BY ordem ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tarefa_id', (int)$tarefaId, PDO::PARAM_INT);
        $stmt->execute();
        $itens = [];
        while ($linha = $stmt->fetch()) {
            $itens[] = (new Subtarefa($linha))->toArray();
        }
        return $itens;
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM subtarefas WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $linha = $stmt->fetch();
        return $linha ? new Subtarefa($linha) : null;
    }

    public function criar(array $dados) {
        $sqlOrdem = "SELECT COALESCE(MAX(ordem), 0) + 1 AS proxima FROM subtarefas WHERE tarefa_id = :tarefa_id";
        $stmtOrdem = $this->db->prepare($sqlOrdem);
        $stmtOrdem->bindValue(':tarefa_id', (int)$dados['tarefa_id'], PDO::PARAM_INT);
        $stmtOrdem->execute();
        $proxima = $stmtOrdem->fetch()['proxima'] ?? 1;

        $sql = "INSERT INTO subtarefas (tarefa_id, titulo, concluida, ordem) VALUES (:tarefa_id, :titulo, :concluida, :ordem)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tarefa_id', (int)$dados['tarefa_id'], PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $dados['titulo'], PDO::PARAM_STR);
        $stmt->bindValue(':concluida', !empty($dados['concluida']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':ordem', (int)($dados['ordem'] ?? $proxima), PDO::PARAM_INT);
        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    public function alternarStatus($id) {
        $sql = "UPDATE subtarefas SET concluida = CASE WHEN concluida = 1 THEN 0 ELSE 1 END WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function atualizar($id, array $dados) {
        $sql = "UPDATE subtarefas SET titulo = :titulo, concluida = :concluida WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $dados['titulo'], PDO::PARAM_STR);
        $stmt->bindValue(':concluida', !empty($dados['concluida']) ? 1 : 0, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deletar($id) {
        $sql = "DELETE FROM subtarefas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
