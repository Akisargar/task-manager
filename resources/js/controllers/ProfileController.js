export default ['$scope', '$window', '$rootScope', 'ApiService', function($scope, $window, $rootScope, ApiService) {
    
    $scope.loading = false;
    $scope.saving = false;
    $scope.successMessage = null;
    $scope.errorMessage = null;
    $scope.departments = ['Finance', 'HR', 'IT', 'Operation'];

    $scope.profileForm = {
        name: '',
        email: '',
        role: '',
        department: '',
        years_of_experience: 0,
        location: '',
        active_task_count: 0
    };

    $scope.loadProfile = function() {
        $scope.loading = true;
        const cachedUser = $window.localStorage.getItem('user');
        if (cachedUser) {
            try { $scope.setFormData(JSON.parse(cachedUser)); } catch (e) {}
        }

        ApiService.getProfile().then(function(res) {
            const user = res.data.data;
            if (user) {
                $scope.setFormData(user);
                $window.localStorage.setItem('user', JSON.stringify(user));
                $rootScope.$broadcast('user:updated', user);
            }
        }).catch(function(err) {
            $scope.errorMessage = err.data?.message || 'Failed to fetch profile.';
        }).finally(function() {
            $scope.loading = false;
        });
    };

    $scope.setFormData = function(u) {
        $scope.profileForm = {
            id: u.id,
            name: u.name || '',
            email: u.email || '',
            role: u.role || 'user',
            department: u.department || '',
            years_of_experience: parseInt(u.years_of_experience) || 0,
            location: u.location || '',
            active_task_count: u.active_task_count || 0
        };
    };

    $scope.saveProfile = function() {
        $scope.saving = true;
        $scope.successMessage = null;
        $scope.errorMessage = null;

        const data = {
            name: $scope.profileForm.name,
            department: $scope.profileForm.department || null,
            years_of_experience: parseInt($scope.profileForm.years_of_experience) || 0,
            location: $scope.profileForm.location || null
        };

        ApiService.updateProfile(data).then(function(res) {
            const user = res.data.data;
            $scope.setFormData(user);
            $window.localStorage.setItem('user', JSON.stringify(user));
            $rootScope.$broadcast('user:updated', user);
            $scope.successMessage = 'Profile updated successfully.';
        }).catch(function(err) {
            $scope.errorMessage = err.data?.errors ? Object.values(err.data.errors).flat().join(' ') : (err.data?.message || 'Update failed.');
        }).finally(function() {
            $scope.saving = false;
        });
    };

    $scope.goToTasks = function() {
        $window.location.href = '/tasks';
    };

    $scope.loadProfile();
}];
