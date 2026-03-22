<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole(['admin', 'teacher']);
$page_title = 'เช็คชื่อนักเรียน - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-check2-square"></i> เช็คชื่อนักเรียน</h1>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-funnel"></i> เลือกคาบเรียน</div>
    <div class="card-body">
        <div class="filter-bar">
            <div class="form-group">
                <label class="form-label">คาบเรียน</label>
                <select class="form-select" id="attendanceFilterSchedule">
                    <option value="">-- เลือกคาบเรียน --</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">วันที่</label>
                <input type="date" class="form-control" id="attendanceDate" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group" style="flex:0;">
                <label class="form-label">&nbsp;</label>
                <button class="btn-primary-custom" onclick="loadAttendanceList()">
                    <i class="bi bi-search"></i> ค้นหา
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card" style="display:none;">
    <div class="card-header">
        <i class="bi bi-people"></i> รายชื่อนักเรียน
        <small class="text-muted ms-2">(คลิกที่สถานะเพื่อเปลี่ยน)</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="attendanceTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>รหัสนักเรียน</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th width="160">สถานะ</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
