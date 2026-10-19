<?php
/**
 * Service Usuario
 * Regras de negócio e validação para cadastro e gestão de usuários (Restrito para ADM)
 */

require_once __DIR__ . '/../repositories/UsuarioRepository.php';
require_once __DIR__ . '/../helpers/Sanitizer.php';

class UsuarioService {
    private $usuarioRepo;

    public function __construct() {
        $this->usuarioRepo = new UsuarioRepository();
    }

    public function listar() {
        return $this->usuarioRepo->listarTodos();
    }

    public function buscarPorId($id) {
        $usuario = $this->usuarioRepo->buscarPorId((int)$id);
        return $usuario ? $usuario->toArraySeguro() : null;
    }

    public function criar(array $dados) {
        $nome  = Sanitizer::texto($dados['nome'] ?? '', 100);
        $email = Sanitizer::email($dados['email'] ?? '');
        $cargo = Sanitizer::texto($dados['cargo'] ?? 'Colaborador', 100);
        $role  = Sanitizer::enum($dados['role'] ?? 'funcionario', ['admin', 'funcionario'], 'funcionario');
        $ativo = isset($dados['ativo']) ? (int)$dados['ativo'] : 1;
        $senha = trim((string)($dados['senha'] ?? ''));

        if (empty($nome)) {
            return ['sucesso' => false, 'mensagem' => 'O nome do usuário é obrigatório.'];
        }

        if (!$email) {
            return ['sucesso' => false, 'mensagem' => 'Informe um endereço de e-mail válido.'];
        }

        if (mb_strlen($senha) < 6) {
            return ['sucesso' => false, 'mensagem' => 'A senha deve conter no mínimo 6 caracteres.'];
        }

        if ($this->usuarioRepo->emailExiste($email)) {
            return ['sucesso' => false, 'mensagem' => 'Já existe um usuário cadastrado com este e-mail.'];
        }

        // Criptografia segura da senha com BCRYPT
        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

        $novoId = $this->usuarioRepo->criar([
            'nome'  => $nome,
            'email' => $email,
            'senha' => $hashSenha,
            'cargo' => $cargo,
            'role'  => $role,
            'ativo' => $ativo
        ]);

        return [
            'sucesso' => true,
            'mensagem' => 'Usuário cadastrado com sucesso!',
            'dados' => [
                'id'    => $novoId,
                'nome'  => $nome,
                'email' => $email,
                'cargo' => $cargo,
                'role'  => $role,
                'ativo' => $ativo
            ]
        ];
    }

    public function atualizar($id, array $dados) {
        $id = (int)$id;
        $usuarioExistente = $this->usuarioRepo->buscarPorId($id);
        if (!$usuarioExistente) {
            return ['sucesso' => false, 'mensagem' => 'Usuário não encontrado.'];
        }

        $nome  = Sanitizer::texto($dados['nome'] ?? $usuarioExistente->nome, 100);
        $email = Sanitizer::email($dados['email'] ?? $usuarioExistente->email);
        $cargo = Sanitizer::texto($dados['cargo'] ?? $usuarioExistente->cargo, 100);
        $role  = Sanitizer::enum($dados['role'] ?? $usuarioExistente->role, ['admin', 'funcionario'], $usuarioExistente->role);
        $ativo = isset($dados['ativo']) ? (int)$dados['ativo'] : $usuarioExistente->ativo;

        if (empty($nome)) {
            return ['sucesso' => false, 'mensagem' => 'O nome não pode ficar vazio.'];
        }

        if (!$email) {
            return ['sucesso' => false, 'mensagem' => 'E-mail informado é inválido.'];
        }

        if ($this->usuarioRepo->emailExiste($email, $id)) {
            return ['sucesso' => false, 'mensagem' => 'Outro usuário já está utilizando este e-mail.'];
        }

        $dadosAtualizacao = [
            'nome'  => $nome,
            'email' => $email,
            'cargo' => $cargo,
            'role'  => $role,
            'ativo' => $ativo,
        ];

        // Se informou nova senha, atualiza
        if (!empty($dados['senha'])) {
            if (mb_strlen($dados['senha']) < 6) {
                return ['sucesso' => false, 'mensagem' => 'A nova senha deve ter no mínimo 6 caracteres.'];
            }
            $dadosAtualizacao['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }

        $this->usuarioRepo->atualizar($id, $dadosAtualizacao);

        return [
            'sucesso' => true,
            'mensagem' => 'Dados do usuário atualizados com sucesso!'
        ];
    }

    public function deletar($id, $adminAtualId) {
        $id = (int)$id;
        if ($id === (int)$adminAtualId) {
            return ['sucesso' => false, 'mensagem' => 'Você não pode excluir o seu próprio usuário enquanto estiver conectado.'];
        }

        $usuario = $this->usuarioRepo->buscarPorId($id);
        if (!$usuario) {
            return ['sucesso' => false, 'mensagem' => 'Usuário não encontrado para exclusão.'];
        }

        $this->usuarioRepo->deletar($id);
        return ['sucesso' => true, 'mensagem' => 'Usuário removido com sucesso.'];
    }
}
