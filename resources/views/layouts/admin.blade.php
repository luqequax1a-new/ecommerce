<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin Panel - Ecommerce')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @stack('meta')
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Alpine.js for admin interactions --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <meta name="color-scheme" content="light dark">
</head>
<body class="bg-gray-100 text-gray-900">
    <!-- Admin Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.products.index') }}" class="text-xl font-bold text-gray-900">
                        Admin Panel
                    </a>
                    <span class="text-gray-400">|</span>
                    <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800" target="_blank">
                        View Site
                    </a>
                </div>
                
                <!-- Admin Navigation -->
                <nav class="flex space-x-6">
                    <a href="{{ route('admin.products.index') }}" 
                       class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.products.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                        Products
                    </a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Categories
                    </a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Brands
                    </a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        SEO
                    </a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
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