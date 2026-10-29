<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    exit('المستخدم غير موجود.');
}

$account_type = $user['account_type'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($username === '' || $phone === '') {
        $error = 'الرجاء ملء جميع الحقول المطلوبة.';
    } else {
        $update_stmt = $pdo->prepare("UPDATE users SET username = ?, phone = ? WHERE user_id = ?");
        $update_stmt->execute([$username, $phone, $user_id]);
        
        $user['username'] = $username;
        $user['phone'] = $phone;
        $message = 'تم تحديث الملف الشخصي بنجاح.';
    }
}

$stat1_val = 0; $stat1_label = '';
$stat2_val = 0; $stat2_label = '';
$relationship = '';

if ($account_type === 'patient') {
    $stmt_dep = $pdo->prepare("SELECT COUNT(*) FROM patient_dependents WHERE patient_user_id = ? AND status = 'active'");
    $stmt_dep->execute([$user_id]);
    $stat1_val = $stmt_dep->fetchColumn();
    $stat1_label = 'المتابعون';

    $stmt_med = $pdo->prepare("SELECT COUNT(*) FROM medications WHERE user_id = ?");
    $stmt_med->execute([$user_id]);
    $stat2_val = $stmt_med->fetchColumn();
    $stat2_label = 'إجمالي الأدوية';
} else if ($account_type === 'dependent') {
    $stmt_pat = $pdo->prepare("SELECT COUNT(*) FROM patient_dependents WHERE dependent_user_id = ? AND status = 'active'");
    $stmt_pat->execute([$user_id]);
    $stat1_val = $stmt_pat->fetchColumn();
    $stat1_label = 'المرضى المرتبطون';

    $stmt_rel = $pdo->prepare("SELECT relationship FROM patient_dependents WHERE dependent_user_id = ? LIMIT 1");
    $stmt_rel->execute([$user_id]);
    $relationship = $stmt_rel->fetchColumn() ?: 'غير متوفرة';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بصيرة - الملف الشخصي</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <style>
           .dashboard-layout {
    display: flex;
    flex-direction: row; 
    justify-content: flex-start;
    align-items: flex-start;
    min-height: calc(100vh - 140px);
    width: 100%;
    max-width: 100%;
    margin: 0;
}
        .main-content {
            flex: 1;
            padding: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .page-header-title {
            color: var(--text-color);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .card {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 25px;
        }

        .profile-header-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 25px;
            margin-bottom: 25px;
        }

        .user-main-details {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .avatar-box {
            width: 75px;
            height: 75px;
            background: #e6f2f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        .user-text h2 {
            font-size: 22px;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .user-text p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 5px;
        }

        .badge-type {
            display: inline-block;
            background: #e6f4ea;
            color: #1e7e34;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .stats-mini-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-mini-card {
            background: var(--bg-light);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-mini-card h3 {
            color: var(--primary-color);
            font-size: 22px;
            margin-bottom: 5px;
        }

        .stat-mini-card p {
            color: var(--text-muted);
            font-size: 13px;
        }

        .info-section-title {
            font-size: 18px;
            color: var(--text-color);
            margin-bottom: 20px;
            font-weight: 700;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f4f4;
            font-size: 15px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .info-value {
            color: var(--text-color);
            font-weight: 700;
        }

        .edit-form {
            display: none;
            margin-top: 20px;
            margin-bottom: 25px;
            padding: 20px;
            background: #fdfdfd;
            border: 1px solid #eef2f2;
            border-radius: 12px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d7e3e3;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn {
            padding: 8px 22px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            cursor: pointer;
            display: inline-block;
            text-align: center;
            border: none;
        }

        .btn-outline {
            border: 1.5px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .success { background: #e6f4ea; color: #1e7e34; }
        .danger { background: #fce8e6; color: #b42318; }
    </style>
</head>
<body>
 <div class="dashboard-layout">
            <?php include __DIR__ . '/includes/sidebar.php';?>
   
        <main class="main-content">
            <h1 class="page-header-title">الملف الشخصي</h1>

            <?php if ($message): ?>
                <div class="alert success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="profile-header-info">
                    <div class="user-main-details">
                        <div class="avatar-box">👤</div>
                        <div class="user-text">
                            <h2><?= htmlspecialchars($user['username']) ?></h2>
                            <p><?= htmlspecialchars($user['email']) ?></p>
                            <span class="badge-type">
                                <?= $account_type === 'patient' ? 'حساب مريض نشط' : 'حساب تابع نشط' ?>
                            </span>
                        </div>
                    </div>
                    <button class="btn btn-outline" onclick="toggleEditForm()">تعديل الملف</button>
                </div>

                <div class="stats-mini-grid">
                    <?php if ($account_type === 'patient'): ?>
                        <div class="stat-mini-card">
                            <h3><?= $stat1_val ?></h3>
                            <p><?= $stat1_label ?></p>
                        </div>
                        <div class="stat-mini-card">
                            <h3><?= $stat2_val ?></h3>
                            <p><?= $stat2_label ?></p>
                        </div>
                    <?php else: ?>
                        <div class="stat-mini-card">
                            <h3><?= $stat1_val ?></h3>
                            <p><?= $stat1_label ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div id="editFormSection" class="edit-form">
                    <h3 class="info-section-title">تعديل بيانات الحساب</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">الاسم الكامل / اسم المستخدم</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">رقم الجوال</label>
                            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">حفظ التعديلات</button>
                    </form>
                </div>

                <h3 class="info-section-title">المعلومات الشخصية</h3>
                <div class="info-row">
                    <span class="info-label">الاسم الكامل</span>
                    <span class="info-value"><?= htmlspecialchars($user['username']) ?></span>
                </div>
                
                <?php if ($account_type === 'patient'): ?>
                <div class="info-row">
                    <span class="info-label">تاريخ الميلاد</span>
                    <span class="info-value"><?= htmlspecialchars($user['birth_date']) ?></span>
                </div>
                <?php endif; ?>

                <div class="info-row">
                    <span class="info-label">رقم الجوال</span>
                    <span class="info-value"><?= htmlspecialchars($user['phone']) ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">البريد الإلكتروني</span>
                    <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                </div>

                <?php if ($account_type === 'dependent'): ?>
                <div class="info-row">
                    <span class="info-label">صلة القرابة بالمريض</span>
                    <span class="info-value"><?= htmlspecialchars($relationship) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        function toggleEditForm() {
            const formSection = document.getElementById('editFormSection');
            if (formSection.style.display === 'block') {
                formSection.style.display = 'none';
            } else {
                formSection.style.display = 'block';
            }
        }
    </script>
</body>
</html>
