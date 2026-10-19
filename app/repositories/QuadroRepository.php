<?php
/**
 * Repository Quadro
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Quadro.php';

class QuadroRepository {
    private $db;

    public function __construct() {
        $this->db = getDatabaseConnection();
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM quadros WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $linha = $stmt->fetch();
        return $linha ? new Quadro($linha) : null;
    }

    public function listarTodos() {
        $sql = "SELECT q.*, u.nome AS criado_por_nome,
                       (SELECT COUNT(*) FROM tarefas t WHERE t.quadro_id = q.id) AS total_tarefas
                FROM quadros q 
                LEFT JOIN usuarios u ON q.criado_por = u.id 
                ORDER BY q.id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function listarPaginado($offset = 0, $limite = 5, $busca = '') {
        $sql = "SELECT q.*, u.nome AS criado_por_nome,
                       (SELECT COUNT(*) FROM tarefas t WHERE t.quadro_id = q.id) AS total_tarefas
                FROM quadros q 
                LEFT JOIN usuarios u ON q.criado_por = u.id";
        if (!empty($busca)) {
            $sql .= " WHERE q.titulo LIKE :busca OR q.descricao LIKE :busca";
        }
        $sql .= " ORDER BY q.id ASC LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        if (!empty($busca)) {
            $stmt->bindValue(':busca', '%' . $busca . '%', PDO::PARAM_STR);
        }
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contarTotal($busca = '') {
        $sql = "SELECT COUNT(*) AS total FROM quadros";
        if (!empty($busca)) {
            $sql .= " WHERE titulo LIKE :busca OR descricao LIKE :busca";
        }
        $stmt = $this->db->prepare($sql);
        if (!empty($busca)) {
            $stmt->bindValue(':busca', '%' . $busca . '%', PDO::PARAM_STR);
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function criar(array $dados) {
        $sql = "INSERT INTO quadros (titulo, descricao, criado_por) VALUES (:titulo, :descricao, :criado_por)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':titulo', $dados['titulo'], PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $dados['descricao'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':criado_por', $dados['criado_por'] ?? null, $dados['criado_por'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    public function atualizar($id, array $dados) {
        $sql = "UPDATE quadros SET titulo = :titulo, descricao = :descricao WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $dados['titulo'], PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $dados['descricao'] ?? null, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function deletar($id) {
        $sql = "DELETE FROM quadros WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
