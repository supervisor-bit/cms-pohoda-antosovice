<?php
require_once 'config.php';

echo "Debug kategorií:\n";

try {
    // Zkontroluj, zda existuje tabulka
    $stmt = $pdo->query("SHOW TABLES LIKE 'event_categories'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabulka event_categories existuje\n";
        
        // Načti všechny kategorie
        $categories = $pdo->query("SELECT * FROM event_categories")->fetchAll();
        echo "Počet kategorií celkem: " . count($categories) . "\n";
        
        foreach ($categories as $cat) {
            echo "- {$cat['name']} (active: {$cat['is_active']}, position: {$cat['position']})\n";
        }
        
        // Načti pouze aktivní kategorie
        $activeCategories = $pdo->query("SELECT * FROM event_categories WHERE is_active = 1 ORDER BY position")->fetchAll();
        echo "\nPočet aktivních kategorií: " . count($activeCategories) . "\n";
        
    } else {
        echo "✗ Tabulka event_categories neexistuje\n";
        
        // Zkus vytvořit tabulku a naplnit ji
        echo "Vytvářím tabulku...\n";
        
        $pdo->exec("
            CREATE TABLE event_categories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                color VARCHAR(7) DEFAULT '#667eea',
                icon VARCHAR(50),
                position INT DEFAULT 0,
                is_active BOOLEAN DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        echo "✓ Tabulka vytvořena\n";
        
        // Vložit základní kategorie
        $defaultCategories = [
            ['name' => 'Sportovní akce', 'color' => '#e74c3c', 'icon' => 'running', 'position' => 1],
            ['name' => 'Kulturní program', 'color' => '#9b59b6', 'icon' => 'theater-masks', 'position' => 2],
            ['name' => 'Workshopy', 'color' => '#f39c12', 'icon' => 'tools', 'position' => 3],
            ['name' => 'Relaxace', 'color' => '#27ae60', 'icon' => 'spa', 'position' => 4],
            ['name' => 'Výlety', 'color' => '#3498db', 'icon' => 'mountain', 'position' => 5],
        ];
        
        $insertStmt = $pdo->prepare("
            INSERT INTO event_categories (name, color, icon, position, is_active) 
            VALUES (?, ?, ?, ?, 1)
        ");
        
        foreach ($defaultCategories as $cat) {
            $insertStmt->execute([$cat['name'], $cat['color'], $cat['icon'], $cat['position']]);
        }
        
        echo "✓ Vloženo " . count($defaultCategories) . " základních kategorií\n";
    }
    
} catch (Exception $e) {
    echo "✗ Chyba: " . $e->getMessage() . "\n";
}
?>