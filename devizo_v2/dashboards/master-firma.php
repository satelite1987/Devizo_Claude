<?php
/**
 * DEVIZO v2.0 - Master Firma Dashboard
 *
 * Dashboard for Master Firma with company overview and management
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}

// Ensure user is Master Firma
if (!Auth::isMasterFirma()) {
    http_response_code(403);
    die('Acces interzis.');
}

$pageTitle = 'Dashboard Master';

$firma = Auth::firma();
$firmaId = Auth::firmaId();

// Get statistics
$stats = [
    'utilizatori_total' => db()->select('COUNT(*) as count')
        ->from('utilizatori')
        ->where('firma_id = ?', [$firmaId])
        ->fetchColumn(),

    'utilizatori_activi' => db()->select('COUNT(*) as count')
        ->from('utilizatori')
        ->where('firma_id = ? AND activ = 1', [$firmaId])
        ->fetchColumn(),

    'module_alocate' => db()->select('COUNT(*) as count')
        ->from('firme_module')
        ->where('firma_id = ? AND activ = 1', [$firmaId])
        ->fetchColumn(),
];

// Calculate days until subscription expires
$dataExpirare = strtotime($firma['data_expirare_abonament']);
$zileRamase = ceil(($dataExpirare - time()) / 86400);

// Get allocated modules
$moduleAlocate = db()->select('ms.*, fm.data_alocare')
    ->from('firme_module fm')
    ->join('module_sistem ms', 'fm.modul_id = ms.id')
    ->where('fm.firma_id = ? AND fm.activ = 1', [$firmaId])
    ->orderBy('ms.nume')
    ->fetchAll();

// Get team members
$utilizatori = db()->select('u.*, r.nume as rol_nume')
    ->from('utilizatori u')
    ->join('roluri r', 'u.rol_id = r.id')
    ->where('u.firma_id = ?', [$firmaId])
    ->orderBy('u.nume')
    ->fetchAll();

// Get recent activity for this company
$activitateRecenta = db()->select('la.*, u.nume as utilizator_nume')
    ->from('log_activitate la')
    ->join('utilizatori u', 'la.utilizator_id = u.id')
    ->where('u.firma_id = ?', [$firmaId])
    ->orderBy('la.creat_la', 'DESC')
    ->limit(10)
    ->fetchAll();
?>

<!-- Subscription Alert -->
<?php if ($zileRamase <= 30): ?>
    <div class="flash-message <?php echo $zileRamase <= 7 ? 'error' : 'warning'; ?>">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Atenție!</strong>
        Abonamentul firmei expiră în <strong><?php echo $zileRamase; ?> zile</strong>
        (<?php echo format_date_ro($firma['data_expirare_abonament']); ?>).
        Vă rugăm contactați administratorul pentru prelungire.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-building"></i> Dashboard - <?php echo e($firma['denumire']); ?>
    </div>

    <div style="padding: 20px 0;">
        <h3 style="margin-bottom: 20px; color: var(--dark); font-size: 18px;">
            <i class="fas fa-chart-bar"></i> Statistici Firmă
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <!-- Utilizatori Card -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="opacity: 0.9; font-size: 14px; margin-bottom: 5px;">Utilizatori</p>
                        <h2 style="font-size: 36px; font-weight: 700;"><?php echo $stats['utilizatori_total']; ?></h2>
                        <p style="opacity: 0.8; font-size: 12px; margin-top: 5px;">
                            <?php echo $stats['utilizatori_activi']; ?> activi
                        </p>
                    </div>
                    <div style="font-size: 48px; opacity: 0.3;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Module Card -->
            <div style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="opacity: 0.9; font-size: 14px; margin-bottom: 5px;">Module Active</p>
                        <h2 style="font-size: 36px; font-weight: 700;"><?php echo $stats['module_alocate']; ?></h2>
                        <p style="opacity: 0.8; font-size: 12px; margin-top: 5px;">
                            Module disponibile
                        </p>
                    </div>
                    <div style="font-size: 48px; opacity: 0.3;">
                        <i class="fas fa-puzzle-piece"></i>
                    </div>
                </div>
            </div>

            <!-- Subscription Card -->
            <div style="background: linear-gradient(135deg, <?php echo $zileRamase <= 7 ? '#e74c3c, #c0392b' : ($zileRamase <= 30 ? '#f39c12, #e67e22' : '#3498db, #2980b9'); ?>); color: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="opacity: 0.9; font-size: 14px; margin-bottom: 5px;">Zile Abonament</p>
                        <h2 style="font-size: 36px; font-weight: 700;"><?php echo $zileRamase; ?></h2>
                        <p style="opacity: 0.8; font-size: 12px; margin-top: 5px;">
                            Până la <?php echo format_date_ro($firma['data_expirare_abonament']); ?>
                        </p>
                    </div>
                    <div style="font-size: 48px; opacity: 0.3;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Company Info -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-info-circle"></i> Informații Firmă
    </div>

    <div style="padding: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div>
                <p style="color: #666; font-size: 12px; margin-bottom: 5px;">Denumire</p>
                <p style="font-weight: 600; font-size: 16px;"><?php echo e($firma['denumire']); ?></p>
            </div>
            <div>
                <p style="color: #666; font-size: 12px; margin-bottom: 5px;">CUI</p>
                <p style="font-weight: 600; font-size: 16px;"><?php echo e($firma['cui']); ?></p>
            </div>
            <div>
                <p style="color: #666; font-size: 12px; margin-bottom: 5px;">Telefon</p>
                <p style="font-weight: 600; font-size: 16px;"><?php echo e($firma['telefon']); ?></p>
            </div>
            <div>
                <p style="color: #666; font-size: 12px; margin-bottom: 5px;">Email</p>
                <p style="font-weight: 600; font-size: 16px;"><?php echo e($firma['email']); ?></p>
            </div>
            <div>
                <p style="color: #666; font-size: 12px; margin-bottom: 5px;">Adresă</p>
                <p style="font-weight: 600; font-size: 16px;"><?php echo e($firma['adresa']); ?></p>
            </div>
            <div>
                <p style="color: #666; font-size: 12px; margin-bottom: 5px;">Data Creare</p>
                <p style="font-weight: 600; font-size: 16px;"><?php echo format_date_ro($firma['creat_la']); ?></p>
            </div>
        </div>
    </div>

    <div style="padding: 15px 20px; border-top: 1px solid var(--gray); text-align: right;">
        <a href="<?php echo url('setari-firma.php'); ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editează Setări Firmă
        </a>
    </div>
</div>

<!-- Allocated Modules -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-puzzle-piece"></i> Module Alocate
    </div>

    <?php if (empty($moduleAlocate)): ?>
        <p style="padding: 20px; text-align: center; color: #999;">
            <i class="fas fa-inbox"></i> Nu aveți module alocate
        </p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; padding: 20px;">
            <?php foreach ($moduleAlocate as $modul): ?>
                <div style="border: 2px solid var(--gray); border-radius: 8px; padding: 20px; transition: all 0.2s;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                        <div style="font-size: 32px; color: var(--primary);">
                            <i class="fas fa-<?php echo e($modul['icon'] ?? 'puzzle-piece'); ?>"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 16px; margin-bottom: 5px;"><?php echo e($modul['nume']); ?></h4>
                            <p style="font-size: 11px; color: #666;">v<?php echo e($modul['versiune']); ?></p>
                        </div>
                    </div>
                    <p style="font-size: 12px; color: #666; line-height: 1.5;">
                        <?php echo e(truncate($modul['descriere'], 80)); ?>
                    </p>
                    <p style="font-size: 11px; color: #999; margin-top: 10px;">
                        Alocat: <?php echo format_date_ro($modul['data_alocare']); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Team Members -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-users"></i> Echipa
    </div>

    <?php if (empty($utilizatori)): ?>
        <p style="padding: 20px; text-align: center; color: #999;">
            <i class="fas fa-inbox"></i> Nu există utilizatori
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nume</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Ultima Autentificare</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilizatori as $user): ?>
                    <tr>
                        <td><strong><?php echo e($user['nume']); ?></strong></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td>
                            <span style="background: var(--light); padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                <?php echo e($user['rol_nume']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['ultima_autentificare']): ?>
                                <?php echo format_datetime_ro($user['ultima_autentificare']); ?>
                            <?php else: ?>
                                <em style="color: #999;">Niciodată</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['activ']): ?>
                                <span style="color: var(--success); font-weight: 600;">
                                    <i class="fas fa-check-circle"></i> Activ
                                </span>
                            <?php else: ?>
                                <span style="color: var(--danger); font-weight: 600;">
                                    <i class="fas fa-times-circle"></i> Inactiv
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="padding: 15px; text-align: center; border-top: 1px solid var(--gray);">
        <a href="<?php echo url('utilizatori.php'); ?>" class="btn btn-primary">
            <i class="fas fa-users-cog"></i> Gestionează Utilizatori
        </a>
    </div>
</div>

<!-- Recent Activity -->
<?php if (!empty($activitateRecenta)): ?>
    <div class="card">
        <div class="card-header">
            <i class="fas fa-history"></i> Activitate Recentă
        </div>

        <table>
            <thead>
                <tr>
                    <th>Data/Ora</th>
                    <th>Utilizator</th>
                    <th>Acțiune</th>
                    <th>Detalii</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activitateRecenta as $log): ?>
                    <tr>
                        <td><?php echo format_datetime_ro($log['creat_la']); ?></td>
                        <td><strong><?php echo e($log['utilizator_nume']); ?></strong></td>
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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-bolt"></i> Acțiuni Rapide
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; padding: 20px;">
        <a href="<?php echo url('utilizatori.php?action=create'); ?>" class="btn btn-success" style="padding: 15px;">
            <i class="fas fa-user-plus"></i> Adaugă Utilizator
        </a>
        <a href="<?php echo url('setari-firma.php'); ?>" class="btn btn-primary" style="padding: 15px;">
            <i class="fas fa-cog"></i> Setări Firmă
        </a>
        <?php if (Auth::hasModuleAccess('devize')): ?>
            <a href="<?php echo url('modules/devize/index.php'); ?>" class="btn btn-primary" style="padding: 15px;">
                <i class="fas fa-file-invoice"></i> Devize
            </a>
        <?php endif; ?>
        <?php if (Auth::hasModuleAccess('articole')): ?>
            <a href="<?php echo url('modules/articole/index.php'); ?>" class="btn btn-secondary" style="padding: 15px;">
                <i class="fas fa-boxes"></i> Articole
            </a>
        <?php endif; ?>
    </div>
</div>
