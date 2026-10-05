(() => {
  'use strict';

  window.EngelloDashboardState = {
    currentUser: null,
    boards: [],
    currentBoard: null,
    columns: [],
    tasks: [],
    users: [],
    activities: [],
    showActivity: true,
    newCardColumnId: null,
    selectedTaskId: null,
    draggingTaskId: null,

    reset() {
      this.currentUser = null;
      this.boards = [];
      this.currentBoard = null;
      this.columns = [];
      this.tasks = [];
      this.users = [];
      this.activities = [];
      this.showActivity = true;
      this.newCardColumnId = null;
      this.selectedTaskId = null;
      this.draggingTaskId = null;
    }
  };
})();
