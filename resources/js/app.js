import './bootstrap';
import 'angular';
import ApiService from './services/ApiService';
import MainController from './controllers/MainController';
import AuthController from './controllers/AuthController';
import TaskController from './controllers/TaskController';
import ProfileController from './controllers/ProfileController';
import UserController from './controllers/UserController';

const app = angular.module('TaskManagerApp', []);

// Register services
app.factory('ApiService', ApiService);

// Register controllers
app.controller('MainController', MainController);
app.controller('AuthController', AuthController);
app.controller('TaskController', TaskController);
app.controller('ProfileController', ProfileController);
app.controller('UserController', UserController);

// Configure CSRF and interceptors
app.config(['$httpProvider', function($httpProvider) {
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    
    // Auth Interceptor
    $httpProvider.interceptors.push(['$q', '$window', function($q, $window) {
        return {
            request: function(config) {
                const token = $window.localStorage.getItem('token');
                if (token) {
                    config.headers.Authorization = 'Bearer ' + token;
                }
                return config;
            },
            responseError: function(rejection) {
                if (rejection.status === 401) {
                    $window.localStorage.removeItem('token');
                    $window.localStorage.removeItem('user');
                    // Broadcast event or handle redirect globally if needed
                }
                return $q.reject(rejection);
            }
        };
    }]);
}]);
