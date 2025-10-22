<?php
/**
 * DEVIZO - Pagina Principala (Dashboard)
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Verificare autentificare
requireLogin();

// Obtinem datele utilizatorului si firmei
$currentUser = getCurrentUser();
$currentFirma = getCurrentFirma();

// Statistici pentru dashboard
$stats = [];

try {
    $db = getDB();

    if (isSuperAdmin()) {
        // Statistici pentru Super Admin
        $stmt = $db->query("SELECT COUNT(*) as total FROM firme");
        $stats['total_firme'] = $stmt->fetch()['total'];

        $stmt = $db->query("SELECT COUNT(*) as total FROM firme WHERE abonament_activ = 1");
        $stats['firme_active'] = $stmt->fetch()['total'];

        $stmt = $db->query("SELECT COUNT(*) as total FROM utilizatori WHERE activ = 1");
        $stats['total_utilizatori'] = $stmt->fetch()['total'];

        $stmt = $db->query("SELECT COUNT(*) as total FROM devize");
        $stats['total_devize'] = $stmt->fetch()['total'];

        $stmt = $db->query("SELECT COALESCE(SUM(total_general), 0) as total FROM devize");
        $stats['valoare_totala'] = $stmt->fetch()['total'];

        // Firme care expira in curand
        $stmt = $db->query("
            SELECT * FROM firme
            WHERE abonament_activ = 1
            AND data_expirare_abonament <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY data_expirare_abonament ASC
            LIMIT 10
        ");
        $firmeExpirare = $stmt->fetchAll();

    } else {
        // Statistici pentru utilizatori normali
        $firmaId = $_SESSION['firma_id'];

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM devize WHERE firma_id = ?");
        $stmt->execute([$firmaId]);
        $stats['total_devize'] = $stmt->fetch()['total'];

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM devize WHERE firma_id = ? AND status = 'In asteptare'");
        $stmt->execute([$firmaId]);
        $stats['devize_asteptare'] = $stmt->fetch()['total'];

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM devize WHERE firma_id = ? AND status = 'In lucru'");
        $stmt->execute([$firmaId]);
        $stats['devize_lucru'] = $stmt->fetch()['total'];

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM devize WHERE firma_id = ? AND status = 'Finalizat'");
        $stmt->execute([$firmaId]);
        $stats['devize_finalizate'] = $stmt->fetch()['total'];

        $stmt = $db->prepare("SELECT COALESCE(SUM(total_general), 0) as total FROM devize WHERE firma_id = ?");
        $stmt->execute([$firmaId]);
        $stats['valoare_totala'] = $stmt->fetch()['total'];

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM articole WHERE firma_id = ?");
        $stmt->execute([$firmaId]);
        $stats['total_articole'] = $stmt->fetch()['total'];

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM parteneri WHERE firma_id = ?");
        $stmt->execute([$firmaId]);
        $stats['total_parteneri'] = $stmt->fetch()['total'];

        // Devize recente
        $stmt = $db->prepare("
            SELECT d.*, p.denumire as partener_denumire
            FROM devize d
            LEFT JOIN parteneri p ON d.partener_id = p.id
            WHERE d.firma_id = ?
            ORDER BY d.creat_la DESC
            LIMIT 5
        ");
        $stmt->execute([$firmaId]);
        $devizeRecente = $stmt->fetchAll();
    }

} catch (PDOException $e) {
    logError("Eroare dashboard: " . $e->getMessage());
}

?>

<style>
    .dashboard {
        padding: 20px 0;
    }

    .welcome-section {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    .welcome-section h2 {
        color: var(--secondary);
        margin-bottom: 10px;
    }

    .welcome-section p {
        color: #666;
        font-size: 16px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid var(--primary);
    }

    .stat-card.success {
        border-left-color: var(--success);
    }

    .stat-card.warning {
        border-left-color: var(--warning);
    }

    .stat-card.danger {
        border-left-color: var(--danger);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .stat-title {
        font-size: 14px;
        color: #666;
        text-transform: uppercase;
    }

    .stat-icon {
        font-size: 24px;
        color: var(--primary);
    }

    .stat-value {
        font-size: 32px;
        font-weight: bold;
        color: var(--secondary);
    }

    .section-box {
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .section-box h3 {
        color: var(--secondary);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .table th,
    .table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .table th {
        background: var(--light);
        font-weight: 600;
        color: var(--secondary);
    }

    .table tr:hover {
        background: #f9f9f9;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-primary {
        background: #e3f2fd;
        color: #1976d2;
    }

    .badge-success {
        background: #e8f5e9;
        color: #388e3c;
    }

    .badge-warning {
        background: #fff3e0;
        color: #f57c00;
    }

    .badge-danger {
        background: #ffebee;
        color: #d32f2f;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .action-card {
        background: var(--light);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        text-decoration: none;
        color: var(--dark);
        transition: all 0.3s;
    }

    .action-card:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .action-card i {
        font-size: 32px;
        margin-bottom: 10px;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .quick-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h2>Bun venit, <?php echo clean($currentUser['nume']); ?>!</h2>
        <p>
            <?php if (isSuperAdmin()): ?>
                Panou de administrare - gestionati toate firmele si utilizatorii.
            <?php else: ?>
                <?php echo clean($currentFirma['denumire']); ?> -
                Abonament activ pana la <?php echo formatDateRO($currentFirma['data_expirare_abonament']); ?>
            <?php endif; ?>
        </p>
    </div>

    <?php if (isSuperAdmin()): ?>
        <!-- Statistici Super Admin -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Firme</span>
                    <i class="fas fa-building stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_firme']; ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <span class="stat-title">Firme Active</span>
                    <i class="fas fa-check-circle stat-icon" style="color: var(--success);"></i>
                </div>
                <div class="stat-value"><?php echo $stats['firme_active']; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Utilizatori</span>
                    <i class="fas fa-users stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_utilizatori']; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Devize</span>
                    <i class="fas fa-file-invoice stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_devize']; ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <span class="stat-title">Valoare Totala</span>
                    <i class="fas fa-euro-sign stat-icon" style="color: var(--success);"></i>
                </div>
                <div class="stat-value"><?php echo formatPrice($stats['valoare_totala']); ?></div>
            </div>
        </div>

        <!-- Firme care expira in curand -->
        <?php if (!empty($firmeExpirare)): ?>
        <div class="section-box">
            <h3><i class="fas fa-exclamation-triangle"></i> Firme cu Abonament Expirare Apropiata</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Firma</th>
                        <th>CUI</th>
                        <th>Data Expirare</th>
                        <th>Zile Ramase</th>
                        <th>Actiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($firmeExpirare as $firma):
                        $zileRamase = ceil((strtotime($firma['data_expirare_abonament']) - time()) / (60 * 60 * 24));
                    ?>
                    <tr>
                        <td><?php echo clean($firma['denumire']); ?></td>
                        <td><?php echo clean($firma['cui']); ?></td>
                        <td><?php echo formatDateRO($firma['data_expirare_abonament']); ?></td>
                        <td>
                            <span class="badge badge-warning"><?php echo $zileRamase; ?> zile</span>
                        </td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/admin/editeaza-firma.php?id=<?php echo $firma['id']; ?>" class="btn btn-sm">
                                Prelungeste
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Actiuni rapide -->
        <div class="section-box">
            <h3><i class="fas fa-bolt"></i> Actiuni Rapide</h3>
            <div class="quick-actions">
                <a href="<?php echo SITE_URL; ?>/admin/adauga-firma.php" class="action-card">
                    <i class="fas fa-plus-circle"></i>
                    <div>Adauga Firma Noua</div>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/firme.php" class="action-card">
                    <i class="fas fa-building"></i>
                    <div>Gestioneaza Firme</div>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/utilizatori.php" class="action-card">
                    <i class="fas fa-users"></i>
                    <div>Gestioneaza Utilizatori</div>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/rapoarte.php" class="action-card">
                    <i class="fas fa-chart-bar"></i>
                    <div>Rapoarte</div>
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- Statistici Utilizatori Normali -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Devize</span>
                    <i class="fas fa-file-invoice stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_devize']; ?></div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <span class="stat-title">In Asteptare</span>
                    <i class="fas fa-clock stat-icon" style="color: var(--warning);"></i>
                </div>
                <div class="stat-value"><?php echo $stats['devize_asteptare']; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">In Lucru</span>
                    <i class="fas fa-tools stat-icon" style="color: var(--primary);"></i>
                </div>
                <div class="stat-value"><?php echo $stats['devize_lucru']; ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <span class="stat-title">Finalizate</span>
                    <i class="fas fa-check-circle stat-icon" style="color: var(--success);"></i>
                </div>
                <div class="stat-value"><?php echo $stats['devize_finalizate']; ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <span class="stat-title">Valoare Totala</span>
                    <i class="fas fa-euro-sign stat-icon" style="color: var(--success);"></i>
                </div>
                <div class="stat-value"><?php echo formatPrice($stats['valoare_totala']); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Articole</span>
                    <i class="fas fa-boxes stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_articole']; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Parteneri</span>
                    <i class="fas fa-address-book stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_parteneri']; ?></div>
            </div>
        </div>

        <!-- Actiuni rapide -->
        <div class="section-box">
            <h3><i class="fas fa-bolt"></i> Actiuni Rapide</h3>
            <div class="quick-actions">
                <a href="<?php echo SITE_URL; ?>/devize.php?action=new" class="action-card">
                    <i class="fas fa-plus-circle"></i>
                    <div>Deviz Nou</div>
                </a>
                <a href="<?php echo SITE_URL; ?>/articole.php?action=new" class="action-card">
                    <i class="fas fa-box"></i>
                    <div>Adauga Articol</div>
                </a>
                <a href="<?php echo SITE_URL; ?>/parteneri.php?action=new" class="action-card">
                    <i class="fas fa-user-plus"></i>
                    <div>Adauga Partener</div>
                </a>
                <a href="<?php echo SITE_URL; ?>/cereri-oferta.php?action=new" class="action-card">
                    <i class="fas fa-paper-plane"></i>
                    <div>Cerere Oferta</div>
                </a>
            </div>
        </div>

        <!-- Devize recente -->
        <div class="section-box">
            <h3><i class="fas fa-history"></i> Devize Recente</h3>
            <?php if (!empty($devizeRecente)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Numar</th>
                        <th>Data</th>
                        <th>Partener</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Actiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devizeRecente as $deviz): ?>
                    <tr>
                        <td><strong><?php echo clean($deviz['numar_deviz']); ?></strong></td>
                        <td><?php echo formatDateRO($deviz['data_deviz']); ?></td>
                        <td><?php echo clean($deviz['partener_denumire'] ?? '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php
                                echo $deviz['status'] == 'Finalizat' ? 'success' :
                                     ($deviz['status'] == 'In lucru' ? 'primary' :
                                     ($deviz['status'] == 'Acceptat' ? 'success' : 'warning'));
                            ?>">
                                <?php echo clean($deviz['status']); ?>
                            </span>
                        </td>
                        <td><strong><?php echo formatPrice($deviz['total_general']); ?></strong></td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/vezi-deviz.php?id=<?php echo $deviz['id']; ?>" class="btn btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Nu exista devize create inca.</p>
                <a href="<?php echo SITE_URL; ?>/devize.php?action=new" class="btn" style="margin-top: 15px;">
                    <i class="fas fa-plus"></i> Creaza Primul Deviz
                </a>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
