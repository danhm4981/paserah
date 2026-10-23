<?php
require_once __DIR__ . '/db.php';
<<<<<<< HEAD
include __DIR__ . '/includes/header.php';
=======
>>>>>>> 13e589139f5b5777ac133166ef3bd2eb752d5c01

// تعديل أو حذف دواء
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['delete_medicine'])) {
        $id = (int) $_POST['medication_id'];

        $stmt = $pdo->prepare(
            "DELETE FROM medications WHERE medication_id = ?"
        );
        $stmt->execute([$id]);

        header("Location: medications.php");
        exit;
    }

    if (isset($_POST['update_medicine'])) {
        $id = (int) $_POST['medication_id'];
        $name = trim($_POST['medication_name'] ?? '');
        $dose = trim($_POST['medication_shape'] ?? '');
        $description = trim($_POST['medication_description'] ?? '');

        if ($name !== '' && $dose !== '') {
            $stmt = $pdo->prepare(
                "UPDATE medications
                 SET medication_name = ?,
                     medication_shape = ?,
                     medication_description = ?
                 WHERE medication_id = ?"
            );

            $stmt->execute([$name, $dose, $description, $id]);
        }

        header("Location: medications.php");
        exit;
    }
}
<<<<<<< HEAD
=======

>>>>>>> 13e589139f5b5777ac133166ef3bd2eb752d5c01
// جلب جميع الأدوية من قاعدة البيانات
$stmt = $pdo->query(
    "SELECT medication_id, medication_name, medication_shape,
            medication_description, medication_color,
            medication_image, user_id
     FROM medications
     ORDER BY medication_id DESC"
);

$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>بصيرة - لوحة المريض</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <style>
        .dashboard-layout {
    display: flex;
    min-height: calc(100vh - 140px);
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}
=======
    <title>بصيرة | أدويتي</title>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap">

    <style>
        :root {
            --primary-color: #18a6a5;
            --text-color: #075c6d;
            --text-muted: #67858c;
            --bg-color: #f5fbfa;
            --white: #ffffff;
            --danger-bg: #fff0f0;
            --danger-color: #c65b5b;
            --border-color: #cce5e3;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        button, input {
            font-family: inherit;
        }

        button {
            cursor: pointer;
        }

        header {
            background: var(--white);
            padding: 15px 5%;
            box-shadow: 0 2px 10px rgba(0,0,0,.03);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            max-width: 1200px;
            margin: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 800;
            color: var(--primary-color);
        }

        .logo img {
            height: 45px;
            width: auto;
            object-fit: contain;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-color);
        }

        .buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 8px 22px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            border: 1.5px solid transparent;
            background: transparent;
        }

        .btn-outline {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
>>>>>>> 13e589139f5b5777ac133166ef3bd2eb752d5c01

        .btn-danger {
            background: var(--danger-bg);
            color: var(--danger-color);
        }

        .dashboard-content {
            padding: 20px 5% 40px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            flex: 1;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title h2 {
            font-size: 32px;
        }

        .section-title p {
            color: var(--text-muted);
            margin-top: 8px;
        }

        .medicine-list {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .medicine-card {
            background: var(--white);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,.05);
            overflow-wrap: anywhere;
        }

        .medicine-card h3 {
            margin-bottom: 10px;
        }

        .medicine-info {
            color: var(--text-muted);
            line-height: 2;
        }

        .medicine-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .edit-form {
            display: none;
            margin-top: 15px;
        }

        .edit-form input {
            width: 100%;
            padding: 10px;
            margin: 6px 0;
            border: 1px solid var(--border-color);
            border-radius: 10px;
        }

        .empty-message {
            text-align: center;
            color: var(--text-muted);
            margin-top: 40px;
        }

        footer {
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 18px;
            margin-top: 60px;
        }

        @media (max-width: 900px) {
            .medicine-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .nav-links {
                gap: 12px;
            }
        }

        @media (max-width: 600px) {
            nav {
                flex-direction: column;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .medicine-list {
                grid-template-columns: 1fr;
            }

            .dashboard-content {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
<<<<<<< HEAD
 <div class="dashboard-layout">
            <?php include __DIR__ . '/includes/sidebar.php';?>
=======
<header>
    <nav>
        <div class="logo">
            <img src="logo.png" alt="شعار بصيرة">
            <span>بصيرة</span>
        </div>

        <ul class="nav-links">
            <li><a href="index.php">الرئيسية</a></li>
            <li><a href="about.php">عن بصيرة</a></li>
            <li><a href="index.php#features">المميزات</a></li>
            <li><a href="contact.php">تواصل معنا</a></li>
        </ul>

        <div class="buttons">
            <a href="register.php" class="btn btn-outline">إنشاء حساب</a>
            <a href="login.php" class="btn btn-primary">تسجيل الدخول</a>
        </div>
    </nav>
</header>

>>>>>>> 13e589139f5b5777ac133166ef3bd2eb752d5c01
<main class="dashboard-content">
    <div class="section-title">
        <h2>أدويتي</h2>
        <p>إدارة الأدوية والجرعات والمواعيد</p>
<<<<<<< HEAD
    </div></div>

<?php include __DIR__ . '/includes/footer.php';?>
</div>
=======
    </div>

    
<footer>
    <p>© <?= date('Y') ?> بصيرة - جميع الحقوق محفوظة</p>
</footer>
>>>>>>> 13e589139f5b5777ac133166ef3bd2eb752d5c01

<script>
    function toggleEdit(id) {
        const form = document.getElementById('edit-' + id);
        form.style.display =
            form.style.display === 'block' ? 'none' : 'block';
    }
</script>
</body>
</html>