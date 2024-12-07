<?php
// Load sensitive information from a configuration file or environment variables
// Load sensitive information from a configuration file or environment variables
include './host.php';

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Phone number to check
$phone_number = "09363685728";

// Prepare a statement to check if the phone number already exists
$sql = "SELECT * FROM phone_numbers WHERE phone_number = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("s", $phone_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // If the phone number already exists
    echo "شماره تلفن قبلاً ذخیره شده است.<br>";
} else {
    // Insert the phone number if it doesn't exist
    $insert_sql = "INSERT INTO phone_numbers (phone_number) VALUES (?)";
    $insert_stmt = $conn->prepare($insert_sql);
    
    if (!$insert_stmt) {
        die("Insert query preparation failed: " . $conn->error);
    }

    $insert_stmt->bind_param("s", $phone_number);

    if ($insert_stmt->execute()) {
        echo "شماره تلفن با موفقیت ذخیره شد.<br>";

        // Retrieve welcome message from the `messages` table
        $message_sql = "SELECT message FROM messages LIMIT 1";  // Add proper LIMIT
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

    $insert_stmt->close();  // Close the insert statement
}

// Close the prepared statement and the connection
$stmt->close();
$conn->close();

// تابع ارسال پیامک
function sendSMS($phone_numbers, $message) {
    $url = "https://ippanel.com/services.jspd";
    
    // تبدیل شماره‌های تلفن به آرایه
    $rcpt_nm = $phone_numbers;  
    $param = array(
        'uname' => 'berelianco',
        'pass' => 'Mahdi12!@T',
        'from' => '+9850002040000000',  // شما باید این را با یک شماره معتبر پر کنید
        'message' => $message,  // استفاده از پیام دریافتی
        'to' => json_encode($rcpt_nm),
        'op' => 'send'
    );
    
    $handler = curl_init($url);             
    curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($handler, CURLOPT_POSTFIELDS, http_build_query($param));                       
    curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
    
    // غیرفعال کردن تأیید SSL (فقط برای تست، در تولید توصیه نمی‌شود)
    curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, 0);
    
    $response2 = curl_exec($handler);
    
    if (curl_errno($handler)) {
        // مدیریت خطای cURL
        echo 'cURL Error: ' . curl_error($handler) . "<br>";
        return false;  // در صورت وجود خطا، false برمی‌گرداند
    } else {
        $response2 = json_decode($response2, true);
        echo 'ok' . "<br>";  // اگر بخواهید فقط یک پیام موفقیت چاپ کنید
        return true;  // اگر موفقیت‌آمیز بود
    }
    
    curl_close($handler);
}

?>
