<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'Activity Logs - Super Admin';
$current_page = 'admin_logs';

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small uppercase fw-bold tracking-wider">
                    <li class="breadcrumb-item text-muted">Super Admin</li>
                    <li class="breadcrumb-item active text-primary">ประวัติการใช้งาน (Activity Logs)</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0 text-dark">Activity Logs & Audit Trail</h2>
            <p class="text-muted mb-0 small">เฝ้าติดตามความเคลื่อนไหว กิจกรรมการใช้งาน และสถานะความปลอดภัยของระบบ</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="loadLogs()">
            <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรชข้อมูลล่าสุด
        </button>
    </div>

    <!-- Quick Stats Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-gradient-dark text-white p-2">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar-md bg-white-transparent rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;background:rgba(255,255,255,0.1);">
                        <i class="bi bi-activity fs-4 text-white"></i>
                    </div>
                    <div>
                        <small class="text-white-50 d-block">รวมทั้งหมด</small>
                        <h4 class="fw-bold mb-0" id="stat_total">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-2">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar-md bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">กิจกรรมวันนี้</small>
                        <h4 class="fw-bold mb-0" id="stat_today">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-2">
                <div class="card-body d-flex align-items-center text-success">
                    <div class="avatar-md bg-success-subtle rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                        <i class="bi bi-lock-fill fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">การล็อกอิน (วันนี้)</small>
                        <h4 class="fw-bold mb-0" id="stat_logins">--</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-2 border-start border-danger border-4">
                <div class="card-body d-flex align-items-center text-danger">
                    <div class="avatar-md bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                        <i class="bi bi-shield-exclamation fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">ความเสี่ยง/Error (วันนี้)</small>
                        <h4 class="fw-bold mb-0" id="stat_errors">--</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filters -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
        <div class="card-body p-3 bg-light-subtle">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="logSearch" class="form-control border-start-0 ps-0" placeholder="ระบุคำค้นหา (ค้นจากชื่อผู้ใช้ หรือ กิจกรรม)...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="actionFilter" class="form-select border-1">
                        <option value="">ทุกการกระทำ (All Actions)</option>
                        <option value="login_success">Login Success</option>
                        <option value="login_failed">Login Failed</option>
                        <option value="login_blocked">Login Blocked</option>
                        <option value="unauthorized_access">Unauthorized Access</option>
                        <option value="user_create">User Create</option>
                        <option value="user_update">User Update</option>
                        <option value="impersonation_start">Impersonation Start</option>
                        <option value="impersonation_stop">Impersonation Stop</option>
                        <option value="bulk_import">Bulk Import</option>
                        <option value="system_settings_update">System Settings Update</option>
                        <option value="announcement_create">Announcement Create</option>
                        <option value="schedule_create">Schedule Create</option>
                        <option value="schedule_update">Schedule Update</option>
                        <option value="schedule_delete">Schedule Delete</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="timeFilter" class="form-select border-1">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-dark w-100 fw-bold rounded-2 shadow-sm" onclick="loadLogs()">
                        <i class="bi bi-search me-2"></i> Search
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table (Styled Version) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="logsTable">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="ps-4 py-3 border-0 small text-uppercase fw-bold text-muted" style="width: 160px;">Date & Time</th>
                        <th class="py-3 border-0 small text-uppercase fw-bold text-muted" style="width: 220px;">User Identifier</th>
                        <th class="py-3 border-0 small text-uppercase fw-bold text-muted" style="width: 180px;">Action Context</th>
                        <th class="py-3 border-0 small text-uppercase fw-bold text-muted">Activity Description</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <!-- Logs loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<style>
.bg-gradient-dark { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
.animate-slide-up { animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes slideUp { from { transform: translateY(15px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.table-hover tbody tr:hover { background-color: rgba(59, 130, 246, 0.04); transition: background 0.2s; }
.x-small { font-size: 0.72rem; }
.avatar-md { border: 2px solid rgba(255,255,255,0.1); }
.card { border: 1px solid rgba(0,0,0,0.05); }
.badge { font-weight: 600; letter-spacing: 0.025em; border-radius: 6px; }
</style>

<script>
$(document).ready(function() {
    loadLogs();
    loadStats();
});

function loadStats() {
    $.get(BASE_URL + '/api/admin/audit_logs.php?type=stats', function(res) {
        $('#stat_total').text(res.total.toLocaleString());
        $('#stat_today').text(res.today);
        $('#stat_logins').text(res.logins);
        $('#stat_errors').text(res.errors);
    });
}

function loadLogs() {
    const action = $('#actionFilter').val();
    const search = $('#logSearch').val();
    const time = $('#timeFilter').val();

    $('#logsTable tbody').html('<tr><td colspan="4" class="text-center py-5 text-muted"><div class="spinner-grow spinner-grow-sm text-primary me-2"></div> Fetching activity data...</td></tr>');

    $.ajax({
        url: BASE_URL + '/api/admin/audit_logs.php',
        method: 'GET',
        data: { action, search, time },
        dataType: 'json',
        success: function(data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="4" class="text-center py-5 text-muted"><div class="my-4"><i class="bi bi-inbox display-4 opacity-25 d-block mb-3"></i>No logs found matching your criteria.</div></td></tr>';
            }
            data.forEach(function(l, index) {
                let badgeClass = 'bg-secondary-subtle text-secondary border border-secondary';
                if (l.action.includes('success')) badgeClass = 'bg-success-subtle text-success border border-success';
                else if (l.action.includes('failed') || l.action.includes('unauthorized') || l.action.includes('blocked')) badgeClass = 'bg-danger-subtle text-danger border border-danger';
                else if (l.action.includes('create') || l.action.includes('import')) badgeClass = 'bg-primary-subtle text-primary border border-primary';
                else if (l.action.includes('update')) badgeClass = 'bg-warning-subtle text-warning border border-warning';

                const delay = (index * 0.03).toFixed(2);

                rows += `
                <tr class="animate-slide-up" style="animation-delay: ${delay}s">
                    <td class="ps-4">
                        <div class="text-dark small fw-bold">${moment(l.created_at).format('DD MMM YYYY')}</div>
                        <div class="text-muted x-small">${moment(l.created_at).format('HH:mm:ss')} น.</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-xs bg-dark-subtle rounded-3 d-flex align-items-center justify-content-center me-3" style="width:36px;height:36px;background:#f1f5f9;">
                                <i class="bi bi-person-badge text-primary fs-5"></i>
                            </div>
                            <div class="lh-sm">
                                <div class="fw-bold small text-dark">${l.display_name || 'System Operator'}</div>
                                <div class="text-muted x-small">@${l.username || 'n/a'}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge ${badgeClass} text-uppercase px-2 py-1 x-small">${l.action}</span></td>
                    <td class="small text-secondary fw-semibold ps-3" style="border-left: 3px solid #f1f5f9;">${l.description}</td>
                </tr>`;
            });
            $('#logsTable tbody').html(rows);
        }
    });
}
</script>
