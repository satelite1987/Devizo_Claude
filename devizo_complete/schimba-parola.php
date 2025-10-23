<?php
/**
 * DEVIZO - Schimbare Parola
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$pageTitle = 'Schimba Parola';

$errors = [];
$success = false;

// Procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errors[] = 'Toate campurile sunt obligatorii.';
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Parolele noi nu coincid.';
    }
    
    if (empty($errors)) {
        $result = changePassword($_SESSION['user_id'], $oldPassword, $newPassword);
        
        if ($result['success']) {
            $success = true;
            setFlashMessage('Parola a fost schimbata cu succes!', 'success');
        } else {
            $errors[] = $result['message'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
.password-container {
    max-width: 600px;
    margin: 0 auto;
}

.password-card {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.password-card h2 {
    color: var(--secondary);
    margin-bottom: 10px;
}

.password-card p {
    color: #666;
    margin-bottom: 30px;
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

.password-input-wrapper {
    position: relative;
}

.form-control {
    width: 100%;
    padding: 10px;
    padding-right: 40px;
    border: 1px solid var(--gray);
    border-radius: 4px;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
}

.password-requirements {
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.password-requirements h4 {
    color: var(--secondary);
    margin-bottom: 10px;
    font-size: 14px;
}

.password-requirements ul {
    margin: 0;
    padding-left: 20px;
    color: #666;
    font-size: 14px;
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
</style>

<div class="password-container">
    <div class="password-card">
        <h2><i class="fas fa-key"></i> Schimba Parola</h2>
        <p>Pentru securitatea contului, va recomandam sa folositi o parola puternica.</p>
        
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
            <i class="fas fa-check-circle"></i> Parola a fost schimbata cu succes!
        </div>
        <?php endif; ?>
        
        <div class="password-requirements">
            <h4><i class="fas fa-info-circle"></i> Cerinte parola:</h4>
            <ul>
                <li>Minim <?php echo PASSWORD_MIN_LENGTH; ?> caractere</li>
                <li>Recomandam: litere mari, mici, cifre si caractere speciale</li>
                <li>Nu folositi parole evidente sau usor de ghicit</li>
            </ul>
        </div>
        
        <form method="POST" action="" id="passwordForm">
            <div class="form-group">
                <label for="old_password">Parola Veche *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="old_password" name="old_password" 
                           class="form-control" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('old_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="new_password">Parola Noua *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new_password" name="new_password" 
                           class="form-control" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                    <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirma Parola Noua *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="form-control" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="<?php echo SITE_URL; ?>/profil.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Inapoi la Profil
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Schimba Parola
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validare parole identice
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        e.preventDefault();
        alert('Parolele noi nu coincid!');
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
