<?php
/**
 * DEVIZO - Vizualizare/Editare Deviz
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

if (isSuperAdmin()) {
    redirect(SITE_URL . '/admin/index.php');
}

$devizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editMode = isset($_GET['edit']) && $_GET['edit'] == 1;

if (!$devizId) {
    redirect(SITE_URL . '/devize.php');
}

$db = getDB();
$firmaId = $_SESSION['firma_id'];

// Obtinere deviz
try {
    $stmt = $db->prepare("
        SELECT d.*, p.denumire as partener_denumire, p.cui as partener_cui,
               p.adresa as partener_adresa, p.telefon as partener_telefon,
               p.email as partener_email,
               u.nume as utilizator_nume
        FROM devize d
        LEFT JOIN parteneri p ON d.partener_id = p.id
        LEFT JOIN utilizatori u ON d.utilizator_id = u.id
        WHERE d.id = ? AND d.firma_id = ?
    ");
    $stmt->execute([$devizId, $firmaId]);
    $deviz = $stmt->fetch();
    
    if (!$deviz) {
        setFlashMessage('Devizul nu a fost gasit.', 'error');
        redirect(SITE_URL . '/devize.php');
    }
    
    // Obtinere articole deviz
    $stmt = $db->prepare("
        SELECT da.*, a.cod as articol_cod
        FROM deviz_articole da
        LEFT JOIN articole a ON da.articol_id = a.id
        WHERE da.deviz_id = ?
        ORDER BY da.id
    ");
    $stmt->execute([$devizId]);
    $articole = $stmt->fetchAll();
    
} catch (PDOException $e) {
    logError("Eroare deviz.php: " . $e->getMessage());
    setFlashMessage('Eroare la obtinerea devizului.', 'error');
    redirect(SITE_URL . '/devize.php');
}

// Obtinere informatii firma
$firma = getCurrentFirma();

$pageTitle = 'Deviz ' . $deviz['numar_deviz'];
require_once __DIR__ . '/includes/header.php';
?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--primary);
}

.deviz-container {
    background: white;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    max-width: 1200px;
    margin: 0 auto;
}

.deviz-header {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--primary);
}

.firma-info h2 {
    color: var(--primary);
    margin-bottom: 10px;
}

.firma-info p {
    margin: 5px 0;
    color: #666;
}

.deviz-info {
    text-align: right;
}

.deviz-info h1 {
    color: var(--secondary);
    margin-bottom: 10px;
    font-size: 28px;
}

.deviz-info p {
    margin: 5px 0;
}

.status-badge {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 4px;
    font-weight: 600;
    margin-top: 10px;
}

.status-badge.asteptare { background: #fff3cd; color: #856404; }
.status-badge.lucru { background: #cce5ff; color: #004085; }
.status-badge.acceptat { background: #d4edda; color: #155724; }
.status-badge.finalizat { background: #d1ecf1; color: #0c5460; }
.status-badge.anulat { background: #f8d7da; color: #721c24; }

.partener-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 4px;
    margin-bottom: 30px;
}

.partener-section h3 {
    color: var(--secondary);
    margin-bottom: 15px;
}

.articole-table {
    width: 100%;
    border-collapse: collapse;
    margin: 30px 0;
}

.articole-table th {
    background: var(--secondary);
    color: white;
    padding: 12px;
    text-align: left;
    font-weight: 600;
}

.articole-table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

.articole-table tbody tr:hover {
    background: #f8f9fa;
}

.articole-table .text-right {
    text-align: right;
}

.totals-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid var(--gray);
}

.total-row {
    display: flex;
    justify-content: flex-end;
    gap: 100px;
    padding: 10px 0;
    font-size: 16px;
}

.total-row.final {
    font-size: 20px;
    font-weight: bold;
    color: var(--primary);
    border-top: 2px solid var(--primary);
    padding-top: 15px;
    margin-top: 10px;
}

.observatii-section {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 4px;
}

.observatii-section h3 {
    color: var(--secondary);
    margin-bottom: 10px;
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    justify-content: center;
}

@media print {
    .page-header, .action-buttons, .main-nav, .flash-message { display: none; }
    .deviz-container { box-shadow: none; padding: 20px; }
}

@media (max-width: 768px) {
    .deviz-header {
        grid-template-columns: 1fr;
    }
    
    .deviz-info {
        text-align: left;
    }
    
    .total-row {
        gap: 20px;
    }
}
</style>

<div class="page-header">
    <h1><i class="fas fa-file-invoice"></i> <?php echo clean($deviz['numar_deviz']); ?></h1>
    <a href="<?php echo SITE_URL; ?>/devize.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Inapoi la Lista
    </a>
</div>

<div class="deviz-container">
    <!-- Header Deviz -->
    <div class="deviz-header">
        <div class="firma-info">
            <h2><?php echo clean($firma['denumire']); ?></h2>
            <p><strong>CUI:</strong> <?php echo clean($firma['cui']); ?></p>
            <?php if ($firma['nr_reg_com']): ?>
                <p><strong>Nr. Reg. Com.:</strong> <?php echo clean($firma['nr_reg_com']); ?></p>
            <?php endif; ?>
            <?php if ($firma['adresa']): ?>
                <p><strong>Adresa:</strong> <?php echo clean($firma['adresa']); ?></p>
            <?php endif; ?>
            <?php if ($firma['telefon']): ?>
                <p><strong>Telefon:</strong> <?php echo clean($firma['telefon']); ?></p>
            <?php endif; ?>
            <?php if ($firma['email']): ?>
                <p><strong>Email:</strong> <?php echo clean($firma['email']); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="deviz-info">
            <h1>DEVIZ</h1>
            <p><strong>Numar:</strong> <?php echo clean($deviz['numar_deviz']); ?></p>
            <p><strong>Data:</strong> <?php echo formatDateRO($deviz['data_deviz']); ?></p>
            <p><strong>Creat de:</strong> <?php echo clean($deviz['utilizator_nume']); ?></p>
            <span class="status-badge <?php 
                echo strtolower(str_replace(' ', '', $deviz['status'])); 
            ?>">
                <?php echo clean($deviz['status']); ?>
            </span>
        </div>
    </div>
    
    <!-- Informatii Partener -->
    <div class="partener-section">
        <h3><i class="fas fa-user"></i> Client</h3>
        <p><strong><?php echo clean($deviz['partener_denumire']); ?></strong></p>
        <?php if ($deviz['partener_cui']): ?>
            <p><strong>CUI:</strong> <?php echo clean($deviz['partener_cui']); ?></p>
        <?php endif; ?>
        <?php if ($deviz['partener_adresa']): ?>
            <p><strong>Adresa:</strong> <?php echo clean($deviz['partener_adresa']); ?></p>
        <?php endif; ?>
        <?php if ($deviz['partener_telefon']): ?>
            <p><strong>Telefon:</strong> <?php echo clean($deviz['partener_telefon']); ?></p>
        <?php endif; ?>
        <?php if ($deviz['partener_email']): ?>
            <p><strong>Email:</strong> <?php echo clean($deviz['partener_email']); ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Tabel Articole -->
    <table class="articole-table">
        <thead>
            <tr>
                <th>Nr.</th>
                <th>Denumire</th>
                <th>UM</th>
                <th class="text-right">Cantitate</th>
                <th class="text-right">Pret Unitar</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $nr = 1; foreach ($articole as $art): ?>
            <tr>
                <td><?php echo $nr++; ?></td>
                <td><?php echo clean($art['denumire']); ?></td>
                <td><?php echo clean($art['um']); ?></td>
                <td class="text-right"><?php echo number_format($art['cantitate'], 2, '.', ','); ?></td>
                <td class="text-right"><?php echo formatPrice($art['pret_unitar']); ?></td>
                <td class="text-right"><strong><?php echo formatPrice($art['total']); ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Totaluri -->
    <div class="totals-section">
        <div class="total-row">
            <span>Total Materiale:</span>
            <strong><?php echo formatPrice($deviz['total_materiale']); ?></strong>
        </div>
        <div class="total-row">
            <span>Manopera (<?php echo number_format($deviz['procent_manopera'], 2); ?>%):</span>
            <strong><?php echo formatPrice($deviz['total_manopera']); ?></strong>
        </div>
        <div class="total-row final">
            <span>TOTAL GENERAL:</span>
            <strong><?php echo formatPrice($deviz['total_general']); ?></strong>
        </div>
    </div>
    
    <!-- Observatii -->
    <?php if (!empty($deviz['observatii'])): ?>
    <div class="observatii-section">
        <h3><i class="fas fa-comment"></i> Observatii</h3>
        <p><?php echo nl2br(clean($deviz['observatii'])); ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Butoane Actiuni -->
    <div class="action-buttons">
        <button onclick="window.print()" class="btn">
            <i class="fas fa-print"></i> Printeaza
        </button>
        <a href="<?php echo SITE_URL; ?>/api/devize.php?action=pdf&id=<?php echo $deviz['id']; ?>" 
           class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <button onclick="shareDeviz('<?php echo $deviz['link_hash']; ?>')" class="btn">
            <i class="fas fa-share-alt"></i> Partajeaza
        </button>
        <a href="<?php echo SITE_URL; ?>/deviz-nou.php?duplicate=<?php echo $deviz['id']; ?>" 
           class="btn btn-secondary">
            <i class="fas fa-copy"></i> Duplica
        </a>
        <button onclick="changeStatus()" class="btn btn-success">
            <i class="fas fa-edit"></i> Schimba Status
        </button>
        <button onclick="deleteDeviz()" class="btn btn-danger">
            <i class="fas fa-trash"></i> Sterge
        </button>
    </div>
</div>

<script>
function shareDeviz(hash) {
    const url = '<?php echo SITE_URL; ?>/public/deviz.php?hash=' + hash;
    prompt('Link partajabil pentru deviz:', url);
}

function changeStatus() {
    const newStatus = prompt('Introduceti noul status:\n- In asteptare\n- In lucru\n- Acceptat\n- Finalizat\n- Anulat', '<?php echo $deviz['status']; ?>');
    
    if (newStatus) {
        fetch('<?php echo SITE_URL; ?>/api/devize.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: <?php echo $deviz['id']; ?>,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Eroare: ' + data.message);
            }
        });
    }
}

function deleteDeviz() {
    if (!confirm('Sigur doriti sa stergeti acest deviz? Aceasta actiune este ireversibila!')) {
        return;
    }
    
    fetch('<?php echo SITE_URL; ?>/api/devize.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: <?php echo $deviz['id']; ?> })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo SITE_URL; ?>/devize.php';
        } else {
            alert('Eroare: ' + data.message);
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
