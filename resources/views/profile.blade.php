@extends('layouts.app')

@section('title', 'Profile — Task Manager')

@section('content')
<div ng-init="checkAuth(true)">
    <div ng-controller="ProfileController" class="max-w-4xl mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">User Profile</h1>
            <button ng-click="goToTasks()"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                ← Back to Tasks
            </button>
        </div>

        <!-- Success & Error Alerts -->
        <div ng-show="successMessage" class="p-3 rounded-md bg-green-50 border border-green-200 text-green-800 text-sm flex items-center justify-between" ng-cloak>
            <span>@{{ successMessage }}</span>
            <button ng-click="successMessage = null" class="text-green-600 hover:text-green-800 text-xs">✕</button>
        </div>

        <div ng-show="errorMessage" class="p-3 rounded-md bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between" ng-cloak>
            <span>@{{ errorMessage }}</span>
            <button ng-click="errorMessage = null" class="text-red-600 hover:text-red-800 text-xs">✕</button>
        </div>

        <!-- Profile Details Card -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm flex items-center space-x-5">
            <div class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
                @{{ (profileForm.name || 'U').charAt(0).toUpperCase() }}
            </div>
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="text-xl font-bold text-gray-900">@{{ profileForm.name }}</h2>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                          ng-class="{
                              'bg-indigo-100 text-indigo-800': profileForm.role === 'admin',
                              'bg-purple-100 text-purple-800': profileForm.role === 'manager',
                              'bg-gray-100 text-gray-800': profileForm.role === 'user'
                          }">
                        @{{ profileForm.role }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">@{{ profileForm.email }}</p>
            </div>
        </div>

        <!-- Attribute Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                <div class="text-xs font-medium text-gray-500">Department</div>
                <div class="mt-1 text-lg font-bold text-gray-900">@{{ profileForm.department || 'None' }}</div>
            </div>

            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                <div class="text-xs font-medium text-gray-500">Experience</div>
                <div class="mt-1 text-lg font-bold text-gray-900">@{{ profileForm.years_of_experience }} <span class="text-xs font-normal text-gray-500">yrs</span></div>
            </div>

            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                <div class="text-xs font-medium text-gray-500">Location</div>
                <div class="mt-1 text-lg font-bold text-gray-900">@{{ profileForm.location || 'N/A' }}</div>
            </div>

            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                <div class="text-xs font-medium text-gray-500">Active Tasks</div>
                <div class="mt-1 text-lg font-bold text-indigo-600">@{{ profileForm.active_task_count }}</div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h3 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">Edit Details</h3>

            <form ng-submit="saveProfile()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" ng-model="profileForm.name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                        <input type="email" ng-model="profileForm.email" disabled
                               class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md sm:text-sm text-gray-500 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <input type="text" ng-model="profileForm.role" disabled capitalize
                               class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md sm:text-sm text-gray-500 capitalize cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select ng-model="profileForm.department"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">
                            <option value="">Select Department</option>
                            <option ng-repeat="dept in departments" value="@{{ dept }}">@{{ dept }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Experience (Years)</label>
                        <input type="number" min="0" ng-model="profileForm.years_of_experience"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" ng-model="profileForm.location"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md sm:text-sm">
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                    <button type="button" ng-click="loadProfile()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Reset
                    </button>

                    <button type="submit" ng-disabled="saving"
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                        <span ng-show="!saving">Save Changes</span>
                        <span ng-show="saving">Saving...</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
