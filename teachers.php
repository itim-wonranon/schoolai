<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการข้อมูลครู - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-person-badge"></i> จัดการข้อมูลครู</h1>
    <button class="btn-primary-custom" onclick="openTeacherModal()">
        <i class="bi bi-plus-circle"></i> เพิ่มครู
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="teachersTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>รหัสครู</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>หมวดวิชา</th>
                        <th width="100">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="6" class="text-center py-4"><div class="loading-spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Teacher Modal -->
<div class="modal fade" id="teacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teacherModalLabel">เพิ่มข้อมูลครู</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="teacherForm">
                    <input type="hidden" id="teacher_id">
                    <div class="mb-3">
                        <label class="form-label">รหัสครู *</label>
                        <input type="text" class="form-control" id="teacher_code" required placeholder="เช่น T006">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อ *</label>
                        <input type="text" class="form-control" id="teacher_first_name" required placeholder="ชื่อจริง">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">นามสกุล *</label>
                        <input type="text" class="form-control" id="teacher_last_name" required placeholder="นามสกุล">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control" id="teacher_phone" placeholder="0812345678">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมวดวิชา</label>
                        <input type="text" class="form-control" id="teacher_department" placeholder="เช่น คณิตศาสตร์">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn-primary-custom" onclick="saveTeacher()">
                    <i class="bi bi-check-circle"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
