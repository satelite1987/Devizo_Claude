<?php
/**
 * DEVIZO - Header Template
 *
 * Acest fisier contine header-ul comun pentru toate paginile aplicatiei
 * Include: meniul de navigare, informatii utilizator, notificari
 */

if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config/database.php';
}
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser();
$currentFirma = getCurrentFirma();

// Culoarea primara (default sau din setarile firmei)
$culoarePrimara = '#3498db';
if ($currentFirma && !empty($currentFirma['culoare_primara'])) {
    $culoarePrimara = $currentFirma['culoare_primara'];
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DEVIZO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: <?php echo $culoarePrimara; ?>;
            --primary-dark: #2980b9;
            --secondary: #2c3e50;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --light: #f5f5f5;
            --dark: #333;
            --gray: #ddd;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
        }

        /* Header si Navigare */
        .main-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        .btn-danger {
            background-color: var(--danger);
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-success {
            background-color: var(--success);
        }

        .btn-success:hover {
            background-color: #27ae60;
        }

        .btn-secondary {
            background-color: var(--secondary);
        }

        .btn-secondary:hover {
            background-color: #1a252f;
        }

        /* Navigare */
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
            transition: background-color 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.1);
        }

        .nav-link i {
            margin-right: 5px;
        }

        /* Dropdown Menu */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 200px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: none;
            z-index: 1001;
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
        }

        .dropdown-menu a:hover {
            background-color: var(--light);
        }

        /* Container Principal */
        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }

        /* Notificari Flash */
        .flash-message {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
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
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .flash-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .flash-message.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .flash-message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        /* Alert pentru expirare abonament */
        .alert-expirare {
            background: #fff3cd;
            border-left: 4px solid var(--warning);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 18px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }

            .nav-menu {
                display: none;
                flex-direction: column;
                gap: 0;
            }

            .nav-menu.active {
                display: flex;
            }

            .user-info {
                flex-direction: column;
                align-items: flex-end;
                gap: 10px;
            }

            .header-content {
                flex-wrap: wrap;
            }

            .dropdown-menu {
                position: static;
                box-shadow: none;
                border-left: 3px solid var(--primary);
            }
        }

        /* Loading Spinner */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="main-header">
    <div class="header-content">
        <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
            <i class="fas fa-file-invoice"></i>
            DEVIZO
        </a>

        <?php if (isLoggedIn()): ?>
            <div class="user-info">
                <div class="user-details">
                    <div class="user-name"><?php echo clean($currentUser['nume']); ?></div>
                    <div class="user-role"><?php echo clean($currentUser['rol_nume']); ?></div>
                    <?php if ($currentFirma): ?>
                        <div class="firma-badge"><?php echo clean($currentFirma['denumire']); ?></div>
                    <?php endif; ?>
                </div>
                <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Iesire
                </a>
            </div>
        <?php endif; ?>
    </div>
</header>

<?php if (isLoggedIn()): ?>
<!-- Navigare -->
<nav class="main-nav">
    <div class="nav-content">
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="nav-menu" id="mainMenu">
            <li class="nav-item">
                <a href="<?php echo SITE_URL; ?>/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Acasa
                </a>
            </li>

            <?php if (!isSuperAdmin()): ?>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/devize.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'devize.php' ? 'active' : ''; ?>">
                        <i class="fas fa-file-invoice"></i> Devize
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">
                        <i class="fas fa-database"></i> Nomenclatoare <i class="fas fa-caret-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="<?php echo SITE_URL; ?>/articole.php"><i class="fas fa-boxes"></i> Articole</a>
                        <a href="<?php echo SITE_URL; ?>/parteneri.php"><i class="fas fa-address-book"></i> Parteneri</a>
                        <a href="<?php echo SITE_URL; ?>/unitati-masura.php"><i class="fas fa-ruler"></i> Unitati Masura</a>
                    </div>
                </li>

                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/cereri-oferta.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cereri-oferta.php' ? 'active' : ''; ?>">
                        <i class="fas fa-paper-plane"></i> Cereri Oferta
                    </a>
                </li>
            <?php endif; ?>

            <?php if (isSuperAdmin()): ?>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/index.php" class="nav-link">
                        <i class="fas fa-cog"></i> Administrare
                    </a>
                </li>
            <?php endif; ?>

            <?php if (isMasterFirma() || isSuperAdmin()): ?>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/utilizatori.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'utilizatori.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Utilizatori
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item dropdown">
                <a href="#" class="nav-link">
                    <i class="fas fa-user-circle"></i> Contul Meu <i class="fas fa-caret-down"></i>
                </a>
                <div class="dropdown-menu">
                    <a href="<?php echo SITE_URL; ?>/profil.php"><i class="fas fa-user"></i> Profilul Meu</a>
                    <?php if (!isSuperAdmin()): ?>
                        <a href="<?php echo SITE_URL; ?>/setari-firma.php"><i class="fas fa-building"></i> Setari Firma</a>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/schimba-parola.php"><i class="fas fa-key"></i> Schimba Parola</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
<?php endif; ?>

<!-- Container Principal -->
<div class="container">
    <?php
    // Afisare mesaj flash
    $flashMessage = getFlashMessage();
    if ($flashMessage):
    ?>
        <div class="flash-message <?php echo $flashMessage['type']; ?>">
            <i class="fas fa-<?php
                echo $flashMessage['type'] == 'success' ? 'check-circle' :
                     ($flashMessage['type'] == 'error' ? 'exclamation-circle' :
                     ($flashMessage['type'] == 'warning' ? 'exclamation-triangle' : 'info-circle'));
            ?>"></i>
            <?php echo clean($flashMessage['text']); ?>
        </div>
    <?php endif; ?>

    <?php
    // Alert pentru expirare abonament (daca este cazul)
    if (isLoggedIn() && !isSuperAdmin() && $currentFirma):
        $dataExpirare = strtotime($currentFirma['data_expirare_abonament']);
        $zileRamase = ceil(($dataExpirare - time()) / (60 * 60 * 24));

        if ($zileRamase <= 30 && $zileRamase > 0):
    ?>
        <div class="alert-expirare">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Atentie!</strong> Abonamentul firmei expira in <?php echo $zileRamase; ?> zile (<?php echo formatDateRO($currentFirma['data_expirare_abonament']); ?>).
            Va rugam contactati administratorul pentru prelungire.
        </div>
    <?php
        endif;
    endif;
    ?>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mainMenu');
    menu.classList.toggle('active');
}
</script>
