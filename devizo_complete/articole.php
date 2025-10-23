<?php
/**
 * DEVIZO - Gestionare Articole
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

if (isSuperAdmin()) {
    redirect(SITE_URL . '/admin/index.php');
}

$pageTitle = 'Articole';

$db = getDB();
$firmaId = $_SESSION['firma_id'];

// Paginare si filtrare
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? clean($_GET['search']) : '';

// Construire query
$where = ['firma_id = ?'];
$params = [$firmaId];

if ($search) {
    $where[] = '(denumire LIKE ? OR cod LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

// Total articole
$stmtCount = $db->prepare("SELECT COUNT(*) as total FROM articole WHERE $whereClause");
$stmtCount->execute($params);
$totalArticole = $stmtCount->fetch()['total'];
$totalPages = ceil($totalArticole / $perPage);

// Obtinere articole
$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT a.*, um.simbol as um_simbol
    FROM articole a
    LEFT JOIN unitati_masura um ON a.um = um.id
    WHERE $whereClause
    ORDER BY a.denumire
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$articole = $stmt->fetchAll();

// Obtinere unitati masura pentru modal
$stmt = $db->prepare("SELECT * FROM unitati_masura WHERE firma_id = ? OR firma_id IS NULL ORDER BY simbol");
$stmt->execute([$firmaId]);
$unitatiMasura = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
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

.form-group input,
.form-group select,
.form-group textarea {
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
    <h1><i class="fas fa-boxes"></i> Articole</h1>
    <button onclick="openModal()" class="btn btn-success">
        <i class="fas fa-plus"></i> Articol Nou
    </button>
</div>

<div class="search-box">
    <form method="GET">
        <input type="text" name="search" placeholder="Cauta articol..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn">
            <i class="fas fa-search"></i> Cauta
        </button>
        <?php if ($search): ?>
            <a href="articole.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Reseteaza
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="table-container">
    <?php if (empty($articole)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>Nu au fost gasite articole</h3>
            <p>Adaugati primul articol pentru a incepe!</p>
            <button onclick="openModal()" class="btn" style="margin-top: 15px;">
                <i class="fas fa-plus"></i> Articol Nou
            </button>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Cod</th>
                    <th>Denumire</th>
                    <th>UM</th>
                    <th>Pret Unitar</th>
                    <th>Actiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articole as $art): ?>
                <tr>
                    <td><strong><?php echo clean($art['cod']); ?></strong></td>
                    <td><?php echo clean($art['denumire']); ?></td>
                    <td><?php echo clean($art['um_simbol'] ?? $art['um']); ?></td>
                    <td><?php echo formatPrice($art['pret_unitar']); ?></td>
                    <td>
                        <button onclick='editArticol(<?php echo json_encode($art); ?>)' 
                                class="btn btn-sm" title="Editeaza">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteArticol(<?php echo $art['id']; ?>)" 
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

<!-- Modal Adauga/Editeaza Articol -->
<div id="articolModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-plus"></i> Articol Nou</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        
        <form id="articolForm">
            <input type="hidden" id="articol_id" name="id">
            
            <div class="form-group">
                <label for="cod">Cod Articol</label>
                <input type="text" id="cod" name="cod" required>
            </div>
            
            <div class="form-group">
                <label for="denumire">Denumire</label>
                <input type="text" id="denumire" name="denumire" required>
            </div>
            
            <div class="form-group">
                <label for="um">Unitate Masura</label>
                <select id="um" name="um" required>
                    <option value="">-- Selecteaza UM --</option>
                    <?php foreach ($unitatiMasura as $um): ?>
                        <option value="<?php echo $um['id']; ?>">
                            <?php echo clean($um['simbol']); ?> - <?php echo clean($um['denumire']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="pret_unitar">Pret Unitar (EUR)</label>
                <input type="number" id="pret_unitar" name="pret_unitar" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="descriere">Descriere (optional)</label>
                <textarea id="descriere" name="descriere" rows="3"></textarea>
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
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Articol Nou';
    document.getElementById('articolForm').reset();
    document.getElementById('articol_id').value = '';
    document.getElementById('articolModal').classList.add('active');
}

function closeModal() {
    document.getElementById('articolModal').classList.remove('active');
}

function editArticol(articol) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editeaza Articol';
    document.getElementById('articol_id').value = articol.id;
    document.getElementById('cod').value = articol.cod;
    document.getElementById('denumire').value = articol.denumire;
    document.getElementById('um').value = articol.um;
    document.getElementById('pret_unitar').value = articol.pret_unitar;
    document.getElementById('descriere').value = articol.descriere || '';
    document.getElementById('articolModal').classList.add('active');
}

document.getElementById('articolForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    const method = data.id ? 'PUT' : 'POST';
    
    fetch('<?php echo SITE_URL; ?>/api/articole.php', {
        method: method,
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

function deleteArticol(id) {
    if (!confirm('Sigur doriti sa stergeti acest articol?')) {
        return;
    }
    
    fetch('<?php echo SITE_URL; ?>/api/articole.php', {
        method: 'DELETE',
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
document.getElementById('articolModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
