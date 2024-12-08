<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// اگر توکن وجود ندارد، تولید توکن جدید
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(16));
}


// بررسی وجود فایل‌ها برای جلوگیری از خطا
if (file_exists('verifyLicense.php')) {
    include 'verifyLicense.php';
} else {
    die("فایل verifyLicense.php پیدا نشد.");
}

if (file_exists('header.php')) {
    include 'header.php';
} else {
    die("فایل header.php پیدا نشد.");
}
?>
<body class="bg-gray-100 font-Vazir">
<div class="flex h-screen">
<?php 
if (file_exists('sidebar.php')) {
    include 'sidebar.php';
} else {
    die("فایل sidebar.php پیدا نشد.");
}
?>

<!-- Main Content -->
<div class="flex-1 p-6">
    <h2 class="text-3xl font-bold text-gray-800">
        خوش آمدید به پنل مدیریتی
    </h2>
</div>
</div>
</body>
