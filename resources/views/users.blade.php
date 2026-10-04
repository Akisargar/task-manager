@extends('layouts.app')

@section('title', 'Users — Task Manager')

@section('content')
<div ng-init="checkAuth(true)">
    <div ng-controller="UserController" ng-init="loadUsers()">
        <div ng-if="isAdmin()">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Users</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Company staff directory, workload status, and assignment profiles</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-gray-500">
                        Total: <strong class="text-gray-800">@{{ meta.total || users.length }}</strong> users
                    </span>
                    <button ng-if="currentUser.role === 'admin'"
                        ng-click="openCreateModal()"
                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-semibold shadow-xs transition">
                        + Add User
                    </button>
                </div>
            </div>

            <!-- Success Message -->
            <div ng-if="successMessage" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded">
                @{{ successMessage }}
            </div>

            <!-- Error Message -->
            <div ng-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded">
                @{{ errorMessage }}
            </div>

            <!-- Filter Toolbar -->
            <div class="bg-white p-3 rounded-md border border-gray-200 mb-5 flex flex-wrap items-center justify-between gap-3 text-sm">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="text-gray-500 text-xs font-medium">Filter:</span>

                    <!-- Department Filter -->
                    <select ng-model="filters.department" ng-change="onFilterChange()" class="border border-gray-300 rounded px-2.5 py-1 text-xs bg-white text-gray-700">
                        <option value="">All Departments</option>
                        <option ng-repeat="dept in departments" value="@{{ dept }}">@{{ dept }}</option>
                    </select>

                    <!-- Role Filter -->
                    <select ng-model="filters.role" ng-change="onFilterChange()" class="border border-gray-300 rounded px-2.5 py-1 text-xs capitalize bg-white text-gray-700">
                        <option value="">All Roles</option>
                        <option ng-repeat="r in roles" value="@{{ r }}">@{{ r }}</option>
                    </select>

                    <!-- Search Input -->
                    <input type="text" ng-model="filters.search" ng-change="onFilterChange()" placeholder="Search name, email, city..."
                        class="border border-gray-300 rounded px-2.5 py-1 text-xs w-48 sm:w-56">

                    <button ng-if="filters.department || filters.role || filters.search"
                        ng-click="resetFilters()"
                        class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                        Reset
                    </button>
                </div>

                <div class="text-xs text-gray-400">
                    12 users per page
                </div>
            </div>

            <!-- Loading -->
            <div ng-show="loading" class="py-12 text-center text-sm text-gray-500">
                Loading users...
            </div>

            <!-- Users Table -->
            <div ng-show="!loading" class="bg-white border border-gray-200 rounded-md shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                        <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-3 py-3 w-12 text-center">#</th>
                                <th class="px-4 py-3">User</th>
                                <th class="px-3 py-3">Role</th>
                                <th class="px-3 py-3">Department</th>
                                <th class="px-3 py-3">Experience</th>
                                <th class="px-3 py-3">Location</th>
                                <th class="px-3 py-3 text-center">Active Tasks</th>
                                <th class="px-3 py-3 text-center">Total Assigned</th>
                                <th class="px-3 py-3">Joined</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr ng-repeat="user in users" class="hover:bg-gray-50 transition">
                                <!-- ID -->
                                <td class="px-3 py-3 whitespace-nowrap text-center text-gray-400 font-mono text-[11px]">
                                    #@{{ user.id }}
                                </td>

                                <!-- Name & Email -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-gray-900 cursor-pointer hover:text-indigo-600" ng-click="viewDetails(user)">
                                        @{{ user.name }}
                                    </div>
                                    <div class="text-gray-500 text-[11px]">@{{ user.email }}</div>
                                </td>

                                <!-- Role -->
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-medium capitalize"
                                        ng-class="{
                                          'bg-indigo-100 text-indigo-800': user.role === 'admin',
                                          'bg-purple-100 text-purple-800': user.role === 'manager',
                                          'bg-gray-100 text-gray-800': user.role === 'user'
                                      }">
                                        @{{ user.role }}
                                    </span>
                                </td>

                                <!-- Department -->
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span ng-if="user.department" class="px-2 py-0.5 bg-gray-100 rounded text-gray-700 font-medium text-[11px]">
                                        @{{ user.department }}
                                    </span>
                                    <span ng-if="!user.department" class="text-gray-400 italic">None</span>
                                </td>

                                <!-- Experience -->
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    @{{ user.years_of_experience || 0 }} yrs
                                </td>

                                <!-- Location -->
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    @{{ user.location || 'N/A' }}
                                </td>

                                <!-- Active Task Count -->
                                <td class="px-3 py-3 whitespace-nowrap text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                        ng-class="{
                                          'bg-emerald-100 text-emerald-800': user.active_task_count === 0,
                                          'bg-blue-100 text-blue-800': user.active_task_count > 0 && user.active_task_count < 3,
                                          'bg-amber-100 text-amber-800': user.active_task_count >= 3
                                      }">
                                        @{{ user.active_task_count || 0 }}
                                    </span>
                                </td>

                                <!-- Total Assigned Tasks -->
                                <td class="px-3 py-3 whitespace-nowrap text-center text-gray-600 font-medium">
                                    @{{ user.assigned_tasks_count !== undefined ? user.assigned_tasks_count : '—' }}
                                </td>

                                <!-- Created At -->
                                <td class="px-3 py-3 whitespace-nowrap text-gray-400 text-[11px]">
                                    @{{ user.created_at | date:'mediumDate' }}
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-3 whitespace-nowrap text-right space-x-2">
                                    <button ng-click="viewDetails(user)"
                                        class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">
                                        Details
                                    </button>
                                    <button ng-if="currentUser.role === 'admin'"
                                        ng-click="openEditModal(user)"
                                        class="text-gray-600 hover:text-gray-900 font-medium text-xs">
                                        Edit
                                    </button>
                                </td>
                            </tr>

                            <tr ng-if="users.length === 0">
                                <td colspan="10" class="px-4 py-10 text-center text-gray-400 italic">
                                    No users found matching the filter criteria.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div ng-if="meta.total > 0" class="mt-5 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 pt-4 gap-3 text-xs">
                <div class="text-gray-500">
                    Showing @{{ getFromIndex() }} to @{{ getToIndex() }} of @{{ meta.total }} users
                </div>

                <div class="flex items-center space-x-1" ng-if="meta.last_page > 1">
                    <button ng-click="changePage(filters.page - 1)" ng-disabled="filters.page <= 1"
                        class="px-2.5 py-1 border border-gray-300 rounded bg-white text-gray-700 disabled:opacity-40">
                        Previous
                    </button>

                    <button ng-repeat="p in getPages()" ng-click="changePage(p)"
                        class="px-2.5 py-1 border rounded"
                        ng-class="p === meta.current_page ? 'bg-indigo-600 border-indigo-600 text-white font-bold' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'">
                        @{{ p }}
                    </button>

                    <button ng-click="changePage(filters.page + 1)" ng-disabled="filters.page >= meta.last_page"
                        class="px-2.5 py-1 border border-gray-300 rounded bg-white text-gray-700 disabled:opacity-40">
                        Next
                    </button>
                </div>
            </div>

            <!-- USER DETAILS MODAL -->
            <div ng-if="selectedUser" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden text-xs">

                    <!-- Modal Header -->
                    <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                        <div>
                            <div class="flex items-center space-x-2">
                                <h2 class="text-base font-bold text-gray-900">@{{ selectedUser.name }}</h2>
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium capitalize"
                                    ng-class="{
                                      'bg-indigo-100 text-indigo-800': selectedUser.role === 'admin',
                                      'bg-purple-100 text-purple-800': selectedUser.role === 'manager',
                                      'bg-gray-100 text-gray-800': selectedUser.role === 'user'
                                  }">
                                    @{{ selectedUser.role }}
                                </span>
                            </div>
                            <p class="text-gray-500 text-[11px] mt-0.5">User #@{{ selectedUser.id }} &bull; @{{ selectedUser.email }}</p>
                        </div>
                        <button ng-click="closeDetailsModal()" class="text-gray-400 hover:text-gray-600 text-lg leading-none p-1">&times;</button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-5 overflow-y-auto space-y-5 flex-grow">

                        <!-- Overview Attributes Grid -->
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Profile Details</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50 p-3 rounded border border-gray-200 text-xs">
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Department</span>
                                    <span class="font-medium text-gray-800">@{{ selectedUser.department || 'Not specified' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Experience</span>
                                    <span class="font-medium text-gray-800">@{{ selectedUser.years_of_experience || 0 }} years</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Location</span>
                                    <span class="font-medium text-gray-800">@{{ selectedUser.location || 'Not specified' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Active Tasks</span>
                                    <span class="font-semibold text-gray-800">@{{ selectedUser.active_task_count || 0 }} active</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Total Assigned</span>
                                    <span class="font-medium text-gray-800">@{{ selectedUser.assigned_tasks_count || (selectedUser.assigned_tasks ? selectedUser.assigned_tasks.length : 0) }} tasks</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Created Tasks</span>
                                    <span class="font-medium text-gray-800">@{{ selectedUser.created_tasks_count || (selectedUser.created_tasks ? selectedUser.created_tasks.length : 0) }} tasks</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Joined Date</span>
                                    <span class="text-gray-700">@{{ selectedUser.created_at | date:'mediumDate' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Last Updated</span>
                                    <span class="text-gray-700">@{{ selectedUser.updated_at | date:'mediumDate' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Assigned Tasks Section -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Assigned Tasks (@{{ selectedUser.assigned_tasks ? selectedUser.assigned_tasks.length : 0 }})
                                </h3>
                                <span ng-if="loadingDetails" class="text-gray-400 text-[11px]">Loading tasks...</span>
                            </div>

                            <div ng-if="selectedUser.assigned_tasks && selectedUser.assigned_tasks.length > 0"
                                class="border border-gray-200 rounded overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                                    <thead class="bg-gray-50 text-gray-600 font-medium">
                                        <tr>
                                            <th class="px-3 py-2">Task</th>
                                            <th class="px-3 py-2">Priority</th>
                                            <th class="px-3 py-2">Status</th>
                                            <th class="px-3 py-2">Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr ng-repeat="task in selectedUser.assigned_tasks" class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium text-gray-800">
                                                @{{ task.title }}
                                            </td>
                                            <td class="px-3 py-2">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium capitalize"
                                                    ng-class="{
                                                      'bg-red-100 text-red-700': task.priority === 'urgent',
                                                      'bg-amber-100 text-amber-700': task.priority === 'high',
                                                      'bg-blue-100 text-blue-700': task.priority === 'medium',
                                                      'bg-gray-100 text-gray-700': task.priority === 'low'
                                                  }">
                                                    @{{ task.priority }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium capitalize"
                                                    ng-class="{
                                                      'bg-emerald-100 text-emerald-700': task.status === 'completed',
                                                      'bg-blue-100 text-blue-700': task.status === 'in_progress',
                                                      'bg-gray-100 text-gray-700': task.status === 'todo'
                                                  }">
                                                    @{{ task.status }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-gray-500">
                                                @{{ task.due_date ? formatDate(task.due_date) : 'None' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div ng-if="!selectedUser.assigned_tasks || selectedUser.assigned_tasks.length === 0"
                                class="p-4 bg-gray-50 rounded border border-gray-200 text-center text-gray-400 italic">
                                No tasks currently assigned to this user.
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="p-3 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                        <div class="space-x-2">
                            <button ng-if="currentUser.role === 'admin'"
                                ng-click="openEditModal(selectedUser)"
                                class="px-3 py-1.5 border border-gray-300 rounded text-gray-700 hover:bg-gray-100 font-medium">
                                Edit Profile
                            </button>
                            <button ng-if="currentUser.role === 'admin' && currentUser.id !== selectedUser.id"
                                ng-click="deleteUser(selectedUser)"
                                class="px-3 py-1.5 border border-red-200 text-red-600 hover:bg-red-50 rounded font-medium">
                                Delete User
                            </button>
                        </div>

                        <button ng-click="closeDetailsModal()"
                            class="px-4 py-1.5 bg-gray-800 hover:bg-gray-900 text-white rounded font-medium">
                            Close
                        </button>
                    </div>

                </div>
            </div>

            <!-- CREATE USER MODAL (ADMIN ONLY) -->
            <div ng-if="showCreateModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-lg w-full flex flex-col overflow-hidden text-xs">

                    <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                        <h2 class="text-sm font-bold text-gray-900">Add New User</h2>
                        <button ng-click="closeCreateModal()" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                    </div>

                    <form ng-submit="submitCreateUser()" class="p-4 space-y-3">

                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Full Name *</label>
                            <input type="text" ng-model="newUser.name" required placeholder="John Doe"
                                class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Email Address *</label>
                                <input type="email" ng-model="newUser.email" required placeholder="john@example.com"
                                    class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Password *</label>
                                <input type="password" ng-model="newUser.password" required minlength="6" placeholder="At least 6 chars"
                                    class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Role *</label>
                                <select ng-model="newUser.role" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs capitalize bg-white">
                                    <option ng-repeat="r in roles" value="@{{ r }}">@{{ r }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Department</label>
                                <select ng-model="newUser.department" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs bg-white">
                                    <option value="">Select Department</option>
                                    <option ng-repeat="dept in departments" value="@{{ dept }}">@{{ dept }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Years of Experience</label>
                                <input type="number" ng-model="newUser.years_of_experience" min="0" max="60"
                                    class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Location</label>
                                <input type="text" ng-model="newUser.location" placeholder="City or Office"
                                    class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs">
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-200 flex justify-end space-x-2">
                            <button type="button" ng-click="closeCreateModal()"
                                class="px-3 py-1.5 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" ng-disabled="saving"
                                class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded font-semibold disabled:opacity-50">
                                @{{ saving ? 'Saving...' : 'Create User' }}
                            </button>
                        </div>

                    </form>

                </div>
            </div>

            <!-- EDIT USER MODAL (ADMIN ONLY) -->
            <div ng-if="showEditModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-lg w-full flex flex-col overflow-hidden text-xs">

                    <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                        <h2 class="text-sm font-bold text-gray-900">Edit User: @{{ editUserData.name }}</h2>
                        <button ng-click="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                    </div>

                    <form ng-submit="submitEditUser()" class="p-4 space-y-3">

                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Full Name</label>
                            <input type="text" ng-model="editUserData.name" required
                                class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Role</label>
                                <select ng-model="editUserData.role" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs capitalize bg-white">
                                    <option ng-repeat="r in roles" value="@{{ r }}">@{{ r }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Department</label>
                                <select ng-model="editUserData.department" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs bg-white">
                                    <option value="">Select Department</option>
                                    <option ng-repeat="dept in departments" value="@{{ dept }}">@{{ dept }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Years of Experience</label>
                                <input type="number" ng-model="editUserData.years_of_experience" min="0" max="60"
                                    class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Location</label>
                                <input type="text" ng-model="editUserData.location" placeholder="City or Office"
                                    class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs">
                            </div>
                        </div>

                        <p class="text-[11px] text-gray-500 italic">
                            * Note: Changing department, experience, or location automatically recomputes dynamic task eligibility.
                        </p>

                        <div class="pt-3 border-t border-gray-200 flex justify-end space-x-2">
                            <button type="button" ng-click="closeEditModal()"
                                class="px-3 py-1.5 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" ng-disabled="saving"
                                class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded font-semibold disabled:opacity-50">
                                @{{ saving ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <!-- Access Denied for Non-Admins -->
        <div ng-if="!isAdmin()" class="py-16 text-center" ng-cloak>
            <div class="max-w-md mx-auto bg-white p-8 rounded-lg border border-gray-200 shadow-sm">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900 mb-1">Access Denied</h2>
                <p class="text-sm text-gray-500 mb-5">You do not have permission to access the user directory.</p>
                <a href="{{ url('/tasks') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded text-xs font-semibold hover:bg-indigo-700 transition">
                    Return to Tasks
                </a>
            </div>
        </div>

    </div>
</div>
@endsection