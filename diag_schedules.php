<?php
require 'config/db.php';
$pdo = getDB();
header('Content-Type: text/plain');

echo "SCHEDULES COUNT: " . $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn() . "\n\n";

echo "SAMPLE SCHEDULES (First 5):\n";
$stmt = $pdo->query("SELECT s.*, p.period_no, p.level FROM schedules s LEFT JOIN school_periods p ON s.period_id = p.id LIMIT 5");
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "ID:{$r['id']} CLASS:{$r['class_id']} P:{$r['period_no']} LV:{$r['level']} DAY:{$r['day_of_week']}\n";
}

echo "\nCLASSES:\n";
foreach($pdo->query("SELECT id, class_name FROM classes")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "ID:{$r['id']} NAME:{$r['class_name']}\n";
}

echo "\nPERIODS SUMMARY:\n";
$stmt = $pdo->query("SELECT level, COUNT(*) as cnt FROM school_periods GROUP BY level");
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "LV:{$r['level']} COUNT:{$r['cnt']}\n";
}
?>
