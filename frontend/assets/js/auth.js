(() => {
  'use strict';

  const API = window.FlowBoardAPI;

  window.EngelloAuth = Object.freeze({
    isAuthenticated() {
      return Boolean(API.getSession()?.uuid);
    },

    async requireAuthentication() {
      if (!this.isAuthenticated()) {
        window.location.href = 'login.php';
        return null;
      }

      try {
        return await API.me();
      } catch {
        API.clearSession();
        window.location.href = 'login.php';
        return null;
      }
    },

    logout() {
      API.logoutLocal();
      window.location.href = 'login.php';
    }
  });
})();
