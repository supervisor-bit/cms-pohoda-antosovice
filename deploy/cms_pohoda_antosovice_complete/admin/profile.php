<?php
require_once '../config.php';
requireLogin();

$success_message = '';
$error_message = '';

// Zpracování změny hesla
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = 'Všechna pole jsou povinná.';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'Nová hesla se neshodují.';
        } elseif (strlen($new_password) < 6) {
            $error_message = 'Nové heslo musí mít alespoň 6 znaků.';
        } else {
            try {
                // Ověření aktuálního hesla
                $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin && password_verify($current_password, $admin['password'])) {
                    // Aktualizace hesla
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['admin_id']]);
                    
                    $success_message = 'Heslo bylo úspěšně změněno.';
                } else {
                    $error_message = 'Aktuální heslo je nesprávné.';
                }
            } catch (Exception $e) {
                $error_message = 'Chyba při změně hesla: ' . $e->getMessage();
            }
        }
    }
}

// Získání informací o uživateli
$stmt = $pdo->prepare("SELECT username, created_at FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include 'includes/admin_header.php'; ?>

<div class="container-fluid">
    <div class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-user-circle me-3"></i>Uživatelský profil</h1>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Informace o profilu -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informace o účtu</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Uživatelské jméno:</strong></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Účet vytvořen:</strong></td>
                                <td><?= $user['created_at'] ? date('d.m.Y H:i', strtotime($user['created_at'])) : 'Neznámo' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Poslední přihlášení:</strong></td>
                                <td><?= date('d.m.Y H:i') ?> (aktuální relace)</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Změna hesla -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Změna hesla</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" onsubmit="return validatePasswordForm()">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Aktuální heslo *</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nové heslo *</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                                <small class="form-text text-muted">Minimálně 6 znaků</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Potvrdit nové heslo *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Změnit heslo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bezpečnostní tipy -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Bezpečnostní doporučení</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-lock text-success me-2"></i>Silné heslo obsahuje:</h6>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Alespoň 8 znaků</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Velká i malá písmena</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Číslice</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Speciální znaky (!@#$%^&*)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-exclamation-triangle text-warning me-2"></i>Bezpečnostní zásady:</h6>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="fas fa-times text-danger me-2"></i>Nesdílejte heslo s nikým</li>
                                    <li><i class="fas fa-times text-danger me-2"></i>Nepoužívejte stejné heslo jinde</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Pravidelně měňte heslo</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Odhlašujte se po práci</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validatePasswordForm() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        alert('Nová hesla se neshodují.');
        return false;
    }
    
    if (newPassword.length < 6) {
        alert('Nové heslo musí mít alespoň 6 znaků.');
        return false;
    }
    
    return confirm('Opravdu chcete změnit heslo?');
}

// Zobrazení síly hesla
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    let feedback = [];
    
    if (password.length >= 8) strength++; else feedback.push('alespoň 8 znaků');
    if (/[a-z]/.test(password)) strength++; else feedback.push('malá písmena');
    if (/[A-Z]/.test(password)) strength++; else feedback.push('velká písmena');
    if (/[0-9]/.test(password)) strength++; else feedback.push('číslice');
    if (/[^a-zA-Z0-9]/.test(password)) strength++; else feedback.push('speciální znaky');
    
    const strengthColors = ['danger', 'danger', 'warning', 'info', 'success', 'success'];
    const strengthTexts = ['Velmi slabé', 'Slabé', 'Střední', 'Dobré', 'Silné', 'Velmi silné'];
    
    let strengthDiv = document.getElementById('password-strength');
    if (!strengthDiv) {
        strengthDiv = document.createElement('div');
        strengthDiv.id = 'password-strength';
        strengthDiv.className = 'mt-2';
        this.parentNode.appendChild(strengthDiv);
    }
    
    if (password.length > 0) {
        strengthDiv.innerHTML = `
            <div class="progress" style="height: 5px;">
                <div class="progress-bar bg-${strengthColors[strength]}" style="width: ${(strength/5)*100}%"></div>
            </div>
            <small class="text-${strengthColors[strength]}">
                ${strengthTexts[strength]}${feedback.length > 0 ? ' (chybí: ' + feedback.join(', ') + ')' : ''}
            </small>
        `;
    } else {
        strengthDiv.innerHTML = '';
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>
