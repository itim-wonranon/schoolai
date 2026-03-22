<?php
require_once __DIR__ . '/../../includes/session_check.php';
requireRole('admin');

header('Content-Type: application/json');
$pdo = getDB();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        echo json_encode($settings);
    } 
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($data as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        
        $pdo->commit();
        logActivity('system_settings_update', "Updated global system settings");
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
