export default ['$scope', '$window', 'ApiService', function($scope, $window, ApiService) {
    
    $scope.currentUser = null;

    $scope.init = function() {
        const userStr = $window.localStorage.getItem('user');
        if (userStr) {
            try {
                $scope.currentUser = JSON.parse(userStr);
            } catch (e) {
                // ignore
            }
        }
    };

    $scope.isAuthenticated = function() {
        return !!$scope.currentUser && !!$window.localStorage.getItem('token');
    };

    $scope.isAdmin = function() {
        return $scope.currentUser && $scope.currentUser.role === 'admin';
    };

    $scope.isManager = function() {
        return $scope.currentUser && $scope.currentUser.role === 'manager';
    };

    $scope.checkAuth = function(required = true) {
        const token = $window.localStorage.getItem('token');
        if (required && !token) {
            $window.location.href = '/';
        } else if (!required && token) {
            $window.location.href = '/tasks';
        }
    };

    $scope.logout = function() {
        ApiService.logout().then(function() {
            $window.localStorage.removeItem('token');
            $window.localStorage.removeItem('user');
            $scope.currentUser = null;
            $window.location.href = '/';
        }).catch(function() {
            // Force logout locally even if API fails
            $window.localStorage.removeItem('token');
            $window.localStorage.removeItem('user');
            $scope.currentUser = null;
            $window.location.href = '/';
        });
    };

    $scope.$on('user:loggedIn', function(event, user) {
        $scope.currentUser = user;
    });

    $scope.$on('user:updated', function(event, user) {
        $scope.currentUser = user;
    });

    $scope.init();
}];
