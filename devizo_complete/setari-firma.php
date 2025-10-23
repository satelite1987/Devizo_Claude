<?php
/**
 * DEVIZO - Setari Firma
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

if (isSuperAdmin()) {
    redirect(SITE_URL . '/admin/index.php');
}

// Doar Master poate modifica setarile
if (!isMasterFirma()) {
    setFlashMessage('Doar administratorul firmei poate modifica setarile.', 'error');
    redirect(SITE_URL . '/index.php');
}

$pageTitle = 'Setari Firma';

$db = getDB();
$firmaId = $_SESSION['firma_id'];
$errors = [];
$success = false;

// Procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $denumire = clean($_POST['denumire'] ?? '');
    $cui = clean($_POST['cui'] ?? '');
    $nrRegCom = clean($_POST['nr_reg_com'] ?? '');
    $adresa = clean($_POST['adresa'] ?? '');
    $telefon = clean($_POST['telefon'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $iban = clean($_POST['iban'] ?? '');
    $banca = clean($_POST['banca'] ?? '');
    $culoarePrimara = clean($_POST['culoare_primara'] ?? '#3498db');
    $procentManopera = (float)($_POST['procent_manopera'] ?? 0);
    
    if (empty($denumire)) {
        $errors[] = 'Denumirea este obligatorie.';
    }
    
    if (empty($cui)) {
        $errors[] = 'CUI-ul este obligatoriu.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE firme 
                SET denumire = ?, cui = ?, nr_reg_com = ?, adresa = ?, 
                    telefon = ?, email = ?, iban = ?, banca = ?,
                    culoare_primara = ?, procent_manopera = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $denumire, $cui, $nrRegCom, $adresa, $telefon, $email,
                $iban, $banca, $culoarePrimara, $procentManopera, $firmaId
            ]);
            
            $_SESSION['firma_denumire'] = $denumire;
            $_SESSION['culoare_primara'] = $culoarePrimara;
            
            logActivity($_SESSION['user_id'], 'update_firma_settings', 'Actualizare setari firma');
            
            $success = true;
            setFlashMessage('Setarile firmei au fost actualizate cu succes!', 'success');
            
        } catch (PDOException $e) {
            logError("Eroare setari firma: " . $e->getMessage());
            $errors[] = 'Eroare la actualizarea setarilor.';
        }
    }
}

$firma = getCurrentFirma();

require_once __DIR__ . '/includes/header.php';
?>

<style>
.settings-container {
    max-width: 900px;
    margin: 0 auto;
}

.settings-card {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.settings-card h2 {
    color: var(--secondary);
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--primary);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
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

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--gray);
    border-radius: 4px;
}

.color-preview {
    width: 50px;
    height: 50px;
    border-radius: 4px;
    border: 2px solid var(--gray);
    cursor: pointer;
    margin-top: 5px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.info-box {
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
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

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="settings-container">
    <h1><i class="fas fa-building"></i> Setari Firma</h1>
    
    <div class="settings-card">
        <h2><i class="fas fa-info-circle"></i> Informatii Firma</h2>
        
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
        
        <div class="info-box">
            <strong><i class="fas fa-calendar-alt"></i> Abonament:</strong>
            Activ pana la <strong><?php echo formatDateRO($firma['data_expirare_abonament']); ?></strong>
            <br>
            <strong><i class="fas fa-users"></i> Utilizatori permisi:</strong> 
            <strong><?php echo $firma['max_utilizatori']; ?></strong>
        </div>
        
        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="denumire">Denumire Firma *</label>
                    <input type="text" id="denumire" name="denumire" class="form-control"
                           value="<?php echo clean($firma['denumire']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="cui">CUI *</label>
                    <input type="text" id="cui" name="cui" class="form-control"
                           value="<?php echo clean($firma['cui']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nr_reg_com">Nr. Reg. Com.</label>
                    <input type="text" id="nr_reg_com" name="nr_reg_com" class="form-control"
                           value="<?php echo clean($firma['nr_reg_com'] ?? ''); ?>">
                </div>
                
                <div class="form-group full-width">
                    <label for="adresa">Adresa</label>
                    <textarea id="adresa" name="adresa" class="form-control" rows="2"><?php echo clean($firma['adresa'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="telefon">Telefon</label>
                    <input type="text" id="telefon" name="telefon" class="form-control"
                           value="<?php echo clean($firma['telefon'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?php echo clean($firma['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="iban">IBAN</label>
                    <input type="text" id="iban" name="iban" class="form-control"
                           value="<?php echo clean($firma['iban'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="banca">Banca</label>
                    <input type="text" id="banca" name="banca" class="form-control"
                           value="<?php echo clean($firma['banca'] ?? ''); ?>">
                </div>
            </div>
            
            <h3 style="margin: 30px 0 15px; color: var(--secondary);">
                <i class="fas fa-cog"></i> Setari Personalizare
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="culoare_primara">Culoare Primara</label>
                    <input type="color" id="culoare_primara" name="culoare_primara" class="form-control"
                           value="<?php echo clean($firma['culoare_primara'] ?? '#3498db'); ?>"
                           style="height: 50px; cursor: pointer;">
                </div>
                
                <div class="form-group">
                    <label for="procent_manopera">Procent Manopera Implicit (%)</label>
                    <input type="number" id="procent_manopera" name="procent_manopera" class="form-control"
                           value="<?php echo $firma['procent_manopera'] ?? 0; ?>"
                           min="0" max="100" step="0.01">
                    <small>Acest procent se va aplica automat la devizele noi</small>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Inapoi
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Salveaza Modificarile
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
