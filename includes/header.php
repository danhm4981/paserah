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
        }  </style>
   <header>
        <nav>
            <div class="logo">
                <img src="logo.png" alt="شعار بصيرة" style="height: 45px; width: auto; object-fit: contain;">
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
</body>
</html>