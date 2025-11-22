<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

try {
    if (!isset($_FILES['image'])) {
        echo json_encode(['success' => false, 'error' => 'Nebyl vybrán žádný soubor']);
        exit;
    }
    
    $file = $_FILES['image'];
    
    // Kontrola chyb uploadu
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Chyba při nahrávání souboru']);
        exit;
    }
    
    // Kontrola velikosti (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'Soubor je příliš velký (max 5MB)']);
        exit;
    }
    
    // Kontrola typu souboru pomocí MIME type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'Nepodporovaný formát obrázku']);
        exit;
    }
    
    // Vytvoření uploads složky pokud neexistuje
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generování unikátního názvu
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . strtolower($extension);
    $filepath = $uploadDir . $filename;
    
    // Upload souboru
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        echo json_encode([
            'success' => true, 
            'url' => 'uploads/' . $filename,
            'filename' => $filename
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Nepodařilo se uložit soubor']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Chyba serveru: ' . $e->getMessage()]);
}
?>
