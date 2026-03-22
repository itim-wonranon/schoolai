<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

header('Content-Type: application/json');
$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$role = getUserRole();
$refId = getRefId();

// Grade calculation function
function calculateGrade($score) {
    if ($score >= 80) return '4';
    if ($score >= 70) return '3';
    if ($score >= 60) return '2';
    if ($score >= 50) return '1';
    return '0';
}

try {
    switch ($method) {
        case 'GET':
            // Student viewing own grades
            if (isset($_GET['my']) && $role === 'student') {
                $stmt = $pdo->prepare("SELECT g.*, sub.subject_code, sub.subject_name
                    FROM grades g
                    JOIN subjects sub ON g.subject_id = sub.id
                    WHERE g.student_id = ?
                    ORDER BY sub.subject_code");
                $stmt->execute([$refId]);
                echo json_encode($stmt->fetchAll());
                break;
            }

            // Load students for grading (by subject + class)
            $subjectId = $_GET['subject_id'] ?? 0;
            $classId = $_GET['class_id'] ?? 0;

            if ($subjectId && $classId) {
                // For teacher role, verify they teach this subject
                if ($role === 'teacher') {
                    $stmtCheck = $pdo->prepare("SELECT id FROM schedules WHERE subject_id = ? AND teacher_id = ? LIMIT 1");
                    $stmtCheck->execute([$subjectId, $refId]);
                    if (!$stmtCheck->fetch()) {
                        echo json_encode([]);
                        break;
                    }
                }

                $stmt = $pdo->prepare("SELECT s.id AS student_id, s.student_code, s.first_name, s.last_name,
                    g.score, g.grade
                    FROM students s
                    LEFT JOIN grades g ON g.student_id = s.id AND g.subject_id = ?
                    WHERE s.class_id = ?
                    ORDER BY s.student_code");
                $stmt->execute([$subjectId, $classId]);
                echo json_encode($stmt->fetchAll());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $subjectId = $data['subject_id'];
            $grades = $data['grades'];

            $stmt = $pdo->prepare("INSERT INTO grades (student_id, subject_id, score, grade)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE score = VALUES(score), grade = VALUES(grade)");

            foreach ($grades as $g) {
                $score = floatval($g['score']);
                $grade = calculateGrade($score);
                $stmt->execute([$g['student_id'], $subjectId, $score, $grade]);
            }

            echo json_encode(['success' => true, 'message' => 'บันทึกคะแนนสำเร็จ']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
