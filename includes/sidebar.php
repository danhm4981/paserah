<!DOCTYPE html>
<head>
    <style>
         .sidebar {
            width: 260px;
            background-color: var(--white);
            padding: 30px 20px;
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            flex-shrink: 0;
        }

        .sidebar-header h2 {
            color: var(--text-color);
            font-size: 20px;
            margin-bottom: 30px;
            padding-right: 15px;
            font-weight: bold;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 18px;
            color: #557075;
            text-decoration: none;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .sidebar-menu li.active a {
            background-color: #e6f4f4;
            color: var(--primary-color);
            font-weight: bold;
        }

        .sidebar-menu li a:hover {
            background-color: #f4f9f9;
            color: var(--primary-color);
        }

        .sidebar-menu li.logout {
            margin-top: 40px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .sidebar-menu li.logout a {
            color: #d9534f;
        }

        .sidebar-menu li.logout a:hover {
            background-color: #fdf2f2;
        }
        .main-content {
            flex-grow: 1;
            padding: 40px;
        }  </style>
    </head>
<body>
        <!-- القائمة الجانبية -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>لوحة المريض</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><span class="icon">🏠</span> الرئيسية</a></li>
                <li><a href="medications.php"><span class="icon">💊</span> أدويتي</a></li>
                <li><a href="add-medication.php"><span class="icon">➕</span> إضافة دواء</a></li>
                <li><a href="notifications.php"><span class="icon">🔔</span> التنبيهات</a></li>
                <li><a href="dependents.php"><span class="icon">👥</span> التابع</a></li>
                <li><a href="subscription.php"><span class="icon">⭐</span> الاشتراك</a></li>
                <li><a href="profile.php"><span class="icon">⚙️</span> الملف الشخصي</a></li>
                <li class="logout"><a href="logout.php"><span class="icon">🚪</span> تسجيل الخروج</a></li>
            </ul>
        </aside>
</body>
</html>