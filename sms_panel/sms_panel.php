<?php
session_start();

// بررسی اینکه توکن در درخواست POST وجود دارد یا خیر
if (!isset($_POST['token']) || $_SESSION['token'] !== $_POST['token']) {
    // اگر توکن معتبر نباشد، هدایت به صفحه اصلی
    header('Location: /modules/voip_modules/index.php');  
    exit();
}

// حذف توکن پس از استفاده برای جلوگیری از استفاده مجدد
unset($_SESSION['token']);
include './../header.php';
?>

<body class="bg-gray-100 font-Vazir">
<?php include './../sidebar.php'; ?>

<div class="flex-1 p-6">
    <?php
    // اطلاعات پایگاه داده
    include './host.php';

    // اتصال به سرور MySQL بدون اشاره به پایگاه داده
    $conn = new mysqli($servername, $username, $password);

    // بررسی اتصال
    if ($conn->connect_error) {
        die("<div class='text-red-500'>اتصال به سرور MySQL با خطا مواجه شد: " . $conn->connect_error . "</div>");
    }

    // ایجاد پایگاه داده در صورت عدم وجود
    $create_db_sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf16 COLLATE utf16_persian_ci";
    if (!$conn->query($create_db_sql)) {
        die("<div class='text-red-500'>خطا در ایجاد پایگاه داده: " . $conn->error . "</div>");
    }

    // انتخاب پایگاه داده
    if (!$conn->select_db($dbname)) {
        die("<div class='text-red-500'>خطا در انتخاب پایگاه داده: " . $conn->error . "</div>");
    }

    // بررسی و ایجاد جدول sms_settings اگر وجود نداشته باشد
    $create_sms_settings_table = "
        CREATE TABLE IF NOT EXISTS sms_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            uname VARCHAR(255) NOT NULL,
            pass VARCHAR(255) NOT NULL,
            `from` VARCHAR(255) NOT NULL
        ) CHARACTER SET utf16 COLLATE utf16_persian_ci";
    if (!$conn->query($create_sms_settings_table)) {
        die("<div class='text-red-500'>خطا در ایجاد جدول sms_settings: " . $conn->error . "</div>");
    }

    // بررسی و ایجاد جدول messages
    $create_messages_table = "
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) CHARACTER SET utf16 COLLATE utf16_persian_ci";
    if (!$conn->query($create_messages_table)) {
        die("<div class='text-red-500'>خطا در ایجاد جدول messages: " . $conn->error . "</div>");
    }

    // بررسی و ایجاد جدول phone_numbers
    $create_phone_numbers_table = "
        CREATE TABLE IF NOT EXISTS phone_numbers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone_number VARCHAR(15) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) CHARACTER SET utf16 COLLATE utf16_persian_ci";
    if (!$conn->query($create_phone_numbers_table)) {
        die("<div class='text-red-500'>خطا در ایجاد جدول phone_numbers: " . $conn->error . "</div>");
    }

    // خواندن پیام خوش‌آمدگویی
    $message_sql = "SELECT message FROM messages ORDER BY id DESC LIMIT 1";
    $message_result = $conn->query($message_sql);
    $current_message = 'پیامی موجود نیست.';

    if ($message_result && $message_result->num_rows > 0) {
        $row = $message_result->fetch_assoc();
        $current_message = htmlspecialchars($row['message']);
    } elseif (!$message_result) {
        echo "<div class='text-red-500'>خطا در خواندن پیام: " . $conn->error . "</div>";
    }

    // دریافت تنظیمات SMS از پایگاه داده (در صورت وجود)
    $sms_username = '';
    $sms_password = '';
    $sms_sender = '';

    // خواندن تنظیمات SMS از جدول sms_settings (اگر موجود باشد)
    $sql = "SELECT uname, pass, `from` FROM sms_settings LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sms_username = $row['uname'];
        $sms_password = $row['pass'];
        $sms_sender = $row['from'];
    }
    ?>

<div class="flex space-x-6 mt-6">
    <!-- فرم تغییر پیام خوش‌آمدگویی -->
    <div class="bg-white p-6 rounded-lg shadow-md flex-1">
        <h3 class="text-xl font-bold mb-4">تغییر پیام</h3>
        <div class="mb-4 p-4 border border-gray-300 rounded bg-gray-50">
            <strong>پیام قبلی:</strong> <span><?php echo $current_message; ?></span>
        </div>
        <form action="save_message.php" method="POST">
            <label for="message" class="block text-right mb-2">متن پیام جدید:</label>
            <textarea name="message" id="message" required class="h-40 border border-gray-300 rounded w-full p-2 resize-none focus:ring-2 focus:ring-green-500"></textarea>
            <input type="submit" value="ثبت پیام" class="bg-green-500 text-white font-bold py-2 px-4 rounded hover:bg-green-600 transition duration-200 w-full">
        </form>
    </div>

    <!-- فرم تنظیمات پیامک -->
    <div class="bg-white p-6 rounded-lg shadow-md flex-1">
        <h3 class="text-xl font-bold mb-4">تنظیمات پیامک</h3>
        <form action="save_sms.php" method="POST">
            <label for="sms_username" class="block text-right mb-2">نام کاربری :</label>
            <input type="text" name="sms_username" id="sms_username" required value="<?php echo htmlspecialchars($sms_username); ?>" class="border border-gray-300 rounded w-full p-2 mb-4 focus:ring-2 focus:ring-green-500">

            <label for="sms_password" class="block text-right mb-2">رمز عبور :</label>
            <input type="text" name="sms_password" id="sms_password" required value="<?php echo htmlspecialchars($sms_password); ?>" class="border border-gray-300 rounded w-full p-2 mb-4 focus:ring-2 focus:ring-green-500">

            <label for="sms_sender" class="block text-right mb-2">فرستنده :</label>
            <input type="text" name="sms_sender" id="sms_sender" required value="<?php echo htmlspecialchars($sms_sender); ?>" class="border border-gray-300 rounded w-full p-2 mb-4 focus:ring-2 focus:ring-green-500">

            <input type="submit" value="ثبت تنظیمات " class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition duration-200 w-full">
        </form>
    </div>
</div>



    <!-- لیست شماره‌های تلفن -->
    <div class="mt-6 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold mb-4">شماره‌های تلفن ذخیره شده</h3>
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2 text-right border-b">شماره تلفن</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // خواندن شماره‌های تلفن
                $phone_sql = "SELECT phone_number FROM phone_numbers";
                $phone_result = $conn->query($phone_sql);

                if ($phone_result && $phone_result->num_rows > 0) {
                    while ($row = $phone_result->fetch_assoc()) {
                        echo "<tr class='border-b hover:bg-gray-100 transition duration-200'>";
                        echo "<td class='px-4 py-2 text-right'>" . htmlspecialchars($row['phone_number']) . "</td>";
                        echo "</tr>";
                    }
                } elseif (!$phone_result) {
                    echo "<tr><td colspan='1' class='px-4 py-2 text-right text-red-500'>خطا در دریافت شماره‌ها: " . $conn->error . "</td></tr>";
                } else {
                    echo "<tr><td class='px-4 py-2 text-right'>هیچ شماره‌ای وجود ندارد.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // مدیریت نمایش منو در نوار کناری
    const dropdownButton = document.getElementById('dropdownButton');
    const dropdownMenu = document.getElementById('dropdownMenu');

    dropdownButton?.addEventListener('click', () => {
        dropdownMenu?.classList.toggle('hidden');
    });

    // بستن منو در صورت کلیک خارج از آن
    window.addEventListener('click', (event) => {
        if (!event.target.closest('#dropdownButton')) {
            dropdownMenu?.classList.add('hidden');
        }
    });
</script>

<?php
$conn->close(); // بستن اتصال پایگاه داده بعد از انجام تمامی عملیات‌ها
?>
</body>
</html>
