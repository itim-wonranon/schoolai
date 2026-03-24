<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../config/db.php';
if (PHP_SAPI !== 'cli') {
    requireRole('admin');
}

$pdo = getDB();
$counts = ['duplicates' => 0, 'removed' => 0];

try {
    // We define duplicates as entries with same (class_id, day_of_week, period_id)
    // where period_id is NOT NULL. 
    // For NULL period_id, we use (class_id, day_of_week, start_time).

    // 1. Find duplicates with period_id
    $sql = "SELECT class_id, day_of_week, period_id, COUNT(*) as cnt, MIN(id) as keep_id 
            FROM schedules 
            WHERE period_id IS NOT NULL 
            GROUP BY class_id, day_of_week, period_id 
            HAVING cnt > 1";
    $duplicates = $pdo->query($sql)->fetchAll();

    foreach ($duplicates as $dup) {
        $counts['duplicates']++;
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE class_id = ? AND day_of_week = ? AND period_id = ? AND id != ?");
        $stmt->execute([$dup['class_id'], $dup['day_of_week'], $dup['period_id'], $dup['keep_id']]);
        $counts['removed'] += ($dup['cnt'] - 1);
    }

    // 2. Find duplicates without period_id (manual time entries)
    $sqlNull = "SELECT class_id, day_of_week, start_time, COUNT(*) as cnt, MIN(id) as keep_id 
                FROM schedules 
                WHERE period_id IS NULL 
                GROUP BY class_id, day_of_week, start_time 
                HAVING cnt > 1";
    $duplicatesNull = $pdo->query($sqlNull)->fetchAll();

    foreach ($duplicatesNull as $dup) {
        $counts['duplicates']++;
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE class_id = ? AND day_of_week = ? AND start_time = ? AND period_id IS NULL AND id != ?");
        $stmt->execute([$dup['class_id'], $dup['day_of_week'], $dup['start_time'], $dup['keep_id']]);
        $counts['removed'] += ($dup['cnt'] - 1);
    }

    echo json_encode(['success' => true, 'message' => "Cleanup complete. Found {$counts['duplicates']} duplicate sets, removed {$counts['removed']} extra rows.", 'details' => $counts]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
