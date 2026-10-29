<!DOCTYPE html>
<head>
    <style>
        .sidebar {
            width: 260px;
            background-color: var(--white, #ffffff);
            padding: 30px 20px;
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            flex-shrink: 0;
            
            /* تمدد السايدبار لطول الصفحة بالكامل */
            align-self: stretch;
            min-height: 100%;
            box-sizing: border-box;
        }

        .sidebar-header h2 {
            color: var(--text-color, #2b3e42);
            font-size: 20px;
            margin-bottom: 30px;
            padding-right: 15px;
            font-weight: bold;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            height: 100%; /* ضمان أخذ المساحة الكاملة للامتداد */
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
            
            /* خلفية مظللة خفيفة جداً لجميع المربعات بشكل دائم أو متناسق */
            background-color: rgba(0, 128, 128, 0.02); 
        }

        /* تظليل خفيف عند المرور بالفأرة */
        .sidebar-menu li a:hover {
            background-color: #eaf2f2;
            color: var(--primary-color, #008080);
        }

        /* العنصر النشط (الصفحة الحالية) */
        .sidebar-menu li.active a {
            background-color: #e6f4f4;
            color: var(--primary-color, #008080);
            font-weight: bold;
        }

        .sidebar-menu li.logout {
            margin-top: auto; /* يدفع خيار تسجيل الخروج دائماً للأسفل */
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .sidebar-menu li.logout a {
            color: #d9534f;
            background-color: rgba(217, 83, 79, 0.04); /* تظليل خفيف جداً لزر الخروج أيضاً */
        }

        .sidebar-menu li.logout a:hover {
            background-color: #fdf2f2;
        }

        .main-content {
            flex-grow: 1;
            padding: 40px;
        }
    </style>
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