export default ['$http', function($http) {
    const API_URL = '/api';

    return {
        login: function(credentials) {
            return $http.post(`${API_URL}/login`, credentials);
        },
        register: function(userData) {
            return $http.post(`${API_URL}/register`, userData);
        },
        logout: function() {
            return $http.post(`${API_URL}/logout`);
        },
        getTasks: function(filters = {}) {
            return $http.get(`${API_URL}/tasks`, { params: filters });
        },
        createTask: function(taskData) {
            return $http.post(`${API_URL}/tasks`, taskData);
        },
        updateTask: function(id, taskData) {
            return $http.put(`${API_URL}/tasks/${id}`, taskData);
        },
        updateTaskStatus: function(id, statusData) {
            return $http.patch(`${API_URL}/tasks/${id}/status`, statusData);
        },
        deleteTask: function(id) {
            return $http.delete(`${API_URL}/tasks/${id}`);
        },
        getMyTasks: function(params = {}) {
            return $http.get(`${API_URL}/my-eligible-tasks`, { params });
        },
        getEligibleUsers: function(taskId) {
            return $http.get(`${API_URL}/tasks/${taskId}/eligible-users`);
        },
        recomputeEligibility: function() {
            return $http.post(`${API_URL}/tasks/recompute-eligibility`);
        },
        assignUnassignedTasks: function(data = {}) {
            return $http.post(`${API_URL}/tasks/assign-unassigned`, data);
        },
        getProfile: function() {
            return $http.get(`${API_URL}/user`);
        },
        updateProfile: function(profileData) {
            return $http.put(`${API_URL}/user`, profileData);
        },
        getUsers: function(filters = {}) {
            return $http.get(`${API_URL}/users`, { params: filters });
        },
        getUser: function(id) {
            return $http.get(`${API_URL}/users/${id}`);
        },
        createUser: function(userData) {
            return $http.post(`${API_URL}/users`, userData);
        },
        updateUser: function(id, userData) {
            return $http.put(`${API_URL}/users/${id}`, userData);
        },
        deleteUser: function(id) {
            return $http.delete(`${API_URL}/users/${id}`);
        }
    };
}];
