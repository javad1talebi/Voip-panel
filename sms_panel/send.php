<?php
// نمایش خطاها برای عیب‌یابی (در محیط تولید غیرفعال کنید)
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

if ($sms_settings_result->num_rows === 0) {
    // وارد کردن یک مقدار پیش‌فرض به جدول
    $default_sms_sql = "
        INSERT INTO sms_settings (uname, pass, `from`) 
        VALUES ('default_user', 'default_pass', '+9850002040000000')
    ";
    if (!$conn->query($default_sms_sql)) {
        die("خطا در وارد کردن مقدار پیش‌فرض به جدول sms_settings: " . $conn->error);
    }
    echo "مقدار پیش‌فرض به جدول sms_settings اضافه شد.<br>";
}

// ادامه کد...

// پاکسازی و بررسی شماره تلفن
$phone_number = preg_replace('/[^0-9]/', '', "09363685728");
if (strlen($phone_number) !== 11 || !preg_match('/^09[0-9]{9}$/', $phone_number)) {
    die("شماره تلفن نامعتبر است.");
}

// بررسی وجود شماره تلفن
$sql = "SELECT * FROM phone_numbers WHERE phone_number = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("خطا در آماده‌سازی کوئری SELECT: " . $conn->error);
}

$stmt->bind_param("s", $phone_number);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "شماره تلفن قبلاً ذخیره شده است.<br>";
} else {
    // اضافه کردن شماره تلفن جدید
    $insert_sql = "INSERT INTO phone_numbers (phone_number) VALUES (?)";
    $insert_stmt = $conn->prepare($insert_sql);

    if (!$insert_stmt) {
        die("خطا در آماده‌سازی کوئری INSERT: " . $conn->error);
    }

    $insert_stmt->bind_param("s", $phone_number);

    if ($insert_stmt->execute()) {
        echo "شماره تلفن با موفقیت ذخیره شد.<br>";

        // دریافت پیام خوش‌آمدگویی
        $message_sql = "SELECT message FROM messages LIMIT 1";
        $message_result = $conn->query($message_sql);

        if ($message_result && $message_result->num_rows > 0) {
            $row = $message_result->fetch_assoc();
            $welcome_message = $row['message'];

            // دریافت اطلاعات ارسال پیامک از پایگاه داده
            $sms_data = $sms_settings_result->fetch_assoc();
            $sms_result = sendSMS([$phone_number], $welcome_message, $sms_data);
            if ($sms_result === true) {
                echo "پیام خوش‌آمدگویی ارسال شد: " . $welcome_message . "<br>";
            } else {
                echo "خطا در ارسال پیام خوش‌آمدگویی: " . $sms_result . "<br>";
            }
        } else {
            echo "پیام خوش‌آمدگویی پیدا نشد.<br>";
        }
    } else {
        echo "خطا در ذخیره شماره تلفن: " . $insert_stmt->error . "<br>";
    }

    $insert_stmt->close();
}

$stmt->close();
$conn->close();

// تابع ارسال پیامک
function sendSMS($phone_numbers, $message, $sms_data) {
    $url = "https://ippanel.com/services.jspd";

    $param = array(
        'uname' => $sms_data['uname'],
        'pass' => $sms_data['pass'],
        'from' => $sms_data['from'],
        'message' => $message,
        'to' => json_encode($phone_numbers),
        'op' => 'send'
    );

    $handler = curl_init($url);
    curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($handler, CURLOPT_POSTFIELDS, http_build_query($param));
    curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, 1);

    $response = curl_exec($handler);

    if (curl_errno($handler)) {
        return 'cURL Error: ' . curl_error($handler);
    } else {
        $response = json_decode($response, true);
        if (isset($response['status']) && $response['status'] === 'OK') {
            return true;
        } else {
            return isset($response['message']) ? $response['message'] : 'خطای نامشخص.';
        }
    }

    curl_close($handler);
}
?>
