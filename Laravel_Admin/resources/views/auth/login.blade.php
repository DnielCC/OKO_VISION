<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>OKO VISION - Iniciar Sesión</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />
    
    <!-- Heroicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #050A18 0%, #0D1B35 100%);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        /* Animated Background */
        .bg-animation {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            opacity: 0.3;
        }
        
        .bg-animation::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--accent-cyan) 0%, transparent 70%);
            border-radius: 50%;
            top: -200px;
            right: -200px;
            animation: float 20s infinite ease-in-out;
        }
        
        .bg-animation::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(0, 242, 255, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -150px;
            left: -150px;
            animation: float 15s infinite ease-in-out reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }
        
        .login-card {
            background: rgba(13, 27, 53, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 242, 255, 0.2);
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent-cyan), transparent);
            animation: scan 3s infinite linear;
        }
        
        @keyframes scan {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-cyan), #0080ff);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 10px 30px rgba(0, 242, 255, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(0, 242, 255, 0.3); }
            50% { transform: scale(1.05); box-shadow: 0 15px 40px rgba(0, 242, 255, 0.5); }
        }
        
        .logo i {
            font-size: 2rem;
            color: white;
        }
        
        .brand-name {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent-cyan), #0080ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .tagline {
            color: #9ca3af;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .input-field {
            width: 100%;
            background: rgba(13, 27, 53, 0.6);
            border: 1px solid rgba(0, 242, 255, 0.3);
            color: var(--text-primary);
            padding: 1rem;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--accent-cyan);
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.4);
            background: rgba(13, 27, 53, 0.8);
        }
        
        .input-field::placeholder {
            color: #6b7280;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-cyan);
            opacity: 0.7;
        }
        
        .input-icon .input-field {
            padding-right: 3rem;
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--accent-cyan), #0080ff);
            color: var(--bg-primary);
            font-weight: 600;
            font-size: 1rem;
            padding: 1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(0, 242, 255, 0.4);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        
        .login-btn:hover::before {
            left: 100%;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .forgot-password a {
            color: var(--accent-cyan);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: #0080ff;
            text-shadow: 0 0 10px rgba(0, 242, 255, 0.5);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }
        
        .alert-danger {
            background: rgba(255, 49, 49, 0.1);
            border-left-color: var(--danger);
            color: var(--danger);
        }
        
        .alert-success {
            background: rgba(0, 255, 135, 0.1);
            border-left-color: var(--success);
            color: var(--success);
        }
        
        /* Responsive Design */
        @media (max-width: 480px) {
            .login-container {
                padding: 1rem;
            }
            
            .login-card {
                padding: 2rem;
            }
            
            .brand-name {
                font-size: 1.5rem;
            }
        }
        
        /* Loading Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading .spinner {
            display: block;
        }
        
        .loading .btn-text {
            display: none;
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo">
                    <i class="fas fa-eye"></i>
                </div>
                <h1 class="brand-name">OKO VISION</h1>
                <p class="tagline">Sistema Inteligente de Control de Acceso</p>
            </div>
            
            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="list-unstyled mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif
                
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <div class="input-icon">
                        <input 
                            id="email" 
                            type="email" 
                            class="input-field" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autocomplete="email" 
                            autofocus 
                            placeholder="admin@okovision.com"
                        >
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                
                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-icon">
                        <input 
                            id="password" 
                            type="password" 
                            class="input-field" 
                            name="password" 
                            required 
                            autocomplete="current-password" 
                            placeholder="••••••••"
                        >
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                
                <!-- Remember Me -->
                <div class="form-group" style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                    <input 
                        id="remember" 
                        type="checkbox" 
                        name="remember" 
                        style="margin-right: 0.5rem; width: auto;"
                    >
                    <label for="remember" style="margin: 0; font-weight: normal; font-size: 0.9rem;">
                        Recordarme
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="login-btn" id="submitBtn">
                    <span class="btn-text">Iniciar Sesión</span>
                    <div class="spinner"></div>
                </button>
            </form>
            
            <!-- Forgot Password -->
            <div class="forgot-password">
                <a href="#">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Form submission loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
        
        // Add input focus effects
        const inputs = document.querySelectorAll('.input-field');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
