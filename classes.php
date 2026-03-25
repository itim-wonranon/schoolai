<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการข้อมูลชั้นเรียน - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-diagram-3"></i> จัดการข้อมูลชั้นเรียน</h1>
    <button class="btn-primary-custom" onclick="openClassModal()">
        <i class="bi bi-plus-circle"></i> เพิ่มชั้นเรียน
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="classesTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>รหัสชั้นเรียน</th>
                        <th>ชื่อชั้นเรียน</th>
                        <th>สายการเรียน</th>
                        <th>ครูประจำชั้น</th>
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

<!-- Class Modal -->
<div class="modal fade" id="classModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classModalLabel">เพิ่มข้อมูลชั้นเรียน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="classForm">
                    <input type="hidden" id="class_id">
                    <div class="mb-3">
                        <label class="form-label">รหัสชั้นเรียน *</label>
                        <input type="text" class="form-control" id="class_code" required placeholder="เช่น M1-3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อชั้นเรียน *</label>
                        <input type="text" class="form-control" id="class_name" required placeholder="เช่น ม.1/3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สายการเรียน</label>
                        <input type="text" class="form-control" id="study_track" placeholder="เช่น วิทย์-คณิต, ศิลป์-ภาษา (ปล่อยว่างถ้าไม่มี)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ครูประจำชั้น</label>
                        <select class="form-select" id="homeroom_teacher_id">
                            <option value="">-- เลือกครูประจำชั้น --</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn-primary-custom" onclick="saveClass()">
                    <i class="bi bi-check-circle"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
