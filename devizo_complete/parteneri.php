<?php
/**
 * DEVIZO - Gestionare Parteneri
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

if (isSuperAdmin()) {
    redirect(SITE_URL . '/admin/index.php');
}

$pageTitle = 'Parteneri';

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
    $where[] = '(denumire LIKE ? OR cui LIKE ? OR email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

// Total parteneri
$stmtCount = $db->prepare("SELECT COUNT(*) as total FROM parteneri WHERE $whereClause");
$stmtCount->execute($params);
$totalParteneri = $stmtCount->fetch()['total'];
$totalPages = ceil($totalParteneri / $perPage);

// Obtinere parteneri
$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT * FROM parteneri
    WHERE $whereClause
    ORDER BY denumire
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$parteneri = $stmt->fetchAll();

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

.parteneri-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.partener-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.partener-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.partener-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--primary);
}

.partener-header h3 {
    margin: 0;
    color: var(--secondary);
    font-size: 18px;
}

.partener-actions {
    display: flex;
    gap: 5px;
}

.partener-info p {
    margin: 8px 0;
    color: #666;
    font-size: 14px;
}

.partener-info strong {
    color: var(--dark);
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
    max-width: 700px;
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

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--secondary);
}

.form-group input,
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

.btn-anaf {
    margin-top: 5px;
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
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
}
</style>

<div class="page-header">
    <h1><i class="fas fa-address-book"></i> Parteneri</h1>
    <button onclick="openModal()" class="btn btn-success">
        <i class="fas fa-plus"></i> Partener Nou
    </button>
</div>

<div class="search-box">
    <form method="GET">
        <input type="text" name="search" placeholder="Cauta partener..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn">
            <i class="fas fa-search"></i> Cauta
        </button>
        <?php if ($search): ?>
            <a href="parteneri.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Reseteaza
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if (empty($parteneri)): ?>
    <div class="empty-state">
        <i class="fas fa-address-book"></i>
        <h3>Nu au fost gasiti parteneri</h3>
        <p>Adaugati primul partener pentru a incepe!</p>
        <button onclick="openModal()" class="btn" style="margin-top: 15px;">
            <i class="fas fa-plus"></i> Partener Nou
        </button>
    </div>
<?php else: ?>
    <div class="parteneri-grid">
        <?php foreach ($parteneri as $p): ?>
        <div class="partener-card">
            <div class="partener-header">
                <h3><?php echo clean($p['denumire']); ?></h3>
                <div class="partener-actions">
                    <button onclick='editPartener(<?php echo json_encode($p); ?>)' 
                            class="btn btn-sm" title="Editeaza">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deletePartener(<?php echo $p['id']; ?>)" 
                            class="btn btn-sm btn-danger" title="Sterge">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="partener-info">
                <?php if ($p['cui']): ?>
                    <p><strong>CUI:</strong> <?php echo clean($p['cui']); ?></p>
                <?php endif; ?>
                <?php if ($p['nr_reg_com']): ?>
                    <p><strong>Nr. Reg. Com.:</strong> <?php echo clean($p['nr_reg_com']); ?></p>
                <?php endif; ?>
                <?php if ($p['adresa']): ?>
                    <p><strong>Adresa:</strong> <?php echo clean($p['adresa']); ?></p>
                <?php endif; ?>
                <?php if ($p['telefon']): ?>
                    <p><strong>Telefon:</strong> <?php echo clean($p['telefon']); ?></p>
                <?php endif; ?>
                <?php if ($p['email']): ?>
                    <p><strong>Email:</strong> <?php echo clean($p['email']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

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

<!-- Modal Adauga/Editeaza Partener -->
<div id="partenerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-plus"></i> Partener Nou</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        
        <form id="partenerForm">
            <input type="hidden" id="partener_id" name="id">
            
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="denumire">Denumire *</label>
                    <input type="text" id="denumire" name="denumire" required>
                </div>
                
                <div class="form-group">
                    <label for="cui">CUI</label>
                    <input type="text" id="cui" name="cui">
                    <button type="button" class="btn btn-sm btn-anaf" onclick="cautaANAF()">
                        <i class="fas fa-search"></i> Cauta in ANAF
                    </button>
                </div>
                
                <div class="form-group">
                    <label for="nr_reg_com">Nr. Reg. Com.</label>
                    <input type="text" id="nr_reg_com" name="nr_reg_com">
                </div>
                
                <div class="form-group full-width">
                    <label for="adresa">Adresa</label>
                    <textarea id="adresa" name="adresa" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="telefon">Telefon</label>
                    <input type="text" id="telefon" name="telefon">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                
                <div class="form-group">
                    <label for="iban">IBAN</label>
                    <input type="text" id="iban" name="iban">
                </div>
                
                <div class="form-group">
                    <label for="banca">Banca</label>
                    <input type="text" id="banca" name="banca">
                </div>
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
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Partener Nou';
    document.getElementById('partenerForm').reset();
    document.getElementById('partener_id').value = '';
    document.getElementById('partenerModal').classList.add('active');
}

function closeModal() {
    document.getElementById('partenerModal').classList.remove('active');
}

function editPartener(partener) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editeaza Partener';
    document.getElementById('partener_id').value = partener.id;
    document.getElementById('denumire').value = partener.denumire;
    document.getElementById('cui').value = partener.cui || '';
    document.getElementById('nr_reg_com').value = partener.nr_reg_com || '';
    document.getElementById('adresa').value = partener.adresa || '';
    document.getElementById('telefon').value = partener.telefon || '';
    document.getElementById('email').value = partener.email || '';
    document.getElementById('iban').value = partener.iban || '';
    document.getElementById('banca').value = partener.banca || '';
    document.getElementById('partenerModal').classList.add('active');
}

document.getElementById('partenerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    const method = data.id ? 'PUT' : 'POST';
    
    fetch('<?php echo SITE_URL; ?>/api/parteneri.php', {
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

function deletePartener(id) {
    if (!confirm('Sigur doriti sa stergeti acest partener?')) {
        return;
    }
    
    fetch('<?php echo SITE_URL; ?>/api/parteneri.php', {
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

function cautaANAF() {
    const cui = document.getElementById('cui').value;
    if (!cui) {
        alert('Introduceti CUI-ul mai intai!');
        return;
    }
    
    fetch('<?php echo SITE_URL; ?>/api/anaf.php?cui=' + cui)
    .then(response => response.json())
    .then(result => {
        if (result.success && result.data) {
            document.getElementById('denumire').value = result.data.denumire || '';
            document.getElementById('adresa').value = result.data.adresa || '';
            document.getElementById('telefon').value = result.data.telefon || '';
        } else {
            alert(result.message || 'Nu s-au gasit informatii pentru acest CUI.');
        }
    })
    .catch(error => {
        alert('Eroare la comunicarea cu ANAF: ' + error);
    });
}

// Close modal on outside click
document.getElementById('partenerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
