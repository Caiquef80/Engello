/**
 * ENGELLO - Lógica Frontend do Kanban
 * Colaboração aberta: Todos os funcionários podem criar, editar,
 * mover e excluir qualquer tarefa.
 */

let idArrastando = null;

// ======================= DRAG AND DROP =======================

function permitirSoltar(ev) {
    ev.preventDefault();
}

function iniciarArrasto(ev, tarefaId) {
    idArrastando = tarefaId;
    ev.dataTransfer.setData("text/plain", tarefaId);
}

function soltarTarefa(ev, colunaDestinoId) {
    ev.preventDefault();
    if (!idArrastando) return;

    // Enviar requisição direta para a API
    fetch('../api/tarefas.php?acao=mover', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id: parseInt(idArrastando),
            coluna_id: parseInt(colunaDestinoId)
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            // Mover elemento no DOM
            const cardElement = document.getElementById(`task-card-${idArrastando}`);
            const colunaCards = document.getElementById(`coluna-cards-${colunaDestinoId}`);
            if (cardElement && colunaCards) {
                colunaCards.appendChild(cardElement);
            }
            window.location.reload(); // Recarrega para sincronizar contadores
        } else {
            alert(data.mensagem || 'Erro ao mover tarefa.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro de comunicação com a API.');
    })
    .finally(() => {
        idArrastando = null;
    });
}

// ======================= MODAIS =======================

function fecharModais() {
    const ids = ['modalNovaTarefa', 'modalEditarTarefa', 'modalNovoQuadro', 'modalEditarQuadro'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') fecharModais();
});

document.addEventListener('click', function(e) {
    if (e.target && e.target.classList && e.target.classList.contains('modal-overlay')) {
        fecharModais();
    }
});

function abrirModalNovaTarefa(colunaIdPadrao = null) {
    if (colunaIdPadrao) {
        document.getElementById('nova_coluna_id').value = colunaIdPadrao;
    }
    document.getElementById('nova_titulo').value = '';
    document.getElementById('nova_descricao').value = '';
    document.getElementById('nova_data_entrega').value = '';
    document.getElementById('nova_prioridade').value = 'media';
    document.getElementById('modalNovaTarefa').classList.remove('hidden');
}

function salvarNovaTarefa(ev) {
    ev.preventDefault();

    const quadroIdInput = document.getElementById('nova_quadro_id');
    const quadroId = quadroIdInput ? parseInt(quadroIdInput.value) : 1;

    const payload = {
        quadro_id: quadroId,
        coluna_id: parseInt(document.getElementById('nova_coluna_id').value),
        titulo: document.getElementById('nova_titulo').value.trim(),
        descricao: document.getElementById('nova_descricao').value.trim(),
        prioridade: document.getElementById('nova_prioridade').value,
        responsavel_id: document.getElementById('nova_responsavel_id').value || null,
        data_entrega: document.getElementById('nova_data_entrega').value || null
    };

    fetch('../api/tarefas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            window.location.reload();
        } else {
            alert(data.mensagem || 'Erro ao criar tarefa.');
        }
    })
    .catch(err => {
        alert('Falha na comunicação com o servidor: ' + err.message);
    });
}

function abrirModalEditarTarefa(tarefa) {
    document.getElementById('edit_id').value = tarefa.id;
    document.getElementById('edit_titulo').value = tarefa.titulo;
    document.getElementById('edit_descricao').value = tarefa.descricao || '';
    document.getElementById('edit_coluna_id').value = tarefa.coluna_id;
    document.getElementById('edit_prioridade').value = tarefa.prioridade;
    document.getElementById('edit_responsavel_id').value = tarefa.responsavel_id || '';
    document.getElementById('edit_data_entrega').value = tarefa.data_entrega || '';

    // Carregar itens do checklist
    carregarSubtarefas(tarefa.id);

    document.getElementById('modalEditarTarefa').classList.remove('hidden');
}

// ======================= CHECKLIST / SUBTAREFAS =======================

function carregarSubtarefas(tarefaId) {
    const container = document.getElementById('checklist_itens_container');
    const labelProgresso = document.getElementById('checklist_progresso_label');
    const barraProgresso = document.getElementById('checklist_progresso_barra');

    container.innerHTML = '<div style="padding:10px;color:#94a3b8;font-size:12px;">Carregando checklist...</div>';

    fetch(`../api/subtarefas.php?tarefa_id=${tarefaId}`)
        .then(res => res.json())
        .then(res => {
            if (!res.sucesso) {
                container.innerHTML = '<div style="color:#ef4444;font-size:12px;">Não foi possível carregar o checklist.</div>';
                return;
            }

            const itens = res.dados || [];
            atualizarProgressoChecklist(itens);

            if (itens.length === 0) {
                container.innerHTML = '<div style="padding:8px 0;color:#94a3b8;font-size:12px;font-style:italic;">Nenhuma subtarefa adicionada ainda.</div>';
                return;
            }

            container.innerHTML = '';
            itens.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = `checklist-item ${item.concluida ? 'item-concluido' : ''}`;
                itemDiv.innerHTML = `
                    <label class="checklist-item-label">
                        <input type="checkbox" ${item.concluida ? 'checked' : ''} onchange="alternarSubtarefa(${item.id}, ${tarefaId})">
                        <span class="checklist-item-titulo">${item.titulo}</span>
                    </label>
                    <button type="button" class="btn-subtask-del" title="Excluir subtarefa" onclick="excluirSubtarefa(${item.id}, ${tarefaId})">
                        ×
                    </button>
                `;
                container.appendChild(itemDiv);
            });
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<div style="color:#ef4444;font-size:12px;">Erro ao carregar itens do checklist.</div>';
        });
}

function atualizarProgressoChecklist(itens) {
    const labelProgresso = document.getElementById('checklist_progresso_label');
    const barraProgresso = document.getElementById('checklist_progresso_barra');

    const total = itens.length;
    const concluidas = itens.filter(i => i.concluida == 1).length;
    const pct = total > 0 ? Math.round((concluidas / total) * 100) : 0;

    labelProgresso.textContent = `${concluidas}/${total} (${pct}%)`;
    barraProgresso.style.width = `${pct}%`;
}

function adicionarSubtarefa() {
    const tarefaId = parseInt(document.getElementById('edit_id').value);
    const input = document.getElementById('novo_subtask_titulo');
    const titulo = input.value.trim();

    if (!titulo) return;

    fetch('../api/subtarefas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            tarefa_id: tarefaId,
            titulo: titulo
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            input.value = '';
            carregarSubtarefas(tarefaId);
        } else {
            alert(data.mensagem || 'Erro ao adicionar item.');
        }
    })
    .catch(err => {
        alert('Erro ao comunicar com a API: ' + err.message);
    });
}

function alternarSubtarefa(subtarefaId, tarefaId) {
    fetch('../api/subtarefas.php?acao=toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: subtarefaId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            carregarSubtarefas(tarefaId);
        } else {
            alert(data.mensagem || 'Erro ao alterar status.');
        }
    })
    .catch(err => {
        alert('Erro ao atualizar subtarefa: ' + err.message);
    });
}

function excluirSubtarefa(subtarefaId, tarefaId) {
    fetch('../api/subtarefas.php?acao=deletar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: subtarefaId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            carregarSubtarefas(tarefaId);
        } else {
            alert(data.mensagem || 'Erro ao excluir subtarefa.');
        }
    })
    .catch(err => {
        alert('Erro ao excluir subtarefa: ' + err.message);
    });
}

function salvarEdicaoTarefa(ev) {
    ev.preventDefault();

    const id = parseInt(document.getElementById('edit_id').value);
    const payload = {
        id: id,
        titulo: document.getElementById('edit_titulo').value.trim(),
        descricao: document.getElementById('edit_descricao').value.trim(),
        prioridade: document.getElementById('edit_prioridade').value,
        responsavel_id: document.getElementById('edit_responsavel_id').value || null,
        data_entrega: document.getElementById('edit_data_entrega').value || null
    };

    const novaColunaId = parseInt(document.getElementById('edit_coluna_id').value);

    // Salvar atualização dos dados
    fetch('../api/tarefas.php?acao=atualizar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (!data.sucesso) {
            throw new Error(data.mensagem);
        }

        // Mover para coluna se mudou
        return fetch('../api/tarefas.php?acao=mover', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, coluna_id: novaColunaId })
        });
    })
    .then(() => {
        window.location.reload();
    })
    .catch(err => {
        alert('Erro ao atualizar tarefa: ' + err.message);
    });
}

function excluirTarefa() {
    const id = parseInt(document.getElementById('edit_id').value);
    const titulo = document.getElementById('edit_titulo').value;

    if (!confirm(`Deseja realmente excluir a tarefa "${titulo}"?\n(Esta ação é permitida para todos os funcionários e não pode ser desfeita).`)) {
        return;
    }

    fetch('../api/tarefas.php?acao=deletar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            window.location.reload();
        } else {
            alert(data.mensagem || 'Erro ao excluir tarefa.');
        }
    })
    .catch(err => {
        alert('Erro ao excluir tarefa: ' + err.message);
    });
}

// ======================= GESTÃO DE QUADROS =======================

function abrirModalNovoQuadro() {
    document.getElementById('novo_quadro_titulo').value = '';
    document.getElementById('novo_quadro_descricao').value = '';
    document.getElementById('modalNovoQuadro').classList.remove('hidden');
}

function abrirModalEditarQuadro(id, titulo, descricao) {
    document.getElementById('edit_quadro_id').value = id;
    document.getElementById('edit_quadro_titulo').value = titulo;
    document.getElementById('edit_quadro_descricao').value = descricao || '';
    document.getElementById('modalEditarQuadro').classList.remove('hidden');
}

function salvarNovoQuadro(ev) {
    ev.preventDefault();

    const titulo = document.getElementById('novo_quadro_titulo').value.trim();
    const descricao = document.getElementById('novo_quadro_descricao').value.trim();

    if (!titulo) return;

    fetch('../api/quadros.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ titulo, descricao })
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            const novoId = data.dados ? data.dados.id : null;
            if (novoId) {
                window.location.href = `dashboard.php?quadro_id=${novoId}&sucesso=${encodeURIComponent('Quadro criado com sucesso!')}`;
            } else {
                window.location.reload();
            }
        } else {
            alert(data.mensagem || 'Erro ao criar quadro.');
        }
    })
    .catch(err => {
        alert('Erro ao comunicar com a API: ' + err.message);
    });
}

function salvarEdicaoQuadro(ev) {
    ev.preventDefault();

    const id = parseInt(document.getElementById('edit_quadro_id').value);
    const titulo = document.getElementById('edit_quadro_titulo').value.trim();
    const descricao = document.getElementById('edit_quadro_descricao').value.trim();

    if (!titulo) return;

    fetch('../api/quadros.php?acao=atualizar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, titulo, descricao })
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            window.location.reload();
        } else {
            alert(data.mensagem || 'Erro ao atualizar quadro.');
        }
    })
    .catch(err => {
        alert('Erro ao comunicar com a API: ' + err.message);
    });
}

function excluirQuadro(id, titulo) {
    if (!confirm(`Deseja realmente excluir o quadro "${titulo}"?\nTodas as colunas e tarefas deste quadro serão excluídas.`)) {
        return;
    }

    fetch('../api/quadros.php?acao=deletar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.sucesso) {
            window.location.href = 'dashboard.php?sucesso=' + encodeURIComponent('Quadro excluído com sucesso!');
        } else {
            alert(data.mensagem || 'Erro ao excluir quadro.');
        }
    })
    .catch(err => {
        alert('Erro ao excluir quadro: ' + err.message);
    });
}
