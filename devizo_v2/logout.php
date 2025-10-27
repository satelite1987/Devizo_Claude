<?php
/**
 * DEVIZO v2.0 - Logout Handler
 *
 * Handles user logout and session cleanup
 */

define('DEVIZO_APP', true);

require_once __DIR__ . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Helpers.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Auth.php';

Session::init();

// Perform logout
Auth::logout();

// Redirect to login page with success message
redirect(url('login.php'));
