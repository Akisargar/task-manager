@extends('layouts.app')

@section('title', 'Sign In page')

@section('content')
<div ng-init="checkAuth(false)">
    <div ng-controller="AuthController" class="min-h-[70vh] flex flex-col justify-center py-8 sm:px-6 lg:px-8">

        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-6 shadow-sm rounded-xl sm:px-10 border border-gray-200">

                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">
                        @{{ isLoginMode ? 'Sign in to your account' : 'Create an account' }}
                    </h2>
                </div>

                <!-- Error Alert -->
                <div ng-show="error" class="bg-red-50 border-l-4 border-red-500 p-3 rounded mb-5" ng-cloak>
                    <p class="text-xs text-red-700">@{{ error }}</p>
                </div>

                <!-- LOGIN FORM -->
                <form ng-show="isLoginMode" ng-submit="login()" class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <input id="email" type="email" ng-model="credentials.email" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" type="password" ng-model="credentials.password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div class="pt-2">
                        <button type="submit" ng-disabled="loading"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition">
                            <span ng-show="!loading">Sign in</span>
                            <span ng-show="loading">Signing in...</span>
                        </button>
                    </div>
                </form>

                <!-- REGISTER FORM -->
                <form ng-show="!isLoginMode" ng-submit="register()" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" ng-model="registerData.name" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email address</label>
                        <input type="email" ng-model="registerData.email" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" ng-model="registerData.password" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" ng-model="registerData.password_confirmation" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select ng-model="registerData.role" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                                <option value="user">User</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Department</label>
                            <select ng-model="registerData.department" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                                <option value="">Select Department</option>
                                <option value="Finance">Finance</option>
                                <option value="HR">HR</option>
                                <option value="IT">IT</option>
                                <option value="Operation">Operation</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Experience (Years)</label>
                            <input type="number" min="0" ng-model="registerData.years_of_experience"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <input type="text" ng-model="registerData.location"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm sm:text-sm">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" ng-disabled="loading"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition">
                            <span ng-show="!loading">Register</span>
                            <span ng-show="loading">Registering...</span>
                        </button>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <a href="#" ng-click="toggleMode(); $event.preventDefault()" class="text-sm text-indigo-600 hover:text-indigo-500">
                        @{{ isLoginMode ? "Need an account? Register" : "Already have an account? Sign in" }}
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection