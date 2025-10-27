<?php
/**
 * DEVIZO v2.0 - Authentication & Authorization
 *
 * Handles multi-tenant authentication, role-based access control,
 * and permission management
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}

class Auth {

    /**
     * Attempt to authenticate user
     *
     * @param string $email User email
     * @param string $password Plain text password
     * @return bool Success status
     */
    public static function login($email, $password) {
        // Check if user is locked out
        if (self::isLockedOut($email)) {
            $remaining = self::getLockoutTimeRemaining($email);
            throw new Exception("Cont blocat temporar. Încercați din nou în {$remaining} minute.");
        }

        try {
            // Get user from database
            $user = db()->select('u.*, r.nume as rol_nume, r.nivel as rol_nivel')
                ->from('utilizatori u')
                ->join('roluri r', 'u.rol_id = r.id')
                ->where('u.email = ?', [$email])
                ->fetchOne();

            if (!$user) {
                self::recordLoginAttempt($email, false);
                return false;
            }

            // Generate rol_slug from rol_nivel
            $rolSlugs = [
                1 => 'super-admin',
                2 => 'master-firma',
                3 => 'utilizator'
            ];
            $user['rol_slug'] = $rolSlugs[$user['rol_nivel']] ?? 'utilizator';

            // Check if user is active
            if (!$user['activ']) {
                throw new Exception('Cont dezactivat. Contactați administratorul.');
            }

            // Verify password
            if (!password_verify($password, $user['parola'])) {
                self::recordLoginAttempt($email, false);
                return false;
            }

            // Check if firma is active (for non-super admins)
            if ($user['firma_id'] !== null) {
                $firma = db()->select('*')
                    ->from('firme')
                    ->where('id = ?', [$user['firma_id']])
                    ->fetchOne();

                if (!$firma) {
                    throw new Exception('Firma nu există.');
                }

                if (!$firma['activa']) {
                    throw new Exception('Firma este dezactivată. Contactați administratorul.');
                }

                // Check subscription expiration
                if (strtotime($firma['data_expirare_abonament']) < time()) {
                    throw new Exception('Abonamentul firmei a expirat. Contactați administratorul.');
                }

                // Store firma info in session
                $_SESSION['firma'] = $firma;
            }

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'nume' => $user['nume'],
                'rol_id' => $user['rol_id'],
                'rol_nume' => $user['rol_nume'],
                'rol_nivel' => $user['rol_nivel'],
                'rol_slug' => $user['rol_slug'],
                'firma_id' => $user['firma_id'],
                'avatar' => $user['avatar']
            ];

            // Load user permissions
            self::loadPermissions($user['id']);

            // Record successful login
            self::recordLoginAttempt($email, true);
            self::recordSession($user['id']);
            self::logActivity($user['id'], 'login', 'Autentificare reușită');

            // Update last login
            db()->update('utilizatori', [
                'ultima_autentificare' => date('Y-m-d H:i:s'),
                'ip_ultima_autentificare' => get_client_ip()
            ], 'id = ?', [$user['id']]);

            return true;

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Load user permissions into session
     */
    private static function loadPermissions($userId) {
        $permissions = db()->select('up.*, ms.slug as modul_slug')
            ->from('utilizatori_permisiuni up')
            ->join('module_sistem ms', 'up.modul_id = ms.id')
            ->where('up.utilizator_id = ?', [$userId])
            ->fetchAll();

        $perms = [];
        foreach ($permissions as $perm) {
            $perms[$perm['modul_slug']] = [
                'vizualizare' => (bool) $perm['vizualizare'],
                'creare' => (bool) $perm['creare'],
                'editare' => (bool) $perm['editare'],
                'stergere' => (bool) $perm['stergere'],
                'export' => (bool) $perm['export']
            ];
        }

        $_SESSION['permissions'] = $perms;
    }

    /**
     * Logout current user
     */
    public static function logout() {
        if (isset($_SESSION['user_id'])) {
            self::logActivity($_SESSION['user_id'], 'logout', 'Deconectare');
            self::endSession();
        }

        // Clear session
        $_SESSION = [];

        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy session
        session_destroy();
    }

    /**
     * Check if user is authenticated
     */
    public static function check() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user']);
    }

    /**
     * Get current authenticated user
     */
    public static function user() {
        return self::check() ? $_SESSION['user'] : null;
    }

    /**
     * Get current user's ID
     */
    public static function id() {
        return self::check() ? $_SESSION['user_id'] : null;
    }

    /**
     * Get current firma
     */
    public static function firma() {
        return isset($_SESSION['firma']) ? $_SESSION['firma'] : null;
    }

    /**
     * Get current firma ID
     */
    public static function firmaId() {
        $user = self::user();
        return $user ? $user['firma_id'] : null;
    }

    // ============================================
    // ROLE CHECKING
    // ============================================

    /**
     * Check if current user is Super Admin
     */
    public static function isSuperAdmin() {
        $user = self::user();
        return $user && $user['rol_slug'] === 'super-admin';
    }

    /**
     * Check if current user is Master Firma
     */
    public static function isMasterFirma() {
        $user = self::user();
        return $user && $user['rol_slug'] === 'master-firma';
    }

    /**
     * Check if current user is regular User
     */
    public static function isUser() {
        $user = self::user();
        return $user && $user['rol_slug'] === 'utilizator';
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($roleSlug) {
        $user = self::user();
        return $user && $user['rol_slug'] === $roleSlug;
    }

    /**
     * Check if user role level is at least the specified level
     */
    public static function hasRoleLevel($minLevel) {
        $user = self::user();
        return $user && $user['rol_nivel'] >= $minLevel;
    }

    // ============================================
    // PERMISSION CHECKING
    // ============================================

    /**
     * Check if user has access to module
     */
    public static function hasModuleAccess($moduleSlug) {
        // Super Admin has access to everything
        if (self::isSuperAdmin()) {
            return true;
        }

        // Check if module is allocated to firma
        $firmaId = self::firmaId();
        if (!$firmaId) {
            return false;
        }

        $allocated = db()->select('COUNT(*) as count')
            ->from('firme_module fm')
            ->join('module_sistem ms', 'fm.modul_id = ms.id')
            ->where('fm.firma_id = ? AND ms.slug = ? AND fm.activ = 1', [$firmaId, $moduleSlug])
            ->fetchColumn();

        if (!$allocated) {
            return false;
        }

        // Master has access to all allocated modules
        if (self::isMasterFirma()) {
            return true;
        }

        // Regular user - check permissions
        return isset($_SESSION['permissions'][$moduleSlug]);
    }

    /**
     * Check if user has specific permission for a module
     */
    public static function hasPermission($moduleSlug, $permission) {
        // Super Admin has all permissions
        if (self::isSuperAdmin()) {
            return true;
        }

        // Master has all permissions for allocated modules
        if (self::isMasterFirma() && self::hasModuleAccess($moduleSlug)) {
            return true;
        }

        // Check user permission
        if (!isset($_SESSION['permissions'][$moduleSlug])) {
            return false;
        }

        return isset($_SESSION['permissions'][$moduleSlug][$permission]) &&
               $_SESSION['permissions'][$moduleSlug][$permission];
    }

    /**
     * Require authentication (redirect to login if not authenticated)
     */
    public static function requireLogin() {
        if (!self::check()) {
            $currentUrl = current_url();
            redirect(url('login.php', ['redirect' => $currentUrl]));
        }
    }

    /**
     * Require specific role
     */
    public static function requireRole($roleSlug) {
        self::requireLogin();

        if (!self::hasRole($roleSlug)) {
            http_response_code(403);
            die('Acces interzis. Nu aveți rolul necesar.');
        }
    }

    /**
     * Require minimum role level
     */
    public static function requireRoleLevel($minLevel) {
        self::requireLogin();

        if (!self::hasRoleLevel($minLevel)) {
            http_response_code(403);
            die('Acces interzis. Nu aveți nivelul de rol necesar.');
        }
    }

    /**
     * Require module access
     */
    public static function requireModuleAccess($moduleSlug) {
        self::requireLogin();

        if (!self::hasModuleAccess($moduleSlug)) {
            http_response_code(403);
            die("Acces interzis. Nu aveți acces la modulul '{$moduleSlug}'.");
        }
    }

    /**
     * Require specific permission
     */
    public static function requirePermission($moduleSlug, $permission) {
        self::requireLogin();

        if (!self::hasPermission($moduleSlug, $permission)) {
            http_response_code(403);
            die("Acces interzis. Nu aveți permisiunea '{$permission}' pentru modulul '{$moduleSlug}'.");
        }
    }

    // ============================================
    // LOGIN ATTEMPT TRACKING
    // ============================================

    /**
     * Record login attempt
     */
    private static function recordLoginAttempt($email, $success) {
        try {
            db()->insert('log_activitate', [
                'utilizator_id' => null,
                'actiune' => $success ? 'login_success' : 'login_failed',
                'detalii' => $email,
                'ip' => get_client_ip(),
                'user_agent' => get_user_agent(),
                'creat_la' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to record login attempt: " . $e->getMessage());
        }
    }

    /**
     * Check if email is locked out due to too many failed attempts
     */
    private static function isLockedOut($email) {
        $since = date('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_TIME);

        $attempts = db()->select('COUNT(*) as count')
            ->from('log_activitate')
            ->where('actiune = ? AND detalii = ? AND creat_la >= ?',
                   ['login_failed', $email, $since])
            ->fetchColumn();

        return $attempts >= MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Get remaining lockout time in minutes
     */
    private static function getLockoutTimeRemaining($email) {
        $since = date('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_TIME);

        $lastAttempt = db()->select('creat_la')
            ->from('log_activitate')
            ->where('actiune = ? AND detalii = ? AND creat_la >= ?',
                   ['login_failed', $email, $since])
            ->orderBy('creat_la', 'DESC')
            ->fetchColumn();

        if (!$lastAttempt) {
            return 0;
        }

        $diff = time() - strtotime($lastAttempt);
        $remaining = ceil((LOGIN_LOCKOUT_TIME - $diff) / 60);

        return max(1, $remaining);
    }

    // ============================================
    // SESSION TRACKING
    // ============================================

    /**
     * Record new session
     */
    private static function recordSession($userId) {
        try {
            db()->insert('sesiuni', [
                'utilizator_id' => $userId,
                'sesiune_id' => session_id(),
                'ip' => get_client_ip(),
                'user_agent' => get_user_agent(),
                'inceput' => date('Y-m-d H:i:s'),
                'ultima_activitate' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to record session: " . $e->getMessage());
        }
    }

    /**
     * Update session activity
     */
    public static function updateSessionActivity() {
        if (!self::check()) {
            return;
        }

        try {
            db()->update('sesiuni', [
                'ultima_activitate' => date('Y-m-d H:i:s')
            ], 'sesiune_id = ?', [session_id()]);
        } catch (Exception $e) {
            error_log("Failed to update session activity: " . $e->getMessage());
        }
    }

    /**
     * End current session
     */
    private static function endSession() {
        try {
            db()->update('sesiuni', [
                'sfarsit' => date('Y-m-d H:i:s')
            ], 'sesiune_id = ?', [session_id()]);
        } catch (Exception $e) {
            error_log("Failed to end session: " . $e->getMessage());
        }
    }

    // ============================================
    // ACTIVITY LOGGING
    // ============================================

    /**
     * Log user activity
     */
    public static function logActivity($userId, $action, $details = null) {
        try {
            db()->insert('log_activitate', [
                'utilizator_id' => $userId,
                'actiune' => $action,
                'detalii' => $details,
                'ip' => get_client_ip(),
                'user_agent' => get_user_agent(),
                'creat_la' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }

    // ============================================
    // PASSWORD MANAGEMENT
    // ============================================

    /**
     * Change user password
     */
    public static function changePassword($userId, $oldPassword, $newPassword) {
        // Get current password hash
        $currentHash = db()->select('parola')
            ->from('utilizatori')
            ->where('id = ?', [$userId])
            ->fetchColumn();

        if (!$currentHash) {
            throw new Exception('Utilizator negăsit.');
        }

        // Verify old password
        if (!password_verify($oldPassword, $currentHash)) {
            throw new Exception('Parola curentă este incorectă.');
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            throw new Exception('Parola nouă trebuie să aibă minim 8 caractere.');
        }

        // Hash new password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        db()->update('utilizatori', [
            'parola' => $newHash,
            'actualizat_la' => date('Y-m-d H:i:s')
        ], 'id = ?', [$userId]);

        self::logActivity($userId, 'password_change', 'Parolă schimbată');

        return true;
    }

    /**
     * Reset password (admin function)
     */
    public static function resetPassword($userId, $newPassword) {
        // Only Super Admin and Master Firma can reset passwords
        if (!self::isSuperAdmin() && !self::isMasterFirma()) {
            throw new Exception('Nu aveți permisiunea de a reseta parole.');
        }

        // Master can only reset passwords for users in their firma
        if (self::isMasterFirma()) {
            $user = db()->select('firma_id')
                ->from('utilizatori')
                ->where('id = ?', [$userId])
                ->fetchOne();

            if ($user['firma_id'] != self::firmaId()) {
                throw new Exception('Nu puteți reseta parola pentru utilizatori din alte firme.');
            }
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            throw new Exception('Parola nouă trebuie să aibă minim 8 caractere.');
        }

        // Hash new password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        db()->update('utilizatori', [
            'parola' => $newHash,
            'actualizat_la' => date('Y-m-d H:i:s')
        ], 'id = ?', [$userId]);

        self::logActivity(self::id(), 'password_reset', "Resetare parolă pentru utilizator ID: {$userId}");

        return true;
    }
}
