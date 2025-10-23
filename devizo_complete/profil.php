<?php
/**
 * DEVIZO - Profil Utilizator
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$pageTitle = 'Profilul Meu';

$db = getDB();
$errors = [];
$success = false;

// Procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume = clean($_POST['nume'] ?? '');
    $telefon = clean($_POST['telefon'] ?? '');
    
    if (empty($nume)) {
        $errors[] = 'Numele este obligatoriu.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("UPDATE utilizatori SET nume = ?, telefon = ? WHERE id = ?");
            $stmt->execute([$nume, $telefon, $_SESSION['user_id']]);
            
            $_SESSION['nume'] = $nume;
            
            logActivity($_SESSION['user_id'], 'update_profile', 'Actualizare profil');
            
            $success = true;
            setFlashMessage('Profilul a fost actualizat cu succes!', 'success');
            
        } catch (PDOException $e) {
            logError("Eroare actualizare profil: " . $e->getMessage());
            $errors[] = 'Eroare la actualizarea profilului.';
        }
    }
}

$user = getCurrentUser();

require_once __DIR__ . '/includes/header.php';
?>

<style>
.profile-container {
    max-width: 800px;
    margin: 0 auto;
}

.profile-card {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.profile-header {
    text-align: center;
    padding-bottom: 20px;
    margin-bottom: 30px;
    border-bottom: 2px solid var(--primary);
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    margin: 0 auto 15px;
}

.profile-header h2 {
    color: var(--secondary);
    margin-bottom: 5px;
}

.profile-header p {
    color: #666;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.info-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
}

.info-item label {
    display: block;
    font-weight: 600;
    color: var(--secondary);
    margin-bottom: 5px;
    font-size: 14px;
}

.info-item p {
    color: #666;
    margin: 0;
}

.form-group {
    margin-bottom: 20px;
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

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
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

.success-message {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h2><?php echo clean($user['nume']); ?></h2>
            <p><?php echo clean($user['rol_nume']); ?></p>
            <?php if (!isSuperAdmin()): ?>
                <p><small><?php echo clean($user['firma_denumire']); ?></small></p>
            <?php endif; ?>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <label>Email</label>
                <p><?php echo clean($user['email']); ?></p>
            </div>
            
            <div class="info-item">
                <label>Ultima Autentificare</label>
                <p><?php echo $user['ultima_autentificare'] ? formatDateTimeRO($user['ultima_autentificare']) : 'Prima autentificare'; ?></p>
            </div>
            
            <div class="info-item">
                <label>Cont Creat</label>
                <p><?php echo formatDateRO($user['creat_la']); ?></p>
            </div>
            
            <div class="info-item">
                <label>Status</label>
                <p><?php echo $user['activ'] ? '<span style="color: var(--success);">Activ</span>' : '<span style="color: var(--danger);">Inactiv</span>'; ?></p>
            </div>
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
        
        <?php if ($success): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> Profilul a fost actualizat cu succes!
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <h3 style="margin-bottom: 20px; color: var(--secondary);">
                <i class="fas fa-edit"></i> Editeaza Profil
            </h3>
            
            <div class="form-group">
                <label for="nume">Nume Complet *</label>
                <input type="text" id="nume" name="nume" class="form-control"
                       value="<?php echo clean($user['nume']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telefon">Telefon</label>
                <input type="text" id="telefon" name="telefon" class="form-control"
                       value="<?php echo clean($user['telefon'] ?? ''); ?>">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Salveaza Modificarile
                </button>
                <a href="<?php echo SITE_URL; ?>/schimba-parola.php" class="btn btn-secondary">
                    <i class="fas fa-key"></i> Schimba Parola
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
