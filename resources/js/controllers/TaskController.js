export default ['$scope', '$timeout', 'ApiService', function ($scope, $timeout, ApiService) {

    $scope.tasks = [];
    $scope.myTasks = [];
    $scope.meta = {};
    $scope.loading = false;

    $scope.filters = {
        status: '',
        priority: '',
        assignment_status: '',
        page: 1,
        per_page: 12
    };

    // Modal state
    $scope.isTaskModalOpen = false;
    $scope.taskModalMode = 'create'; // 'create' or 'edit'
    $scope.taskForm = {
        id: null,
        title: '',
        description: '',
        priority: 'medium',
        status: 'todo',
        rules: []
    };
    $scope.taskError = null;
    $scope.taskLoading = false;

    // Rule fields and operators
    $scope.ruleFields = ['department', 'years_of_experience', 'location', 'active_task_count', 'role'];
    $scope.ruleOperators = ['=', '!=', '>', '>=', '<', '<=', 'in', 'not_in'];

    $scope.formatRuleField = function(field) {
        if (!field) return '';
        const map = {
            'department': 'Department',
            'years_of_experience': 'Experience',
            'location': 'Location',
            'active_task_count': 'Active Tasks',
            'role': 'Role'
        };
        return map[field] || field.replace(/_/g, ' ');
    };

    $scope.formatDate = function(dateStr) {
        if (!dateStr) return null;
        const clean = ('' + dateStr).split('T')[0];
        const parts = clean.split('-');
        if (parts.length === 3) {
            const year = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10) - 1;
            const day = parseInt(parts[2], 10);
            if (!isNaN(year) && !isNaN(month) && !isNaN(day)) {
                const d = new Date(year, month, day);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            }
        }
        return clean;
    };

    $scope.onFilterChange = function() {
        $scope.filters.page = 1;
        $scope.loadTasks();
    };

    $scope.loadTasks = function () {
        if (!$scope.isAuthenticated()) return;

        $scope.loading = true;

        if ($scope.isAdmin()) {
            const cleanFilters = {};
            for (const [key, value] of Object.entries($scope.filters)) {
                if (value !== '' && value !== null && value !== undefined) {
                    cleanFilters[key] = value;
                }
            }

            ApiService.getTasks(cleanFilters).then(function (response) {
                $scope.tasks = response.data.data;
                $scope.meta = {
                    current_page: response.data.meta.current_page,
                    last_page: response.data.meta.last_page,
                    per_page: response.data.meta.per_page,
                    total: response.data.meta.total
                };
            }).catch(function (error) {
                console.error('Failed to load tasks', error);
            }).finally(function () {
                $scope.loading = false;
            });
        } else {
            // Normal user loads my-eligible-tasks
            $scope.loadMyTasks();
        }
    };

    $scope.loadMyTasks = function () {
        ApiService.getMyTasks().then(function (response) {
            $scope.myTasks = response.data.data;
        }).catch(function (error) {
            console.error('Failed to load my tasks', error);
        }).finally(function () {
            $scope.loading = false;
        });
    };

    $scope.changePage = function (page) {
        if (page > 0 && page <= $scope.meta.last_page && page !== $scope.meta.current_page) {
            $scope.filters.page = page;
            $scope.loadTasks();
        }
    };

    $scope.getPages = function () {
        if (!$scope.meta || !$scope.meta.last_page) return [];
        const pages = [];
        const current = $scope.meta.current_page || 1;
        const total = $scope.meta.last_page;
        
        let start = Math.max(1, current - 2);
        let end = Math.min(total, current + 2);

        if (end - start < 4) {
            if (start === 1) {
                end = Math.min(total, start + 4);
            } else if (end === total) {
                start = Math.max(1, end - 4);
            }
        }

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        return pages;
    };

    $scope.getFromTaskIndex = function () {
        if (!$scope.meta || !$scope.meta.total || $scope.tasks.length === 0) return 0;
        return (($scope.meta.current_page || 1) - 1) * ($scope.meta.per_page || 12) + 1;
    };

    $scope.getToTaskIndex = function () {
        if (!$scope.meta || !$scope.meta.total) return $scope.tasks.length;
        const to = ($scope.meta.current_page || 1) * ($scope.meta.per_page || 12);
        return Math.min(to, $scope.meta.total);
    };

    // Delete Modal state
    $scope.isDeleteModalOpen = false;
    $scope.taskToDelete = null;
    $scope.deleteError = null;
    $scope.deleteLoading = false;

    $scope.confirmDelete = function (task) {
        $scope.taskToDelete = task;
        $scope.deleteError = null;
        $scope.isDeleteModalOpen = true;
    };

    $scope.closeDeleteModal = function () {
        $scope.isDeleteModalOpen = false;
        $scope.taskToDelete = null;
    };

    $scope.deleteTask = function () {
        if (!$scope.taskToDelete) return;

        $scope.deleteLoading = true;
        $scope.deleteError = null;

        ApiService.deleteTask($scope.taskToDelete.id).then(function () {
            $scope.isDeleteModalOpen = false;
            $scope.taskToDelete = null;
            $scope.loadTasks();
        }).catch(function (error) {
            $scope.deleteError = error.data?.message || 'Failed to delete task';
        }).finally(function () {
            $scope.deleteLoading = false;
        });
    };

    $scope.showCreateTaskModal = function () {
        $scope.taskModalMode = 'create';
        $scope.taskForm = {
            id: null,
            title: '',
            description: '',
            priority: 'medium',
            status: 'todo',
            due_date: '',
            rules: []
        };
        $scope.taskError = null;
        $scope.isTaskModalOpen = true;
    };

    $scope.showEditTaskModal = function (task) {
        $scope.taskModalMode = 'edit';
        let editDueDate = null;
        if (task.due_date) {
            const dateStr = ('' + task.due_date).split('T')[0];
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                editDueDate = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            }
        }

        $scope.taskForm = {
            id: task.id,
            title: task.title,
            description: task.description,
            priority: task.priority,
            status: task.status,
            due_date: editDueDate,
            rules: task.rules ? angular.copy(task.rules) : []
        };
        $scope.taskError = null;
        $scope.isTaskModalOpen = true;
    };

    $scope.closeTaskModal = function () {
        $scope.isTaskModalOpen = false;
    };

    $scope.addRule = function () {
        $scope.taskForm.rules.push({ field: 'department', operator: '=', value: '' });
    };

    $scope.removeRule = function (index) {
        $scope.taskForm.rules.splice(index, 1);
    };

    $scope.saveTask = function () {
        $scope.taskLoading = true;
        $scope.taskError = null;

        const payload = angular.copy($scope.taskForm);
        if ($scope.taskForm.due_date) {
            const raw = $scope.taskForm.due_date;
            if (raw instanceof Date && !isNaN(raw.getTime())) {
                const year = raw.getFullYear();
                const month = String(raw.getMonth() + 1).padStart(2, '0');
                const day = String(raw.getDate()).padStart(2, '0');
                payload.due_date = `${year}-${month}-${day}`;
            } else if (typeof raw === 'string' && raw.trim() !== '') {
                payload.due_date = raw.split('T')[0];
            } else {
                payload.due_date = null;
            }
        } else {
            payload.due_date = null;
        }

        const request = $scope.taskModalMode === 'create'
            ? ApiService.createTask(payload)
            : ApiService.updateTask(payload.id, payload);

        request.then(function () {
            $scope.isTaskModalOpen = false;
            $scope.loadTasks();
        }).catch(function (error) {
            if (error.data?.errors) {
                $scope.taskError = Object.values(error.data.errors).flat().join(' ');
            } else {
                $scope.taskError = error.data?.message || 'Failed to save task';
            }
        }).finally(function () {
            $scope.taskLoading = false;
        });
    };

    $scope.updateTaskStatus = function (task, newStatus) {
        ApiService.updateTaskStatus(task.id, { status: newStatus }).then(function () {
            task.status = newStatus;
        }).catch(function (error) {
            alert('Failed to update status');
        });
    };

    // Eligible Users Modal
    $scope.isEligibleUsersModalOpen = false;
    $scope.eligibleUsers = [];
    $scope.eligibleTask = null;
    $scope.eligibleLoading = false;

    $scope.viewEligibleUsers = function(task) {
        $scope.eligibleTask = task;
        $scope.eligibleUsers = [];
        $scope.eligibleLoading = true;
        $scope.isEligibleUsersModalOpen = true;

        ApiService.getEligibleUsers(task.id).then(function(response) {
            $scope.eligibleUsers = response.data.data;
        }).catch(function(error) {
            console.error('Failed to load eligible users', error);
        }).finally(function() {
            $scope.eligibleLoading = false;
        });
    };

    $scope.closeEligibleUsersModal = function() {
        $scope.isEligibleUsersModalOpen = false;
        $scope.eligibleTask = null;
        $scope.eligibleUsers = [];
    };

    // Recompute eligibility
    $scope.recomputeLoading = false;
    $scope.recomputeNotice = null;
    $scope.recomputeAll = function() {
        $scope.recomputeLoading = true;
        $scope.recomputeNotice = null;
        ApiService.recomputeEligibility().then(function(res) {
            $scope.recomputeNotice = (res.data && res.data.message) ? res.data.message : 'Recomputation dispatched! Tasks are updating.';
            $scope.loadTasks();
            $timeout(function() {
                $scope.recomputeNotice = null;
            }, 4000);
        }).catch(function(err) {
            console.error('Failed to trigger recomputation', err);
            const msg = (err.data && err.data.message) ? err.data.message : 'Failed to trigger recomputation';
            alert(msg);
        }).finally(function() {
            $scope.recomputeLoading = false;
        });
    };

    // Assign Unassigned Tasks
    $scope.assignUnassignedLoading = false;
    $scope.assignUnassignedTasks = function() {
        $scope.assignUnassignedLoading = true;
        $scope.recomputeNotice = null;
        ApiService.assignUnassignedTasks().then(function(res) {
            const msg = (res.data && res.data.message) ? res.data.message : 'Unassigned tasks evaluated and assigned.';
            $scope.recomputeNotice = msg;
            $scope.loadTasks();
            $timeout(function() {
                $scope.recomputeNotice = null;
            }, 5000);
        }).catch(function(err) {
            console.error('Failed to assign unassigned tasks', err);
            const msg = (err.data && err.data.message) ? err.data.message : 'Failed to assign unassigned tasks';
            alert(msg);
        }).finally(function() {
            $scope.assignUnassignedLoading = false;
        });
    };

    $scope.$on('user:loggedIn', function () {
        $scope.filters.page = 1;
        $scope.loadTasks();
    });
}];
