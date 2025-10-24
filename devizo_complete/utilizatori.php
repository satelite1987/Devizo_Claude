<?php
/**
 * DEVIZO - Gestionare Utilizatori
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

// Doar Master Firma si Super Admin pot accesa aceasta pagina
if (!isMasterFirma() && !isSuperAdmin()) {
    redirect(SITE_URL . '/index.php?error=access_denied');
}

// Super Admin nu poate vedea utilizatori, doar firme
if (isSuperAdmin()) {
    redirect(SITE_URL . '/admin/index.php');
}

$pageTitle = 'Utilizatori';

$db = getDB();
$firmaId = $_SESSION['firma_id'];

// Obtinere informatii firma pentru verificare limita
$stmt = $db->prepare("SELECT max_utilizatori FROM firme WHERE id = ?");
$stmt->execute([$firmaId]);
$firma = $stmt->fetch();
$maxUtilizatori = $firma['max_utilizatori'];

// Paginare si filtrare
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? clean($_GET['search']) : '';

// Construire query
$where = ['firma_id = ?'];
$params = [$firmaId];

if ($search) {
    $where[] = '(nume LIKE ? OR email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

// Total utilizatori
$stmtCount = $db->prepare("SELECT COUNT(*) as total FROM utilizatori WHERE $whereClause");
$stmtCount->execute($params);
$totalUtilizatori = $stmtCount->fetch()['total'];
$totalPages = ceil($totalUtilizatori / $perPage);

// Obtinere utilizatori
$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT u.*, r.nume as rol_nume
    FROM utilizatori u
    LEFT JOIN roluri r ON u.rol_id = r.id
    WHERE $whereClause
    ORDER BY u.nume
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$utilizatori = $stmt->fetchAll();

// Obtinere roluri disponibile (doar Utilizator Firma - id 3)
$stmt = $db->prepare("SELECT * FROM roluri WHERE id = 3");
$stmt->execute();
$roluri = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.limit-info {
    background: #fff3cd;
    border: 1px solid #ffc107;
    padding: 10px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.limit-info i {
    color: #856404;
}

.limit-info.limit-reached {
    background: #f8d7da;
    border-color: #dc3545;
}

.limit-info.limit-reached i {
    color: #721c24;
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
.form-group select {
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
    <h1><i class="fas fa-users"></i> Utilizatori</h1>
    <?php if ($totalUtilizatori < $maxUtilizatori): ?>
        <button onclick="openModal()" class="btn btn-success">
            <i class="fas fa-plus"></i> Utilizator Nou
        </button>
    <?php endif; ?>
</div>

<div class="limit-info <?php echo $totalUtilizatori >= $maxUtilizatori ? 'limit-reached' : ''; ?>">
    <i class="fas fa-info-circle"></i>
    <div>
        <strong>Utilizatori: <?php echo $totalUtilizatori; ?> / <?php echo $maxUtilizatori; ?></strong>
        <?php if ($totalUtilizatori >= $maxUtilizatori): ?>
            <br>
            <small>Ati atins limita maxima de utilizatori. Contactati administratorul pentru a mari limita.</small>
        <?php endif; ?>
    </div>
</div>

<div class="search-box">
    <form method="GET">
        <input type="text" name="search" placeholder="Cauta utilizator..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn">
            <i class="fas fa-search"></i> Cauta
        </button>
        <?php if ($search): ?>
            <a href="utilizatori.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Reseteaza
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="table-container">
    <?php if (empty($utilizatori)): ?>
        <div class="empty-state">
            <i class="fas fa-user-slash"></i>
            <h3>Nu au fost gasiti utilizatori</h3>
            <p>Adaugati primul utilizator pentru a incepe!</p>
            <?php if ($totalUtilizatori < $maxUtilizatori): ?>
                <button onclick="openModal()" class="btn" style="margin-top: 15px;">
                    <i class="fas fa-plus"></i> Utilizator Nou
                </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nume</th>
                    <th>Email</th>
                    <th>Telefon</th>
                    <th>Rol</th>
                    <th>Status</th>
                    <th>Ultima Autentificare</th>
                    <th>Actiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilizatori as $user): ?>
                <tr>
                    <td><strong><?php echo clean($user['nume']); ?></strong></td>
                    <td><?php echo clean($user['email']); ?></td>
                    <td><?php echo clean($user['telefon'] ?? '-'); ?></td>
                    <td><?php echo clean($user['rol_nume']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $user['activ'] ? 'success' : 'danger'; ?>">
                            <?php echo $user['activ'] ? 'Activ' : 'Inactiv'; ?>
                        </span>
                    </td>
                    <td><?php echo $user['ultima_autentificare'] ? formatDateTimeRO($user['ultima_autentificare']) : 'Niciodata'; ?></td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['user_id']): // Nu poate edita/sterge pe el insusi ?>
                            <button onclick='editUtilizator(<?php echo json_encode($user); ?>)'
                                    class="btn btn-sm" title="Editeaza">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteUtilizator(<?php echo $user['id']; ?>)"
                                    class="btn btn-sm btn-danger" title="Sterge">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php else: ?>
                            <span style="color: #999; font-size: 12px;">Utilizator curent</span>
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

<!-- Modal Adauga/Editeaza Utilizator -->
<div id="utilizatorModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-plus"></i> Utilizator Nou</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>

        <form id="utilizatorForm">
            <input type="hidden" id="utilizator_id" name="id">

            <div class="form-group">
                <label for="nume">Nume Complet <span style="color:red;">*</span></label>
                <input type="text" id="nume" name="nume" required>
            </div>

            <div class="form-group">
                <label for="email">Email <span style="color:red;">*</span></label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="telefon">Telefon</label>
                <input type="tel" id="telefon" name="telefon">
            </div>

            <div class="form-group" id="parolaGroup">
                <label for="parola">Parola <span style="color:red;">*</span></label>
                <input type="password" id="parola" name="parola" minlength="6">
                <small style="color: #666;">Minim 6 caractere</small>
            </div>

            <div class="form-group">
                <label for="rol_id">Rol</label>
                <select id="rol_id" name="rol_id" required>
                    <?php foreach ($roluri as $rol): ?>
                        <option value="<?php echo $rol['id']; ?>">
                            <?php echo clean($rol['nume']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="activ">Status</label>
                <select id="activ" name="activ" required>
                    <option value="1">Activ</option>
                    <option value="0">Inactiv</option>
                </select>
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
let isEditMode = false;

function openModal() {
    isEditMode = false;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Utilizator Nou';
    document.getElementById('utilizatorForm').reset();
    document.getElementById('utilizator_id').value = '';
    document.getElementById('parolaGroup').style.display = 'block';
    document.getElementById('parola').setAttribute('required', 'required');
    document.getElementById('utilizatorModal').classList.add('active');
}

function closeModal() {
    document.getElementById('utilizatorModal').classList.remove('active');
}

function editUtilizator(user) {
    isEditMode = true;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editeaza Utilizator';
    document.getElementById('utilizator_id').value = user.id;
    document.getElementById('nume').value = user.nume;
    document.getElementById('email').value = user.email;
    document.getElementById('telefon').value = user.telefon || '';
    document.getElementById('rol_id').value = user.rol_id;
    document.getElementById('activ').value = user.activ;
    document.getElementById('parolaGroup').style.display = 'none';
    document.getElementById('parola').removeAttribute('required');
    document.getElementById('utilizatorModal').classList.add('active');
}

document.getElementById('utilizatorForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    // Daca suntem in edit mode si parola este goala, o stergem din date
    if (isEditMode && !data.parola) {
        delete data.parola;
    }

    const action = data.id ? 'update' : 'create';

    fetch('<?php echo SITE_URL; ?>/api/utilizatori.php?action=' + action, {
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

function deleteUtilizator(id) {
    if (!confirm('Sigur doriti sa stergeti acest utilizator? Actiunea este ireversibila!')) {
        return;
    }

    fetch('<?php echo SITE_URL; ?>/api/utilizatori.php?action=delete', {
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
document.getElementById('utilizatorModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
