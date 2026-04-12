<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>OKO VISION - Sistema Inteligente de Control de Acceso</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />
    
    <!-- Heroicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS (CDN for quick fix) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        :root {
            /* Dark theme (default) */
            --bg-primary: #050A18;
            --bg-secondary: #0D1B35;
            --accent-cyan: #00F2FF;
            --text-primary: #E0E6ED;
            --text-secondary: #9CA3AF;
            --success: #00FF87;
            --danger: #FF3131;
            --warning: #FFA500;
            --glass: rgba(13, 27, 53, 0.7);
            --border: rgba(0, 242, 255, 0.1);
        }

        [data-theme="light"] {
            /* Light theme para administrador - texto negro */
            --bg-primary: #E2E8F0;
            --bg-secondary: #FFFFFF;
            --accent-cyan: #0369A1;
            --text-primary: #000000;
            --text-secondary: #374151;
            --success: #059669;
            --danger: #DC2626;
            --warning: #D97706;
            --glass: rgba(255, 255, 255, 0.9);
            --border: rgba(3, 105, 161, 0.2);
        }
        
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
        }
        
        .glassmorphism {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .neon-border {
            border: 1px solid var(--accent-cyan);
            box-shadow: 0 0 8px rgba(3, 105, 161, 0.2);
        }
        
        .neon-glow {
            box-shadow: 0 0 16px rgba(3, 105, 161, 0.15);
        }
        
        .neon-text {
            color: var(--accent-cyan);
            text-shadow: 0 0 10px rgba(0, 242, 255, 0.5);
        }
        
        .sidebar-item {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-item:hover {
            background: rgba(3, 105, 161, 0.08);
            color: var(--accent-cyan);
            transform: translateX(3px);
        }
        
        [data-theme="light"] .sidebar-item {
            color: var(--text-primary);
        }
        
        [data-theme="light"] .sidebar-item:hover {
            color: var(--accent-cyan);
        }
        
        /* Theme-aware text colors - Global */
        body {
            color: var(--text-primary);
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: var(--text-primary);
        }
        
        p, span, div, td, th, li, a {
            color: var(--text-primary);
        }
        
        .text-gray-600, .text-gray-700, .text-gray-800 {
            color: var(--text-primary) !important;
        }
        
        .text-gray-400, .text-gray-500 {
            color: var(--text-secondary) !important;
        }
        
        /* Theme-aware text classes */
        [data-theme="light"] .theme-text {
            color: var(--text-primary) !important;
        }
        
        [data-theme="light"] .theme-text-secondary {
            color: var(--text-secondary) !important;
        }
        
        /* Override ALL text colors in light mode - Complete override */
        [data-theme="light"] * {
            color: inherit;
        }
        
        [data-theme="light"] body,
        [data-theme="light"] h1,
        [data-theme="light"] h2,
        [data-theme="light"] h3,
        [data-theme="light"] h4,
        [data-theme="light"] h5,
        [data-theme="light"] h6,
        [data-theme="light"] p,
        [data-theme="light"] span,
        [data-theme="light"] div,
        [data-theme="light"] td,
        [data-theme="light"] th,
        [data-theme="light"] li {
            color: var(--text-primary) !important;
        }
        
        /* Override all Tailwind text color classes in light mode */
        [data-theme="light"] .text-white,
        [data-theme="light"] .text-gray-300,
        [data-theme="light"] .text-gray-400,
        [data-theme="light"] .text-gray-500,
        [data-theme="light"] .text-gray-600,
        [data-theme="light"] .text-gray-700,
        [data-theme="light"] .text-gray-800 {
            color: var(--text-primary) !important;
        }
        
        [data-theme="light"] .text-gray-400,
        [data-theme="light"] .text-gray-500,
        [data-theme="light"] .text-secondary {
            color: var(--text-secondary) !important;
        }
        
        /* Keep accent colors but make them theme-aware */
        [data-theme="light"] .text-cyan-400 {
            color: var(--accent-cyan) !important;
        }
        
        [data-theme="light"] .text-green-400 {
            color: var(--success) !important;
        }
        
        [data-theme="light"] .text-red-500 {
            color: var(--danger) !important;
        }
        
        [data-theme="light"] .text-blue-600 {
            color: var(--accent-cyan) !important;
        }
        
        /* Keep white text on dark backgrounds */
        [data-theme="light"] .bg-black\/50 *,
        [data-theme="light"] .bg-gray-900 *,
        [data-theme="light"] .bg-gray-700 *,
        [data-theme="light"] .bg-gray-800 *,
        [data-theme="light"] .bg-black\/70 *,
        [data-theme="light"] [class*="bg-gray-7"] *,
        [data-theme="light"] [class*="bg-black"] *,
        [data-theme="light"] [style*="background: rgb"] *,
        [data-theme="light"] [style*="background:#"] *,
        [data-theme="light"] .bg-cyan-400\/80 *,
        [data-theme="light"] .bg-green-400\/80 *,
        [data-theme="light"] .bg-cyan-400 *,
        [data-theme="light"] .bg-green-400 * {
            color: white !important;
        }
        
        /* Specific elements that should stay white */
        [data-theme="light"] .text-white {
            color: white !important;
        }
        
        /* Detection boxes and overlays */
        [data-theme="light"] [style*="border-cyan-400"] *,
        [data-theme="light"] [style*="border-green-400"] * {
            color: black !important;
        }
        
        [data-theme="light"] [style*="bg-cyan-400"] *,
        [data-theme="light"] [style*="bg-green-400"] * {
            color: black !important;
        }
        
        .sidebar-item.active {
            background: var(--accent-cyan);
            color: white;
        }
        
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
        }
        
        .btn-primary {
            background: var(--accent-cyan);
            color: var(--bg-primary);
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary:hover {
            box-shadow: 0 0 12px rgba(3, 105, 161, 0.3);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--accent-cyan);
            border: 1px solid var(--accent-cyan);
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: rgba(3, 105, 161, 0.08);
            box-shadow: 0 0 8px rgba(3, 105, 161, 0.15);
        }
        
        .input-field {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--border);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--accent-cyan);
            box-shadow: 0 0 8px rgba(3, 105, 161, 0.15);
        }
        
        .badge-success {
            background: var(--success);
            color: var(--bg-primary);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-danger {
            background: var(--danger);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-warning {
            background: var(--warning);
            color: var(--bg-primary);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .table-custom {
            background: var(--bg-secondary);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table-custom th {
            background: rgba(3, 105, 161, 0.06);
            color: var(--accent-cyan);
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .table-custom td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
        }
        
        .table-custom tr:hover {
            background: rgba(3, 105, 161, 0.04);
        }

        .theme-toggle {
            background: rgba(3, 105, 161, 0.08);
            border: 1px solid rgba(3, 105, 161, 0.25);
            color: var(--accent-cyan);
            padding: 0.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            width: 36px;
            height: 36px;
        }
        
        .theme-toggle:hover {
            background: var(--accent-cyan);
            color: white;
            box-shadow: 0 0 12px rgba(3, 105, 161, 0.3);
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
                height: 100vh;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="sidebar glassmorphism w-64 min-h-screen fixed left-0 top-0 z-40 transition-transform duration-300">
        <div class="p-6">
            <!-- Logo -->
            <div class="flex items-center space-x-3 mb-8">
                <div class="w-10 h-10 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-eye text-white text-lg"></i>
                </div>
                <h1 class="text-xl font-bold theme-text">OKO VISION</h1>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="space-y-2">
                <a href="{{ route('dashboard') }}" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg theme-text hover:text-accent-cyan">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ route('alertas') }}" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg theme-text hover:text-accent-cyan">
                    <i class="fas fa-exclamation-triangle w-5"></i>
                    <span>Alertas</span>
                </a>
                
                <a href="{{ route('usuarios') }}" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg theme-text hover:text-accent-cyan">
                    <i class="fas fa-users w-5"></i>
                    <span>Usuarios</span>
                </a>
                
                <a href="{{ route('reportes') }}" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg theme-text hover:text-accent-cyan">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>Reportes</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg theme-text hover:text-red-500 w-full transition-colors duration-200">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </nav>
            
            <!-- User Profile -->
            @auth
            <div class="absolute bottom-6 left-6 right-6">
                <div class="glassmorphism p-4 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium theme-text">{{ Auth::user()->nombre }} {{ Auth::user()->apellidos }}</p>
                            <p class="text-xs theme-text-secondary">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </aside>
    
    <!-- Mobile Menu Toggle -->
    <button id="menuToggle" class="md:hidden fixed top-4 left-4 z-50 p-2 rounded-lg glassmorphism">
        <i class="fas fa-bars text-cyan-400"></i>
    </button>
    
    <!-- Main Content -->
    <main class="main-content md:ml-64 min-h-screen">
        <!-- Top Bar -->
        <header class="glassmorphism border-b border-gray-700">
            <div class="px-6 py-4 flex items-center justify-between">
                <h2 class="text-2xl font-bold theme-text">{{ $title ?? 'Dashboard' }}</h2>
                <div class="flex items-center space-x-4">
                    <!-- Status Indicator -->
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm theme-text-secondary">Sistema Activo</span>
                    </div>
                    
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
                        <i class="fas fa-sun" id="theme-icon"></i>
                    </button>
                    
                    <!-- Notifications -->
                    <button class="relative p-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-bell text-cyan-400"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <div class="p-6">
            @yield('content')
        </div>
    </main>
    
    <script>
        // Theme toggle functionality
        function initTheme() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            const themeIcon = document.getElementById('theme-icon');
            
            if (savedTheme === 'light') {
                document.documentElement.setAttribute('data-theme', 'light');
                themeIcon.className = 'fas fa-moon';
            } else {
                document.documentElement.removeAttribute('data-theme');
                themeIcon.className = 'fas fa-sun';
            }
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const themeIcon = document.getElementById('theme-icon');
            
            if (currentTheme === 'light') {
                document.documentElement.removeAttribute('data-theme');
                themeIcon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                themeIcon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            }
        }

        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }
        
        // Set active sidebar item and initialize theme
        document.addEventListener('DOMContentLoaded', () => {
            initTheme();
            
            const currentPath = window.location.pathname;
            const sidebarItems = document.querySelectorAll('.sidebar-item');
            
            sidebarItems.forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
