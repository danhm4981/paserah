<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$hdr_logged_in = isset($_SESSION['user_id']);
$hdr_account_type = $_SESSION['account_type'] ?? ($_SESSION['user_type'] ?? '');

$hdr_profile_url = ($hdr_account_type === 'dependent')
    ? '/danah/GProject/paserah/dependent/dep-profile.php'
    : '/danah/GProject/paserah/profile.php';
?>
<!DOCTYPE html>
<head>
    <style>
        :root {
            --primary-color: #008080;
            --primary-dark: #005a5a;
            --bg-light: #f8fcfc;
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
            line-height: 1.6;
            direction: rtl;
        }
      
        
        header {
            background-color: var(--white);
            padding: 15px 5%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            position: sticky;
            top: 0;
            z-index: 1000;
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

        .btn {
            padding: 8px 22px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            cursor: pointer;
            display: inline-block;
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

        .profile-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 1.5px solid var(--primary-color);
            background: #e6f2f2;
            color: var(--primary-color);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s;
        }

        .profile-icon:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-1px);
        }
    </style>
   <header>
        <nav>
            <div class="logo">
                           <img src="/danah/GProject/paserah/logo.png" alt="شعار بصيرة" style="height: 45px; width: auto; object-fit: contain;">
                <span>بصيرة</span>
            </div>
            <ul class="nav-links">
           <a href="/danah/GProject/paserah/index.php">الرئيسية</a>
           <a href="/danah/GProject/paserah/about.php">عن بصيرة</a>
           <a href="/danah/GProject/paserah/index.php#features">المميزات</a>
        <a href="/danah/GProject/paserah/contact.php">تواصل معنا</a>
            </ul>
            <div class="buttons">
                <?php if ($hdr_logged_in): ?>
                    <a href="<?= htmlspecialchars($hdr_profile_url, ENT_QUOTES, 'UTF-8') ?>"
                       class="profile-icon"
                       title="الملف الشخصي"
                       aria-label="الملف الشخصي">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"></path>
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="/danah/GProject/paserah/register.php" class="btn btn-outline">إنشاء حساب</a>
                    <a href="/danah/GProject/paserah/login.php" class="btn btn-primary">تسجيل الدخول</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
</body>
</html>
