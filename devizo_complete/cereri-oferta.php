<?php
/**
 * DEVIZO - Cereri de Oferta catre Furnizori
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

if (isSuperAdmin()) {
    redirect(SITE_URL . '/admin/index.php');
}

$pageTitle = 'Cereri de Oferta';

$db = getDB();
$firmaId = $_SESSION['firma_id'];

// Paginare si filtrare
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? clean($_GET['search']) : '';
$status = isset($_GET['status']) ? clean($_GET['status']) : '';

// Construire query
$where = ['firma_id = ?'];
$params = [$firmaId];

if ($search) {
    $where[] = '(titlu LIKE ? OR descriere LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = 'status = ?';
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);

// Total cereri
$stmtCount = $db->prepare("SELECT COUNT(*) as total FROM cereri_oferta WHERE $whereClause");
$stmtCount->execute($params);
$totalCereri = $stmtCount->fetch()['total'];
$totalPages = ceil($totalCereri / $perPage);

// Obtinere cereri
$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT
        co.*,
        u.nume as utilizator_nume,
        (SELECT COUNT(*) FROM cereri_oferta_randuri WHERE cerere_id = co.id) as nr_articole,
        (SELECT COUNT(*) FROM oferte_furnizori WHERE cerere_id = co.id) as nr_oferte
    FROM cereri_oferta co
    LEFT JOIN utilizatori u ON co.utilizator_id = u.id
    WHERE $whereClause
    ORDER BY co.creat_la DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$cereri = $stmt->fetchAll();

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

.filter-form {
    display: grid;
    grid-template-columns: 2fr 1fr auto;
    gap: 10px;
    align-items: center;
}

.search-box input,
.search-box select {
    padding: 10px;
    border: 1px solid var(--gray);
    border-radius: 4px;
    width: 100%;
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

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
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
    max-width: 800px;
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

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--gray);
    border-radius: 4px;
}

.articole-list {
    margin-top: 20px;
    border: 1px solid var(--gray);
    border-radius: 4px;
    padding: 15px;
}

.articol-item {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 10px;
    margin-bottom: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.articol-item input {
    padding: 8px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .filter-form {
        grid-template-columns: 1fr;
    }

    .articol-item {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="page-header">
    <h1><i class="fas fa-clipboard-list"></i> Cereri de Oferta</h1>
    <button onclick="openModal()" class="btn btn-success">
        <i class="fas fa-plus"></i> Cerere Noua
    </button>
</div>

<div class="info-box">
    <i class="fas fa-info-circle"></i>
    <strong>Informatii:</strong> Creati cereri de oferta pentru a solicita preturi de la furnizori.
    Fiecare cerere genereaza un link unic pe care il puteti trimite furnizorilor.
</div>

<div class="search-box">
    <form method="GET" class="filter-form">
        <input type="text" name="search" placeholder="Cauta dupa titlu..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="status">
            <option value="">Toate statusurile</option>
            <option value="Activa" <?php echo $status == 'Activa' ? 'selected' : ''; ?>>Activa</option>
            <option value="Inchisa" <?php echo $status == 'Inchisa' ? 'selected' : ''; ?>>Inchisa</option>
            <option value="Anulata" <?php echo $status == 'Anulata' ? 'selected' : ''; ?>>Anulata</option>
        </select>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn">
                <i class="fas fa-search"></i> Cauta
            </button>
            <?php if ($search || $status): ?>
                <a href="cereri-oferta.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Reseteaza
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="table-container">
    <?php if (empty($cereri)): ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>Nu au fost gasite cereri de oferta</h3>
            <p>Creati prima cerere pentru a solicita oferte de la furnizori!</p>
            <button onclick="openModal()" class="btn" style="margin-top: 15px;">
                <i class="fas fa-plus"></i> Cerere Noua
            </button>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Titlu</th>
                    <th>Articole</th>
                    <th>Oferte Primite</th>
                    <th>Data Limita</th>
                    <th>Status</th>
                    <th>Creat La</th>
                    <th>Actiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cereri as $cerere): ?>
                <tr>
                    <td><strong><?php echo clean($cerere['titlu']); ?></strong></td>
                    <td><?php echo $cerere['nr_articole']; ?> articole</td>
                    <td><?php echo $cerere['nr_oferte']; ?> oferte</td>
                    <td><?php echo $cerere['data_limita'] ? formatDateRO($cerere['data_limita']) : '-'; ?></td>
                    <td>
                        <span class="badge badge-<?php
                            echo $cerere['status'] == 'Activa' ? 'success' :
                                ($cerere['status'] == 'Inchisa' ? 'warning' : 'danger');
                        ?>">
                            <?php echo clean($cerere['status']); ?>
                        </span>
                    </td>
                    <td><?php echo formatDateTimeRO($cerere['creat_la']); ?></td>
                    <td>
                        <button onclick="viewCerere(<?php echo $cerere['id']; ?>)"
                                class="btn btn-sm" title="Vizualizeaza">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="shareCerere('<?php echo $cerere['link_hash']; ?>')"
                                class="btn btn-sm" title="Partajeaza">
                            <i class="fas fa-share-alt"></i>
                        </button>
                        <?php if ($cerere['status'] == 'Activa'): ?>
                            <button onclick="closeCerere(<?php echo $cerere['id']; ?>)"
                                    class="btn btn-sm btn-warning" title="Inchide">
                                <i class="fas fa-lock"></i>
                            </button>
                        <?php endif; ?>
                        <button onclick="deleteCerere(<?php echo $cerere['id']; ?>)"
                                class="btn btn-sm btn-danger" title="Sterge">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?>">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?>">
                    Urmator <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal Cerere Noua -->
<div id="cerereModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Cerere de Oferta Noua</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>

        <form id="cerereForm">
            <div class="form-group">
                <label for="titlu">Titlu <span style="color:red;">*</span></label>
                <input type="text" id="titlu" name="titlu" required placeholder="Ex: Materiale pentru proiect X">
            </div>

            <div class="form-group">
                <label for="descriere">Descriere</label>
                <textarea id="descriere" name="descriere" rows="3" placeholder="Detalii suplimentare despre cerere..."></textarea>
            </div>

            <div class="form-group">
                <label for="data_limita">Data Limita</label>
                <input type="date" id="data_limita" name="data_limita">
            </div>

            <div class="articole-list">
                <label><strong>Articole Solicitate</strong></label>
                <div id="articoleContainer">
                    <div class="articol-item">
                        <input type="text" placeholder="Denumire articol" name="articole[0][denumire]" required>
                        <input type="text" placeholder="UM (ex: buc)" name="articole[0][um]" required>
                        <input type="number" step="0.01" placeholder="Cantitate" name="articole[0][cantitate]" required>
                        <button type="button" onclick="removeArticol(this)" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addArticol()" class="btn btn-sm" style="margin-top: 10px;">
                    <i class="fas fa-plus"></i> Adauga Articol
                </button>
            </div>

            <div class="form-actions">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Anuleaza
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Creeaza Cerere
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let articolCount = 1;

function openModal() {
    document.getElementById('cerereForm').reset();
    document.getElementById('articoleContainer').innerHTML = `
        <div class="articol-item">
            <input type="text" placeholder="Denumire articol" name="articole[0][denumire]" required>
            <input type="text" placeholder="UM (ex: buc)" name="articole[0][um]" required>
            <input type="number" step="0.01" placeholder="Cantitate" name="articole[0][cantitate]" required>
            <button type="button" onclick="removeArticol(this)" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    articolCount = 1;
    document.getElementById('cerereModal').classList.add('active');
}

function closeModal() {
    document.getElementById('cerereModal').classList.remove('active');
}

function addArticol() {
    const container = document.getElementById('articoleContainer');
    const newArticol = document.createElement('div');
    newArticol.className = 'articol-item';
    newArticol.innerHTML = `
        <input type="text" placeholder="Denumire articol" name="articole[${articolCount}][denumire]" required>
        <input type="text" placeholder="UM (ex: buc)" name="articole[${articolCount}][um]" required>
        <input type="number" step="0.01" placeholder="Cantitate" name="articole[${articolCount}][cantitate]" required>
        <button type="button" onclick="removeArticol(this)" class="btn btn-sm btn-danger">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(newArticol);
    articolCount++;
}

function removeArticol(btn) {
    const container = document.getElementById('articoleContainer');
    if (container.children.length > 1) {
        btn.parentElement.remove();
    } else {
        alert('Trebuie sa existe cel putin un articol in cerere!');
    }
}

document.getElementById('cerereForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = {
        titlu: formData.get('titlu'),
        descriere: formData.get('descriere'),
        data_limita: formData.get('data_limita'),
        articole: []
    };

    // Colectam articolele
    let i = 0;
    while (formData.get(`articole[${i}][denumire]`)) {
        data.articole.push({
            denumire: formData.get(`articole[${i}][denumire]`),
            um: formData.get(`articole[${i}][um]`),
            cantitate: formData.get(`articole[${i}][cantitate]`)
        });
        i++;
    }

    fetch('<?php echo SITE_URL; ?>/api/cereri-oferta.php?action=create', {
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

function viewCerere(id) {
    window.location.href = '<?php echo SITE_URL; ?>/cerere-oferta-view.php?id=' + id;
}

function shareCerere(hash) {
    const url = '<?php echo SITE_URL; ?>/public/cerere/' + hash;
    prompt('Link partajabil pentru cerere de oferta:', url);
}

function closeCerere(id) {
    if (!confirm('Sigur doriti sa inchideti aceasta cerere? Nu va mai puteti primi oferte noi.')) {
        return;
    }

    fetch('<?php echo SITE_URL; ?>/api/cereri-oferta.php?action=close', {
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

function deleteCerere(id) {
    if (!confirm('Sigur doriti sa stergeti aceasta cerere? Actiunea este ireversibila!')) {
        return;
    }

    fetch('<?php echo SITE_URL; ?>/api/cereri-oferta.php?action=delete', {
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
document.getElementById('cerereModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
