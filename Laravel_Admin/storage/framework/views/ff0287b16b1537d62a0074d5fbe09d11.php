<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <title>OKO VISION - Portal Admin</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #050810;
            --bg-secondary: #0A1225;
            --accent-cyan: #00D2FF;
            --accent-blue: #0077EE;
            --text-primary: #D1D9E6;
            --text-secondary: #8A99AF;
            --success: #2ECC71;
            --danger: #FF3131;
            --glass: rgba(10, 18, 37, 0.85);
            --border: rgba(0, 210, 255, 0.2);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #050810;
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        
        /* Cinematic High-Tech Background (Final Refined Version) */
        .bg-tech-master {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at center, #080D1A 0%, #050810 100%);
            z-index: -1;
            overflow: hidden;
        }

        /* Cinematic Grain/Noise Overlay */
        .bg-tech-master::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.03;
            pointer-events: none;
            z-index: 1;
        }

        /* Radial Vignette to Focus on Card */
        .bg-vignette {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, transparent 30%, rgba(0, 0, 0, 0.6) 100%);
            z-index: 2;
            pointer-events: none;
        }

        .nebula-glow {
            position: absolute;
            width: 120%;
            height: 120%;
            top: -10%;
            left: -10%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(0, 210, 255, 0.2) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(0, 119, 238, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(0, 210, 255, 0.05) 0%, transparent 60%);
            filter: blur(80px);
            animation: nebula-pulse 12s infinite alternate ease-in-out;
            z-index: 0;
        }

        @keyframes nebula-pulse {
            from { opacity: 0.4; transform: scale(1) rotate(0deg); }
            to { opacity: 0.7; transform: scale(1.1) rotate(5deg); }
        }

        .grid-3d {
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background-image: 
                linear-gradient(rgba(0, 210, 255, 0.1) 1.5px, transparent 1.5px),
                linear-gradient(90deg, rgba(0, 210, 255, 0.1) 1.5px, transparent 1.5px);
            background-size: 100px 100px;
            transform: perspective(800px) rotateX(60deg);
            animation: grid-travel 20s linear infinite, grid-glitch 10s infinite;
            z-index: 1;
            mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
        }

        @keyframes grid-glitch {
            0%, 94%, 100% { transform: perspective(800px) rotateX(60deg) skewX(0deg); opacity: 0.8; }
            95% { transform: perspective(800px) rotateX(60deg) skewX(1deg); opacity: 0.5; filter: hue-rotate(90deg); }
            96% { transform: perspective(800px) rotateX(60deg) skewX(-1deg); opacity: 0.6; filter: hue-rotate(-90deg); }
            97% { transform: perspective(800px) rotateX(60deg) skewX(0deg); opacity: 0.8; }
        }

        @keyframes grid-travel {
            from { background-position: 0 0; }
            to { background-position: 0 100px; }
        }

        /* Scan Effect in the Background (Final Sharp Line) */
        .bg-scanner {
            position: absolute;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(0, 210, 255, 0.3) 15%, 
                var(--accent-cyan) 50%, 
                rgba(0, 210, 255, 0.3) 85%, 
                transparent 100%
            );
            box-shadow: 
                0 0 30px var(--accent-cyan), 
                0 0 60px rgba(0, 210, 255, 0.4),
                0 0 100px rgba(0, 210, 255, 0.2);
            z-index: 3;
            pointer-events: none;
            animation: background-scan 10s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        .bg-scanner::after {
            content: 'SCAN_AUDIT_ACTIVE';
            position: absolute;
            right: 80px;
            top: -25px;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            font-weight: 900;
            color: var(--accent-cyan);
            letter-spacing: 5px;
            text-shadow: 0 0 15px var(--accent-cyan);
            animation: scan-text-blink 1.5s steps(2) infinite;
        }

        @keyframes scan-text-blink {
            0% { opacity: 0.1; transform: translateX(5px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        @keyframes background-scan {
            0% { transform: translateY(-15vh); opacity: 0; }
            8%, 92% { opacity: 1; }
            50% { transform: translateY(115vh); opacity: 1; }
            100% { transform: translateY(-15vh); opacity: 0; }
        }

        /* HUD Corners (Refined) */
        .hud-corner {
            position: fixed;
            width: 120px;
            height: 120px;
            z-index: 100;
            pointer-events: none;
            opacity: 0.4;
            transition: opacity 0.5s;
        }

        .corner-tl { top: 30px; left: 30px; }
        .corner-tr { top: 30px; right: 30px; }
        .corner-bl { bottom: 30px; left: 30px; }
        .corner-br { bottom: 30px; right: 30px; }

        .hud-corner::before, .hud-corner::after {
            content: '';
            position: absolute;
            background: var(--accent-cyan);
            box-shadow: 0 0 15px var(--accent-cyan);
        }

        .hud-corner::before { width: 30px; height: 3px; }
        .hud-corner::after { width: 3px; height: 30px; }

        .corner-tl::before { top: 0; left: 0; } .corner-tl::after { top: 0; left: 0; }
        .corner-tr::before { top: 0; right: 0; } .corner-tr::after { top: 0; right: 0; }
        .corner-bl::before { bottom: 0; left: 0; } .corner-bl::after { bottom: 0; left: 0; }
        .corner-br::before { bottom: 0; right: 0; } .corner-br::after { bottom: 0; right: 0; }

        /* Data Streams (More Cinematic) */
        .data-stream {
            position: absolute;
            width: 1px;
            height: 150px;
            background: linear-gradient(to bottom, transparent, var(--accent-cyan), transparent);
            opacity: 0.2;
            animation: data-drop linear infinite;
            z-index: 1;
        }

        @keyframes data-drop {
            0% { transform: translateY(-200px); opacity: 0; }
            20% { opacity: 0.4; }
            80% { opacity: 0.4; }
            100% { transform: translateY(110vh); opacity: 0; }
        }

        .tech-particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: var(--accent-cyan);
            border-radius: 50%;
            filter: drop-shadow(0 0 5px var(--accent-cyan));
            pointer-events: none;
            animation: particle-fly linear infinite;
        }

        @keyframes particle-fly {
            from { transform: translate(var(--startX), var(--startY)); opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            to { transform: translate(var(--endX), var(--endY)); opacity: 0; }
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
            padding: 2rem;
            animation: container-entrance 1.2s cubic-bezier(0.19, 1, 0.22, 1) forwards;
        }

        @keyframes container-entrance {
            0% { transform: scale(0.9) translateY(40px); opacity: 0; filter: blur(10px); }
            100% { transform: scale(1) translateY(0); opacity: 1; filter: blur(0); }
        }

        .login-card {
            background: var(--glass);
            border-radius: 40px;
            padding: 3.5rem 4rem;
            box-shadow: 
                0 0 100px rgba(0, 0, 0, 0.8), 
                0 0 40px rgba(0, 210, 255, 0.1),
                inset 0 0 30px rgba(0, 210, 255, 0.05);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 210, 255, 0.2);
            backdrop-filter: blur(25px);
            z-index: 1;
            display: flex;
            flex-direction: column;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(
                transparent, 
                rgba(0, 210, 255, 0.1), 
                var(--accent-cyan), 
                var(--accent-blue), 
                rgba(0, 210, 255, 0.1), 
                transparent
            );
            animation: rotate-border 10s linear infinite;
            z-index: -2;
        }

        .login-card::after {
            content: '';
            position: absolute;
            inset: 2px;
            background: var(--bg-secondary);
            border-radius: 38px;
            z-index: -1;
            box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.7);
        }
        
        @keyframes rotate-border {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .logo {
            width: 110px;
            height: 110px;
            background: transparent;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 5;
            perspective: 1000px;
            animation: logo-entrance 1.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }
        
        @keyframes logo-entrance {
            0% { transform: scale(0.5) rotateY(-180deg); opacity: 0; filter: blur(15px); }
            100% { transform: scale(1) rotateY(0deg); opacity: 1; filter: blur(0); }
        }

        /* Digital Iris Effect */
        .logo-inner {
            width: 80px;
            height: 80px;
            background: #050810;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
            box-shadow: 
                inset 0 0 40px rgba(0, 210, 255, 0.4), 
                0 0 40px rgba(0, 0, 0, 0.9),
                0 0 10px var(--accent-cyan);
            border: 1px solid rgba(0, 210, 255, 0.3);
            overflow: hidden;
        }

        .eye-blink-wrapper {
            animation: eye-blink 5s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .eye-pulse-wrapper {
            animation: eye-pulse 4s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo i {
            font-size: 3rem;
            color: var(--accent-cyan);
            text-shadow: 
                0 0 10px rgba(0, 210, 255, 0.8),
                2px 0 5px rgba(255, 0, 128, 0.5), 
                -2px 0 5px rgba(0, 255, 255, 0.5);
            animation: eye-glitch 6s skew infinite;
            position: relative;
            z-index: 3;
            display: block;
        }

        @keyframes eye-blink {
            0%, 90%, 100% { transform: scaleY(1); }
            95% { transform: scaleY(0.1); }
        }

        @keyframes eye-pulse {
            0%, 100% { transform: scale(1); filter: brightness(1); }
            50% { transform: scale(1.05); filter: brightness(1.3); }
        }

        @keyframes eye-glitch {
            0%, 94%, 100% { transform: skew(0deg) translate(0); opacity: 1; }
            95% { transform: skew(15deg) translate(2px); opacity: 0.7; color: #ff0080; }
            96% { transform: skew(-15deg) translate(-2px); opacity: 0.8; color: #00ffff; }
            97% { transform: skew(0deg) translate(0); opacity: 1; }
        }

        /* Triple Rotating Rings */
        .logo::before {
            content: '';
            position: absolute;
            inset: -15px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, 
                transparent, 
                var(--accent-cyan) 5%, 
                transparent 15%, 
                var(--accent-blue) 45%, 
                transparent 55%);
            animation: rotate-outer 6s linear infinite;
            mask: radial-gradient(transparent 65%, black 66%);
            -webkit-mask: radial-gradient(transparent 65%, black 66%);
            filter: drop-shadow(0 0 15px var(--accent-cyan));
            opacity: 0.8;
        }

        .logo::after {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 1px dashed var(--accent-cyan);
            animation: rotate-inner 4s linear infinite reverse;
            opacity: 0.4;
        }

        @keyframes rotate-outer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes rotate-inner {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .brand-name {
            font-size: 2rem;
            font-weight: 900;
            color: #fff;
            margin-bottom: 0.3rem;
            letter-spacing: 2px;
            position: relative;
            display: inline-block;
            background: linear-gradient(90deg, #fff, var(--accent-cyan), #fff);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 5s linear infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        
        .tagline {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            opacity: 0.8;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.8rem;
            color: var(--accent-cyan);
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .input-field {
            width: 100%;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(0, 210, 255, 0.2);
            color: #fff;
            padding: 1.1rem 1.4rem;
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--accent-cyan);
            background: rgba(0, 210, 255, 0.05);
            box-shadow: inset 0 0 15px rgba(0, 210, 255, 0.1);
        }
        
        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-cyan);
            opacity: 0.6;
        }
        
        .input-icon .input-field {
            padding-right: 3.5rem;
        }
        
        .login-btn {
            width: 100%;
            background: transparent;
            border: 2px solid var(--accent-cyan);
            color: var(--accent-cyan);
            font-weight: 800;
            font-size: 1.1rem;
            padding: 1.2rem;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-transform: uppercase;
            letter-spacing: 3px;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            background: rgba(0, 210, 255, 0.1);
            transform: translateY(-5px);
            box-shadow: 0 0 30px rgba(0, 210, 255, 0.2);
            text-shadow: 0 0 10px var(--accent-cyan);
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 210, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .login-btn:hover::before {
            left: 100%;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid var(--danger);
            background: rgba(255, 49, 49, 0.1);
            color: var(--danger);
            font-family: monospace;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            border-color: var(--success);
            color: var(--success);
        }
        
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(0, 210, 255, 0.3);
            border-top: 2px solid var(--accent-cyan);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading .spinner { display: block; }
        .loading .btn-text { display: none; }
    </style>
</head>
<body>
    <div class="bg-tech-master">
        <div class="bg-vignette"></div>
        <div class="nebula-glow"></div>
        <div class="grid-3d"></div>
        <div class="bg-scanner"></div>
        <div id="data-streams"></div>
        <div id="tech-particles"></div>
    </div>

    <!-- HUD Elements -->
    <div class="hud-corner corner-tl"></div>
    <div class="hud-corner corner-tr"></div>
    <div class="hud-corner corner-bl"></div>
    <div class="hud-corner corner-br"></div>
    
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo">
                    <div class="logo-inner">
                        <div class="eye-blink-wrapper">
                            <div class="eye-pulse-wrapper">
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <h1 class="brand-name">OKO VISION</h1>
                <p class="tagline">Admin Control Panel</p>
            </div>
            
            <!-- Login Form -->
            <form method="POST" action="<?php echo e(route('login')); ?>" id="loginForm">
                <?php echo csrf_field(); ?>
                
                <!-- Error Messages -->
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul style="list-style: none;">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-icon">
                        <input id="email" type="email" class="input-field" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="email" autofocus placeholder="admin@okovision.com">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                
                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-icon">
                        <input id="password" type="password" class="input-field" name="password" required autocomplete="current-password" placeholder="••••••••">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="login-btn" id="submitBtn">
                    <span class="btn-text">Access System</span>
                    <div class="spinner"></div>
                </button>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        function initTechParticles() {
            const container = document.getElementById('tech-particles');
            const count = 30;
            
            for (let i = 0; i < count; i++) {
                const p = document.createElement('div');
                p.className = 'tech-particle';
                
                const startX = Math.random() * 100 + 'vw';
                const startY = Math.random() * 100 + 'vh';
                const endX = (Math.random() * 100 - 50) + 'vw';
                const endY = (Math.random() * 100 - 50) + 'vh';
                const duration = Math.random() * 20 + 10 + 's';
                const delay = Math.random() * -20 + 's';
                
                p.style.setProperty('--startX', startX);
                p.style.setProperty('--startY', startY);
                p.style.setProperty('--endX', endX);
                p.style.setProperty('--endY', endY);
                p.style.animationDuration = duration;
                p.style.animationDelay = delay;
                
                container.appendChild(p);
            }
        }
        initTechParticles();

        function initDataStreams() {
            const container = document.getElementById('data-streams');
            const count = 25;
            
            for (let i = 0; i < count; i++) {
                const s = document.createElement('div');
                s.className = 'data-stream';
                
                // Add binary text to some streams
                if (Math.random() > 0.6) {
                    const codes = ['01', '10', '11', '00', '>>', '<<', 'SCN'];
                    s.textContent = codes[Math.floor(Math.random() * codes.length)];
                    s.style.color = 'var(--accent-cyan)';
                    s.style.fontSize = '9px';
                    s.style.fontWeight = 'bold';
                    s.style.fontFamily = 'monospace';
                    s.style.opacity = '0.6';
                    s.style.writingMode = 'vertical-rl';
                }
                
                const left = Math.random() * 100 + 'vw';
                const duration = Math.random() * 5 + 4 + 's';
                const delay = Math.random() * -15 + 's';
                
                s.style.left = left;
                s.style.animationDuration = duration;
                s.style.animationDelay = delay;
                
                container.appendChild(s);
            }
        }
        initDataStreams();

        document.addEventListener('mousemove', (e) => {
            const card = document.querySelector('.login-container');
            const x = (window.innerWidth / 2 - e.clientX) / 80;
            const y = (window.innerHeight / 2 - e.clientY) / 80;
            card.style.transform = `rotateY(${x}deg) rotateX(${-y}deg)`;
        });
    </script>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/auth/login.blade.php ENDPATH**/ ?>