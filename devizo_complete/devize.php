<?php
/**
 * DEVIZO - Lista Devize
 * Pagina pentru vizualizarea si gestionarea devizelor
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Verificare autentificare INAINTE de header
requireLogin();

// Super Admin nu are acces la devize
if (isSuperAdmin()) {
    redirect(SITE_URL . '/admin/index.php');
}

$pageTitle = 'Devize';

// Parametri filtrare si paginare
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$search = isset($_GET['search']) ? clean($_GET['search']) : '';
$status = isset($_GET['status']) ? clean($_GET['status']) : '';
$partener = isset($_GET['partener']) ? (int)$_GET['partener'] : 0;

// Construire query
$db = getDB();
$where = ['d.firma_id = :firma_id'];
$params = [':firma_id' => getCurrentUser()['firma_id']];

if ($search) {
    $where[] = '(d.numar_deviz LIKE :search OR p.denumire LIKE :search)';
    $params[':search'] = "%$search%";
}

if ($status) {
    $where[] = 'd.status = :status';
    $params[':status'] = $status;
}

if ($partener) {
    $where[] = 'd.partener_id = :partener';
    $params[':partener'] = $partener;
}

$whereClause = implode(' AND ', $where);

// Total inregistrari
$stmtCount = $db->prepare("SELECT COUNT(*) as total FROM devize d LEFT JOIN parteneri p ON d.partener_id = p.id WHERE $whereClause");
$stmtCount->execute($params);
$totalRecords = $stmtCount->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Selectare devize
$params[':limit'] = $perPage;
$params[':offset'] = $offset;

$stmt = $db->prepare("
    SELECT
        d.*,
        p.denumire as partener_nume,
        u.nume as utilizator_nume,
        (d.total_materiale + d.total_manopera) as total
    FROM devize d
    LEFT JOIN parteneri p ON d.partener_id = p.id
    LEFT JOIN utilizatori u ON d.utilizator_id = u.id
    WHERE $whereClause
    ORDER BY d.created_at DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}

$stmt->execute();
$devize = $stmt->fetchAll();

// Lista parteneri pentru filtru
$stmtParteneri = $db->prepare("SELECT id, denumire FROM parteneri WHERE firma_id = ? ORDER BY denumire");
$stmtParteneri->execute([getCurrentUser()['firma_id']]);
$parteneri = $stmtParteneri->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-file-invoice"></i> Devize</h1>
    <a href="<?php echo SITE_URL; ?>/deviz-nou.php" class="btn btn-success">
        <i class="fas fa-plus"></i> Deviz Nou
    </a>
</div>

<!-- Filtre -->
<div class="card mb-20">
    <form method="get" class="filter-form">
        <div class="form-row">
            <div class="form-group">
                <input type="text" name="search" class="form-control" placeholder="Cauta dupa numar sau partener..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group">
                <select name="status" class="form-control">
                    <option value="">Toate statusurile</option>
                    <option value="In asteptare" <?php echo $status == 'In asteptare' ? 'selected' : ''; ?>>In asteptare</option>
                    <option value="Acceptat" <?php echo $status == 'Acceptat' ? 'selected' : ''; ?>>Acceptat</option>
                    <option value="In lucru" <?php echo $status == 'In lucru' ? 'selected' : ''; ?>>In lucru</option>
                    <option value="Finalizat" <?php echo $status == 'Finalizat' ? 'selected' : ''; ?>>Finalizat</option>
                    <option value="Anulat" <?php echo $status == 'Anulat' ? 'selected' : ''; ?>>Anulat</option>
                </select>
            </div>
            <div class="form-group">
                <select name="partener" class="form-control">
                    <option value="0">Toti partenerii</option>
                    <?php foreach ($parteneri as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo $partener == $p['id'] ? 'selected' : ''; ?>>
                            <?php echo clean($p['denumire']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtreaza
                </button>
                <a href="<?php echo SITE_URL; ?>/devize.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reseteaza
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Lista Devize -->
<div class="card">
    <?php if (count($devize) > 0): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Numar Deviz</th>
                        <th>Partener</th>
                        <th>Status</th>
                        <th>Total Materiale</th>
                        <th>Total Manopera</th>
                        <th>Total General</th>
                        <th>Data Creare</th>
                        <th>Actiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devize as $deviz): ?>
                        <tr>
                            <td>
                                <strong><?php echo clean($deviz['numar_deviz']); ?></strong>
                            </td>
                            <td><?php echo clean($deviz['partener_nume']); ?></td>
                            <td>
                                <span class="badge badge-<?php
                                    echo $deviz['status'] == 'In asteptare' ? 'warning' :
                                        ($deviz['status'] == 'Acceptat' ? 'info' :
                                        ($deviz['status'] == 'In lucru' ? 'primary' :
                                        ($deviz['status'] == 'Finalizat' ? 'success' : 'danger')));
                                ?>">
                                    <?php echo clean($deviz['status']); ?>
                                </span>
                            </td>
                            <td class="text-right"><?php echo formatPrice($deviz['total_materiale']); ?> RON</td>
                            <td class="text-right"><?php echo formatPrice($deviz['total_manopera']); ?> RON</td>
                            <td class="text-right"><strong><?php echo formatPrice($deviz['total']); ?> RON</strong></td>
                            <td><?php echo formatDateRO($deviz['created_at']); ?></td>
                            <td class="actions">
                                <a href="<?php echo SITE_URL; ?>/deviz.php?id=<?php echo $deviz['id']; ?>" class="btn-icon" title="Vizualizeaza">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/deviz.php?id=<?php echo $deviz['id']; ?>&edit=1" class="btn-icon" title="Editeaza">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/api/devize.php?action=pdf&id=<?php echo $deviz['id']; ?>" class="btn-icon" title="Export PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <button onclick="shareDeviz('<?php echo $deviz['link_hash']; ?>')" class="btn-icon" title="Partajeaza">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                                <button onclick="deleteDeviz(<?php echo $deviz['id']; ?>)" class="btn-icon btn-danger" title="Sterge">
                                    <i class="fas fa-trash"></i>
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
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?><?php echo $partener ? '&partener=' . $partener : ''; ?>" class="btn btn-secondary">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>

                <span class="page-info">
                    Pagina <?php echo $page; ?> din <?php echo $totalPages; ?>
                    (<?php echo $totalRecords; ?> devize)
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?><?php echo $partener ? '&partener=' . $partener : ''; ?>" class="btn btn-secondary">
                        Urmator <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-file-invoice fa-3x"></i>
            <h3>Niciun deviz gasit</h3>
            <p>
                <?php if ($search || $status || $partener): ?>
                    Nu exista devize care sa corespunda criteriilor de filtrare.
                    <br>
                    <a href="<?php echo SITE_URL; ?>/devize.php" class="btn btn-secondary mt-10">
                        <i class="fas fa-redo"></i> Reseteaza filtrele
                    </a>
                <?php else: ?>
                    Incepe prin a crea primul tau deviz!
                    <br>
                    <a href="<?php echo SITE_URL; ?>/deviz-nou.php" class="btn btn-success mt-10">
                        <i class="fas fa-plus"></i> Creeaza Deviz Nou
                    </a>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
function shareDeviz(hash) {
    const url = '<?php echo SITE_URL; ?>/public/deviz/' + hash;
    prompt('Link partajabil pentru deviz:', url);
}

function deleteDeviz(id) {
    if (!confirm('Sigur doresti sa stergi acest deviz? Actiunea este ireversibila!')) {
        return;
    }

    fetch('<?php echo SITE_URL; ?>/api/devize.php?action=delete&id=' + id, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Devizul a fost sters cu succes!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(data.message || 'Eroare la stergerea devizului!', 'error');
        }
    })
    .catch(error => {
        showMessage('Eroare de comunicare cu serverul!', 'error');
    });
}
</script>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.filter-form .form-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1.5fr auto;
    gap: 10px;
    align-items: center;
}

.mb-20 {
    margin-bottom: 20px;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.data-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.data-table tbody tr:hover {
    background: #f8f9fa;
}

.text-right {
    text-align: right;
}

.actions {
    white-space: nowrap;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    background: #3498db;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 5px;
    text-decoration: none;
    transition: background 0.3s;
}

.btn-icon:hover {
    background: #2980b9;
}

.btn-icon.btn-danger {
    background: #e74c3c;
}

.btn-icon.btn-danger:hover {
    background: #c0392b;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

.badge-primary {
    background: #cce5ff;
    color: #004085;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-top: 1px solid #ddd;
}

.page-info {
    color: #666;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #666;
    margin-bottom: 10px;
}

.mt-10 {
    margin-top: 10px;
}

@media (max-width: 768px) {
    .filter-form .form-row {
        grid-template-columns: 1fr;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
