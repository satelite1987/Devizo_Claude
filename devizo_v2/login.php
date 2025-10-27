<?php
/**
 * DEVIZO v2.0 - Login Page
 *
 * Authentication page with demo user credentials visible
 */

define('DEVIZO_APP', true);

require_once __DIR__ . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Helpers.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Auth.php';

Session::init();

// If already logged in, redirect to dashboard
if (Auth::check()) {
    redirect(url('index.php'));
}

$error = '';
$info = '';

// Check for timeout
if (isset($_GET['timeout'])) {
    $info = 'Sesiunea dumneavoastră a expirat. Vă rugăm să vă autentificați din nou.';
}

// Check for security issue
if (isset($_GET['security'])) {
    $error = 'Sesiune invalidă detectată. Vă rugăm să vă autentificați din nou.';
}

// Handle login form submission
if (is_post()) {
    $email = post('email', '');
    $password = post('password', '');
    $remember = post('remember', false);

    // Validate CSRF token
    $csrfToken = post('csrf_token', '');
    if (!verify_csrf_token($csrfToken)) {
        $error = 'Token de securitate invalid. Vă rugăm să încercați din nou.';
    } else {
        try {
            if (Auth::login($email, $password)) {
                // Login successful
                $redirectUrl = get('redirect', url('index.php'));
                redirect($redirectUrl);
            } else {
                $error = 'Email sau parolă incorectă.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autentificare - DEVIZO v2.0</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: flex;
            min-height: 600px;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-left h1 {
            font-size: 48px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .login-left p {
            font-size: 18px;
            opacity: 0.95;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            list-style: none;
            margin-top: 30px;
        }

        .features li {
            padding: 12px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
        }

        .features li i {
            font-size: 20px;
            opacity: 0.9;
        }

        .login-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-info {
            background: #e7f3ff;
            color: #0066cc;
            border: 1px solid #b3d9ff;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 25px;
        }

        .remember-me input {
            width: auto;
        }

        .remember-me label {
            margin: 0;
            font-weight: normal;
            color: #666;
            font-size: 14px;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .demo-users {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .demo-users h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-user {
            background: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }

        .demo-user:hover {
            border-color: #667eea;
            transform: translateX(5px);
        }

        .demo-user:last-child {
            margin-bottom: 0;
        }

        .demo-user strong {
            display: block;
            color: #333;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .demo-user small {
            display: block;
            color: #666;
            font-size: 12px;
        }

        .demo-user code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
            color: #495057;
        }

        .version {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-left {
                padding: 40px 30px;
            }

            .login-left h1 {
                font-size: 36px;
            }

            .features {
                display: none;
            }

            .login-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-left">
            <h1>
                <i class="fas fa-file-invoice"></i>
                DEVIZO
            </h1>
            <p>Platforma Modernă pentru Gestiunea Devizelor și Ofertelor</p>
            <p style="font-size: 16px; opacity: 0.9;">
                Versiune 2.0 - Arhitectură Modulară Multi-Tenant
            </p>

            <ul class="features">
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Devize profesionale cu calcul automat manoperă</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Multi-currency cu curs BNR actualizat</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Export către software contabilitate</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Sistem modular extensibil</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Control permisiuni granular RBAC</span>
                </li>
            </ul>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-header">
                <h2>Bun venit!</h2>
                <p>Autentificați-vă pentru a continua</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($info): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php echo e($info); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="nume@exemplu.ro"
                        required
                        autofocus
                        value="<?php echo e(post('email', '')); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Parolă
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                    >
                </div>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Ține-mă minte</label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Autentificare
                </button>
            </form>

            <!-- Demo Users -->
            <div class="demo-users">
                <h3>
                    <i class="fas fa-users"></i> Utilizatori Demo
                </h3>

                <div class="demo-user" onclick="fillLogin('dominus@demo.devizo.ro', 'Demo123')">
                    <strong>🔷 Master Firmă</strong>
                    <small>Email: <code>dominus@demo.devizo.ro</code></small>
                    <small>Parolă: <code>Demo123</code></small>
                </div>

                <div class="demo-user" onclick="fillLogin('executor@demo.devizo.ro', 'Demo123')">
                    <strong>👤 Utilizator</strong>
                    <small>Email: <code>executor@demo.devizo.ro</code></small>
                    <small>Parolă: <code>Demo123</code></small>
                </div>
            </div>

            <div class="version">
                DEVIZO v2.0 - Modular Multi-Tenant SaaS
            </div>
        </div>
    </div>

    <script>
        function fillLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            document.getElementById('email').focus();
        }
    </script>
</body>
</html>
