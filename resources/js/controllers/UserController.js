export default ['$scope', '$timeout', 'ApiService', function ($scope, $timeout, ApiService) {
    
    $scope.users = [];
    $scope.meta = {};
    $scope.loading = false;
    $scope.saving = false;
    $scope.loadingDetails = false;
    $scope.selectedUser = null;
    $scope.showCreateModal = false;
    $scope.showEditModal = false;
    $scope.errorMessage = '';
    $scope.successMessage = '';

    $scope.departments = ['Finance', 'HR', 'IT', 'Operation'];
    $scope.roles = ['admin', 'manager', 'user'];

    $scope.filters = {
        department: '',
        role: '',
        search: '',
        page: 1,
        per_page: 12
    };

    $scope.newUser = {
        name: '',
        email: '',
        password: '',
        role: 'user',
        department: 'IT',
        years_of_experience: 0,
        location: ''
    };

    $scope.editUserData = {};

    function flashSuccess(msg) {
        $scope.successMessage = msg;
        $timeout(function () {
            $scope.successMessage = '';
        }, 4000);
    }

    function flashError(msg) {
        $scope.errorMessage = msg;
        $timeout(function () {
            $scope.errorMessage = '';
        }, 5000);
    }

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

    $scope.loadUsers = function () {
        if (!$scope.isAuthenticated() || !$scope.isAdmin()) return;

        $scope.loading = true;

        const cleanFilters = {};
        for (const [key, value] of Object.entries($scope.filters)) {
            if (value !== '' && value !== null && value !== undefined) {
                cleanFilters[key] = value;
            }
        }

        ApiService.getUsers(cleanFilters).then(function (res) {
            $scope.users = res.data.data;
            $scope.meta = res.data.meta || {};
        }).catch(function (err) {
            console.error('Failed to load users', err);
            flashError('Failed to load users list');
        }).finally(function () {
            $scope.loading = false;
        });
    };

    $scope.onFilterChange = function () {
        $scope.filters.page = 1;
        $scope.loadUsers();
    };

    $scope.changePage = function (page) {
        if (page > 0 && page <= $scope.meta.last_page && page !== $scope.meta.current_page) {
            $scope.filters.page = page;
            $scope.loadUsers();
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

    $scope.getFromIndex = function () {
        if (!$scope.meta || !$scope.meta.total || $scope.users.length === 0) return 0;
        return (($scope.meta.current_page || 1) - 1) * ($scope.meta.per_page || 12) + 1;
    };

    $scope.getToIndex = function () {
        if (!$scope.meta || !$scope.meta.total) return $scope.users.length;
        const to = ($scope.meta.current_page || 1) * ($scope.meta.per_page || 12);
        return Math.min(to, $scope.meta.total);
    };

    $scope.resetFilters = function () {
        $scope.filters.department = '';
        $scope.filters.role = '';
        $scope.filters.search = '';
        $scope.onFilterChange();
    };

    // User Details Modal
    $scope.viewDetails = function (user) {
        $scope.selectedUser = angular.copy(user);
        $scope.loadingDetails = true;

        ApiService.getUser(user.id).then(function (res) {
            $scope.selectedUser = res.data.data;
        }).catch(function (err) {
            console.error('Failed to load user full details', err);
        }).finally(function () {
            $scope.loadingDetails = false;
        });
    };

    $scope.closeDetailsModal = function () {
        $scope.selectedUser = null;
    };

    // Create User Modal
    $scope.openCreateModal = function () {
        $scope.newUser = {
            name: '',
            email: '',
            password: '',
            role: 'user',
            department: 'IT',
            years_of_experience: 1,
            location: ''
        };
        $scope.errorMessage = '';
        $scope.showCreateModal = true;
    };

    $scope.closeCreateModal = function () {
        $scope.showCreateModal = false;
    };

    $scope.submitCreateUser = function () {
        if (!$scope.newUser.name || !$scope.newUser.email || !$scope.newUser.password) {
            flashError('Name, email and password are required');
            return;
        }

        $scope.saving = true;
        ApiService.createUser($scope.newUser).then(function (res) {
            flashSuccess('User created successfully');
            $scope.closeCreateModal();
            $scope.loadUsers();
        }).catch(function (err) {
            const msg = (err.data && err.data.message) ? err.data.message : 'Failed to create user';
            flashError(msg);
        }).finally(function () {
            $scope.saving = false;
        });
    };

    // Edit User Modal
    $scope.openEditModal = function (user) {
        $scope.editUserData = {
            id: user.id,
            name: user.name,
            role: user.role,
            department: user.department,
            years_of_experience: user.years_of_experience || 0,
            location: user.location || ''
        };
        $scope.showEditModal = true;
    };

    $scope.closeEditModal = function () {
        $scope.showEditModal = false;
    };

    $scope.submitEditUser = function () {
        $scope.saving = true;
        ApiService.updateUser($scope.editUserData.id, $scope.editUserData).then(function (res) {
            flashSuccess('User updated successfully');
            $scope.closeEditModal();
            $scope.loadUsers();
            if ($scope.selectedUser && $scope.selectedUser.id === $scope.editUserData.id) {
                $scope.selectedUser = res.data.data;
            }
        }).catch(function (err) {
            const msg = (err.data && err.data.message) ? err.data.message : 'Failed to update user';
            flashError(msg);
        }).finally(function () {
            $scope.saving = false;
        });
    };

    // Delete User
    $scope.deleteUser = function (user) {
        if (!confirm(`Are you sure you want to delete user "${user.name}"?`)) return;

        ApiService.deleteUser(user.id).then(function () {
            flashSuccess('User deleted successfully');
            if ($scope.selectedUser && $scope.selectedUser.id === user.id) {
                $scope.closeDetailsModal();
            }
            $scope.loadUsers();
        }).catch(function (err) {
            const msg = (err.data && err.data.message) ? err.data.message : 'Failed to delete user';
            flashError(msg);
        });
    };

}];
