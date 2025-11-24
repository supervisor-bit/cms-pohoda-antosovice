<!DOCTYPE html>
<html>
<head>
    <title>Přihlášení - Admin</title>
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
        }
        .btn:hover {
            background: #005a87;
        }
        .alert {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Přihlášení do administrace</h2>
        
        <?php
        require_once '../config.php';

        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
            header('Location: index.php');
            exit;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($username && $password) {
                try {
                    $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
                    $stmt->execute([$username]);
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($admin) {
                        // DEBUG: Výpis pro ladění
                        error_log("DEBUG: Admin nalezen - ID: " . $admin['id'] . ", Username: " . $admin['username']);
                        
                        if (password_verify($password, $admin['password'])) {
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_id'] = $admin['id'];
                            $_SESSION['admin_username'] = $admin['username'];
                            header('Location: index.php');
                            exit;
                        } else {
                            error_log("DEBUG: Password verify failed for user: $username");
                            $error = 'Nesprávné heslo.';
                        }
                    } else {
                        error_log("DEBUG: User not found: $username");
                        $error = 'Uživatel nenalezen.';
                    }
                } catch(PDOException $e) {
                    error_log("DEBUG: Database error: " . $e->getMessage());
                    $error = 'Chyba při přihlašování: ' . $e->getMessage();
                }
            } else {
                $error = 'Vyplňte všechna pole.';
            }
        }
        ?>

        <?php if ($error): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Uživatelské jméno:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Heslo:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Přihlásit se</button>
            </div>
        </form>
        
        <div style="text-align: center; margin-top: 1rem;">
            <a href="forgot_password.php" style="color: #007cba; text-decoration: none; font-size: 0.9rem;">
                Zapomenuté heslo?
            </a>
        </div>
    </div>
</body>
</html>
