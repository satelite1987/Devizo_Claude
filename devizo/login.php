<?php
/**
 * DEVIZO - Pagina de Login
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Daca utilizatorul este deja autentificat, redirectionam la pagina principala
if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

$error = '';
$success = '';

// Procesare formular login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = login($email, $password);

        if ($result['success']) {
            redirect(SITE_URL . '/index.php');
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Autentificare';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - DEVIZO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }

        .login-header {
            background: #3498db;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #2980b9;
        }

        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .demo-info {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }

        .demo-info h3 {
            margin-bottom: 10px;
            color: #3498db;
        }

        .demo-info ul {
            list-style: none;
            padding: 0;
        }

        .demo-info li {
            padding: 5px 0;
            font-size: 14px;
        }

        .demo-info code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h1><i class="fas fa-file-invoice"></i> DEVIZO</h1>
        <p>Aplicatie de Gestiune Devize</p>
    </div>

    <div class="login-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo clean($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo clean($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" id="email" name="email" required placeholder="exemplu@email.ro">
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Parola
                </label>
                <input type="password" id="password" name="password" required placeholder="********">
            </div>

            <button type="submit" name="login" class="btn">
                <i class="fas fa-sign-in-alt"></i> Autentificare
            </button>
        </form>

        <div class="demo-info">
            <h3><i class="fas fa-info-circle"></i> Conturi Demo</h3>
            <ul>
                <li><strong>Super Admin:</strong></li>
                <li>Email: <code>admin@devizo.ro</code></li>
                <li>Parola: <code>admin123</code></li>
                <li><hr style="margin: 10px 0;"></li>
                <li><strong>Master Firma:</strong></li>
                <li>Email: <code>doru@zaninstal.ro</code></li>
                <li>Parola: <code>demo123</code></li>
                <li><hr style="margin: 10px 0;"></li>
                <li><strong>Utilizator:</strong></li>
                <li>Email: <code>user@zaninstal.ro</code></li>
                <li>Parola: <code>user123</code></li>
            </ul>
        </div>

        <div class="footer-note">
            &copy; <?php echo date('Y'); ?> DEVIZO. Toate drepturile rezervate.
        </div>
    </div>
</div>

</body>
</html>
