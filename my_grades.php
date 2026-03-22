<?php
require_once __DIR__ . '/includes/session_check.php';
requireRole('student');
$page_title = 'ผลการเรียนของฉัน - โรงเรียนสาธิตวิทยา';
require_once __DIR__ . '/includes/layout_header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-clipboard-data"></i> ผลการเรียนของฉัน</h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="myGradesTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>รหัสวิชา</th>
                        <th>ชื่อวิชา</th>
                        <th width="100">คะแนน</th>
                        <th width="80">เกรด</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" class="text-center py-4"><div class="loading-spinner"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_footer.php'; ?>
