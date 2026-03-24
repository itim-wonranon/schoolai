<?php
require 'config/db.php';
$pdo = getDB();

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE schedules");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    
    // Fetch period IDs dynamically to be safe
    $middlePids = $pdo->query("SELECT id FROM school_periods WHERE level = 'middle' AND is_lunch = 0")->fetchAll(PDO::FETCH_COLUMN);
    $highPids = $pdo->query("SELECT id FROM school_periods WHERE level = 'high' AND is_lunch = 0")->fetchAll(PDO::FETCH_COLUMN);

    $subjects = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    $teachers = [1, 2, 3, 4, 5, 1, 2, 3, 4, 5];
    $classrooms = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    $stmt = $pdo->prepare("INSERT INTO schedules (subject_id, teacher_id, class_id, classroom_id, day_of_week, period_id, start_time, end_time) 
                           SELECT ?, ?, ?, ?, ?, id, start_time, end_time FROM school_periods WHERE id = ?");

    // ม.1/1 (Class ID 1)
    foreach ($days as $dayIndex => $day) {
        foreach ($middlePids as $pIndex => $pid) {
            $subIdx = ($dayIndex + $pIndex) % count($subjects);
            $stmt->execute([$subjects[$subIdx], $teachers[$subIdx], 1, $classrooms[$subIdx], $day, $pid]);
        }
    }

    // ม.4/1 (Class ID 7)
    foreach ($days as $dayIndex => $day) {
        foreach ($highPids as $pIndex => $pid) {
            $subIdx = ($dayIndex + $pIndex + 5) % count($subjects);
            $stmt->execute([$subjects[$subIdx], $teachers[$subIdx], 7, $classrooms[$subIdx], $day, $pid]);
        }
    }

    echo "Sample schedules created successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
