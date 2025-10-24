<?php
/**
 * DEVIZO - Admin Panel (Super Administrator)
 * Gestionare firme, abonamente, utilizatori sistem
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Verificare autentificare
requireLogin();

// DOAR Super Admin
if (!isSuperAdmin()) {
    redirect(SITE_URL . '/index.php');
}

$pageTitle = 'Administrare Sistem';
$db = getDB();

// Statistici generale
$stats = [];

// Total firme
$stmt = $db->query("SELECT COUNT(*) as total FROM firme");
$stats['total_firme'] = $stmt->fetch()['total'];

// Firme active
$stmt = $db->query("SELECT COUNT(*) as total FROM firme WHERE abonament_activ = 1");
$stats['firme_active'] = $stmt->fetch()['total'];

// Total utilizatori
$stmt = $db->query("SELECT COUNT(*) as total FROM utilizatori WHERE rol_id != 1");
$stats['total_utilizatori'] = $stmt->fetch()['total'];

// Total devize
$stmt = $db->query("SELECT COUNT(*) as total FROM devize");
$stats['total_devize'] = $stmt->fetch()['total'];

// Lista firme
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$search = isset($_GET['search']) ? clean($_GET['search']) : '';

$where = [];
$params = [];

if ($search) {
    $where[] = '(denumire LIKE :search OR cui LIKE :search OR email LIKE :search)';
    $params[':search'] = "%$search%";
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Total
$stmtCount = $db->prepare("SELECT COUNT(*) as total FROM firme $whereClause");
$stmtCount->execute($params);
$totalRecords = $stmtCount->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Selectare firme
$params[':limit'] = $perPage;
$params[':offset'] = $offset;

$stmt = $db->prepare("
    SELECT
        f.*,
        (SELECT COUNT(*) FROM utilizatori WHERE firma_id = f.id) as nr_utilizatori,
        (SELECT COUNT(*) FROM devize WHERE firma_id = f.id) as nr_devize
    FROM firme f
    $whereClause
    ORDER BY f.creat_la DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}

$stmt->execute();
$firme = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-cog"></i> Administrare Sistem</h1>
</div>

<!-- Statistici -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $stats['total_firme']; ?></div>
            <div class="stat-label">Total Firme</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $stats['firme_active']; ?></div>
            <div class="stat-label">Firme Active</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $stats['total_utilizatori']; ?></div>
            <div class="stat-label">Utilizatori</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $stats['total_devize']; ?></div>
            <div class="stat-label">Devize Totale</div>
        </div>
    </div>
</div>

<!-- Filtre -->
<div class="card mb-20">
    <form method="get" class="filter-form">
        <div class="form-row">
            <div class="form-group">
                <input type="text" name="search" class="form-control" placeholder="Cauta firma (denumire, CUI, email)..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cauta
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Lista Firme -->
<div class="card">
    <div class="card-header">
        <h2>Gestionare Firme</h2>
        <button onclick="showAddFirmaModal()" class="btn btn-success">
            <i class="fas fa-plus"></i> Adauga Firma
        </button>
    </div>

    <?php if (count($firme) > 0): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Denumire</th>
                        <th>CUI</th>
                        <th>Email</th>
                        <th>Utilizatori</th>
                        <th>Devize</th>
                        <th>Abonament</th>
                        <th>Data Expirare</th>
                        <th>Status</th>
                        <th>Actiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($firme as $firma): ?>
                        <?php
                        $zileRamase = ceil((strtotime($firma['data_expirare_abonament']) - time()) / 86400);
                        $expired = $zileRamase < 0;
                        ?>
                        <tr class="<?php echo $expired ? 'row-expired' : ''; ?>">
                            <td><strong><?php echo clean($firma['denumire']); ?></strong></td>
                            <td><?php echo clean($firma['cui']); ?></td>
                            <td><?php echo clean($firma['email']); ?></td>
                            <td><?php echo $firma['nr_utilizatori']; ?> / <?php echo $firma['max_utilizatori']; ?></td>
                            <td><?php echo $firma['nr_devize']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $firma['abonament_activ'] ? 'success' : 'danger'; ?>">
                                    <?php echo $firma['abonament_activ'] ? 'Activ' : 'Inactiv'; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo formatDateRO($firma['data_expirare_abonament']); ?>
                                <?php if (!$expired && $zileRamase <= 30): ?>
                                    <br><small class="text-warning">(<?php echo $zileRamase; ?> zile)</small>
                                <?php elseif ($expired): ?>
                                    <br><small class="text-danger">(Expirat)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($expired): ?>
                                    <span class="badge badge-danger">Expirat</span>
                                <?php elseif (!$firma['abonament_activ']): ?>
                                    <span class="badge badge-warning">Suspendat</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Activ</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <button onclick="editFirma(<?php echo $firma['id']; ?>)" class="btn-icon" title="Editeaza">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="viewUtilizatori(<?php echo $firma['id']; ?>)" class="btn-icon" title="Utilizatori">
                                    <i class="fas fa-users"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginare -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>

                <span class="page-info">
                    Pagina <?php echo $page; ?> din <?php echo $totalPages; ?>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-secondary">
                        Urmator <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-building fa-3x"></i>
            <h3>Nicio firma gasita</h3>
            <p>Adauga prima firma din sistem.</p>
            <button onclick="showAddFirmaModal()" class="btn btn-success mt-10">
                <i class="fas fa-plus"></i> Adauga Firma
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Editare Firma -->
<div id="editFirmaModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Editeaza Firma</h2>
            <button onclick="closeModal('editFirmaModal')" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="firmaForm" onsubmit="saveFirma(event)">
                <input type="hidden" id="firma_id">

                <div class="form-group">
                    <label>Denumire Firma *</label>
                    <input type="text" id="denumire" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>CUI *</label>
                    <input type="text" id="cui" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Email Contact *</label>
                    <input type="email" id="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Max Utilizatori *</label>
                    <input type="number" id="max_utilizatori" class="form-control" min="1" required>
                </div>

                <div class="form-group">
                    <label>Abonament Activ</label>
                    <select id="abonament_activ" class="form-control">
                        <option value="1">Da</option>
                        <option value="0">Nu</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data Expirare Abonament *</label>
                    <input type="date" id="data_expirare_abonament" class="form-control" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salveaza
                    </button>
                    <button type="button" onclick="closeModal('editFirmaModal')" class="btn btn-secondary">
                        Anuleaza
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editFirma(id) {
    fetch('<?php echo SITE_URL; ?>/api/admin.php?action=get&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const f = data.data;
                document.getElementById('firma_id').value = f.id;
                document.getElementById('denumire').value = f.denumire;
                document.getElementById('cui').value = f.cui;
                document.getElementById('email').value = f.email;
                document.getElementById('max_utilizatori').value = f.max_utilizatori;
                document.getElementById('abonament_activ').value = f.abonament_activ;
                document.getElementById('data_expirare_abonament').value = f.data_expirare_abonament;
                document.getElementById('modalTitle').textContent = 'Editeaza Firma';
                showModal('editFirmaModal');
            }
        });
}

function showAddFirmaModal() {
    document.getElementById('firmaForm').reset();
    document.getElementById('firma_id').value = '';
    document.getElementById('modalTitle').textContent = 'Adauga Firma Noua';
    // Set default values
    document.getElementById('max_utilizatori').value = 10;
    document.getElementById('abonament_activ').value = 1;
    const nextYear = new Date();
    nextYear.setFullYear(nextYear.getFullYear() + 1);
    document.getElementById('data_expirare_abonament').value = nextYear.toISOString().split('T')[0];
    showModal('editFirmaModal');
}

function saveFirma(e) {
    e.preventDefault();
    const formData = new FormData();
    const id = document.getElementById('firma_id').value;

    formData.append('action', id ? 'update' : 'create');
    if (id) formData.append('id', id);
    formData.append('denumire', document.getElementById('denumire').value);
    formData.append('cui', document.getElementById('cui').value);
    formData.append('email', document.getElementById('email').value);
    formData.append('max_utilizatori', document.getElementById('max_utilizatori').value);
    formData.append('abonament_activ', document.getElementById('abonament_activ').value);
    formData.append('data_expirare_abonament', document.getElementById('data_expirare_abonament').value);

    fetch('<?php echo SITE_URL; ?>/api/admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            closeModal('editFirmaModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(data.message, 'error');
        }
    });
}

function viewUtilizatori(firmaId) {
    // TODO: Implement user view modal
    alert('Vizualizare utilizatori firma #' + firmaId + ' - in dezvoltare');
}

function showModal(id) {
    document.getElementById(id).style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-icon.blue { background: #3498db; }
.stat-icon.green { background: #2ecc71; }
.stat-icon.orange { background: #f39c12; }
.stat-icon.purple { background: #9b59b6; }

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #2c3e50;
}

.stat-label {
    font-size: 14px;
    color: #7f8c8d;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.card-header h2 {
    margin: 0;
    font-size: 20px;
}

.row-expired {
    background: #fff5f5 !important;
}

.text-warning {
    color: #f39c12;
}

.text-danger {
    color: #e74c3c;
}

.mb-20 {
    margin-bottom: 20px;
}

.mt-10 {
    margin-top: 10px;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
