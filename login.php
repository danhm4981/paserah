<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        
        $stmt = $pdo->prepare("SELECT user_id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($user && password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            
            header("Location: index.php");
            exit;
        } else {
            $error = "البريد الإلكتروني أو كلمة المرور غير صحيح.";
        }
    } else {
        $error = "الرجاء تعبئة جميع الحقول.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - بصيرة</title>
   
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-light);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        main {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            flex-grow: 1;
        }

        .login-card {
            background-color: var(--white);
            padding: 35px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            border: 1px solid #e0f0f0;
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .login-card h2 {
            color: var(--text-color);
            margin-bottom: 25px;
            font-size: 24px;
            font-weight: 700;
        }

        .error-message {
            background-color: #fff0ed;
            color: #a33b2e;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 16px;
            text-align: right;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-color);
            font-weight: 700;
            font-size: 13px;
        }

        .form-group input {
            width: 100%;
            padding: 11px;
            border: 1px solid #dcdcdc;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
            background-color: #fafafa;
            color: var(--text-color);
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            background-color: var(--white);
            outline: 2px solid var(--primary-color);
        }

        .signup-link {
            margin-top: 20px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 700;
        }
        @media(max-width: 768px) {
            .nav-links { display: none; }
        }
    </style>
</head>
<body>
    <main>
        <div class="login-card">
            <h2>تسجيل الدخول</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="أدخل البريد الإلكتروني" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" minlength="8" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">تسجيل الدخول</button>
            </form>
            
            <div class="signup-link">
                ليس لديك حساب؟ <a href="register.php">إنشاء حساب</a>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.php';?>

</body>
</html>