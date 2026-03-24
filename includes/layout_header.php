<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$base_url = '/schoolai';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'ระบบบริหารจัดการโรงเรียนสาธิตวิทยา'; ?></title>
    <meta name="description" content="ระบบบริหารจัดการโรงเรียนสาธิตวิทยา - School Management System">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo $base_url; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navbar -->
    <nav class="top-navbar">
        <div class="navbar-left ps-4">
            <a href="<?php echo $base_url; ?>/index.php" class="navbar-brand-text text-decoration-none" style="color: inherit;">
                <i class="bi bi-mortarboard-fill"></i> โรงเรียนสาธิตวิทยา
            </a>
        </div>
        <div class="navbar-right">
            <?php if (isImpersonating()): ?>
            <div class="impersonation-badge">
                <i class="bi bi-eye-fill"></i> <strong>โหมดจำลอง:</strong> <?php echo htmlspecialchars(getDisplayName()); ?>
                <a href="<?php echo $base_url; ?>/api/admin/impersonate.php?action=stop" class="btn btn-sm btn-outline-warning ms-2">คืนค่าผู้บริหาร</a>
            </div>
            <?php endif; ?>

            <div class="user-info">
                <span class="user-role-badge"><?php
                    $roleMap = [
                        'admin' => 'ผู้ดูแลระบบ', 
                        'registrar' => 'นายทะเบียน', 
                        'discipline' => 'ฝ่ายปกครอง', 
                        'teacher' => 'ครู', 
                        'student' => 'นักเรียน'
                    ];
                    echo $roleMap[getUserRole()] ?? getUserRole();
                ?></span>
                <span class="user-name"><?php echo htmlspecialchars(getDisplayName()); ?></span>
            </div>
            <a href="<?php echo $base_url; ?>/logout.php" class="btn btn-logout" title="ออกจากระบบ">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </nav>

    <!-- Layout Wrapper -->
    <div class="layout-wrapper">
        <?php require_once __DIR__ . '/sidebar.php'; ?>
        <main class="main-content" id="mainContent">
            <?php if (isImpersonating()): ?>
            <div class="alert alert-warning border-0 rounded-0 mb-0 py-2 d-flex align-items-center justify-content-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                ขณะนี้คุณกำลังใช้งานในนามของ <strong><?php echo htmlspecialchars(getDisplayName()); ?></strong> ความเคลื่อนไหวทั้งหมดจะถูกบันทึกใน Audit Logs
                <a href="<?php echo $base_url; ?>/api/admin/impersonate.php?action=stop" class="alert-link ms-3"><u>หยุดการจำลอง</u></a>
            </div>
            <?php endif; ?>
