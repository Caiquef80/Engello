/**
 * ENGELLO - Lógica Frontend do Painel de Usuários (ADM)
 */

document.addEventListener('DOMContentLoaded', () => {
    const formNovo = document.getElementById('formNovoUsuario');
    if (formNovo) {
        formNovo.addEventListener('submit', (e) => {
            const senha = document.getElementById('senha').value;
            if (senha.length < 6) {
                e.preventDefault();
                alert('A senha inicial deve ter no mínimo 6 caracteres.');
            }
        });
    }
});
