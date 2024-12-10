<?php

// نمایش خطاها برای عیب‌یابی
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// اتصال به پایگاه داده
include './host.php';

$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("خطا در اتصال به پایگاه داده: " . $conn->connect_error);
}

// بررسی و ایجاد جدول sms_settings در صورت عدم وجود
$create_table_sql = "
    CREATE TABLE IF NOT EXISTS sms_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        uname VARCHAR(255) NOT NULL,
        pass VARCHAR(255) NOT NULL,
        `from` VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
";
if (!$conn->query($create_table_sql)) {
    die("خطا در ایجاد جدول sms_settings: " . $conn->error);
}

// بررسی اینکه آیا داده‌ای در جدول sms_settings وجود دارد
$sms_settings_sql = "SELECT uname, pass, `from` FROM sms_settings LIMIT 1";
$sms_settings_result = $conn->query($sms_settings_sql);

if ($sms_settings_result === false) {
    die("خطا در اجرای کوئری sms_settings: " . $conn->error);
}

if ($sms_settings_result->num_rows === 0) {
    // وارد کردن مقدار پیش‌فرض به جدول
    $default_sms_sql = "
        INSERT INTO sms_settings (uname, pass, `from`) 
        VALUES ('default_user', 'default_pass', '+9850002040000000')
    ";
    if (!$conn->query($default_sms_sql)) {
        die("خطا در وارد کردن مقدار پیش‌فرض به جدول sms_settings: " . $conn->error);
    }
    echo "مقدار پیش‌فرض به جدول sms_settings اضافه شد.<br>";

    // بارگذاری مجدد داده‌ها از جدول
    $sms_settings_result = $conn->query($sms_settings_sql);
}

if ($sms_settings_result->num_rows > 0) {
    $sms_settings = $sms_settings_result->fetch_assoc();
} else {
    die("جدول sms_settings خالی است.");
}

// ارسال پیامک
$url = "https://ippanel.com/services.jspd";
$rcpt_nm = array('09386215728'); // شماره پیش‌فرض گیرنده

// تنظیم پارامترها با اطلاعات دریافت‌شده از جدول
$param = array(
    'uname' => $sms_settings['uname'],
    'pass' => $sms_settings['pass'],
    'from' => $sms_settings['from'],
    'message' => 'ok',
    'to' => json_encode($rcpt_nm),
    'op' => 'send'
);

$handler = curl_init($url);
curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
$response2 = curl_exec($handler);

$response2 = json_decode($response2);
$res_code = $response2[0];
$res_data = $response2[1];

echo $res_data;

$conn->close();

?>
