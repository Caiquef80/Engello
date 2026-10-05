window.FlowBoardTheme = (() => {
  const key = 'flowboard-theme';

  function mode() {
    return localStorage.getItem(key) || 'light';
  }

  function apply(next) {
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem(key, next);
    updateButtons();
  }

  function toggle() {
    apply(mode() === 'light' ? 'dark' : 'light');
  }

  function updateButtons() {
    const current = mode();
    document.querySelectorAll('#theme-toggle').forEach(button => {
      button.innerHTML = current === 'light' ? '☾ <span>Escuro</span>' : '☀ <span>Claro</span>';
      button.title = current === 'light' ? 'Ativar modo escuro' : 'Ativar modo claro';
    });
  }

  document.documentElement.setAttribute('data-theme', mode());
  document.addEventListener('DOMContentLoaded', () => {
    updateButtons();
    document.querySelectorAll('#theme-toggle').forEach(button => button.addEventListener('click', toggle));
  });

  return { mode, apply, toggle };
})();
