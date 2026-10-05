document.addEventListener('DOMContentLoaded', () => {
  const API = window.FlowBoardAPI;
  const form = document.getElementById('login-form');
  const email = document.getElementById('email');
  const password = document.getElementById('password');
  const error = document.getElementById('login-error');
  const submit = document.getElementById('login-submit');
  const toggle = document.getElementById('toggle-password');

  if (API.getSession()?.uuid) {
    window.location.href = 'dashboard.php';
    return;
  }

  toggle?.addEventListener('click', () => {
    const visible = password.type === 'text';
    password.type = visible ? 'password' : 'text';
    toggle.textContent = visible ? '◉' : '◌';
    toggle.setAttribute('aria-label', visible ? 'Mostrar senha' : 'Ocultar senha');
  });

  form.addEventListener('submit', async event => {
    event.preventDefault();
    hideError();

    const emailValue = email.value.trim();
    const senhaValue = password.value;

    if (!emailValue || !senhaValue) {
      showError('Preencha o e-mail e a senha.');
      return;
    }

    setLoading(true);

    try {
      await API.login(emailValue, senhaValue);
      window.location.href = 'dashboard.php';
    } catch (err) {
      showError(err.message || 'Não foi possível entrar.');
    } finally {
      setLoading(false);
    }
  });

  function setLoading(loading) {
    submit.disabled = loading;
    submit.textContent = loading ? 'Entrando…' : 'Entrar';
  }

  function showError(message) {
    error.textContent = message;
    error.classList.remove('hidden');
  }

  function hideError() {
    error.classList.add('hidden');
    error.textContent = '';
  }
});
