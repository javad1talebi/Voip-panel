<?php
// اتصال به پایگاه داده
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "voip_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $welcome_message = $_POST['message'];

    $check_sql = "SELECT * FROM messages LIMIT 1"; 
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {

        $update_sql = "UPDATE messages SET message = ? WHERE message IS NOT NULL"; 
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("s", $welcome_message); 

        if ($update_stmt->execute()) {
        
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
        $insert_stmt->bind_param("s", $welcome_message);

        if ($insert_stmt->execute()) {
           
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
