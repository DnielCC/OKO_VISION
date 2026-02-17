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
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --bg-primary: #050A18;
            --bg-secondary: #0D1B35;
            --accent-cyan: #00F2FF;
            --text-primary: #E0E6ED;
            --success: #00FF87;
            --danger: #FF3131;
            --warning: #FFA500;
        }
        
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
        }
        
        .glassmorphism {
            background: rgba(13, 27, 53, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 242, 255, 0.1);
        }
        
        .neon-border {
            border: 1px solid var(--accent-cyan);
            box-shadow: 0 0 10px rgba(0, 242, 255, 0.5);
        }
        
        .neon-glow {
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.3);
        }
        
        .neon-text {
            color: var(--accent-cyan);
            text-shadow: 0 0 10px rgba(0, 242, 255, 0.5);
        }
        
        .sidebar-item {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .sidebar-item:hover {
            background: rgba(0, 242, 255, 0.1);
            border-left: 3px solid var(--accent-cyan);
        }
        
        .sidebar-item.active {
            background: rgba(0, 242, 255, 0.15);
            border-left: 3px solid var(--accent-cyan);
        }
        
        .card {
            background: var(--bg-secondary);
            border: 1px solid rgba(0, 242, 255, 0.2);
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
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.5);
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
            background: rgba(0, 242, 255, 0.1);
            box-shadow: 0 0 10px rgba(0, 242, 255, 0.3);
        }
        
        .input-field {
            background: rgba(13, 27, 53, 0.5);
            border: 1px solid rgba(0, 242, 255, 0.3);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--accent-cyan);
            box-shadow: 0 0 10px rgba(0, 242, 255, 0.3);
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
            background: rgba(0, 242, 255, 0.1);
            color: var(--accent-cyan);
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 242, 255, 0.2);
        }
        
        .table-custom td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 242, 255, 0.1);
            color: var(--text-primary);
        }
        
        .table-custom tr:hover {
            background: rgba(0, 242, 255, 0.05);
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
                <h1 class="text-xl font-bold neon-text">OKO VISION</h1>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="space-y-2">
                <a href="{{ route('dashboard') }}" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg text-gray-300 hover:text-white">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ route('alertas') }}" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg text-gray-300 hover:text-white">
                    <i class="fas fa-exclamation-triangle w-5"></i>
                    <span>Alertas</span>
                </a>
                
                <a href="{{ route('usuarios') }}" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg text-gray-300 hover:text-white">
                    <i class="fas fa-users w-5"></i>
                    <span>Usuarios</span>
                </a>
                
                <a href="{{ route('reportes') }}" class="sidebar-item flex items-center space-x-3 p-3 rounded-lg text-gray-300 hover:text-white">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>Reportes</span>
                </a>
            </nav>
            
            <!-- User Profile -->
            <div class="absolute bottom-6 left-6 right-6">
                <div class="glassmorphism p-4 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-white">Administrador</p>
                            <p class="text-xs text-gray-400">admin@okovision.com</p>
                        </div>
                    </div>
                </div>
            </div>
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
                <h2 class="text-2xl font-bold text-white">{{ $title ?? 'Dashboard' }}</h2>
                <div class="flex items-center space-x-4">
                    <!-- Status Indicator -->
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-300">Sistema Activo</span>
                    </div>
                    
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
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }
        
        // Set active sidebar item
        document.addEventListener('DOMContentLoaded', () => {
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
