<?php
/**
 * DEVIZO - Deconectare Utilizator
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Deconectare
logout();

// Redirectionare la pagina de login
redirect(SITE_URL . '/login.php');
