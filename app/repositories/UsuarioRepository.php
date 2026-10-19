<?php
/**
 * Repository Usuario
 * Executa consultas seguras com PDO e Prepared Statements (Anti SQL Injection)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioRepository {
    private $db;

    public function __construct() {
        $this->db = getDatabaseConnection();
    }

    public function buscarPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $linha = $stmt->fetch();
        return $linha ? new Usuario($linha) : null;
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $linha = $stmt->fetch();
        return $linha ? new Usuario($linha) : null;
    }

    public function listarTodos() {
        $sql = "SELECT id, nome, email, cargo, role, ativo, criado_em, atualizado_em FROM usuarios ORDER BY nome ASC";
        $stmt = $this->db->query($sql);
        $usuarios = [];
        while ($linha = $stmt->fetch()) {
            $usuarios[] = (new Usuario($linha))->toArraySeguro();
        }
        return $usuarios;
    }

    public function criar(array $dados) {
        $sql = "INSERT INTO usuarios (nome, email, senha, cargo, role, ativo) 
                VALUES (:nome, :email, :senha, :cargo, :role, :ativo)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', $dados['nome'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $dados['email'], PDO::PARAM_STR);
        $stmt->bindValue(':senha', $dados['senha'], PDO::PARAM_STR);
        $stmt->bindValue(':cargo', $dados['cargo'] ?? 'Colaborador', PDO::PARAM_STR);
        $stmt->bindValue(':role', $dados['role'] ?? 'funcionario', PDO::PARAM_STR);
        $stmt->bindValue(':ativo', isset($dados['ativo']) ? (int)$dados['ativo'] : 1, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    public function atualizar($id, array $dados) {
        $campos = ["nome = :nome", "email = :email", "cargo = :cargo", "role = :role", "ativo = :ativo"];
        if (!empty($dados['senha'])) {
            $campos[] = "senha = :senha";
        }

        $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $dados['nome'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $dados['email'], PDO::PARAM_STR);
        $stmt->bindValue(':cargo', $dados['cargo'], PDO::PARAM_STR);
        $stmt->bindValue(':role', $dados['role'], PDO::PARAM_STR);
        $stmt->bindValue(':ativo', (int)$dados['ativo'], PDO::PARAM_INT);
        if (!empty($dados['senha'])) {
            $stmt->bindValue(':senha', $dados['senha'], PDO::PARAM_STR);
        }
        return $stmt->execute();
    }

    public function deletar($id) {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function emailExiste($email, $ignorarId = null) {
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        if ($ignorarId) {
            $sql .= " AND id != :ignorarId";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        if ($ignorarId) {
            $stmt->bindValue(':ignorarId', (int)$ignorarId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (bool)$stmt->fetch();
    }
}
