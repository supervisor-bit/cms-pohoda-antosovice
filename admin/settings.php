<?php
require_once '../config.php';
requireLogin();

$success_message = '';
$error_message = '';

// Uložení nastavení
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings_to_update = [
        'site_title',
        'site_description',
        'contact_email',
        'contact_phone',
        'address',
        'opening_hours',
        'facebook_url',
        'instagram_url'
    ];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($settings_to_update as $key) {
            $value = sanitize($_POST[$key] ?? '');
            
            // Aktualizace nebo vložení nastavení
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([$key, $value]);
        }
        
        $pdo->commit();
        $success_message = 'Nastavení bylo úspěšně uloženo.';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = 'Chyba při ukládání nastavení.';
    }
}

// Načtení současných nastavení
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings");
$stmt->execute();
$settings_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settings_data as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Include admin header with themes
include 'includes/admin_header.php';
?>

            <h1>Nastavení webu</h1>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= $success_message ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="section">
                    <h2>Základní informace</h2>
                    
                    <div class="form-group">
                        <label for="site_title">Název webu:</label>
                        <input type="text" id="site_title" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_description">Popis webu:</label>
                        <textarea id="site_description" name="site_description" class="form-control" rows="3"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="section">
                    <h2>Kontaktní informace</h2>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="contact_email">E-mail:</label>
                            <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_phone">Telefon:</label>
                            <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Adresa:</label>
                        <textarea id="address" name="address" class="form-control" rows="2"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="opening_hours">Otevírací doba:</label>
                        <input type="text" id="opening_hours" name="opening_hours" value="<?= htmlspecialchars($settings['opening_hours'] ?? '') ?>" class="form-control" placeholder="např. Květen - Září: 8:00 - 22:00">
                    </div>
                </div>
                
                <div class="section">
                    <h2>Sociální sítě</h2>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="facebook_url">Facebook URL:</label>
                            <input type="url" id="facebook_url" name="facebook_url" value="<?= htmlspecialchars($settings['facebook_url'] ?? '') ?>" class="form-control" placeholder="https://facebook.com/vase-stranka">
                            <small class="form-text text-muted">Zadejte URL vaší Facebook stránky</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="instagram_url">Instagram URL:</label>
                            <input type="url" id="instagram_url" name="instagram_url" value="<?= htmlspecialchars($settings['instagram_url'] ?? '') ?>" class="form-control" placeholder="https://instagram.com/vase-stranka">
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <button type="submit" class="btn btn-primary">Uložit nastavení</button>
                </div>
            </form>
            
            <div class="section">
                <h2>Užitečné odkazy</h2>
                <div class="quick-actions">
                    <a href="../" target="_blank" class="btn btn-secondary">Zobrazit web</a>
                    <a href="pages.php" class="btn btn-secondary">Spravovat stránky</a>
                    <a href="posts.php" class="btn btn-secondary">Spravovat články</a>
                    <a href="index.php" class="btn btn-secondary">Dashboard</a>
                </div>
            </div>
            
            <div class="section">
                <h2>Databáze</h2>
                <p>Pokud jste ještě nevytvořili databázi, spusťte následující SQL příkazy v phpMyAdmin:</p>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin: 1rem 0; font-family: monospace; font-size: 0.9rem;">
                    Otevřete soubor <strong>database.sql</strong> v kořenové složce projektu a spusťte SQL příkazy v phpMyAdmin.
                </div>
                <p><small>Výchozí přihlášení do administrace: <strong>admin</strong> / <strong>password</strong></small></p>
            </div>
        </div>
    </div>

<?php include 'includes/admin_footer.php'; ?>
