<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการข้อมูลนักเรียน - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-people"></i> จัดการข้อมูลนักเรียน</h1>
    <button class="btn-primary-custom" onclick="openStudentModal()">
        <i class="bi bi-plus-circle"></i> เพิ่มนักเรียน
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="studentsTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>รหัสนักเรียน</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>วันเกิด</th>
                        <th>ชั้นเรียน</th>
                        <th>สายการเรียน</th>
                        <th width="100">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="7" class="text-center py-4"><div class="loading-spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Student Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentModalLabel">เพิ่มข้อมูลนักเรียน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="studentForm">
                    <input type="hidden" id="student_id">
                    <div class="mb-3">
                        <label class="form-label">รหัสนักเรียน *</label>
                        <input type="text" class="form-control" id="student_code" required placeholder="เช่น S011">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อ *</label>
                        <input type="text" class="form-control" id="student_first_name" required placeholder="ชื่อจริง">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">นามสกุล *</label>
                        <input type="text" class="form-control" id="student_last_name" required placeholder="นามสกุล">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันเกิด</label>
                        <input type="date" class="form-control" id="student_birthdate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชั้นเรียน</label>
                        <select class="form-select" id="student_class_id">
                            <option value="">-- เลือกชั้นเรียน --</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn-primary-custom" onclick="saveStudent()">
                    <i class="bi bi-check-circle"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
