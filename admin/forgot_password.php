<!DOCTYPE html>
<html>
<head>
    <title>Zapomenuté heslo - Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 400px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input[type="text"] {
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
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Obnovení hesla</h2>
        
        <?php
        require_once '../config.php';

        $success_message = '';
        $error_message = '';
        $token_generated = false;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = sanitize($_POST['username'] ?? '');

            if ($username) {
                try {
                    // Najít uživatele
                    $stmt = $pdo->prepare("SELECT id, username FROM admins WHERE username = ?");
                    $stmt->execute([$username]);
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($admin) {
                        // Generování bezpečného tokenu
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token vyprší za hodinu

                        // Uložení tokenu do databáze
                        $stmt = $pdo->prepare("UPDATE admins SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                        $stmt->execute([$token, $expires, $admin['id']]);

                        $token_generated = true;
                        $success_message = 'Token pro obnovení hesla byl vygenerován.';
                    } else {
                        $error_message = 'Uživatel s tímto jménem neexistuje.';
                    }
                } catch(PDOException $e) {
                    $error_message = 'Chyba při generování tokenu: ' . $e->getMessage();
                }
            } else {
                $error_message = 'Vyplňte uživatelské jméno.';
            }
        }
        ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <?php if (!$token_generated): ?>
            <p class="info-text">
                Zadejte vaše uživatelské jméno a my vygenerujeme token pro obnovení hesla.
                Token bude platný po dobu 1 hodiny.
            </p>

            <form method="post">
                <div class="form-group">
                    <label for="username">Uživatelské jméno:</label>
                    <input type="text" id="username" name="username" required 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Generovat token</button>
                    <a href="login.php" class="btn btn-secondary">Zpět na přihlášení</a>
                </div>
            </form>
        <?php else: ?>
            <div style="background: #e7f3ff; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                <h4 style="margin: 0 0 0.5rem 0; color: #0066cc;">Token pro obnovení hesla:</h4>
                <div style="background: white; padding: 0.75rem; border: 1px solid #ccc; border-radius: 3px; font-family: monospace; word-break: break-all; font-size: 14px;">
                    <?= htmlspecialchars($token) ?>
                </div>
                <small style="color: #666; display: block; margin-top: 0.5rem;">
                    <strong>Důležité:</strong> Zkopírujte tento token a použijte ho na stránce pro reset hesla.
                    Token vyprší za 1 hodinu.
                </small>
            </div>

            <div class="form-group">
                <a href="reset_password.php" class="btn">Pokračovat k resetu hesla</a>
                <a href="login.php" class="btn btn-secondary">Zpět na přihlášení</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
