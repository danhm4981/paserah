<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// التحقق من تسجيل دخول 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$message = '';
$error = '';

// معالجة عملية الدفع وإتمام الاشتراك 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $plan_name = trim($_POST['plan_name'] ?? 'باقة المتابعة الشاملة');
    $amount = trim($_POST['amount'] ?? '99.00');
    $card_number = trim($_POST['card_number'] ?? '');
    $card_expiry = trim($_POST['card_expiry'] ?? '');
    $card_cvv = trim($_POST['card_cvv'] ?? '');

    // التحقق من المدخلات
    if (empty($card_number) || empty($card_expiry) || empty($card_cvv)) {
        $error = 'يرجى تعبئة جميع بيانات البطاقة البنكية المطلوبة.';
    } elseif (strlen($card_number) < 16) {
        $error = 'رقم البطاقة غير صحيح، يجب أن يتكون من 16 رقماً.';
    } else {
        try {
            // إدخال سجل الاشتراك في قاعدة البيانات
            $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_name, amount, payment_status) VALUES (?, ?, ?, 'active')");
            $stmt->execute([$user_id, $plan_name, $amount]);

            $message = 'تمت عملية الدفع بنجاح! تم تفعيل اشتراكك في منصة بصيرة.';
        } catch (PDOException $e) {
            $error = 'حدث خطأ أثناء معالجة الاشتراك، يجى المحاولة لاحقاً.';
        }
    }
}

// تضمين الهيدر الثابت للمشروع
include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بصيرة - اشتراك ودفع الأجور</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            background-color: #f4fbfb;
            color: #2b3e42;
            font-family: 'Tajawal', sans-serif;
            margin: 0;
        }
        .subscribe-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .card {
            background: #fff;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .card h2 {
            color: #008080;
            margin-top: 0;
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center;
        }
        .intro-text {
            text-align: center;
            color: #6c7a7d;
            margin-bottom: 30px;
            font-size: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d7e3e3;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            font-family: 'Tajawal', sans-serif;
        }
        .form-group input:focus {
            border-color: #008080;
        }
        .row-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .btn-pay {
            background: #008080;
            color: white;
            border: none;
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-pay:hover {
            background: #005a5a;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        .success { background: #e6f4ea; color: #1e7e34; }
        .danger { background: #fce8e6; color: #b42318; }
        
        .plan-box {
            background: #f4fbfb;
            border: 1.5px solid #008080;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .plan-box h4 { margin: 0; color: #008080; font-size: 18px; }
        .plan-box span { font-weight: 700; font-size: 18px; color: #2b3e42; }
    </style>
</head>
<body>

<div class="subscribe-container">
    <div class="card">
        <h2>صفحة الدفع والاشتراك</h2>
        <p class="intro-text">أدخلي معلومات البطاقة البنكية لإتمام تفعيل اشتراكك في المنصة.</p>

        <?php if ($message): ?>
            <div class="alert success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert danger"><?. htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($message)): ?>
        <form method="POST">
            <!-- تفاصيل الباقة المختارة -->
            <div class="plan-box">

            <input type="hidden" name="plan_name" value="باقة المتابعة الشاملة">
            <input type="hidden" name="amount" value="99.00">

            <div class="form-group">
                <label for="card_number">رقم البطاقة البنكية</label>
                <input type="text" id="card_number" name="card_number" placeholder="4000 1234 5678 9010" maxlength="19" required>
            </div>

            <div class="row-group">
                <div class="form-group">
                    <label for="card_expiry">تاريخ الانتهاء</label>
                    <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5" required>
                </div>
                <div class="form-group">
                    <label for="card_cvv">رمز الأمان (CVV)</label>
                    <input type="password" id="card_cvv" name="card_cvv" placeholder="123" maxlength="4" required>
                </div>
            </div>

            <button type="submit" name="process_payment" class="btn-pay">تأكيد الدفع والاشتراك</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php'; 
?>

</body>
</html>
