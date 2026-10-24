<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$patient_id = $_SESSION['user_id'] ?? 1;

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_dependent'])) {
    $dependent_email = trim($_POST['dependent_email']);
    $relationship = trim($_POST['relationship']);

    if (!empty($dependent_email) && !empty($relationship)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND account_type = 'dependent'");
        $stmt->execute([$dependent_email]);
        $dep_user = $stmt->fetch();

        if ($dep_user) {
            $dependent_user_id = $dep_user['user_id'];

            $stmt_insert = $pdo->prepare("INSERT INTO patient_dependents (patient_user_id, dependent_user_id, relationship, status) VALUES (?, ?, ?, 'pending')");
            
            if ($stmt_insert->execute([$patient_id, $dependent_user_id, $relationship])) {
                $message = "تم إرسال طلب الربط للتابع بنجاح!";
            } else {
                $error = "حدث خطأ أثناء إضافة التابع، أو أن الطلب مضاف مسبقاً.";
            }
        } else {
            $error = "لم يتم العثور على حساب تابع بهذا البريد الإلكتروني.";
        }
    } else {
        $error = "يرجى تعبئة جميع الحقول المطلوبة.";
    }
}

$query = "SELECT pd.id, pd.relationship, pd.status, u.username, u.email, u.phone 
          FROM patient_dependents pd 
          JOIN users u ON pd.dependent_user_id = u.user_id 
          WHERE pd.patient_user_id = ?";
$stmt_list = $pdo->prepare($query);
$stmt_list->execute([$patient_id]);
$dependents = $stmt_list->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة التوابع - بصيرة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>

        .dashboard-layout {
    display: flex;
    flex-direction: row; /* ترتيب العناصر بجانب بعضها */
    justify-content: flex-start;
    align-items: flex-start;
    min-height: calc(100vh - 140px);
    width: 100%;
    max-width: 100%; /* لجعله يمتد بالشكل المناسب على الشاشة */
    margin: 0;
}
        :root {
            --primary-color: #008080;
            --primary-dark: #005a5a;
            --bg-light: #f4fbfb;
            --text-color: #2b3e42;
            --text-muted: #6c7a7d;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-color);
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .card h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 22px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1.5px solid #dcdcdc;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }

        table th {
            background-color: var(--bg-light);
            color: var(--primary-color);
        }

        .badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 600;
        }

        .badge-active { background-color: #e6f4ea; color: #1e7e34; }
        .badge-pending { background-color: #fff3cd; color: #856404; }

        footer {
            background-color: var(--primary-color);
            color: var(--white);
            text-align: center;
            padding: 18px;
            font-size: 14px;
            margin-top: 60px;
        }
    </style>
</head>
<body>

 <div class="dashboard-layout">
            <?php include __DIR__ . '/includes/sidebar.php';?>
    <div class="container">
        <div class="card">
            <h2>إضافة تابع جديد</h2>
            
            <?php if ($message): ?>
                <div class="alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>البريد الإلكتروني للتابع:</label>
                    <input type="email" name="dependent_email" required placeholder="example@domain.com">
                </div>
                <div class="form-group">
                    <label>صلة القرابة:</label>
                    <input type="text" name="relationship" required placeholder="مثال: ابن، ابنة، مرافق">
                </div>
                <button type="submit" name="add_dependent" class="btn btn-primary">إرسال طلب ربط</button>
            </form>
        </div>

        <div class="card">
            <h2>قائمة التوابع المربوطين</h2>
            <table>
                <thead>
                    <tr>
                        <th>اسم التابع</th>
                        <th>البريد الإلكتروني</th>
                        <th>صلة القرابة</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($dependents) > 0): ?>
                        <?php foreach ($dependents as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['relationship']); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'active'): ?>
                                        <span class="badge badge-active">نشط</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">قيد الانتظار</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">لا يوجد توابع مرتبطيين حالياً.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
     </div>

    <?php include __DIR__ . '/includes/footer.php';?>


</body>
</html>
