<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการข้อมูลห้องเรียน - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-door-open"></i> จัดการข้อมูลห้องเรียน</h1>
    <button class="btn-primary-custom" onclick="openClassroomModal()">
        <i class="bi bi-plus-circle"></i> เพิ่มห้องเรียน
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="classroomsTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>รหัสห้อง</th>
                        <th>ชื่อห้อง</th>
                        <th width="100">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="4" class="text-center py-4"><div class="loading-spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Classroom Modal -->
<div class="modal fade" id="classroomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classroomModalLabel">เพิ่มข้อมูลห้องเรียน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="classroomForm">
                    <input type="hidden" id="classroom_id">
                    <div class="mb-3">
                        <label class="form-label">รหัสห้อง *</label>
                        <input type="text" class="form-control" id="room_code" required placeholder="เช่น R401">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อห้อง *</label>
                        <input type="text" class="form-control" id="room_name" required placeholder="เช่น ห้อง 401">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn-primary-custom" onclick="saveClassroom()">
                    <i class="bi bi-check-circle"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
