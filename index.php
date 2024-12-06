<?php 
session_start();

// اگر توکن وجود ندارد، تولید توکن جدید
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));  // تولید توکن تصادفی
}

include 'verifyLicense.php'; 
include 'header.php';
?>
<body class="bg-gray-100 font-Vazir">
<div class="flex h-screen">
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div class="flex-1 p-6">
    <h2 class="text-3xl font-bold text-gray-800">خوش آمدید به پنل مدیریتی</h2>
   
</div>

</div>
</body>
</html>
