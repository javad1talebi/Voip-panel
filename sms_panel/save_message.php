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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $welcome_message = $_POST['message'];

    // بررسی وجود پیام در جدول
    $check_sql = "SELECT message FROM messages LIMIT 1"; 
    $check_stmt = $conn->prepare($check_sql);

    // بررسی صحت آماده‌سازی کوئری
    if (!$check_stmt) {
        die("خطا در آماده‌سازی کوئری: " . $conn->error);
    }

    $check_stmt->execute();
    $check_stmt->store_result();  // ذخیره نتایج برای جلوگیری از مشکلات همزمانی

    if ($check_stmt->num_rows > 0) {
        // اگر پیام وجود دارد، به‌روزرسانی انجام می‌شود
        $update_sql = "UPDATE messages SET message = ? WHERE message IS NOT NULL"; 
        $update_stmt = $conn->prepare($update_sql);

        // بررسی صحت آماده‌سازی کوئری UPDATE
        if (!$update_stmt) {
            die("خطا در آماده‌سازی کوئری UPDATE: " . $conn->error);
        }

        $update_stmt->bind_param("s", $welcome_message);

        if ($update_stmt->execute()) {
            // هدایت به صفحه پس از موفقیت
            header("Location: ./sms_panel.php");
            exit();
        } else {
            echo "خطا در به‌روزرسانی پیام خوش‌آمدگویی: " . $update_stmt->error;
        }

        $update_stmt->close(); 
    } else {
        // اگر پیام وجود نداشت، ایجاد می‌شود
        $insert_sql = "INSERT INTO messages (message) VALUES (?)";
        $insert_stmt = $conn->prepare($insert_sql);

        // بررسی صحت آماده‌سازی کوئری INSERT
        if (!$insert_stmt) {
            die("خطا در آماده‌سازی کوئری INSERT: " . $conn->error);
        }

        $insert_stmt->bind_param("s", $welcome_message);

        if ($insert_stmt->execute()) {
            // هدایت به صفحه پس از موفقیت
            header("Location: ./sms_panel.php");
            exit();
        } else {
            echo "خطا در ذخیره پیام خوش‌آمدگویی: " . $insert_stmt->error;
        }

        $insert_stmt->close();
    }

    $check_stmt->close(); 
}

// بستن اتصال
$conn->close();
?>
