<?php
/**
 * DEVIZO v2.0 - User Dashboard
 *
 * Dashboard for regular users with limited access based on permissions
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}

$pageTitle = 'Dashboard Utilizator';

$currentUser = Auth::user();
$firma = Auth::firma();
$firmaId = Auth::firmaId();

// Get user's accessible modules
$permissions = $_SESSION['permissions'] ?? [];
$moduleAccess = array_keys($permissions);

// Get allocated modules for firma
$moduleAlocate = db()->select('ms.*')
    ->from('firme_module fm')
    ->join('module_sistem ms', 'fm.modul_id = ms.id')
    ->where('fm.firma_id = ? AND fm.activ = 1', [$firmaId])
    ->orderBy('ms.nume')
    ->fetchAll();

// Filter modules by user access
$moduleDisponibile = array_filter($moduleAlocate, function($modul) use ($permissions) {
    return isset($permissions[$modul['slug']]);
});

// Get user's recent activity
$activitateRecenta = db()->select('*')
    ->from('log_activitate')
    ->where('utilizator_id = ?', [Auth::id()])
    ->orderBy('creat_la', 'DESC')
    ->limit(10)
    ->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-tachometer-alt"></i> Dashboard - <?php echo e($currentUser['nume']); ?>
    </div>

    <div style="padding: 30px; text-align: center;">
        <div style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 40px; border-radius: 10px; margin-bottom: 20px;">
            <i class="fas fa-building" style="font-size: 48px; margin-bottom: 15px; opacity: 0.9;"></i>
            <h2 style="font-size: 24px; margin-bottom: 5px;"><?php echo e($firma['denumire']); ?></h2>
            <p style="opacity: 0.9; font-size: 14px;"><?php echo e($currentUser['rol_nume']); ?></p>
        </div>

        <p style="color: #666; font-size: 16px; max-width: 600px; margin: 0 auto;">
            Bun venit în DEVIZO v2.0! Accesați modulele disponibile pentru a începe lucrul cu devizele și ofertele.
        </p>
    </div>
</div>

<!-- Available Modules -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-puzzle-piece"></i> Module Disponibile
    </div>

    <?php if (empty($moduleDisponibile)): ?>
        <div style="padding: 40px; text-align: center;">
            <i class="fas fa-lock" style="font-size: 64px; color: #ddd; margin-bottom: 20px;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">Nu aveți permisiuni pentru module</h3>
            <p style="color: #999; font-size: 14px;">
                Contactați administratorul firmei pentru a vă acorda acces la module.
            </p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; padding: 25px;">
            <?php foreach ($moduleDisponibile as $modul): ?>
                <?php
                $moduleSlug = $modul['slug'];
                $perm = $permissions[$moduleSlug] ?? [];
                $canView = $perm['vizualizare'] ?? false;
                $canCreate = $perm['creare'] ?? false;
                $canEdit = $perm['editare'] ?? false;
                $canDelete = $perm['stergere'] ?? false;
                $canExport = $perm['export'] ?? false;
                ?>

                <div style="border: 2px solid var(--gray); border-radius: 10px; padding: 25px; transition: all 0.3s; background: white; cursor: pointer; position: relative;"
                     onclick="window.location='<?php echo url('modules/' . $moduleSlug . '/index.php'); ?>';"
                     onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.1)';"
                     onmouseout="this.style.borderColor='var(--gray)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">

                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="font-size: 40px; color: var(--primary);">
                            <i class="fas fa-<?php echo e($modul['icon'] ?? 'puzzle-piece'); ?>"></i>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 18px; margin-bottom: 5px; color: var(--dark);"><?php echo e($modul['nume']); ?></h4>
                            <p style="font-size: 11px; color: #999;">v<?php echo e($modul['versiune']); ?></p>
                        </div>
                    </div>

                    <p style="font-size: 13px; color: #666; line-height: 1.6; margin-bottom: 15px;">
                        <?php echo e(truncate($modul['descriere'], 100)); ?>
                    </p>

                    <!-- Permissions Badge -->
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--gray);">
                        <?php if ($canView): ?>
                            <span style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-eye"></i> Vizualizare
                            </span>
                        <?php endif; ?>
                        <?php if ($canCreate): ?>
                            <span style="background: #e8f5e9; color: #388e3c; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-plus"></i> Creare
                            </span>
                        <?php endif; ?>
                        <?php if ($canEdit): ?>
                            <span style="background: #fff3e0; color: #f57c00; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-edit"></i> Editare
                            </span>
                        <?php endif; ?>
                        <?php if ($canDelete): ?>
                            <span style="background: #ffebee; color: #d32f2f; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-trash"></i> Ștergere
                            </span>
                        <?php endif; ?>
                        <?php if ($canExport): ?>
                            <span style="background: #f3e5f5; color: #7b1fa2; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-download"></i> Export
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Access Button -->
                    <div style="margin-top: 15px;">
                        <span style="display: inline-block; width: 100%; padding: 10px; background: var(--primary); color: white; text-align: center; border-radius: 6px; font-weight: 600; font-size: 14px;">
                            <i class="fas fa-arrow-right"></i> Accesează Modulul
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Stats -->
<?php if (!empty($moduleDisponibile)): ?>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">

    <!-- Devize Module Stats -->
    <?php if (Auth::hasModuleAccess('devize')): ?>
        <?php
        $devizeCount = db()->select('COUNT(*) as count')
            ->from('devize')
            ->where('firma_id = ?', [$firmaId])
            ->fetchColumn();
        ?>
        <div class="card">
            <div style="padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #666; font-size: 12px; margin-bottom: 5px;">Devize Totale</p>
                    <h3 style="font-size: 32px; font-weight: 700; color: var(--primary);"><?php echo $devizeCount; ?></h3>
                    <a href="<?php echo url('modules/devize/index.php'); ?>" style="font-size: 12px; color: var(--primary); text-decoration: none;">
                        Vezi toate →
                    </a>
                </div>
                <div style="font-size: 48px; color: var(--primary); opacity: 0.2;">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Articole Module Stats -->
    <?php if (Auth::hasModuleAccess('articole')): ?>
        <?php
        $articoleCount = db()->select('COUNT(*) as count')
            ->from('articole')
            ->where('firma_id = ?', [$firmaId])
            ->fetchColumn();
        ?>
        <div class="card">
            <div style="padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #666; font-size: 12px; margin-bottom: 5px;">Articole</p>
                    <h3 style="font-size: 32px; font-weight: 700; color: var(--success);"><?php echo $articoleCount; ?></h3>
                    <a href="<?php echo url('modules/articole/index.php'); ?>" style="font-size: 12px; color: var(--success); text-decoration: none;">
                        Vezi toate →
                    </a>
                </div>
                <div style="font-size: 48px; color: var(--success); opacity: 0.2;">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Parteneri Module Stats -->
    <?php if (Auth::hasModuleAccess('parteneri')): ?>
        <?php
        $parteneriCount = db()->select('COUNT(*) as count')
            ->from('parteneri')
            ->where('firma_id = ?', [$firmaId])
            ->fetchColumn();
        ?>
        <div class="card">
            <div style="padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="color: #666; font-size: 12px; margin-bottom: 5px;">Parteneri</p>
                    <h3 style="font-size: 32px; font-weight: 700; color: var(--info);"><?php echo $parteneriCount; ?></h3>
                    <a href="<?php echo url('modules/parteneri/index.php'); ?>" style="font-size: 12px; color: var(--info); text-decoration: none;">
                        Vezi toți →
                    </a>
                </div>
                <div style="font-size: 48px; color: var(--info); opacity: 0.2;">
                    <i class="fas fa-address-book"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Recent Activity -->
<?php if (!empty($activitateRecenta)): ?>
    <div class="card">
        <div class="card-header">
            <i class="fas fa-history"></i> Activitatea Mea Recentă
        </div>

        <table>
            <thead>
                <tr>
                    <th>Data/Ora</th>
                    <th>Acțiune</th>
                    <th>Detalii</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activitateRecenta as $log): ?>
                    <tr>
                        <td><?php echo format_datetime_ro($log['creat_la']); ?></td>
                        <td>
                            <?php
                            $actionLabels = [
                                'login' => '<i class="fas fa-sign-in-alt"></i> Login',
                                'logout' => '<i class="fas fa-sign-out-alt"></i> Logout',
                                'password_change' => '<i class="fas fa-key"></i> Schimbare Parolă',
                            ];
                            echo $actionLabels[$log['actiune']] ?? e($log['actiune']);
                            ?>
                        </td>
                        <td><small><?php echo e(truncate($log['detalii'], 50)); ?></small></td>
                        <td><code style="font-size: 11px;"><?php echo e($log['ip']); ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Help Section -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-question-circle"></i> Ajutor și Suport
    </div>

    <div style="padding: 25px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div style="text-align: center; padding: 20px;">
                <i class="fas fa-book" style="font-size: 48px; color: var(--primary); margin-bottom: 15px;"></i>
                <h4 style="margin-bottom: 10px; color: var(--dark);">Documentație</h4>
                <p style="font-size: 13px; color: #666; line-height: 1.6;">
                    Ghiduri detaliate pentru utilizarea platformei
                </p>
            </div>

            <div style="text-align: center; padding: 20px;">
                <i class="fas fa-video" style="font-size: 48px; color: var(--success); margin-bottom: 15px;"></i>
                <h4 style="margin-bottom: 10px; color: var(--dark);">Tutoriale Video</h4>
                <p style="font-size: 13px; color: #666; line-height: 1.6;">
                    Învățați rapid cum să folosiți fiecare modul
                </p>
            </div>

            <div style="text-align: center; padding: 20px;">
                <i class="fas fa-headset" style="font-size: 48px; color: var(--info); margin-bottom: 15px;"></i>
                <h4 style="margin-bottom: 10px; color: var(--dark);">Suport Tehnic</h4>
                <p style="font-size: 13px; color: #666; line-height: 1.6;">
                    Contactați-ne pentru asistență personalizată
                </p>
            </div>
        </div>
    </div>
</div>
