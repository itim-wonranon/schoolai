<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole(['teacher', 'student']);
$page_title = (getUserRole() === 'teacher' ? 'ตารางสอนของฉัน' : 'ตารางเรียนของฉัน') . ' - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-calendar-week"></i> <?php echo getUserRole() === 'teacher' ? 'ตารางสอนของฉัน' : 'ตารางเรียนของฉัน'; ?></h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="myScheduleTable">
                <thead>
                    <tr>
                        <th>วัน</th>
                        <th>เวลา</th>
                        <th>วิชา</th>
                        <th>ครูผู้สอน</th>
                        <th>ห้อง</th>
                        <th>ชั้นเรียน</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="6" class="text-center py-4"><div class="loading-spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
