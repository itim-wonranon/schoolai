<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

header('Content-Type: application/json');
$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$role = getUserRole();
$refId = getRefId();

try {
    switch ($method) {
        case 'GET':
            // Get schedules for attendance dropdown
            if (isset($_GET['action']) && $_GET['action'] === 'schedules') {
                $currentDay = date('l'); // e.g., 'Monday'
                $sql = "SELECT s.*, sub.subject_name, c.class_name, r.room_name
                        FROM schedules s
                        JOIN subjects sub ON s.subject_id = sub.id
                        JOIN classes c ON s.class_id = c.id
                        JOIN classrooms r ON s.classroom_id = r.id";
                
                if ($role === 'teacher') {
                    $sql .= " WHERE s.teacher_id = ?";
                    $sql .= " ORDER BY (s.day_of_week = ?) DESC, FIELD(s.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday'), s.start_time";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$refId, $currentDay]);
                } else {
                    $sql .= " ORDER BY (s.day_of_week = ?) DESC, FIELD(s.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday'), s.start_time";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$currentDay]);
                }
                echo json_encode($stmt->fetchAll());
                break;
            }

            // Get students for a schedule
            $scheduleId = $_GET['schedule_id'] ?? 0;
            $date = $_GET['date'] ?? date('Y-m-d');

            if ($scheduleId) {
                // Get class_id from schedule
                $stmtSch = $pdo->prepare("SELECT class_id FROM schedules WHERE id = ?");
                $stmtSch->execute([$scheduleId]);
                $schedule = $stmtSch->fetch();
                $classId = $schedule['class_id'] ?? 0;

                $stmt = $pdo->prepare("SELECT s.id AS student_id, s.student_code, s.first_name, s.last_name,
                    a.status
                    FROM students s
                    LEFT JOIN attendance a ON a.student_id = s.id AND a.schedule_id = ? AND a.attend_date = ?
                    WHERE s.class_id = ?
                    ORDER BY s.student_code");
                $stmt->execute([$scheduleId, $date, $classId]);
                echo json_encode($stmt->fetchAll());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO attendance (schedule_id, student_id, attend_date, status)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status)");
            $stmt->execute([$data['schedule_id'], $data['student_id'], $data['date'], $data['status']]);
            echo json_encode(['success' => true, 'message' => 'บันทึกสำเร็จ']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
