<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole(['admin', 'teacher']);
$page_title = 'บันทึกผลการเรียน - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-clipboard-data"></i> บันทึกผลการเรียน</h1>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-funnel"></i> ค้นหานักเรียน</div>
    <div class="card-body">
        <div class="filter-bar">
            <div class="form-group">
                <label class="form-label">รายวิชา</label>
                <select class="form-select" id="gradeFilterSubject">
                    <option value="">-- เลือกรายวิชา --</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">ชั้นเรียน</label>
                <select class="form-select" id="gradeFilterClass">
                    <option value="">-- เลือกชั้นเรียน --</option>
                </select>
            </div>
            <div class="form-group" style="flex:0;">
                <label class="form-label">&nbsp;</label>
                <button class="btn-primary-custom" onclick="loadGradeStudents()">
                    <i class="bi bi-search"></i> ค้นหา
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-check"></i> รายชื่อนักเรียน</span>
        <button class="btn-primary-custom" onclick="saveAllGrades()">
            <i class="bi bi-floppy"></i> บันทึกคะแนนทั้งหมด
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="gradeStudentsTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>รหัสนักเรียน</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th width="120">คะแนน (100)</th>
                        <th width="80">เกรด</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
