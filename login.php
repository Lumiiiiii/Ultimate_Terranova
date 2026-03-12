<?php
ob_start();
session_start();

// Configurazione password fissa
$password_corretta = "naturopata";
$errore = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password_inserita = $_POST['password'] ?? '';

    if ($password_inserita === $password_corretta) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $errore = "Password non corretta. Riprova.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aequa — Login</title>
 <link rel="icon" type="image/png" href="assets/img/logo.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script>
  // 1. Forza immediatamente il tema light per evitare che Bootstrap applichi il nero
  document.documentElement.setAttribute('data-bs-theme', 'light');
</script>

<style>
  /* 2. Definisci subito lo sfondo esatto della tua dashboard nel root */
  :root { 
    background-color: #f8f9fa !important; /* Il grigio chiaro di Bootstrap */
  }
  body { 
    background-color: #f8f9fa !important; 
    visibility: visible !important;
  }
</style>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0f0f23;
            color: #ffffff;
            overflow: hidden;
            position: relative;
        }

        /* ── ANIMATED GRADIENT BACKGROUND ─────────────────────────────── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: 
                radial-gradient(ellipse at 20% 50%, rgba(46, 204, 113, 0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(59, 130, 246, 0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(34, 197, 94, 0.08) 0%, transparent 50%),
                linear-gradient(160deg, #0f0f23 0%, #1a1a2e 40%, #16213e 100%);
            z-index: 0;
            animation: bgShift 12s ease-in-out infinite alternate;
        }

        @keyframes bgShift {
            0%   { opacity: 1; }
            50%  { opacity: 0.85; }
            100% { opacity: 1; }
        }

        /* ── FLOATING PARTICLES ──────────────────────────────────────── */
        .particles {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }
        .particle {
            position: absolute;
            background: rgba(46, 180, 160, 0.3);
            border-radius: 50%;
            animation: float linear infinite;
        }
        .particle:nth-child(1)  { left:  5%; animation-duration: 18s; animation-delay: 0s;   width: 3px; height: 3px; }
        .particle:nth-child(2)  { left: 15%; animation-duration: 22s; animation-delay: -3s;  width: 5px; height: 5px; }
        .particle:nth-child(3)  { left: 25%; animation-duration: 16s; animation-delay: -6s;  width: 2px; height: 2px; }
        .particle:nth-child(4)  { left: 35%; animation-duration: 24s; animation-delay: -2s;  width: 4px; height: 4px; }
        .particle:nth-child(5)  { left: 45%; animation-duration: 19s; animation-delay: -8s;  width: 3px; height: 3px; }
        .particle:nth-child(6)  { left: 55%; animation-duration: 21s; animation-delay: -4s;  width: 6px; height: 6px; }
        .particle:nth-child(7)  { left: 65%; animation-duration: 17s; animation-delay: -10s; width: 2px; height: 2px; }
        .particle:nth-child(8)  { left: 75%; animation-duration: 20s; animation-delay: -1s;  width: 5px; height: 5px; }
        .particle:nth-child(9)  { left: 85%; animation-duration: 23s; animation-delay: -5s;  width: 3px; height: 3px; }
        .particle:nth-child(10) { left: 92%; animation-duration: 15s; animation-delay: -7s;  width: 4px; height: 4px; }

        @keyframes float {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        /* ── CUSTOM BOOTSTRAP OVERRIDES (GLASSMORPHISM) ──────────────── */
        .glass-card {
            background: rgba(255, 255, 255, 0.04) !important;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.05) inset !important;
            animation: cardEntrance 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(30px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #2ecc71, #3b82f6);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(46, 180, 160, 0.3);
            animation: iconPulse 3s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(46, 180, 160, 0.3); }
            50%      { box-shadow: 0 4px 30px rgba(59, 130, 246, 0.5); }
        }

        /* Customizing Bootstrap Input for Dark Mode */
        .form-control.glass-input {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            transition: all 0.3s ease;
        }
        .form-control.glass-input::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }
        .form-control.glass-input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(46, 180, 160, 0.5);
            box-shadow: none;
            color: #ffffff;
        }

        .input-group-text.glass-addon {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
            border-right: none;
        }
        
        .form-control.glass-input {
            border-left: none;
        }
        
        .btn-toggle-pass {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: none;
            color: rgba(255, 255, 255, 0.5);
            transition: color 0.3s;
        }
        .btn-toggle-pass:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .input-group:focus-within .glass-addon,
        .input-group:focus-within .glass-input,
        .input-group:focus-within .btn-toggle-pass {
            border-color: rgba(46, 180, 160, 0.5);
        }
        .input-group:focus-within .glass-addon svg {
            color: #2eb4a0;
        }

        /* Custom Button */
        .btn-primary-custom {
            background: linear-gradient(135deg, #2ecc71, #3b82f6);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                135deg,
                transparent 0%,
                transparent 35%,
                rgba(255, 255, 255, 0.25) 45%,
                rgba(255, 255, 255, 0.35) 50%,
                rgba(255, 255, 255, 0.25) 55%,
                transparent 65%,
                transparent 100%
            );
            animation: waveEffect 2.5s ease-in-out infinite;
            z-index: -1;
            pointer-events: none;
        }
        @keyframes waveEffect {
            0%   { transform: translate(-60%, -60%); }
            100% { transform: translate(60%, 60%); }
        }
        .btn-primary-custom:hover, .btn-primary-custom:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(46, 180, 160, 0.35);
            color: white;
        }

        /* Error Shake Animation */
        .shake-alert {
            animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
        }
        @keyframes shake {
            10%, 90%  { transform: translateX(-1px); }
            20%, 80%  { transform: translateX(2px); }
            30%, 50%, 70% { transform: translateX(-3px); }
            40%, 60%  { transform: translateX(3px); }
        }
    </style>
</head>
<body>

    <!-- Floating particles -->
    <div class="particles">
        <!-- 10 particles -->
        <?php for($i=0; $i<10; $i++): ?><div class="particle"></div><?php endfor; ?>
    </div>

    <!-- Main Container Bootstrap -->
    <div class="container d-flex justify-content-center align-items-center vh-100 position-relative" style="z-index: 1;">
        
        <!-- Formattazione tramite utility class di Bootstrap -->
        <div class="card glass-card p-4 p-sm-5 rounded-4 w-100" style="max-width: 420px;">
            <div class="card-body p-0">
                
                <!-- Brand -->
                <div class="text-center mb-4">
                        <img src="assets/img/logo.png" alt="Logo" style="max-width: 100px; max-height: 100px; object-fit: contain;">
                    <h1 class="h3 fw-bold mb-1" style="background: linear-gradient(135deg, #2ecc71, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Aequa</h1>
                </div>

                <!-- Messaggio di Errore -->
                <?php if (!empty($errore)): ?>
                    <div class="alert shake-alert d-flex align-items-center gap-2 py-2 px-3 rounded-3" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-danger">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small fw-medium"><?php echo htmlspecialchars($errore); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" action="login.php" id="loginForm">
                    <div class="mb-4">
                        <label for="password" class="form-label small fw-semibold text-uppercase text-white-50 letter-spacing-1">Password</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text glass-addon px-3" id="password-addon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="form-control glass-input fs-6"
                                placeholder="Inserisci la password"
                                aria-describedby="password-addon"
                                required
                                autocomplete="current-password"
                            >
                            <button class="btn btn-toggle-pass px-3" type="button" id="togglePassword" aria-label="Mostra password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" id="eyeIcon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100 py-3 rounded-3" id="loginBtn">
                        Accedi
                    </button>
                </form>

                <!-- Footer -->
                <div class="text-center mt-4 pt-4 border-top border-light border-opacity-10">
                    <span class="small text-white-50">&copy; <?php echo date("Y"); ?> Aequa &middot; Area Riservata</span>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle password visibility
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';

            eyeIcon.innerHTML = isPassword
                ? '<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />'
                : '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
        });

        // Submit button loading state using Bootstrap spinner
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Accesso in corso…';
            btn.classList.add('disabled');
        });

        // Auto-focus password field
        window.addEventListener('load', function () {
            document.getElementById('password').focus();
        });
    </script>

</body>
</html>
