<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin Panel - Ecommerce')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('meta')
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Alpine.js for admin interactions --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- SortableJS for drag and drop --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <meta name="color-scheme" content="light dark">
</head>
<body class="bg-gray-100 text-gray-900">
    <!-- Admin Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="/admin" class="text-xl font-bold text-gray-900">
                        Admin Panel
                    </a>
                    <span class="text-gray-400">|</span>
                    <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800" target="_blank">
                        View Site
                    </a>
                </div>
                
                <!-- Admin Navigation - Now points to Angular SPA -->
                <nav class="flex space-x-6">
                    <a href="/admin/dashboard" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Dashboard
                    </a>
                    <a href="/admin/products" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Products
                    </a>
                    <a href="/admin/categories" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Categories
                    </a>
                    <a href="/admin/brands" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Brands
                    </a>
                    <a href="/admin/settings" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Settings
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Admin Content -->
    <main class="py-6">
        @yield('content')
    </main>

    <!-- Admin Footer -->
    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-sm text-gray-500 text-center">
                © {{ date('Y') }} Ecommerce Admin Panel
            </p>
        </div>
    </footer>
    
    @stack('scripts')
</body>
</html>