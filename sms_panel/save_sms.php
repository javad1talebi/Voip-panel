<?php
session_start();



unset($_SESSION['token']);
include './host.php';

$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال به پایگاه داده
if ($conn->connect_error) {
    die("اتصال به پایگاه داده با خطا مواجه شد: " . $conn->connect_error);
}

// گرفتن داده‌ها از فرم
$sms_username = $_POST['sms_username'];
$sms_password = $_POST['sms_password'];
$sms_sender = $_POST['sms_sender'];

// بررسی اینکه آیا رکوردی در جدول sms_settings وجود دارد یا خیر
$sql_check = "SELECT COUNT(*) AS count FROM sms_settings";
$result_check = $conn->query($sql_check);
$row_check = $result_check->fetch_assoc();

if ($row_check['count'] > 0) {
    // اگر رکوردی وجود دارد، آن را آپدیت می‌کنیم
    $sql_update = "UPDATE sms_settings SET uname = ?, pass = ?, `from` = ? WHERE id = 1";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sss", $sms_username, $sms_password, $sms_sender);
    $stmt->execute();
    echo "تنظیمات SMS با موفقیت آپدیت شدند.";
    header("Location: ./sms_panel.php");
    exit();
} else {
    // اگر رکوردی وجود ندارد، یک رکورد جدید ایجاد می‌کنیم
    $sql_insert = "INSERT INTO sms_settings (uname, pass, `from`) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("sss", $sms_username, $sms_password, $sms_sender);
    $stmt->execute();
    echo "تنظیمات SMS با موفقیت ذخیره شدند.";
    header("Location: ./sms_panel.php");
    exit();
}

$stmt->close();
$conn->close();
?>
