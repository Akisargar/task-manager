<!DOCTYPE html>
<html lang="en" ng-app="TaskManagerApp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Task Manager')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body ng-controller="MainController" class="bg-gray-50 min-h-screen text-gray-800 antialiased flex flex-col" ng-cloak>

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">

                <div class="flex items-center space-x-8">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2">
                        <span class="text-xl font-bold text-gray-900">Task Manager</span>
                    </a>

                    <div class="hidden md:flex items-center space-x-2" ng-show="isAuthenticated()">
                        <a href="{{ url('/tasks') }}"
                            class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->is('tasks*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Tasks
                        </a>

                        <a href="{{ url('/users') }}" ng-show="isAdmin()"
                            class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->is('users*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Users
                        </a>

                        <a href="{{ url('/profile') }}"
                            class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->is('profile*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Profile
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-4" ng-show="isAuthenticated()">
                        <a href="{{ url('/profile') }}" class="flex items-center space-x-2 text-sm text-gray-700 hover:text-gray-900">
                            <span class="font-medium">@{{ currentUser.name }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full font-medium capitalize"
                                ng-class="{
                                      'bg-indigo-100 text-indigo-800': currentUser.role === 'admin',
                                      'bg-purple-100 text-purple-800': currentUser.role === 'manager',
                                      'bg-gray-100 text-gray-800': currentUser.role === 'user'
                                  }">
                                @{{ currentUser.role }}
                            </span>
                        </a>

                        <button ng-click="logout()"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                            Logout
                        </button>
                    </div>
                </div>
            </div>

            <div class="md:hidden border-t border-gray-200 py-2 flex space-x-2" ng-show="isAuthenticated()">
                <a href="{{ url('/tasks') }}"
                    class="flex-1 text-center py-2 px-3 rounded-md text-sm font-medium {{ request()->is('tasks*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Tasks
                </a>
                <a href="{{ url('/users') }}" ng-show="isAdmin()"
                    class="flex-1 text-center py-2 px-3 rounded-md text-sm font-medium {{ request()->is('users*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Users
                </a>
                <a href="{{ url('/profile') }}"
                    class="flex-1 text-center py-2 px-3 rounded-md text-sm font-medium {{ request()->is('profile*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Profile
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-grow max-w-7xl w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>
</body>

</html>