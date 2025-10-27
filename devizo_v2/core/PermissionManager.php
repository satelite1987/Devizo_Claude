<?php
/**
 * DEVIZO v2.0 - Permission Manager
 *
 * Manages RBAC (Role-Based Access Control) and module permissions
 * Handles permission allocation and verification
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}

class PermissionManager {

    /**
     * Get all available modules
     */
    public static function getAllModules() {
        return db()->select('*')
            ->from('module_sistem')
            ->where('activ = 1')
            ->orderBy('nume')
            ->fetchAll();
    }

    /**
     * Get modules allocated to a firma
     */
    public static function getFirmaModules($firmaId) {
        return db()->select('ms.*, fm.activ as alocat_activ, fm.data_alocare')
            ->from('firme_module fm')
            ->join('module_sistem ms', 'fm.modul_id = ms.id')
            ->where('fm.firma_id = ?', [$firmaId])
            ->orderBy('ms.nume')
            ->fetchAll();
    }

    /**
     * Get modules NOT allocated to a firma
     */
    public static function getAvailableModulesForFirma($firmaId) {
        $allocated = db()->select('modul_id')
            ->from('firme_module')
            ->where('firma_id = ?', [$firmaId])
            ->fetchAll();

        $allocatedIds = array_column($allocated, 'modul_id');

        if (empty($allocatedIds)) {
            return self::getAllModules();
        }

        return db()->select('*')
            ->from('module_sistem')
            ->where('activ = 1 AND id NOT IN (' . implode(',', $allocatedIds) . ')')
            ->orderBy('nume')
            ->fetchAll();
    }

    /**
     * Allocate module to firma
     */
    public static function allocateModuleToFirma($firmaId, $moduleId) {
        // Check if already allocated
        $exists = db()->select('COUNT(*) as count')
            ->from('firme_module')
            ->where('firma_id = ? AND modul_id = ?', [$firmaId, $moduleId])
            ->fetchColumn();

        if ($exists) {
            throw new Exception('Modulul este deja alocat firmei.');
        }

        // Check if firma exists and is active
        $firma = db()->select('*')
            ->from('firme')
            ->where('id = ?', [$firmaId])
            ->fetchOne();

        if (!$firma) {
            throw new Exception('Firma nu există.');
        }

        if (!$firma['activa']) {
            throw new Exception('Firma nu este activă.');
        }

        // Check if module exists
        $module = db()->select('*')
            ->from('module_sistem')
            ->where('id = ?', [$moduleId])
            ->fetchOne();

        if (!$module) {
            throw new Exception('Modulul nu există.');
        }

        // Allocate module
        db()->insert('firme_module', [
            'firma_id' => $firmaId,
            'modul_id' => $moduleId,
            'activ' => 1,
            'data_alocare' => date('Y-m-d H:i:s')
        ]);

        Auth::logActivity(Auth::id(), 'module_allocate',
            "Modul '{$module['nume']}' alocat firmei '{$firma['denumire']}'");

        return true;
    }

    /**
     * Remove module allocation from firma
     */
    public static function removeModuleFromFirma($firmaId, $moduleId) {
        // Get module info for logging
        $module = db()->select('ms.nume')
            ->from('firme_module fm')
            ->join('module_sistem ms', 'fm.modul_id = ms.id')
            ->where('fm.firma_id = ? AND fm.modul_id = ?', [$firmaId, $moduleId])
            ->fetchOne();

        if (!$module) {
            throw new Exception('Alocarea modulului nu există.');
        }

        // Remove allocation
        db()->delete('firme_module',
            'firma_id = ? AND modul_id = ?',
            [$firmaId, $moduleId]);

        // Remove all user permissions for this module in this firma
        db()->query("
            DELETE up FROM utilizatori_permisiuni up
            INNER JOIN utilizatori u ON up.utilizator_id = u.id
            WHERE u.firma_id = ? AND up.modul_id = ?
        ", [$firmaId, $moduleId]);

        Auth::logActivity(Auth::id(), 'module_deallocate',
            "Modul '{$module['nume']}' eliminat de la firma ID: {$firmaId}");

        return true;
    }

    /**
     * Toggle firma module active status
     */
    public static function toggleFirmaModuleStatus($firmaId, $moduleId) {
        $current = db()->select('activ')
            ->from('firme_module')
            ->where('firma_id = ? AND modul_id = ?', [$firmaId, $moduleId])
            ->fetchColumn();

        if ($current === false) {
            throw new Exception('Alocarea modulului nu există.');
        }

        $newStatus = $current ? 0 : 1;

        db()->update('firme_module', [
            'activ' => $newStatus
        ], 'firma_id = ? AND modul_id = ?', [$firmaId, $moduleId]);

        return $newStatus;
    }

    // ============================================
    // USER PERMISSIONS
    // ============================================

    /**
     * Get user permissions for all modules
     */
    public static function getUserPermissions($userId) {
        return db()->select('up.*, ms.nume as modul_nume, ms.slug as modul_slug')
            ->from('utilizatori_permisiuni up')
            ->join('module_sistem ms', 'up.modul_id = ms.id')
            ->where('up.utilizator_id = ?', [$userId])
            ->orderBy('ms.nume')
            ->fetchAll();
    }

    /**
     * Get user permission for specific module
     */
    public static function getUserModulePermission($userId, $moduleId) {
        $perm = db()->select('*')
            ->from('utilizatori_permisiuni')
            ->where('utilizator_id = ? AND modul_id = ?', [$userId, $moduleId])
            ->fetchOne();

        if (!$perm) {
            return [
                'vizualizare' => 0,
                'creare' => 0,
                'editare' => 0,
                'stergere' => 0,
                'export' => 0
            ];
        }

        return $perm;
    }

    /**
     * Set user permissions for a module
     */
    public static function setUserPermission($userId, $moduleId, $permissions) {
        // Verify user and module exist
        $user = db()->select('u.*, f.id as firma_id')
            ->from('utilizatori u')
            ->leftJoin('firme f', 'u.firma_id = f.id')
            ->where('u.id = ?', [$userId])
            ->fetchOne();

        if (!$user) {
            throw new Exception('Utilizatorul nu există.');
        }

        $module = db()->select('*')
            ->from('module_sistem')
            ->where('id = ?', [$moduleId])
            ->fetchOne();

        if (!$module) {
            throw new Exception('Modulul nu există.');
        }

        // Verify module is allocated to user's firma
        if ($user['firma_id']) {
            $allocated = db()->select('COUNT(*) as count')
                ->from('firme_module')
                ->where('firma_id = ? AND modul_id = ? AND activ = 1',
                       [$user['firma_id'], $moduleId])
                ->fetchColumn();

            if (!$allocated) {
                throw new Exception('Modulul nu este alocat firmei utilizatorului.');
            }
        }

        // Verify current user can set permissions
        if (!Auth::isSuperAdmin() && !Auth::isMasterFirma()) {
            throw new Exception('Nu aveți permisiunea de a seta permisiuni.');
        }

        // Master can only set permissions for users in their firma
        if (Auth::isMasterFirma() && $user['firma_id'] != Auth::firmaId()) {
            throw new Exception('Nu puteți seta permisiuni pentru utilizatori din alte firme.');
        }

        // Check if permission entry exists
        $exists = db()->select('COUNT(*) as count')
            ->from('utilizatori_permisiuni')
            ->where('utilizator_id = ? AND modul_id = ?', [$userId, $moduleId])
            ->fetchColumn();

        $data = [
            'vizualizare' => isset($permissions['vizualizare']) ? 1 : 0,
            'creare' => isset($permissions['creare']) ? 1 : 0,
            'editare' => isset($permissions['editare']) ? 1 : 0,
            'stergere' => isset($permissions['stergere']) ? 1 : 0,
            'export' => isset($permissions['export']) ? 1 : 0
        ];

        if ($exists) {
            // Update existing
            db()->update('utilizatori_permisiuni', $data,
                'utilizator_id = ? AND modul_id = ?',
                [$userId, $moduleId]);
        } else {
            // Insert new
            $data['utilizator_id'] = $userId;
            $data['modul_id'] = $moduleId;
            db()->insert('utilizatori_permisiuni', $data);
        }

        Auth::logActivity(Auth::id(), 'permission_set',
            "Permisiuni setate pentru utilizator ID: {$userId}, modul: {$module['nume']}");

        return true;
    }

    /**
     * Remove all user permissions for a module
     */
    public static function removeUserPermission($userId, $moduleId) {
        db()->delete('utilizatori_permisiuni',
            'utilizator_id = ? AND modul_id = ?',
            [$userId, $moduleId]);

        Auth::logActivity(Auth::id(), 'permission_remove',
            "Permisiuni eliminate pentru utilizator ID: {$userId}, modul ID: {$moduleId}");

        return true;
    }

    /**
     * Copy permissions from one user to another
     */
    public static function copyUserPermissions($fromUserId, $toUserId) {
        // Verify both users exist and are in same firma
        $fromUser = db()->select('firma_id')
            ->from('utilizatori')
            ->where('id = ?', [$fromUserId])
            ->fetchOne();

        $toUser = db()->select('firma_id')
            ->from('utilizatori')
            ->where('id = ?', [$toUserId])
            ->fetchOne();

        if (!$fromUser || !$toUser) {
            throw new Exception('Unul sau ambii utilizatori nu există.');
        }

        if ($fromUser['firma_id'] != $toUser['firma_id']) {
            throw new Exception('Utilizatorii trebuie să fie din aceeași firmă.');
        }

        // Get source permissions
        $permissions = db()->select('*')
            ->from('utilizatori_permisiuni')
            ->where('utilizator_id = ?', [$fromUserId])
            ->fetchAll();

        if (empty($permissions)) {
            throw new Exception('Utilizatorul sursă nu are permisiuni de copiat.');
        }

        // Delete existing permissions for target user
        db()->delete('utilizatori_permisiuni', 'utilizator_id = ?', [$toUserId]);

        // Copy permissions
        foreach ($permissions as $perm) {
            db()->insert('utilizatori_permisiuni', [
                'utilizator_id' => $toUserId,
                'modul_id' => $perm['modul_id'],
                'vizualizare' => $perm['vizualizare'],
                'creare' => $perm['creare'],
                'editare' => $perm['editare'],
                'stergere' => $perm['stergere'],
                'export' => $perm['export']
            ]);
        }

        Auth::logActivity(Auth::id(), 'permission_copy',
            "Permisiuni copiate de la utilizator ID: {$fromUserId} la ID: {$toUserId}");

        return true;
    }

    /**
     * Grant all permissions for allocated modules to a user
     */
    public static function grantAllPermissions($userId) {
        // Get user's firma
        $user = db()->select('firma_id')
            ->from('utilizatori')
            ->where('id = ?', [$userId])
            ->fetchOne();

        if (!$user || !$user['firma_id']) {
            throw new Exception('Utilizatorul nu există sau nu aparține unei firme.');
        }

        // Get all allocated modules for firma
        $modules = db()->select('modul_id')
            ->from('firme_module')
            ->where('firma_id = ? AND activ = 1', [$user['firma_id']])
            ->fetchAll();

        if (empty($modules)) {
            throw new Exception('Firma nu are module alocate.');
        }

        // Delete existing permissions
        db()->delete('utilizatori_permisiuni', 'utilizator_id = ?', [$userId]);

        // Grant full permissions for all modules
        foreach ($modules as $module) {
            db()->insert('utilizatori_permisiuni', [
                'utilizator_id' => $userId,
                'modul_id' => $module['modul_id'],
                'vizualizare' => 1,
                'creare' => 1,
                'editare' => 1,
                'stergere' => 1,
                'export' => 1
            ]);
        }

        Auth::logActivity(Auth::id(), 'permission_grant_all',
            "Toate permisiunile acordate utilizatorului ID: {$userId}");

        return true;
    }

    /**
     * Revoke all permissions for a user
     */
    public static function revokeAllPermissions($userId) {
        db()->delete('utilizatori_permisiuni', 'utilizator_id = ?', [$userId]);

        Auth::logActivity(Auth::id(), 'permission_revoke_all',
            "Toate permisiunile revocate pentru utilizator ID: {$userId}");

        return true;
    }

    // ============================================
    // ROLE MANAGEMENT
    // ============================================

    /**
     * Get all roles
     */
    public static function getAllRoles() {
        return db()->select('*')
            ->from('roluri')
            ->orderBy('nivel', 'DESC')
            ->fetchAll();
    }

    /**
     * Get role by ID
     */
    public static function getRole($roleId) {
        return db()->select('*')
            ->from('roluri')
            ->where('id = ?', [$roleId])
            ->fetchOne();
    }

    /**
     * Check if user can manage target user (based on role level)
     */
    public static function canManageUser($targetUserId) {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return false;
        }

        // Super Admin can manage anyone
        if (Auth::isSuperAdmin()) {
            return true;
        }

        // Get target user
        $targetUser = db()->select('u.*, r.nivel as rol_nivel')
            ->from('utilizatori u')
            ->join('roluri r', 'u.rol_id = r.id')
            ->where('u.id = ?', [$targetUserId])
            ->fetchOne();

        if (!$targetUser) {
            return false;
        }

        // Master can only manage users in their firma with lower role level
        if (Auth::isMasterFirma()) {
            return $targetUser['firma_id'] == Auth::firmaId() &&
                   $targetUser['rol_nivel'] < $currentUser['rol_nivel'];
        }

        return false;
    }
}
