<?php
/**
 * DEVIZO - Gestionare Unitati de Masura
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

if (isSuperAdmin()) {
    redirect(SITE_URL . '/admin/index.php');
}

$pageTitle = 'Unitati de Masura';

$db = getDB();
$firmaId = $_SESSION['firma_id'];

// Paginare si filtrare
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? clean($_GET['search']) : '';

// Construire query - afisam si UM globale (firma_id IS NULL) si cele custom
$where = ['(firma_id = ? OR firma_id IS NULL)'];
$params = [$firmaId];

if ($search) {
    $where[] = '(simbol LIKE ? OR denumire LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

// Total unitati
$stmtCount = $db->prepare("SELECT COUNT(*) as total FROM unitati_masura WHERE $whereClause");
$stmtCount->execute($params);
$totalUnitati = $stmtCount->fetch()['total'];
$totalPages = ceil($totalUnitati / $perPage);

// Obtinere unitati
$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT *
    FROM unitati_masura
    WHERE $whereClause
    ORDER BY simbol
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$unitati = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.info-box {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.info-box i {
    color: #0c5460;
}

.search-box {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.search-box form {
    display: flex;
    gap: 10px;
}

.search-box input {
    flex: 1;
    padding: 10px;
    border: 1px solid var(--gray);
    border-radius: 4px;
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.table th {
    background: var(--light);
    font-weight: 600;
}

.table tbody tr:hover {
    background: #f9f9f9;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--primary);
}

.modal-header h3 {
    margin: 0;
    color: var(--secondary);
}

.close-modal {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--secondary);
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--gray);
    border-radius: 4px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 20px;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid var(--gray);
    border-radius: 4px;
    text-decoration: none;
    color: var(--dark);
}

.pagination .active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
}
</style>

<div class="page-header">
    <h1><i class="fas fa-ruler"></i> Unitati de Masura</h1>
    <button onclick="openModal()" class="btn btn-success">
        <i class="fas fa-plus"></i> Unitate Noua
    </button>
</div>

<div class="info-box">
    <i class="fas fa-info-circle"></i>
    <strong>Informatii:</strong> Aici puteti gestiona unitatile de masura folosite in devize.
    Unitatile marcate cu "Globala" sunt predefinite si disponibile tuturor firmelor.
    Puteti adauga unitati custom specifice firmei dvs.
</div>

<div class="search-box">
    <form method="GET">
        <input type="text" name="search" placeholder="Cauta unitate de masura..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn">
            <i class="fas fa-search"></i> Cauta
        </button>
        <?php if ($search): ?>
            <a href="unitati-masura.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Reseteaza
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="table-container">
    <?php if (empty($unitati)): ?>
        <div class="empty-state">
            <i class="fas fa-ruler-combined"></i>
            <h3>Nu au fost gasite unitati de masura</h3>
            <p>Adaugati prima unitate pentru a incepe!</p>
            <button onclick="openModal()" class="btn" style="margin-top: 15px;">
                <i class="fas fa-plus"></i> Unitate Noua
            </button>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Simbol</th>
                    <th>Denumire</th>
                    <th>Tip</th>
                    <th>Actiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unitati as $um): ?>
                <tr>
                    <td><strong><?php echo clean($um['simbol']); ?></strong></td>
                    <td><?php echo clean($um['denumire']); ?></td>
                    <td>
                        <?php if ($um['firma_id'] === null): ?>
                            <span class="badge badge-info">Globala</span>
                        <?php else: ?>
                            <span class="badge badge-success">Custom</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($um['firma_id'] !== null): // Doar UM custom pot fi editate ?>
                            <button onclick='editUnitate(<?php echo json_encode($um); ?>)'
                                    class="btn btn-sm" title="Editeaza">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteUnitate(<?php echo $um['id']; ?>)"
                                    class="btn btn-sm btn-danger" title="Sterge">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php else: ?>
                            <span style="color: #999; font-size: 12px;">Globala - nu poate fi editata</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                    Urmator <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal Adauga/Editeaza Unitate -->
<div id="unitateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-plus"></i> Unitate Noua</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>

        <form id="unitateForm">
            <input type="hidden" id="unitate_id" name="id">

            <div class="form-group">
                <label for="simbol">Simbol <span style="color:red;">*</span></label>
                <input type="text" id="simbol" name="simbol" required placeholder="Ex: buc, kg, m, mp">
                <small style="color: #666;">Simbolul scurt al unitatii (ex: buc, kg, m, mp)</small>
            </div>

            <div class="form-group">
                <label for="denumire">Denumire <span style="color:red;">*</span></label>
                <input type="text" id="denumire" name="denumire" required placeholder="Ex: Bucata, Kilogram, Metru">
                <small style="color: #666;">Denumirea completa a unitatii</small>
            </div>

            <div class="form-actions">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Anuleaza
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Salveaza
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Unitate Noua';
    document.getElementById('unitateForm').reset();
    document.getElementById('unitate_id').value = '';
    document.getElementById('unitateModal').classList.add('active');
}

function closeModal() {
    document.getElementById('unitateModal').classList.remove('active');
}

function editUnitate(unitate) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editeaza Unitate';
    document.getElementById('unitate_id').value = unitate.id;
    document.getElementById('simbol').value = unitate.simbol;
    document.getElementById('denumire').value = unitate.denumire;
    document.getElementById('unitateModal').classList.add('active');
}

document.getElementById('unitateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    const action = data.id ? 'update' : 'create';

    fetch('<?php echo SITE_URL; ?>/api/unitati-masura.php?action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            location.reload();
        } else {
            alert('Eroare: ' + result.message);
        }
    })
    .catch(error => {
        alert('Eroare de comunicare: ' + error);
    });
});

function deleteUnitate(id) {
    if (!confirm('Sigur doriti sa stergeti aceasta unitate de masura?')) {
        return;
    }

    fetch('<?php echo SITE_URL; ?>/api/unitati-masura.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            location.reload();
        } else {
            alert('Eroare: ' + result.message);
        }
    });
}

// Close modal on outside click
document.getElementById('unitateModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
