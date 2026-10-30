<!DOCTYPE html>
<head>
    <style>
           footer {
            background-color: var(--primary-color);
            color: var(--white);
            text-align: center;
            padding: 18px;
            font-size: 14px; }
       </style></head>
        <body>
            <footer>
        <p>© <?php echo date("Y"); ?> بصيرة - جميع الحقوق محفوظة</p>
    </footer>
<?php include __DIR__ . '/../notify-widget.php'; ?>
</body>
</html>