<?php
// Temporary script to trigger log translation
define('BASE_URL', 'http://localhost/schoolai'); // Just in case
require_once __DIR__ . '/includes/session_check.php';

$pdo = getDB();
$translations = [
    'User logged in successfully' => 'เข้าสู่ระบบสำเร็จ',
    'Failed login attempt for username: %' => 'เข้าสู่ระบบไม่สำเร็จ (ชื่อผู้ใช้หรือรหัสผ่านผิด): %',
    'Suspended user tried to login: %' => 'เข้าสู่ระบบไม่สำเร็จ (บัญชีถูกระงับ): %',
    'Suspended account attempted to access the system' => 'การเข้าสู่ระบบถูกระงับ (บัญชีถูกระงับการใช้งาน)',
    'Attempted access to restricted page with role: %' => 'พยายามเข้าถึงหน้าเว็บที่ไม่มีสิทธิ์โดยมีสิทธิ์: %',
    'Created new user: %' => 'สร้างผู้ใช้ใหม่: %',
    'Updated user ID: %' => 'อัปเดตข้อมูลผู้ใช้ ID: %',
    'Admin started impersonating user: %' => 'เริ่มการสวมรอยเป็นผู้ใช้: %',
    'Admin stopped impersonation' => 'ยกเลิกการสวมรอย',
    'Performed bulk import for %' => 'ดำเนินการนำเข้าข้อมูลจำนวนมาก: %',
    'Updated global system settings' => 'อัปเดตการตั้งค่าระบบส่วนกลาง',
    'Broadcasted announcement: %' => 'ส่งประกาศข่าวสาร: %',
    'Added schedule for %' => 'เพิ่มตารางเรียนสำหรับ %',
    'Updated schedule ID: %' => 'อัปเดตตารางเรียน ID: %',
    'Deleted schedule ID: %' => 'ลบตารางเรียน ID: %'
];

foreach ($translations as $old => $new) {
    if (strpos($old, '%') === false) {
        $stmt = $pdo->prepare("UPDATE activity_logs SET description = ? WHERE description = ?");
        $stmt->execute([$new, $old]);
    } else {
        $baseOld = str_replace('%', '', $old);
        $baseNew = str_replace('%', '', $new);
        $stmt = $pdo->prepare("UPDATE activity_logs SET description = REPLACE(description, ?, ?) WHERE description LIKE ?");
        $stmt->execute([$baseOld, $baseNew, $baseOld . '%']);
    }
}
echo "Migration successful\n";
unlink(__file__); // Delete itself
?>
