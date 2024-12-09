<?php
$servername = "localhost";
$username = "root";
$password = ""; // رمز عبور را در صورت نیاز وارد کنید
$dbname = "voip_db";

// ایجاد اتصال به MySQL
$conn = new mysqli($servername, $username, $password);

// بررسی خطای اتصال
if ($conn->connect_error) {
    die("خطا در اتصال به سرور MySQL: " . $conn->connect_error);
}

// بررسی وجود دیتابیس و ایجاد آن در صورت عدم وجود
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === FALSE) {
    echo "خطا در ایجاد دیتابیس: " . $conn->error;
}

// بستن اتصال
$conn->close();
?>
