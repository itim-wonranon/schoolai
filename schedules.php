<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole('admin');
$page_title = 'จัดการตารางเรียน - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-calendar-week"></i> จัดการตารางเรียน</h1>
    <button class="btn-primary-custom" onclick="openScheduleModal()">
        <i class="bi bi-plus-circle"></i> เพิ่มตารางเรียน
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="schedulesTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>วิชา</th>
                        <th>ครูผู้สอน</th>
                        <th>ชั้นเรียน</th>
                        <th>ห้องเรียน</th>
                        <th>วัน</th>
                        <th>เวลา</th>
                        <th width="100">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="8" class="text-center py-4"><div class="loading-spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">เพิ่มตารางเรียน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <input type="hidden" id="schedule_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">รายวิชา *</label>
                            <select class="form-select" id="schedule_subject_id" required>
                                <option value="">-- เลือกรายวิชา --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ครูผู้สอน *</label>
                            <select class="form-select" id="schedule_teacher_id" required>
                                <option value="">-- เลือกครู --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ชั้นเรียน *</label>
                            <select class="form-select" id="schedule_class_id" required>
                                <option value="">-- เลือกชั้นเรียน --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ห้องเรียน *</label>
                            <select class="form-select" id="schedule_classroom_id" required>
                                <option value="">-- เลือกห้องเรียน --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">วัน *</label>
                            <select class="form-select" id="schedule_day" required>
                                <option value="">-- เลือกวัน --</option>
                                <option value="Monday">จันทร์</option>
                                <option value="Tuesday">อังคาร</option>
                                <option value="Wednesday">พุธ</option>
                                <option value="Thursday">พฤหัสบดี</option>
                                <option value="Friday">ศุกร์</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">เวลาเริ่ม *</label>
                            <input type="time" class="form-control" id="schedule_start_time" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">เวลาสิ้นสุด *</label>
                            <input type="time" class="form-control" id="schedule_end_time" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn-primary-custom" onclick="saveSchedule()">
                    <i class="bi bi-check-circle"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
