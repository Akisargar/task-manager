export default ['$scope', '$window', '$rootScope', 'ApiService', function($scope, $window, $rootScope, ApiService) {
    
    $scope.isLoginMode = true;
    
    $scope.credentials = {
        email: '',
        password: ''
    };

    $scope.registerData = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'user',
        department: '',
        years_of_experience: 0,
        location: ''
    };

    $scope.loading = false;
    $scope.error = null;

    $scope.toggleMode = function() {
        $scope.isLoginMode = !$scope.isLoginMode;
        $scope.error = null;
    };

    $scope.login = function() {
        if (!$scope.credentials.email || !$scope.credentials.password) {
            $scope.error = 'Please enter email and password.';
            return;
        }

        $scope.loading = true;
        $scope.error = null;

        ApiService.login($scope.credentials).then(function(response) {
            const data = response.data.data;
            if (data && data.token) {
                $window.localStorage.setItem('token', data.token);
                $window.localStorage.setItem('user', JSON.stringify(data.user));
                $rootScope.$broadcast('user:loggedIn', data.user);
                $window.location.href = '/tasks';
            }
        }).catch(function(error) {
            $scope.error = error.data?.message || 'Login failed. Please check your credentials.';
        }).finally(function() {
            $scope.loading = false;
        });
    };

    $scope.register = function() {
        if ($scope.registerData.password !== $scope.registerData.password_confirmation) {
            $scope.error = 'Passwords do not match.';
            return;
        }

        $scope.loading = true;
        $scope.error = null;

        ApiService.register($scope.registerData).then(function(response) {
            const data = response.data.data;
            if (data && data.token) {
                $window.localStorage.setItem('token', data.token);
                $window.localStorage.setItem('user', JSON.stringify(data.user));
                $rootScope.$broadcast('user:loggedIn', data.user);
                $window.location.href = '/tasks';
            }
        }).catch(function(error) {
            $scope.error = error.data?.message || 'Registration failed. Please check your details.';
        }).finally(function() {
            $scope.loading = false;
        });
    };

    // Auto redirect if already logged in
    if ($window.localStorage.getItem('token') && $window.location.pathname !== '/tasks' && $window.location.pathname !== '/profile') {
        $window.location.href = '/tasks';
    }
}];
