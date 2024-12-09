<?php
// نمایش خطاهای PHP برای عیب‌یابی
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// اتصال به پایگاه داده
include './host.php';

$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// شماره تلفن برای بررسی
$phone_number = preg_replace('/[^0-9]/', '', "09363685728");

// بررسی اینکه آیا شماره تلفن وجود دارد
$sql = "SELECT * FROM phone_numbers WHERE phone_number = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("خطا در آماده‌سازی کوئری SELECT: " . $conn->error);
}

$stmt->bind_param("s", $phone_number);
$stmt->execute();
$stmt->store_result(); // ذخیره نتایج

if ($stmt->num_rows > 0) {
    // اگر شماره تلفن قبلاً وجود دارد
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

        if ($message_result->num_rows > 0) {
            $row = $message_result->fetch_assoc();
            $welcome_message = $row['message'];

            // ارسال پیامک خوش‌آمدگویی
            if (sendSMS([$phone_number], $welcome_message)) {
                echo "پیام خوش‌آمدگویی ارسال شد: " . $welcome_message . "<br>";
            } else {
                echo "خطا در ارسال پیام خوش‌آمدگویی.<br>";
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
function sendSMS($phone_numbers, $message) {
    $url = "https://ippanel.com/services.jspd";

    $param = array(
        'uname' => 'berelianco',
        'pass' => 'Mahdi12!@T',
        'from' => '+9850002040000000',
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

    $response2 = curl_exec($handler);

    if (curl_errno($handler)) {
        echo 'cURL Error: ' . curl_error($handler) . "<br>";
        return false;
    } else {
        $response2 = json_decode($response2, true);
        echo "Response: " . print_r($response2, true) . "<br>";
        return true;
    }

    curl_close($handler);
}
?>
