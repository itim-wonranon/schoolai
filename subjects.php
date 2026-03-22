<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการข้อมูลรายวิชา - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-book"></i> จัดการข้อมูลรายวิชา</h1>
    <button class="btn-primary-custom" onclick="openSubjectModal()">
        <i class="bi bi-plus-circle"></i> เพิ่มรายวิชา
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="subjectsTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>รหัสวิชา</th>
                        <th>ชื่อวิชา</th>
                        <th>หน่วยกิต</th>
                        <th width="100">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" class="text-center py-4"><div class="loading-spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Subject Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subjectModalLabel">เพิ่มข้อมูลรายวิชา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="subjectForm">
                    <input type="hidden" id="subject_id">
                    <div class="mb-3">
                        <label class="form-label">รหัสวิชา *</label>
                        <input type="text" class="form-control" id="subject_code" required placeholder="เช่น SUB011">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อวิชา *</label>
                        <input type="text" class="form-control" id="subject_name" required placeholder="ชื่อรายวิชา">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">จำนวนหน่วยกิต *</label>
                        <input type="number" class="form-control" id="subject_credits" min="1" max="6" value="1" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn-primary-custom" onclick="saveSubject()">
                    <i class="bi bi-check-circle"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
