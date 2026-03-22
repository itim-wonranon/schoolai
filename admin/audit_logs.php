<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'Activity Logs - Super Admin';
$current_page = 'admin_logs';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Activity Logs & Audit Trail</h2>
            <p class="text-muted">ตรวจสอบความเคลื่อนไหวและประวัติการใช้งานในระบบ</p>
        </div>
        <button class="btn btn-outline-secondary shadow-sm" onclick="loadLogs()">
            <i class="bi bi-arrow-clockwise me-2"></i> รีเฟรชข้อมูล
        </button>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="small text-muted mb-1">ประเภทการกระทำ</label>
                    <select id="actionFilter" class="form-control">
                        <option value="">ทุกการกระทำ</option>
                        <option value="login_success">Login Success</option>
                        <option value="login_failed">Login Failed</option>
                        <option value="user_create">User Create</option>
                        <option value="user_update">User Update</option>
                        <option value="impersonation_start">Impersonation Start</option>
                        <option value="bulk_import">Bulk Import</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted mb-1">ค้นหาข้อความ</label>
                    <input type="text" id="logSearch" class="form-control" placeholder="ชื่อผู้ใช้, คำอธิบาย...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="loadLogs()">กรอง</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="logsTable">
                <thead class="bg-light text-muted">
                    <tr>
                        <th class="ps-4">วันเวลา</th>
                        <th>ผู้ใช้งาน</th>
                        <th>การกระทำ</th>
                        <th>รายละเอียด</th>
                        <th>IP Address</th>
                        <th class="text-end pe-4">ตัวเลือก</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">กำลังโหลดข้อมูลบันทึก...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">รายละเอียด Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="bg-light p-3 rounded mb-3">
                    <pre id="logDetailsRaw" class="mb-0 overflow-auto" style="max-height: 300px;font-size:0.85rem;"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<script>
$(document).ready(function() {
    loadLogs();
});

function loadLogs() {
    const action = $('#actionFilter').val();
    const search = $('#logSearch').val();

    $.get(BASE_URL + '/api/admin/audit_logs.php', { action, search }, function(data) {
        let rows = '';
        if (data.length === 0) {
            rows = '<tr><td colspan="6" class="text-center py-5 text-muted">ไม่พบข้อมูลบันทึกในระบบ</td></tr>';
        }
        data.forEach(function(l) {
            let actionBadge = l.action;
            if (l.action.includes('success')) actionBadge = `<span class="badge bg-success-subtle text-success">${l.action}</span>`;
            else if (l.action.includes('failed')) actionBadge = `<span class="badge bg-danger-subtle text-danger">${l.action}</span>`;
            else if (l.action.includes('delete')) actionBadge = `<span class="badge bg-danger-subtle text-danger">${l.action}</span>`;
            else actionBadge = `<span class="badge bg-primary-subtle text-primary">${l.action}</span>`;

            rows += `
            <tr class="animate-fade-in small">
                <td class="ps-4 font-monospace">${l.created_at}</td>
                <td class="fw-bold">${l.display_name || 'System'} <br><small class="text-muted fw-normal">@${l.username || 'n/a'}</small></td>
                <td>${actionBadge}</td>
                <td>${l.description}</td>
                <td><small class="text-muted">${l.ip_address}</small></td>
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-light" onclick='viewLogDetails(${JSON.stringify(l)})' title="ดูข้อมูลดิบ"><i class="bi bi-search"></i></button>
                </td>
            </tr>`;
        });
        $('#logsTable tbody').html(rows);
    });
}

function viewLogDetails(log) {
    let details = log.details;
    try {
        if (typeof details === 'string') details = JSON.parse(details);
        $('#logDetailsRaw').text(JSON.stringify(details, null, 2));
    } catch(e) {
        $('#logDetailsRaw').text(details || 'No additional details');
    }
    new bootstrap.Modal($('#logModal')).show();
}
</script>
