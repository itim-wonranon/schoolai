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
                        <th width="60">โปรไฟล์</th>
                        <th>รหัสครู</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ระดับชั้น</th>
                        <th>ครูประจำชั้น</th>
                        <th>หมวดวิชา</th>
                        <th width="100">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="8" class="text-center py-4"><div class="loading-spinner"></div></td></tr>
                </tbody>
            </table>
...
<!-- Teacher Modal -->
<div class="modal fade" id="teacherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teacherModalLabel">เพิ่มข้อมูลครู</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="teacherForm" enctype="multipart/form-data">
                    <input type="hidden" id="teacher_id" name="id">
                    <input type="hidden" id="teacher_action" name="action" value="POST">
                    
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="profile-upload-preview mb-3">
                                <img src="assets/images/default-avatar.png" id="profilePreview" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <label class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-camera"></i> เลือกรูปโปรไฟล์
                                <input type="file" id="teacher_profile_image" name="profile_image" hidden accept="image/*" onchange="previewImage(this)">
                            </label>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">รหัสครู *</label>
                                    <input type="text" class="form-control" name="teacher_code" id="teacher_code" required placeholder="T001">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ระดับชั้นที่สอน *</label>
                                    <select class="form-select" name="teaching_level" id="teacher_teaching_level" required>
                                        <option value="middle">มัธยมต้น (M.1 - M.3)</option>
                                        <option value="high">มัธยมปลาย (M.4 - M.6)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ชื่อ *</label>
                                    <input type="text" class="form-control" name="first_name" id="teacher_first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">นามสกุล *</label>
                                    <input type="text" class="form-control" name="last_name" id="teacher_last_name" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">เบอร์โทรศัพท์</label>
                                    <input type="text" class="form-control" name="phone" id="teacher_phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">หมวดวิชา</label>
                                    <input type="text" class="form-control" name="department" id="teacher_department">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ครูประจำชั้น (ถ้ามี)</label>
                                <select class="form-select" name="homeroom_class_id" id="teacher_homeroom_class_id">
                                    <option value="">-- ไม่ได้เป็นครูประจำชั้น --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn-primary-custom" onclick="saveTeacher()">
                    <i class="bi bi-check-circle"></i> บันทึกข้อมูล
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3" style="font-size: 3rem;">
                    <i class="bi bi-exclamation-octagon"></i>
                </div>
                <h5>ยืนยันการลบ?</h5>
                <p class="text-muted small">ข้อมูลครูและประวัติการสอนจะถูกลบออกจากระบบถาวร</p>
                <div class="d-grid gap-2">
                    <button type="button" id="btnConfirmDelete" class="btn btn-danger">ยืนยันการลบ</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
