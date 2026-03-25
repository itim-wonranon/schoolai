<?php
require_once 'config/db.php';
$pdo = getDB();

echo "--- Attendance Records ---\n";
$att = $pdo->query("SELECT * FROM attendance LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($att);

echo "\n--- Grade Records ---\n";
$gr = $pdo->query("SELECT * FROM grades LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($gr);

echo "\n--- Joined Grade Test ---\n";
$gradeStmt = $pdo->query("SELECT s.first_name, s.last_name, s.student_code, c.class_name, sub.subject_name, g.grade, g.score
    FROM grades g
    LEFT JOIN students s ON g.student_id = s.id
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN subjects sub ON g.subject_id = sub.id
    ORDER BY g.created_at DESC LIMIT 5");
$res = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
?>
