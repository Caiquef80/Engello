<?php
/**
 * Repository Tarefa
 * Colaboração aberta: Todos os funcionários cadastrados podem ler,
 * criar, atualizar, mover e excluir tarefas de qualquer pessoa.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Tarefa.php';

class TarefaRepository {
    private $db;

    public function __construct() {
        $this->db = getDatabaseConnection();
    }

    public function listarPorQuadro($quadroId) {
        $sql = "SELECT t.*, 
                       u_cria.nome AS criado_por_nome,
                       u_resp.nome AS responsavel_nome,
                       u_resp.cargo AS responsavel_cargo,
                       (SELECT COUNT(*) FROM subtarefas st WHERE st.tarefa_id = t.id) AS total_subtarefas,
                       (SELECT COUNT(*) FROM subtarefas st WHERE st.tarefa_id = t.id AND st.concluida = 1) AS concluidas_subtarefas
                FROM tarefas t
                LEFT JOIN usuarios u_cria ON t.criado_por = u_cria.id
                LEFT JOIN usuarios u_resp ON t.responsavel_id = u_resp.id
                WHERE t.quadro_id = :quadro_id
                ORDER BY t.coluna_id ASC, t.ordem ASC, t.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':quadro_id', (int)$quadroId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscarPorId($id) {
        $sql = "SELECT t.*, 
                       u_cria.nome AS criado_por_nome,
                       u_resp.nome AS responsavel_nome,
                       (SELECT COUNT(*) FROM subtarefas st WHERE st.tarefa_id = t.id) AS total_subtarefas,
                       (SELECT COUNT(*) FROM subtarefas st WHERE st.tarefa_id = t.id AND st.concluida = 1) AS concluidas_subtarefas
                FROM tarefas t
                LEFT JOIN usuarios u_cria ON t.criado_por = u_cria.id
                LEFT JOIN usuarios u_resp ON t.responsavel_id = u_resp.id
                WHERE t.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $linha = $stmt->fetch();
        return $linha ?: null;
    }

    public function criar(array $dados) {
        // Obter próxima ordem dentro da coluna
        $sqlOrdem = "SELECT COALESCE(MAX(ordem), 0) + 1 AS proxima FROM tarefas WHERE coluna_id = :coluna_id";
        $stmtOrdem = $this->db->prepare($sqlOrdem);
        $stmtOrdem->bindValue(':coluna_id', (int)$dados['coluna_id'], PDO::PARAM_INT);
        $stmtOrdem->execute();
        $proxima = $stmtOrdem->fetch()['proxima'] ?? 1;

        $sql = "INSERT INTO tarefas (quadro_id, coluna_id, titulo, descricao, prioridade, data_entrega, ordem, criado_por, responsavel_id) 
                VALUES (:quadro_id, :coluna_id, :titulo, :descricao, :prioridade, :data_entrega, :ordem, :criado_por, :responsavel_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':quadro_id', (int)$dados['quadro_id'], PDO::PARAM_INT);
        $stmt->bindValue(':coluna_id', (int)$dados['coluna_id'], PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $dados['titulo'], PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $dados['descricao'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':prioridade', $dados['prioridade'] ?? 'media', PDO::PARAM_STR);
        $stmt->bindValue(':data_entrega', !empty($dados['data_entrega']) ? $dados['data_entrega'] : null, PDO::PARAM_STR);
        $stmt->bindValue(':ordem', (int)($dados['ordem'] ?? $proxima), PDO::PARAM_INT);
        $stmt->bindValue(':criado_por', !empty($dados['criado_por']) ? (int)$dados['criado_por'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':responsavel_id', !empty($dados['responsavel_id']) ? (int)$dados['responsavel_id'] : null, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    public function atualizar($id, array $dados) {
        $sql = "UPDATE tarefas SET 
                    titulo = :titulo,
                    descricao = :descricao,
                    prioridade = :prioridade,
                    data_entrega = :data_entrega,
                    responsavel_id = :responsavel_id
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $dados['titulo'], PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $dados['descricao'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':prioridade', $dados['prioridade'] ?? 'media', PDO::PARAM_STR);
        $stmt->bindValue(':data_entrega', !empty($dados['data_entrega']) ? $dados['data_entrega'] : null, PDO::PARAM_STR);
        $stmt->bindValue(':responsavel_id', !empty($dados['responsavel_id']) ? (int)$dados['responsavel_id'] : null, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function mover($id, $colunaId, $novaOrdem = null) {
        $sql = "UPDATE tarefas SET coluna_id = :coluna_id";
        if ($novaOrdem !== null) {
            $sql .= ", ordem = :ordem";
        }
        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':coluna_id', (int)$colunaId, PDO::PARAM_INT);
        if ($novaOrdem !== null) {
            $stmt->bindValue(':ordem', (int)$novaOrdem, PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    public function deletar($id) {
        $sql = "DELETE FROM tarefas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
