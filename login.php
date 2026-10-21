<?php
require_once __DIR__ . '/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

       
        header {
            background-color: var(--white);
            padding: 15px 5%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
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
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary-color);
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
            transition: all 0.3s;
            cursor: pointer;
            display: inline-block;
            text-align: center;
        }

        .btn-outline {
            border: 1.5px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            border: 1.5px solid var(--primary-color);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
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

       
        footer {
            background-color: var(--primary-dark);
            color: var(--white);
            text-align: center;
            padding: 20px;
            font-size: 13px;
            margin-top: auto;
        }

        @media(max-width: 768px) {
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

   
    <header>
        <nav>
            <div class="logo">
                <img src="logo.png" alt="شعار بصيرة">
                <span>بصيرة</span>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">الرئيسية</a></li>
                <li><a href="about.php">عن بصيرة</a></li>
                <li><a href="#features">المميزات</a></li> 
                <li><a href="contact.php">تواصل معنا</a></li>
            </ul>
            <div class="buttons">
                <a href="register.php" class="btn btn-outline">إنشاء حساب</a>
                <a href="login.php" class="btn btn-primary">تسجيل الدخول</a>
            </div>
        </nav>
    </header>

    
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

    <!-- Footer -->
    <footer>
        © 2026 بصيرة - جميع الحقوق محفوظة
    </footer>

</body>
</html>
