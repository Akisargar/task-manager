@extends('layouts.app')

@section('title', 'Tasks — Task Manager')

@section('content')
<div ng-init="checkAuth(true)">
    <div ng-controller="TaskController" ng-init="loadTasks()">

        <div ng-show="recomputeNotice" class="mb-4 p-3 bg-blue-50 border border-blue-200 text-blue-800 text-sm rounded flex justify-between items-center" ng-cloak>
            <span>@{{ recomputeNotice }}</span>
            <button ng-click="recomputeNotice = null" class="text-blue-500 hover:text-blue-700">✕</button>
        </div>

        <div ng-if="isAdmin()">

            <div class="flex items-center justify-between mb-5">
                <h1 class="text-2xl font-bold text-gray-900">Task Dashboard</h1>

                <div class="flex items-center space-x-2">
                    <button ng-click="assignUnassignedTasks()" ng-disabled="assignUnassignedLoading"
                        class="px-3 py-1.5 border border-indigo-200 text-sm rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                        @{{ assignUnassignedLoading ? 'Auto-Assigning...' : 'Auto-Assign Tasks' }}
                    </button>

                    <button ng-click="loadTasks()" ng-disabled="loading"
                        class="px-3 py-1.5 border border-gray-300 text-sm rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 inline-flex items-center space-x-1.5">
                        <span>Refresh</span>
                    </button>

                    <button ng-click="showCreateTaskModal()"
                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md">
                        + New Task
                    </button>
                </div>
            </div>

            <div class="bg-white p-3 rounded-md border border-gray-200 mb-5 flex flex-wrap items-center justify-between gap-3 text-sm">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-gray-500">Filter:</span>

                    <select ng-model="filters.status" ng-change="onFilterChange()" class="border border-gray-300 rounded px-2.5 py-1 text-sm">
                        <option value="">All Statuses</option>
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>

                    <select ng-model="filters.priority" ng-change="onFilterChange()" class="border border-gray-300 rounded px-2.5 py-1 text-sm">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>

                    <select ng-model="filters.assignment_status" ng-change="onFilterChange()" class="border border-gray-300 rounded px-2.5 py-1 text-sm">
                        <option value="">All Assignments</option>
                        <option value="assigned">Assigned</option>
                        <option value="unassigned">Unassigned</option>
                    </select>

                    <button ng-if="filters.status || filters.priority || filters.assignment_status"
                        ng-click="filters.status = ''; filters.priority = ''; filters.assignment_status = ''; onFilterChange()"
                        class="text-indigo-600 hover:text-indigo-800 text-xs">
                        Reset
                    </button>
                </div>

                <div class="text-gray-500 text-xs">
                    Total: <span class="font-semibold text-gray-700">@{{ meta.total || tasks.length }}</span>
                </div>
            </div>

            <div ng-show="loading" class="py-10 text-center text-sm text-gray-500">
                Loading tasks...
            </div>

            <div ng-show="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div ng-repeat="task in tasks" class="bg-white rounded-md border border-gray-200 p-4 flex flex-col justify-between shadow-xs hover:border-gray-300 transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2 py-0.5 rounded text-xs capitalize font-medium"
                                ng-class="{
                                      'bg-gray-100 text-gray-800': task.status == 'todo',
                                      'bg-blue-100 text-blue-800': task.status == 'in_progress',
                                      'bg-green-100 text-green-800': task.status == 'done'
                                  }">
                                @{{ task.status.replace('_', ' ') }}
                            </span>

                            <span class="px-2 py-0.5 rounded text-xs capitalize font-medium"
                                ng-class="{
                                      'bg-red-100 text-red-800': task.priority == 'critical',
                                      'bg-orange-100 text-orange-800': task.priority == 'high',
                                      'bg-yellow-100 text-yellow-800': task.priority == 'medium',
                                      'bg-green-100 text-green-800': task.priority == 'low'
                                  }">
                                @{{ task.priority }}
                            </span>
                        </div>

                        <h3 class="font-semibold text-gray-900 text-sm mb-1">@{{ task.title }}</h3>
                        <p class="text-xs text-gray-500 line-clamp-2 mb-3">@{{ task.description || 'No description provided.' }}</p>

                        <div class="mb-3 p-2 bg-indigo-50/60 border border-indigo-100 rounded text-xs">
                            <div class="text-[11px] font-medium text-indigo-900 mb-1 flex items-center justify-between">
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 text-indigo-600 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Assigned User:</span>
                                </span>
                                <span class="text-[10px] text-indigo-600" ng-if="task.assigned_users.length > 0">
                                    @{{ task.assigned_users.length }} @{{ task.assigned_users.length === 1 ? 'user' : 'users' }}
                                </span>
                            </div>

                            <div ng-if="task.assigned_users && task.assigned_users.length > 0" class="flex flex-wrap gap-1">
                                <span ng-repeat="user in task.assigned_users"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-white text-indigo-950 border border-indigo-200">
                                    @{{ user.name }}
                                    <span ng-if="user.department" class="ml-1 text-[10px] text-indigo-500 font-normal">(@{{ user.department }})</span>
                                </span>
                            </div>
                            <div ng-if="!task.assigned_users || task.assigned_users.length === 0" class="text-gray-400 italic text-[11px]">
                                Unassigned
                            </div>
                        </div>

                        <!-- Assignment Rules -->
                        <div class="mb-3">
                            <div class="text-[11px] font-medium text-gray-500 mb-1">Assignment Rules:</div>
                            <div class="flex flex-wrap gap-1" ng-if="task.rules && task.rules.length > 0">
                                <span ng-repeat="rule in task.rules"
                                    class="px-2 py-0.5 bg-gray-100 border border-gray-200 text-gray-700 rounded text-[11px]">
                                    <span class="font-medium text-gray-900">@{{ formatRuleField(rule.field) }}</span>
                                    <span class="text-indigo-600 font-semibold mx-0.5">@{{ rule.operator }}</span>
                                    <span>@{{ rule.value }}</span>
                                </span>
                            </div>
                            <div ng-if="!task.rules || task.rules.length === 0" class="text-gray-400 italic text-[11px]">
                                None (All staff eligible)
                            </div>
                        </div>

                        <!-- Creator & Due Date -->
                        <div class="text-[11px] text-gray-400 flex items-center justify-between border-t border-gray-100 pt-2">
                            <span ng-if="task.creator">Created by: @{{ task.creator.name }}</span>
                            <span ng-if="!task.creator">Created: @{{ task.created_at | date:'mediumDate' }}</span>
                            <span ng-if="task.due_date" class="font-medium text-gray-600">Due: @{{ formatDate(task.due_date) }}</span>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="pt-3 mt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                        <button ng-click="viewEligibleUsers(task)" class="text-indigo-600 hover:text-indigo-800 font-medium">
                            Eligible Users
                        </button>
                        <div class="space-x-2">
                            <button ng-click="showEditTaskModal(task)" class="text-gray-600 hover:text-gray-900 font-medium">Edit</button>
                            <button ng-if="isAdmin()" ng-click="confirmDelete(task)" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                        </div>
                    </div>
                </div>

                <div ng-if="tasks.length === 0" class="col-span-full py-10 text-center text-sm text-gray-500 bg-white border border-gray-200 rounded">
                    No tasks found.
                </div>
            </div>

            <div ng-if="meta.total > 0" class="mt-6 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 pt-4 gap-3 text-xs">
                <div class="text-gray-500">
                    Showing @{{ getFromTaskIndex() }} to @{{ getToTaskIndex() }} of @{{ meta.total }} tasks
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

        </div>

        <div ng-if="!isAdmin()">
            <div class="flex items-center justify-between mb-5">
                <h1 class="text-2xl font-bold text-gray-900">My Tasks</h1>
                <button ng-click="loadMyTasks()" ng-disabled="loading"
                    class="px-3 py-1.5 border border-gray-300 text-sm rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 inline-flex items-center space-x-1.5">
                    <span>Refresh</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <!-- To Do -->
                <div class="bg-gray-100 p-3 rounded-md">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex justify-between">
                        <span>To Do</span>
                        <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full">@{{ (myTasks | filter:{status:'todo'}).length }}</span>
                    </h3>
                    <div class="space-y-2">
                        <div ng-repeat="task in myTasks | filter:{status:'todo'}" class="bg-white p-3 rounded border border-gray-200 text-xs">
                            <div class="font-medium text-gray-900 mb-1">@{{ task.title }}</div>
                            <div class="text-gray-500 mb-2">@{{ task.description }}</div>
                            <div ng-if="task.due_date" class="text-[11px] font-medium text-gray-600 mb-2">
                                Due: @{{ formatDate(task.due_date) }}
                            </div>
                            <div class="flex flex-wrap gap-1 mb-2" ng-if="task.rules && task.rules.length > 0">
                                <span ng-repeat="rule in task.rules" class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px]">
                                    @{{ formatRuleField(rule.field) }} @{{ rule.operator }} @{{ rule.value }}
                                </span>
                            </div>
                            <button ng-click="updateTaskStatus(task, 'in_progress')" class="w-full py-1 text-center bg-blue-50 text-blue-600 hover:bg-blue-100 rounded font-medium">
                                Start Progress →
                            </button>
                        </div>
                        <div ng-if="(myTasks | filter:{status:'todo'}).length === 0" class="text-center py-6 text-xs text-gray-400">Empty</div>
                    </div>
                </div>

                <div class="bg-blue-50 p-3 rounded-md">
                    <h3 class="text-sm font-semibold text-blue-800 mb-3 flex justify-between">
                        <span>In Progress</span>
                        <span class="text-xs bg-blue-200 px-2 py-0.5 rounded-full">@{{ (myTasks | filter:{status:'in_progress'}).length }}</span>
                    </h3>
                    <div class="space-y-2">
                        <div ng-repeat="task in myTasks | filter:{status:'in_progress'}" class="bg-white p-3 rounded border border-blue-200 text-xs">
                            <div class="font-medium text-gray-900 mb-1">@{{ task.title }}</div>
                            <div class="text-gray-500 mb-2">@{{ task.description }}</div>
                            <div ng-if="task.due_date" class="text-[11px] font-medium text-blue-700 mb-2">
                                Due: @{{ formatDate(task.due_date) }}
                            </div>
                            <div class="flex flex-wrap gap-1 mb-2" ng-if="task.rules && task.rules.length > 0">
                                <span ng-repeat="rule in task.rules" class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px]">
                                    @{{ formatRuleField(rule.field) }} @{{ rule.operator }} @{{ rule.value }}
                                </span>
                            </div>
                            <div class="flex space-x-1">
                                <button ng-click="updateTaskStatus(task, 'todo')" class="flex-1 py-1 text-center bg-gray-100 text-gray-600 hover:bg-gray-200 rounded">
                                    ← To Do
                                </button>
                                <button ng-click="updateTaskStatus(task, 'done')" class="flex-1 py-1 text-center bg-green-50 text-green-700 hover:bg-green-100 rounded font-medium">
                                    Done ✓
                                </button>
                            </div>
                        </div>
                        <div ng-if="(myTasks | filter:{status:'in_progress'}).length === 0" class="text-center py-6 text-xs text-blue-400">Empty</div>
                    </div>
                </div>

                <div class="bg-green-50 p-3 rounded-md">
                    <h3 class="text-sm font-semibold text-green-800 mb-3 flex justify-between">
                        <span>Done</span>
                        <span class="text-xs bg-green-200 px-2 py-0.5 rounded-full">@{{ (myTasks | filter:{status:'done'}).length }}</span>
                    </h3>
                    <div class="space-y-2">
                        <div ng-repeat="task in myTasks | filter:{status:'done'}" class="bg-white p-3 rounded border border-green-200 text-xs opacity-80">
                            <div class="font-medium text-gray-900 mb-1 line-through">@{{ task.title }}</div>
                            <div class="text-gray-500 mb-2 line-through">@{{ task.description }}</div>
                            <div ng-if="task.due_date" class="text-[11px] text-gray-400 mb-2">
                                Due: @{{ formatDate(task.due_date) }}
                            </div>
                            <div class="flex flex-wrap gap-1 mb-2" ng-if="task.rules && task.rules.length > 0">
                                <span ng-repeat="rule in task.rules" class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px]">
                                    @{{ formatRuleField(rule.field) }} @{{ rule.operator }} @{{ rule.value }}
                                </span>
                            </div>
                            <button ng-click="updateTaskStatus(task, 'in_progress')" class="w-full py-1 text-center bg-blue-50 text-blue-600 hover:bg-blue-100 rounded">
                                ← Reopen
                            </button>
                        </div>
                        <div ng-if="(myTasks | filter:{status:'done'}).length === 0" class="text-center py-6 text-xs text-green-400">Empty</div>
                    </div>
                </div>

            </div>
        </div>

        <div ng-show="isTaskModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" ng-cloak>
            <div class="bg-white rounded-lg p-5 max-w-lg w-full shadow-lg">
                <h3 class="text-lg font-bold text-gray-900 mb-3">
                    @{{ taskModalMode === 'create' ? 'Create Task' : 'Edit Task' }}
                </h3>

                <form ng-submit="saveTask()" class="space-y-3 text-sm">
                    <div ng-show="taskError" class="p-2 bg-red-50 text-red-700 text-xs rounded">@{{ taskError }}</div>

                    <div>
                        <label class="block text-gray-700 text-xs font-medium mb-1">Title</label>
                        <input type="text" ng-model="taskForm.title" required class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-sm">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-medium mb-1">Description</label>
                        <textarea ng-model="taskForm.description" required rows="2" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 text-xs font-medium mb-1">Priority</label>
                            <select ng-model="taskForm.priority" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div ng-show="taskModalMode === 'edit'">
                            <label class="block text-gray-700 text-xs font-medium mb-1">Status</label>
                            <select ng-model="taskForm.status" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-sm">
                                <option value="todo">To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                        <div ng-class="{'col-span-2': taskModalMode === 'create'}">
                            <label class="block text-gray-700 text-xs font-medium mb-1">Due Date</label>
                            <input type="date" ng-model="taskForm.due_date" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs bg-white text-gray-700">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-medium text-gray-700">Assignment Rules</span>
                            <button type="button" ng-click="addRule()" class="text-xs text-indigo-600 hover:text-indigo-800">+ Add</button>
                        </div>

                        <div class="space-y-1.5 max-h-36 overflow-y-auto">
                            <div ng-repeat="rule in taskForm.rules track by $index" class="flex items-center space-x-1.5 bg-gray-50 p-1.5 rounded border border-gray-200 text-xs">
                                <select ng-model="rule.field" ng-options="field as field.split('_').join(' ') for field in ruleFields" class="w-1/3 border rounded px-1 py-1 capitalize text-xs"></select>
                                <select ng-model="rule.operator" ng-options="op as op for op in ruleOperators" class="w-1/4 border rounded px-1 py-1 text-xs"></select>

                                <div class="w-1/3">
                                    <select ng-if="rule.field === 'department'" ng-model="rule.value" class="w-full border rounded px-1 py-1 text-xs">
                                        <option value="Finance">Finance</option>
                                        <option value="HR">HR</option>
                                        <option value="IT">IT</option>
                                        <option value="Operation">Operation</option>
                                    </select>
                                    <select ng-if="rule.field === 'role'" ng-model="rule.value" class="w-full border rounded px-1 py-1 text-xs">
                                        <option value="user">User</option>
                                        <option value="manager">Manager</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <input ng-if="rule.field !== 'department' && rule.field !== 'role'" type="text" ng-model="rule.value" placeholder="Value" class="w-full border rounded px-1 py-1 text-xs">
                                </div>

                                <button type="button" ng-click="removeRule($index)" class="text-red-500 px-1">✕</button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex justify-end space-x-2">
                        <button type="button" ng-click="closeTaskModal()" class="px-3 py-1.5 border border-gray-300 rounded text-xs text-gray-700 bg-white">Cancel</button>
                        <button type="submit" ng-disabled="taskLoading" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-medium disabled:opacity-50">
                            Save Task
                        </button>
                    </div>
                </form>
            </div>
            <!-- ========================================== -->
            <!-- ELIGIBLE USERS MODAL                       -->
            <!-- ========================================== -->
            <div ng-show="isEligibleUsersModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" ng-cloak>
                <div class="bg-white rounded-lg p-5 max-w-md w-full shadow-lg">
                    <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-200">
                        <h3 class="font-bold text-gray-900 text-base">Eligible Users</h3>
                        <button type="button" ng-click="closeEligibleUsersModal()" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <div class="text-xs text-gray-500 mb-2">Task: <span class="font-medium text-gray-800">@{{ eligibleTask.title }}</span></div>

                    <div ng-show="eligibleLoading" class="py-4 text-center text-xs text-gray-500">Loading...</div>

                    <div ng-show="!eligibleLoading && eligibleUsers.length > 0" class="max-h-52 overflow-y-auto space-y-1.5">
                        <div ng-repeat="user in eligibleUsers" class="flex justify-between items-center p-2 rounded bg-gray-50 border border-gray-200 text-xs">
                            <div>
                                <div class="font-medium text-gray-900">@{{ user.name }}</div>
                                <div class="text-[11px] text-gray-500">@{{ user.department || 'No Dept' }} • @{{ user.years_of_experience || 0 }}y exp</div>
                            </div>
                            <span class="text-[11px] bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded">@{{ user.active_task_count || 0 }} active</span>
                        </div>
                    </div>

                    <div ng-show="!eligibleLoading && eligibleUsers.length === 0" class="py-4 text-center text-xs text-gray-400">
                        No eligible users.
                    </div>

                    <div class="mt-4 pt-2 border-t border-gray-100 text-right">
                        <button type="button" ng-click="closeEligibleUsersModal()" class="px-3 py-1 border border-gray-300 rounded text-xs text-gray-700">Close</button>
                    </div>
                </div>
            </div>

            <div ng-show="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" ng-cloak>
                <div class="bg-white rounded-lg p-5 max-w-sm w-full shadow-lg">
                    <h3 class="font-bold text-gray-900 text-base mb-2">Delete Task</h3>
                    <p class="text-xs text-gray-600 mb-4">Are you sure you want to delete "@{{ taskToDelete.title }}"?</p>
                    <div ng-show="deleteError" class="mb-2 text-xs text-red-600">@{{ deleteError }}</div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" ng-click="closeDeleteModal()" class="px-3 py-1.5 border border-gray-300 rounded text-xs text-gray-700">Cancel</button>
                        <button type="button" ng-click="deleteTask()" ng-disabled="deleteLoading" class="px-3 py-1.5 bg-red-600 text-white rounded text-xs font-medium disabled:opacity-50">Delete</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endsection