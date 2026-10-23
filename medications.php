<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

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
    <title>بصيرة - لوحة المريض</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
 <div class="dashboard-layout">
            <?php include __DIR__ . '/includes/sidebar.php';?>
<main class="dashboard-content">
    <div class="section-title">
        <h2>أدويتي</h2>
        <p>إدارة الأدوية والجرعات والمواعيد</p>
    </div></div>

<?php include __DIR__ . '/includes/footer.php';?>
</div>

<script>
    function toggleEdit(id) {
        const form = document.getElementById('edit-' + id);
        form.style.display =
            form.style.display === 'block' ? 'none' : 'block';
    }
</script>
</body>
</html>