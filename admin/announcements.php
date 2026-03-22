<?php
require_once __DIR__ . '/../includes/session_check.php';
requireRole('admin');
$page_title = 'ประกาศข่าวสาร - Super Admin';
$current_page = 'admin_announcements'; // Sidebar needs to match this or we dynamic highlight

require_once __DIR__ . '/../includes/layout_header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">ระบบประกาศข่าวสาร (Broadcasting)</h2>
            <p class="text-muted">ส่งประกาศข่าวประชาสัมพันธ์ไปยังกลุ่มเป้าหมายต่างๆ ในโรงเรียน</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="openAnnounceModal()">
            <i class="bi bi-megaphone me-2"></i> สร้างประกาศใหม่
        </button>
    </div>

    <!-- Announcements List -->
    <div class="row" id="announcementsContainer">
        <!-- Loaded via JS -->
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="announceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">สร้างประกาศใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="announceForm">
                    <div class="mb-3">
                        <label class="form-label">หัวข้อประกาศ</label>
                        <input type="text" class="form-control" name="title" id="ann_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เป้าหมาย (Role)</label>
                        <select class="form-control" name="target_role" id="ann_target">
                            <option value="all">ทุกคนในโรงเรียน</option>
                            <option value="teacher">เฉพาะคุณครู</option>
                            <option value="student">เฉพาะนักเรียน</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เนื้อหาประกาศ</label>
                        <textarea class="form-control" name="content" id="ann_content" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveAnnouncement()">ส่งประกาศทันที</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_footer.php'; ?>

<script>
$(document).ready(function() {
    loadAnnouncements();
});

function loadAnnouncements() {
    $.get(BASE_URL + '/api/admin/announcements.php', function(data) {
        let html = '';
        if (data.length === 0) {
            html = '<div class="col-12 text-center py-5 text-muted">ยังไม่มีประกาศข่าวสาร</div>';
        }
        data.forEach(function(a) {
            const roleBadge = a.target_role === 'all' ? 'badge bg-secondary' : 'badge bg-info';
            html += `
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="${roleBadge}">${a.target_role}</span>
                            <small class="text-muted">${a.created_at}</small>
                        </div>
                        <h5 class="fw-bold mb-2">${a.title}</h5>
                        <p class="text-muted small">${a.content}</p>
                        <div class="text-end">
                            <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteAnnouncement(${a.id})"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        $('#announcementsContainer').html(html);
    });
}

function openAnnounceModal() {
    $('#announceForm')[0].reset();
    new bootstrap.Modal($('#announceModal')).show();
}

function saveAnnouncement() {
    const data = {
        title: $('#ann_title').val(),
        target_role: $('#ann_target').val(),
        content: $('#ann_content').val()
    };
    
    $.ajax({
        url: BASE_URL + '/api/admin/announcements.php',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(res) {
            if (res.success) {
                showToast('ส่งประกาศสำเร็จ', 'success');
                bootstrap.Modal.getInstance($('#announceModal')).hide();
                loadAnnouncements();
            }
        }
    });
}

function deleteAnnouncement(id) {
    if (!confirm('ยืนยันระบบลบประกาศ?')) return;
    $.ajax({
        url: BASE_URL + '/api/admin/announcements.php',
        method: 'DELETE',
        data: JSON.stringify({ id: id }),
        contentType: 'application/json',
        success: function(res) {
            if (res.success) {
                showToast('ลบประกาศสำเร็จ', 'success');
                loadAnnouncements();
            }
        }
    });
}
</script>
