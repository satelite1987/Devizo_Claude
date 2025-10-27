<?php
/**
 * DEVIZO v2.0 - Super Admin Dashboard
 *
 * Dashboard for Super Admin with system overview and management
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}

// Ensure user is Super Admin
if (!Auth::isSuperAdmin()) {
    http_response_code(403);
    die('Acces interzis.');
}

$pageTitle = 'Dashboard Super Admin';

// Get statistics
$stats = [
    'firme_total' => db()->select('COUNT(*) as count')->from('firme')->fetchColumn(),
    'firme_active' => db()->select('COUNT(*) as count')->from('firme')->where('activa = 1')->fetchColumn(),
    'utilizatori_total' => db()->select('COUNT(*) as count')->from('utilizatori')->fetchColumn(),
    'utilizatori_activi' => db()->select('COUNT(*) as count')->from('utilizatori')->where('activ = 1')->fetchColumn(),
    'module_total' => db()->select('COUNT(*) as count')->from('module_sistem')->fetchColumn(),
    'module_active' => db()->select('COUNT(*) as count')->from('module_sistem')->where('activ = 1')->fetchColumn(),
];

// Get recent companies
$firmeRecente = db()->select('*')
    ->from('firme')
    ->orderBy('creat_la', 'DESC')
    ->limit(5)
    ->fetchAll();

// Get recent activity
$activitateRecenta = db()->select('la.*, u.nume as utilizator_nume, u.email as utilizator_email')
    ->from('log_activitate la')
    ->leftJoin('utilizatori u', 'la.utilizator_id = u.id')
    ->orderBy('la.creat_la', 'DESC')
    ->limit(10)
    ->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-crown"></i> Dashboard Super Admin
    </div>

    <div style="padding: 20px 0;">
        <h3 style="margin-bottom: 20px; color: var(--dark); font-size: 18px;">
            <i class="fas fa-chart-bar"></i> Statistici Generale
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <!-- Firme Card -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="opacity: 0.9; font-size: 14px; margin-bottom: 5px;">Firme Înregistrate</p>
                        <h2 style="font-size: 36px; font-weight: 700;"><?php echo $stats['firme_total']; ?></h2>
                        <p style="opacity: 0.8; font-size: 12px; margin-top: 5px;">
                            <?php echo $stats['firme_active']; ?> active
                        </p>
                    </div>
                    <div style="font-size: 48px; opacity: 0.3;">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>

            <!-- Utilizatori Card -->
            <div style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="opacity: 0.9; font-size: 14px; margin-bottom: 5px;">Utilizatori Totali</p>
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
            <div style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="opacity: 0.9; font-size: 14px; margin-bottom: 5px;">Module Sistem</p>
                        <h2 style="font-size: 36px; font-weight: 700;"><?php echo $stats['module_total']; ?></h2>
                        <p style="opacity: 0.8; font-size: 12px; margin-top: 5px;">
                            <?php echo $stats['module_active']; ?> active
                        </p>
                    </div>
                    <div style="font-size: 48px; opacity: 0.3;">
                        <i class="fas fa-puzzle-piece"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Companies -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-building"></i> Firme Recente
    </div>

    <?php if (empty($firmeRecente)): ?>
        <p style="padding: 20px; text-align: center; color: #999;">
            <i class="fas fa-inbox"></i> Nu există firme înregistrate
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Denumire</th>
                    <th>CUI</th>
                    <th>Telefon</th>
                    <th>Data Creare</th>
                    <th>Expirare Abonament</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($firmeRecente as $firma): ?>
                    <tr>
                        <td><strong><?php echo e($firma['denumire']); ?></strong></td>
                        <td><?php echo e($firma['cui']); ?></td>
                        <td><?php echo e($firma['telefon']); ?></td>
                        <td><?php echo format_date_ro($firma['creat_la']); ?></td>
                        <td>
                            <?php
                            $zileRamase = ceil((strtotime($firma['data_expirare_abonament']) - time()) / 86400);
                            $color = $zileRamase <= 7 ? 'var(--danger)' : ($zileRamase <= 30 ? 'var(--warning)' : 'var(--success)');
                            ?>
                            <span style="color: <?php echo $color; ?>; font-weight: 600;">
                                <?php echo format_date_ro($firma['data_expirare_abonament']); ?>
                                (<?php echo $zileRamase; ?> zile)
                            </span>
                        </td>
                        <td>
                            <?php if ($firma['activa']): ?>
                                <span style="color: var(--success); font-weight: 600;">
                                    <i class="fas fa-check-circle"></i> Activă
                                </span>
                            <?php else: ?>
                                <span style="color: var(--danger); font-weight: 600;">
                                    <i class="fas fa-times-circle"></i> Inactivă
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="padding: 15px; text-align: center; border-top: 1px solid var(--gray);">
        <a href="<?php echo url('admin/firme.php'); ?>" class="btn btn-primary">
            <i class="fas fa-eye"></i> Vezi Toate Firmele
        </a>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-history"></i> Activitate Recentă
    </div>

    <?php if (empty($activitateRecenta)): ?>
        <p style="padding: 20px; text-align: center; color: #999;">
            <i class="fas fa-inbox"></i> Nu există activitate recentă
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Data/Ora</th>
                    <th>Utilizator</th>
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
                            <?php if ($log['utilizator_nume']): ?>
                                <strong><?php echo e($log['utilizator_nume']); ?></strong>
                                <br><small style="color: #666;"><?php echo e($log['utilizator_email']); ?></small>
                            <?php else: ?>
                                <em style="color: #999;">Necunoscut</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $actionLabels = [
                                'login' => '<i class="fas fa-sign-in-alt"></i> Login',
                                'login_success' => '<i class="fas fa-check-circle" style="color: var(--success);"></i> Login Reușit',
                                'login_failed' => '<i class="fas fa-times-circle" style="color: var(--danger);"></i> Login Eșuat',
                                'logout' => '<i class="fas fa-sign-out-alt"></i> Logout',
                                'password_change' => '<i class="fas fa-key"></i> Schimbare Parolă',
                                'password_reset' => '<i class="fas fa-key"></i> Reset Parolă',
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
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-bolt"></i> Acțiuni Rapide
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; padding: 20px;">
        <a href="<?php echo url('admin/firme.php?action=create'); ?>" class="btn btn-success" style="padding: 15px;">
            <i class="fas fa-plus-circle"></i> Adaugă Firmă Nouă
        </a>
        <a href="<?php echo url('admin/module.php'); ?>" class="btn btn-primary" style="padding: 15px;">
            <i class="fas fa-puzzle-piece"></i> Gestionează Module
        </a>
        <a href="<?php echo url('admin/setari.php'); ?>" class="btn btn-secondary" style="padding: 15px;">
            <i class="fas fa-cog"></i> Setări Sistem
        </a>
        <a href="<?php echo url('admin/logs.php'); ?>" class="btn btn-secondary" style="padding: 15px;">
            <i class="fas fa-file-alt"></i> Loguri Sistem
        </a>
    </div>
</div>
