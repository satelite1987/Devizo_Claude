<?php
/**
 * DEVIZO v2.0 - Header Template
 *
 * Common header for all pages with navigation and user info
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}

$currentUser = Auth::user();
$currentFirma = Auth::firma();
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - DEVIZO v2.0</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --secondary: #2c3e50;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #3498db;
            --light: #f8f9fa;
            --dark: #333;
            --gray: #dee2e6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background: var(--light);
        }

        /* Header */
        .main-header {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: #666;
        }

        .firma-badge {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            margin-top: 2px;
        }

        /* Navigation */
        .main-nav {
            background: var(--secondary);
            border-bottom: 3px solid var(--primary);
        }

        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            gap: 5px;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.2s;
            font-size: 14px;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: rgba(255,255,255,0.1);
        }

        .nav-link i {
            margin-right: 5px;
        }

        /* Dropdown */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 200px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            display: none;
            z-index: 1001;
            border-radius: 4px;
            overflow: hidden;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px 15px;
            color: var(--dark);
            text-decoration: none;
            border-bottom: 1px solid var(--gray);
            font-size: 14px;
        }

        .dropdown-menu a:last-child {
            border-bottom: none;
        }

        .dropdown-menu a:hover {
            background-color: var(--light);
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #27ae60;
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background: #1a252f;
        }

        /* Flash Messages */
        .flash-message {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .flash-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .flash-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .flash-message.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .flash-message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card-header {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gray);
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        th {
            background: var(--primary);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--gray);
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: var(--light);
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--gray);
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .nav-menu {
                flex-direction: column;
                gap: 0;
            }

            .dropdown-menu {
                position: static;
                box-shadow: none;
                border-left: 3px solid var(--primary);
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="main-header">
    <div class="header-content">
        <a href="<?php echo url('index.php'); ?>" class="logo">
            <i class="fas fa-file-invoice"></i>
            DEVIZO <small style="font-size: 12px; font-weight: normal; opacity: 0.7;">v2.0</small>
        </a>

        <div class="user-info">
            <div class="user-details">
                <div class="user-name"><?php echo e($currentUser['nume']); ?></div>
                <div class="user-role"><?php echo e($currentUser['rol_nume']); ?></div>
                <?php if ($currentFirma): ?>
                    <div class="firma-badge"><?php echo e($currentFirma['denumire']); ?></div>
                <?php endif; ?>
            </div>
            <a href="<?php echo url('logout.php'); ?>" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Ieșire
            </a>
        </div>
    </div>
</header>

<!-- Navigation -->
<nav class="main-nav">
    <div class="nav-content">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?php echo url('index.php'); ?>" class="nav-link">
                    <i class="fas fa-home"></i> Acasă
                </a>
            </li>

            <?php if (Auth::isSuperAdmin()): ?>
                <li class="nav-item">
                    <a href="<?php echo url('admin/firme.php'); ?>" class="nav-link">
                        <i class="fas fa-building"></i> Firme
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo url('admin/module.php'); ?>" class="nav-link">
                        <i class="fas fa-puzzle-piece"></i> Module
                    </a>
                </li>
            <?php endif; ?>

            <?php if (!Auth::isSuperAdmin()): ?>
                <?php if (Auth::hasModuleAccess('devize')): ?>
                    <li class="nav-item">
                        <a href="<?php echo url('modules/devize/index.php'); ?>" class="nav-link">
                            <i class="fas fa-file-invoice"></i> Devize
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (Auth::hasModuleAccess('articole') || Auth::hasModuleAccess('parteneri') || Auth::hasModuleAccess('unitati-masura')): ?>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link">
                            <i class="fas fa-database"></i> Nomenclatoare <i class="fas fa-caret-down"></i>
                        </a>
                        <div class="dropdown-menu">
                            <?php if (Auth::hasModuleAccess('articole')): ?>
                                <a href="<?php echo url('modules/articole/index.php'); ?>">
                                    <i class="fas fa-boxes"></i> Articole
                                </a>
                            <?php endif; ?>
                            <?php if (Auth::hasModuleAccess('parteneri')): ?>
                                <a href="<?php echo url('modules/parteneri/index.php'); ?>">
                                    <i class="fas fa-address-book"></i> Parteneri
                                </a>
                            <?php endif; ?>
                            <?php if (Auth::hasModuleAccess('unitati-masura')): ?>
                                <a href="<?php echo url('modules/unitati-masura/index.php'); ?>">
                                    <i class="fas fa-ruler"></i> Unități Măsură
                                </a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (Auth::isMasterFirma()): ?>
                <li class="nav-item">
                    <a href="<?php echo url('utilizatori.php'); ?>" class="nav-link">
                        <i class="fas fa-users"></i> Utilizatori
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item dropdown">
                <a href="#" class="nav-link">
                    <i class="fas fa-user-circle"></i> Contul Meu <i class="fas fa-caret-down"></i>
                </a>
                <div class="dropdown-menu">
                    <a href="<?php echo url('profil.php'); ?>">
                        <i class="fas fa-user"></i> Profilul Meu
                    </a>
                    <?php if (!Auth::isSuperAdmin()): ?>
                        <a href="<?php echo url('setari-firma.php'); ?>">
                            <i class="fas fa-building"></i> Setări Firmă
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo url('schimba-parola.php'); ?>">
                        <i class="fas fa-key"></i> Schimbă Parola
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<!-- Container -->
<div class="container">
    <?php
    // Display flash message
    $flash = Session::getFlash();
    if ($flash):
    ?>
        <div class="flash-message <?php echo e($flash['type']); ?>">
            <i class="fas fa-<?php
                echo $flash['type'] == 'success' ? 'check-circle' :
                     ($flash['type'] == 'error' ? 'exclamation-circle' :
                     ($flash['type'] == 'warning' ? 'exclamation-triangle' : 'info-circle'));
            ?>"></i>
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>
