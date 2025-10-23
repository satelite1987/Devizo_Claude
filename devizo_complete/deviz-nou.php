<?php
/**
 * DEVIZO - Creare Deviz Nou
 * 
 * Pagina pentru crearea unui deviz nou cu selectare articole
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

if (isSuperAdmin()) {
    redirect(SITE_URL . '/admin/index.php');
}

$pageTitle = 'Deviz Nou';

$db = getDB();
$firmaId = $_SESSION['firma_id'];
$errors = [];
$deviz = null;

// Verificare duplicare deviz
$duplicateId = isset($_GET['duplicate']) ? (int)$_GET['duplicate'] : 0;
if ($duplicateId > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM devize WHERE id = ? AND firma_id = ?");
        $stmt->execute([$duplicateId, $firmaId]);
        $deviz = $stmt->fetch();
        
        if ($deviz) {
            // Generam numar nou pentru deviz
            $stmt = $db->prepare("SELECT MAX(CAST(SUBSTRING(numar_deviz, 4) AS UNSIGNED)) as max_nr FROM devize WHERE firma_id = ? AND numar_deviz LIKE 'DV-%'");
            $stmt->execute([$firmaId]);
            $result = $stmt->fetch();
            $nextNr = ($result['max_nr'] ?? 0) + 1;
            $deviz['numar_deviz'] = 'DV-' . str_pad($nextNr, 6, '0', STR_PAD_LEFT);
            $deviz['data_deviz'] = date('Y-m-d');
            $deviz['status'] = 'In asteptare';
        }
    } catch (PDOException $e) {
        logError("Eroare duplicare deviz: " . $e->getMessage());
    }
}

// Procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numarDeviz = clean($_POST['numar_deviz'] ?? '');
    $dataDeviz = clean($_POST['data_deviz'] ?? '');
    $partenerId = (int)($_POST['partener_id'] ?? 0);
    $observatii = clean($_POST['observatii'] ?? '');
    $procentManopera = (float)($_POST['procent_manopera'] ?? 0);
    $articole = $_POST['articole'] ?? [];
    
    // Validari
    if (empty($numarDeviz)) {
        $errors[] = 'Numarul devizului este obligatoriu.';
    }
    
    if (empty($dataDeviz)) {
        $errors[] = 'Data devizului este obligatorie.';
    }
    
    if ($partenerId <= 0) {
        $errors[] = 'Partenerul este obligatoriu.';
    }
    
    if (empty($articole)) {
        $errors[] = 'Trebuie sa adaugati cel putin un articol.';
    }
    
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Calcul totaluri
            $totalMateriale = 0;
            $totalManopera = 0;
            
            foreach ($articole as $art) {
                $cantitate = (float)$art['cantitate'];
                $pretUnitar = (float)$art['pret_unitar'];
                $totalArticol = $cantitate * $pretUnitar;
                $totalMateriale += $totalArticol;
            }
            
            $totalManopera = $totalMateriale * ($procentManopera / 100);
            $totalGeneral = $totalMateriale + $totalManopera;
            
            // Generare hash unic pentru link partajare
            $linkHash = generateUniqueHash('deviz_');
            
            // Inserare deviz
            $stmt = $db->prepare("
                INSERT INTO devize (
                    firma_id, utilizator_id, partener_id, numar_deviz, data_deviz,
                    total_materiale, total_manopera, procent_manopera, total_general,
                    observatii, status, link_hash
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'In asteptare', ?)
            ");
            
            $stmt->execute([
                $firmaId,
                $_SESSION['user_id'],
                $partenerId,
                $numarDeviz,
                $dataDeviz,
                $totalMateriale,
                $totalManopera,
                $procentManopera,
                $totalGeneral,
                $observatii,
                $linkHash
            ]);
            
            $devizId = $db->lastInsertId();
            
            // Inserare articole deviz
            $stmt = $db->prepare("
                INSERT INTO deviz_articole (
                    deviz_id, articol_id, denumire, um, cantitate, pret_unitar, total
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($articole as $art) {
                $cantitate = (float)$art['cantitate'];
                $pretUnitar = (float)$art['pret_unitar'];
                $total = $cantitate * $pretUnitar;
                
                $stmt->execute([
                    $devizId,
                    (int)$art['articol_id'],
                    $art['denumire'],
                    $art['um'],
                    $cantitate,
                    $pretUnitar,
                    $total
                ]);
            }
            
            $db->commit();
            
            logActivity($_SESSION['user_id'], 'create_deviz', "Deviz nou creat: $numarDeviz");
            
            setFlashMessage('Devizul a fost creat cu succes!', 'success');
            redirect(SITE_URL . '/deviz.php?id=' . $devizId);
            
        } catch (PDOException $e) {
            $db->rollBack();
            logError("Eroare creare deviz: " . $e->getMessage());
            $errors[] = 'Eroare la salvarea devizului. Va rugam incercati din nou.';
        }
    }
}

// Obtinere parteneri
$stmt = $db->prepare("SELECT * FROM parteneri WHERE firma_id = ? ORDER BY denumire");
$stmt->execute([$firmaId]);
$parteneri = $stmt->fetchAll();

// Obtinere articole
$stmt = $db->prepare("SELECT * FROM articole WHERE firma_id = ? ORDER BY denumire");
$stmt->execute([$firmaId]);
$articole = $stmt->fetchAll();

// Obtinere unitati masura
$stmt = $db->prepare("SELECT * FROM unitati_masura WHERE firma_id = ? OR firma_id IS NULL ORDER BY simbol");
$stmt->execute([$firmaId]);
$unitatiMasura = $stmt->fetchAll();

// Obtinere setari firma pentru procent manopera implicit
$firma = getCurrentFirma();
$procentManoperaImplicit = $firma['procent_manopera'] ?? 0;

// Generam numar deviz automat daca nu e duplicat
if (!$deviz) {
    $stmt = $db->prepare("SELECT MAX(CAST(SUBSTRING(numar_deviz, 4) AS UNSIGNED)) as max_nr FROM devize WHERE firma_id = ? AND numar_deviz LIKE 'DV-%'");
    $stmt->execute([$firmaId]);
    $result = $stmt->fetch();
    $nextNr = ($result['max_nr'] ?? 0) + 1;
    $numarDevizGenerat = 'DV-' . str_pad($nextNr, 6, '0', STR_PAD_LEFT);
} else {
    $numarDevizGenerat = $deviz['numar_deviz'];
}

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

.form-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--secondary);
}

.form-group label.required::after {
    content: '*';
    color: var(--danger);
    margin-left: 3px;
}

.form-control {
    padding: 10px;
    border: 1px solid var(--gray);
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
}

textarea.form-control {
    min-height: 80px;
    resize: vertical;
}

.error-list {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.error-list ul {
    margin: 0;
    padding-left: 20px;
}

.articole-section {
    margin-top: 30px;
}

.articole-section h3 {
    color: var(--secondary);
    margin-bottom: 15px;
}

.articol-row {
    display: grid;
    grid-template-columns: 3fr 1fr 1fr 1fr 1fr auto;
    gap: 10px;
    align-items: end;
    margin-bottom: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.btn-remove {
    background: var(--danger);
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
}

.totals-section {
    background: var(--light);
    padding: 20px;
    border-radius: 4px;
    margin-top: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--gray);
}

.total-row:last-child {
    border-bottom: none;
    font-weight: bold;
    font-size: 18px;
    color: var(--primary);
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}
</style>

<div class="page-header">
    <h1><i class="fas fa-plus-circle"></i> Deviz Nou</h1>
    <a href="<?php echo SITE_URL; ?>/devize.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Inapoi la Lista
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="error-list">
    <strong><i class="fas fa-exclamation-circle"></i> Erori:</strong>
    <ul>
        <?php foreach ($errors as $error): ?>
            <li><?php echo clean($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" action="" id="devizForm">
    <div class="form-card">
        <h3><i class="fas fa-info-circle"></i> Informatii Generale</h3>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="numar_deviz" class="required">Numar Deviz</label>
                <input type="text" id="numar_deviz" name="numar_deviz" class="form-control"
                       value="<?php echo htmlspecialchars($numarDevizGenerat); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="data_deviz" class="required">Data Deviz</label>
                <input type="date" id="data_deviz" name="data_deviz" class="form-control"
                       value="<?php echo $deviz['data_deviz'] ?? date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="partener_id" class="required">Partener</label>
                <select id="partener_id" name="partener_id" class="form-control" required>
                    <option value="">-- Selecteaza Partener --</option>
                    <?php foreach ($parteneri as $partener): ?>
                        <option value="<?php echo $partener['id']; ?>"
                                <?php echo (isset($deviz) && $deviz['partener_id'] == $partener['id']) ? 'selected' : ''; ?>>
                            <?php echo clean($partener['denumire']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small><a href="<?php echo SITE_URL; ?>/parteneri.php?action=new" target="_blank">+ Adauga Partener Nou</a></small>
            </div>
            
            <div class="form-group">
                <label for="procent_manopera">Procent Manopera (%)</label>
                <input type="number" id="procent_manopera" name="procent_manopera" class="form-control"
                       value="<?php echo $deviz['procent_manopera'] ?? $procentManoperaImplicit; ?>"
                       min="0" max="100" step="0.01">
            </div>
        </div>
        
        <div class="form-group">
            <label for="observatii">Observatii</label>
            <textarea id="observatii" name="observatii" class="form-control"><?php echo $deviz['observatii'] ?? ''; ?></textarea>
        </div>
    </div>
    
    <div class="form-card articole-section">
        <h3><i class="fas fa-boxes"></i> Articole</h3>
        
        <div id="articoleContainer"></div>
        
        <button type="button" class="btn btn-secondary" onclick="adaugaArticol()">
            <i class="fas fa-plus"></i> Adauga Articol
        </button>
        
        <div class="totals-section">
            <div class="total-row">
                <span>Total Materiale:</span>
                <strong id="totalMateriale">0.00 EUR</strong>
            </div>
            <div class="total-row">
                <span>Total Manopera:</span>
                <strong id="totalManopera">0.00 EUR</strong>
            </div>
            <div class="total-row">
                <span>TOTAL GENERAL:</span>
                <strong id="totalGeneral">0.00 EUR</strong>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <a href="<?php echo SITE_URL; ?>/devize.php" class="btn btn-secondary">
            <i class="fas fa-times"></i> Anuleaza
        </a>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Salveaza Deviz
        </button>
    </div>
</form>

<script>
// Date articole pentru JavaScript
const articoleData = <?php echo json_encode($articole); ?>;
const unitatiData = <?php echo json_encode($unitatiMasura); ?>;
let articolIndex = 0;

function adaugaArticol(selectedArticolId = null, cantitate = 1, pretUnitar = null) {
    const container = document.getElementById('articoleContainer');
    const row = document.createElement('div');
    row.className = 'articol-row';
    row.id = 'articol_' + articolIndex;
    
    const articolSelect = document.createElement('select');
    articolSelect.name = 'articole[' + articolIndex + '][articol_id]';
    articolSelect.className = 'form-control';
    articolSelect.required = true;
    articolSelect.onchange = function() { selectArticol(articolIndex); };
    
    articolSelect.innerHTML = '<option value="">-- Selecteaza Articol --</option>';
    articoleData.forEach(art => {
        const option = document.createElement('option');
        option.value = art.id;
        option.textContent = art.denumire;
        option.dataset.um = art.um;
        option.dataset.pret = art.pret_unitar;
        if (selectedArticolId && art.id == selectedArticolId) {
            option.selected = true;
        }
        articolSelect.appendChild(option);
    });
    
    row.innerHTML = `
        <div>
            <label>Articol</label>
        </div>
        <div>
            <label>UM</label>
            <input type="text" name="articole[${articolIndex}][um]" class="form-control" id="um_${articolIndex}" readonly>
            <input type="hidden" name="articole[${articolIndex}][denumire]" id="denumire_${articolIndex}">
        </div>
        <div>
            <label>Cantitate</label>
            <input type="number" name="articole[${articolIndex}][cantitate]" class="form-control" 
                   id="cant_${articolIndex}" value="${cantitate}" min="0.01" step="0.01" required
                   onchange="calculeazaTotaluri()">
        </div>
        <div>
            <label>Pret Unitar</label>
            <input type="number" name="articole[${articolIndex}][pret_unitar]" class="form-control" 
                   id="pret_${articolIndex}" value="${pretUnitar || 0}" min="0" step="0.01" required
                   onchange="calculeazaTotaluri()">
        </div>
        <div>
            <label>Total</label>
            <input type="text" class="form-control" id="total_${articolIndex}" readonly>
        </div>
        <div>
            <label>&nbsp;</label>
            <button type="button" class="btn-remove" onclick="stergeArticol(${articolIndex})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    row.querySelector('div').appendChild(articolSelect);
    container.appendChild(row);
    
    if (selectedArticolId) {
        selectArticol(articolIndex);
    }
    
    articolIndex++;
    calculeazaTotaluri();
}

function selectArticol(index) {
    const select = document.querySelector(`select[name="articole[${index}][articol_id]"]`);
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        document.getElementById('um_' + index).value = selectedOption.dataset.um || '';
        document.getElementById('pret_' + index).value = selectedOption.dataset.pret || 0;
        document.getElementById('denumire_' + index).value = selectedOption.textContent;
    }
    
    calculeazaTotaluri();
}

function stergeArticol(index) {
    document.getElementById('articol_' + index).remove();
    calculeazaTotaluri();
}

function calculeazaTotaluri() {
    let totalMateriale = 0;
    
    for (let i = 0; i < articolIndex; i++) {
        const cantInput = document.getElementById('cant_' + i);
        const pretInput = document.getElementById('pret_' + i);
        const totalInput = document.getElementById('total_' + i);
        
        if (cantInput && pretInput && totalInput) {
            const cant = parseFloat(cantInput.value) || 0;
            const pret = parseFloat(pretInput.value) || 0;
            const total = cant * pret;
            
            totalInput.value = total.toFixed(2);
            totalMateriale += total;
        }
    }
    
    const procentManopera = parseFloat(document.getElementById('procent_manopera').value) || 0;
    const totalManopera = totalMateriale * (procentManopera / 100);
    const totalGeneral = totalMateriale + totalManopera;
    
    document.getElementById('totalMateriale').textContent = totalMateriale.toFixed(2) + ' EUR';
    document.getElementById('totalManopera').textContent = totalManopera.toFixed(2) + ' EUR';
    document.getElementById('totalGeneral').textContent = totalGeneral.toFixed(2) + ' EUR';
}

// Event listener pentru procent manopera
document.getElementById('procent_manopera').addEventListener('input', calculeazaTotaluri);

// Adaugam primul articol automat
adaugaArticol();

// Validare formular
document.getElementById('devizForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('.articol-row');
    if (rows.length === 0) {
        e.preventDefault();
        alert('Trebuie sa adaugati cel putin un articol!');
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
