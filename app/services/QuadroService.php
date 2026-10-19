<?php
/**
 * Service Quadro
 */

require_once __DIR__ . '/../repositories/QuadroRepository.php';
require_once __DIR__ . '/../repositories/ColunaRepository.php';
require_once __DIR__ . '/../helpers/Sanitizer.php';

class QuadroService {
    private $quadroRepo;

    public function __construct() {
        $this->quadroRepo = new QuadroRepository();
    }

    public function listar() {
        return $this->quadroRepo->listarTodos();
    }

    public function listarPaginado($pagina = 1, $limite = 5, $busca = '') {
        $pagina = max(1, (int)$pagina);
        $limite = max(1, min(50, (int)$limite));
        $offset = ($pagina - 1) * $limite;
        $total = $this->quadroRepo->contarTotal($busca);
        $itens = $this->quadroRepo->listarPaginado($offset, $limite, $busca);
        $totalPaginas = max(1, ceil($total / $limite));

        return [
            'quadros' => $itens,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite,
            'total_paginas' => (int)$totalPaginas
        ];
    }

    public function buscarPorId($id) {
        $quadro = $this->quadroRepo->buscarPorId((int)$id);
        return $quadro ? $quadro->toArray() : null;
    }

    public function criar(array $dados, $usuarioId) {
        $titulo = Sanitizer::texto($dados['titulo'] ?? '', 120);
        $descricao = Sanitizer::texto($dados['descricao'] ?? '', 1000);

        if (empty($titulo)) {
            return ['sucesso' => false, 'mensagem' => 'O título do quadro é obrigatório.'];
        }

        $novoId = $this->quadroRepo->criar([
            'titulo' => $titulo,
            'descricao' => $descricao,
            'criado_por' => (int)$usuarioId
        ]);

        // Criação automática das 5 colunas padrão para o novo quadro criado
        $colunaRepo = new ColunaRepository();
        $colunasPadrao = [
            ['titulo' => 'Backlog / Ideias', 'ordem' => 1, 'cor' => '#64748b'],
            ['titulo' => 'A Fazer',          'ordem' => 2, 'cor' => '#3b82f6'],
            ['titulo' => 'Em Andamento',     'ordem' => 3, 'cor' => '#f59e0b'],
            ['titulo' => 'Em Revisão',       'ordem' => 4, 'cor' => '#8b5cf6'],
            ['titulo' => 'Concluído',        'ordem' => 5, 'cor' => '#10b981'],
        ];

        foreach ($colunasPadrao as $cp) {
            $colunaRepo->criar([
                'quadro_id' => $novoId,
                'titulo'    => $cp['titulo'],
                'ordem'     => $cp['ordem'],
                'cor'       => $cp['cor']
            ]);
        }

        return [
            'sucesso' => true,
            'mensagem' => 'Quadro criado com sucesso e colunas padrão inicializadas!',
            'dados' => ['id' => $novoId, 'titulo' => $titulo, 'descricao' => $descricao]
        ];
    }

    public function atualizar($id, array $dados) {
        $id = (int)$id;
        $titulo = Sanitizer::texto($dados['titulo'] ?? '', 120);
        $descricao = Sanitizer::texto($dados['descricao'] ?? '', 1000);

        if (empty($titulo)) {
            return ['sucesso' => false, 'mensagem' => 'O título não pode ser vazio.'];
        }

        $this->quadroRepo->atualizar($id, [
            'titulo' => $titulo,
            'descricao' => $descricao
        ]);

        return ['sucesso' => true, 'mensagem' => 'Quadro atualizado com sucesso!'];
    }

    public function deletar($id) {
        $total = $this->quadroRepo->contarTotal();
        if ($total <= 1) {
            return ['sucesso' => false, 'mensagem' => 'Não é possível excluir o único quadro restante. É necessário manter ao menos 1 quadro no sistema.'];
        }

        $this->quadroRepo->deletar((int)$id);
        return ['sucesso' => true, 'mensagem' => 'Quadro removido com sucesso.'];
    }
}
