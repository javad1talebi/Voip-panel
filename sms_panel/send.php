<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include './host.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("خطا در اتصال به پایگاه داده: " . $conn->connect_error);
}

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

$sms_settings_sql = "SELECT uname, pass, `from` FROM sms_settings LIMIT 1";
$sms_settings_result = $conn->query($sms_settings_sql);

if ($sms_settings_result === false) {
    die("خطا در اجرای کوئری sms_settings: " . $conn->error);
}

if ($sms_settings_result->num_rows === 0) {
    $default_sms_sql = "
        INSERT INTO sms_settings (uname, pass, `from`) 
        VALUES ('default_user', 'default_pass', '+9850002040000000')
    ";
    if (!$conn->query($default_sms_sql)) {
        die("خطا در وارد کردن مقدار پیش‌فرض به جدول sms_settings: " . $conn->error);
    }
    echo "مقدار پیش‌فرض به جدول sms_settings اضافه شد.<br>";
    $sms_settings_result = $conn->query($sms_settings_sql);
}

if ($sms_settings_result->num_rows > 0) {
    $sms_settings = $sms_settings_result->fetch_assoc();
} else {
    die("خطا: جدول sms_settings خالی باقی مانده است.");
}

$fetch_message_sql = "SELECT message FROM messages ORDER BY created_at DESC LIMIT 1";
$message_result = $conn->query($fetch_message_sql);

if ($message_result === false) {
    die("خطا در اجرای کوئری پیام‌ها: " . $conn->error);
}

if ($message_result->num_rows > 0) {
    $message_row = $message_result->fetch_assoc();
    $message = $message_row['message'];
} else {
    die("خطا: هیچ پیامی در جدول messages وجود ندارد.");
}

$url = "https://ippanel.com/services.jspd";

$rcpt_nm = array('09386215728'); // شماره تلفن‌ها
$phone_number = $rcpt_nm[0]; // شماره تلفن اول از آرایه

// بررسی شماره تلفن در جدول
$check_phone_sql = "SELECT id FROM phone_numbers WHERE phone_number = ?";
$stmt = $conn->prepare($check_phone_sql);
$stmt->bind_param('s', $phone_number); // ارسال شماره به bind_param
$stmt->execute();

$stmt->bind_result($phone_id);
$phone_exists = $stmt->fetch();
$stmt->close();

// اگر شماره تلفن موجود بود، ارسال پیام انجام نشود
if ($phone_exists) {
    echo "این شماره تلفن قبلاً در سیستم موجود است و پیام ارسال نخواهد شد.<br>";
    $conn->close();
    exit; // متوقف کردن اجرای کد
}

// اگر شماره تلفن وجود ندارد، آن را به جدول اضافه کن
$insert_phone_sql = "INSERT INTO phone_numbers (phone_number) VALUES (?)";
$insert_stmt = $conn->prepare($insert_phone_sql);
$insert_stmt->bind_param('s', $phone_number);
$insert_stmt->execute();
$insert_stmt->close();
echo "شماره تلفن به جدول phone_numbers اضافه شد.<br>";

$param = array(
    'uname' => $sms_settings['uname'],
    'pass' => $sms_settings['pass'],
    'from' => $sms_settings['from'],
    'message' => $message,
    'to' => json_encode($rcpt_nm),
    'op' => 'send'
);

$handler = curl_init($url);
curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
$response2 = curl_exec($handler);

if (curl_errno($handler)) {
    die('خطا در CURL: ' . curl_error($handler));
}

$response2 = json_decode($response2, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die('خطا در پردازش JSON: ' . json_last_error_msg());
}

if (!isset($response2[0]) || !isset($response2[1])) {
    die('پاسخ نامعتبر از سرور دریافت شد.');
}

echo $response2[1];

$conn->close();

?>
