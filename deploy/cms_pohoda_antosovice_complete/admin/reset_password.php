<!DOCTYPE html>
<html>
<head>
    <title>Reset hesla - Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 400px;
            max-width: 100%;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background: #007cba;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 0.5rem;
        }
        .btn:hover {
            background: #005a87;
        }
        .btn-secondary {
            background: #6c757d;
            text-decoration: none;
            text-align: center;
            display: block;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .alert {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
        }
        .info-text {
            color: #666;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }
        #password-strength {
            margin-top: 0.5rem;
        }
        .progress {
            height: 5px;
            background-color: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            transition: width 0.3s ease;
        }
        .bg-danger { background-color: #dc3545 !important; }
        .bg-warning { background-color: #ffc107 !important; }
        .bg-info { background-color: #17a2b8 !important; }
        .bg-success { background-color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
        .text-warning { color: #856404 !important; }
        .text-info { color: #17a2b8 !important; }
        .text-success { color: #155724 !important; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Nastavení nového hesla</h2>
        
        <?php
        require_once '../config.php';

        $success_message = '';
        $error_message = '';
        $valid_token = false;
        $admin_data = null;

        // Ověření tokenu z URL nebo formuláře
        $token = $_GET['token'] ?? $_POST['token'] ?? '';

        if ($token) {
            try {
                // Ověření platnosti tokenu
                $stmt = $pdo->prepare("SELECT id, username FROM admins WHERE reset_token = ? AND reset_token_expires > NOW()");
                $stmt->execute([$token]);
                $admin_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin_data) {
                    $valid_token = true;
                } else {
                    $error_message = 'Token je neplatný nebo vypršel. Zkuste vygenerovat nový token.';
                }
            } catch(PDOException $e) {
                $error_message = 'Chyba při ověřování tokenu: ' . $e->getMessage();
            }
        }

        // Zpracování formuláře pro reset hesla
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($new_password) || empty($confirm_password)) {
                $error_message = 'Všechna pole jsou povinná.';
            } elseif ($new_password !== $confirm_password) {
                $error_message = 'Hesla se neshodují.';
            } elseif (strlen($new_password) < 6) {
                $error_message = 'Heslo musí mít alespoň 6 znaků.';
            } else {
                try {
                    // Aktualizace hesla a vymazání tokenu
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admins SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                    $stmt->execute([$hashed_password, $admin_data['id']]);
                    
                    $success_message = 'Heslo bylo úspěšně změněno. Můžete se nyní přihlásit s novým heslem.';
                    $valid_token = false; // Skryjeme formulář
                } catch(PDOException $e) {
                    $error_message = 'Chyba při změně hesla: ' . $e->getMessage();
                }
            }
        }
        ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
            <div class="form-group">
                <a href="login.php" class="btn">Přihlásit se</a>
            </div>
        <?php elseif ($error_message): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
            <?php if (!$valid_token): ?>
                <div class="form-group">
                    <a href="forgot_password.php" class="btn">Vygenerovat nový token</a>
                    <a href="login.php" class="btn btn-secondary">Zpět na přihlášení</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$token): ?>
            <p class="info-text">
                Zadejte token, který jste obdrželi pro reset hesla.
            </p>

            <form method="get">
                <div class="form-group">
                    <label for="token">Reset token:</label>
                    <input type="text" id="token" name="token" required 
                           placeholder="Vložte token zde...">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Ověřit token</button>
                    <a href="forgot_password.php" class="btn btn-secondary">Vygenerovat nový token</a>
                </div>
            </form>
        <?php elseif ($valid_token): ?>
            <p class="info-text">
                Nastavte nové heslo pro účet: <strong><?= htmlspecialchars($admin_data['username']) ?></strong>
            </p>

            <form method="post" onsubmit="return validatePasswordForm()">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="form-group">
                    <label for="new_password">Nové heslo:</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <div id="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Potvrdit heslo:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Nastavit nové heslo</button>
                    <a href="login.php" class="btn btn-secondary">Zrušit</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
    function validatePasswordForm() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword !== confirmPassword) {
            alert('Hesla se neshodují.');
            return false;
        }
        
        if (newPassword.length < 6) {
            alert('Heslo musí mít alespoň 6 znaků.');
            return false;
        }
        
        return true;
    }

    // Zobrazení síly hesla
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('new_password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
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
                
                if (password.length > 0) {
                    strengthDiv.innerHTML = `
                        <div class="progress">
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
        }
    });
    </script>
</body>
</html>
