
<?php include './../header.php'; ?>
<body class="bg-gray-100 font-Vazir">
<?php include './../sidebar.php'; ?>
  

        <!-- Main Content -->
        <div class="flex-1 p-6">

            <!-- Change Welcome Message Form -->
            <div class="mt-6 bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-bold mb-4">تغییر پیام </h3>

                <?php
                // اتصال به پایگاه داده
                $servername = "localhost";
                $username = "root";
                $password = "arayerazavi@1403";
                $dbname = "voip_db";

                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // خواندن پیام خوش‌آمدگویی از جدول messages
                $message_sql = "SELECT message FROM messages ORDER BY id DESC LIMIT 1";
                $message_result = $conn->query($message_sql);
                $current_message = '';

                if ($message_result->num_rows > 0) {
                    $row = $message_result->fetch_assoc();
                    $current_message = htmlspecialchars($row['message']);
                } else {
                    $current_message = 'پیامی موجود نیست.';
                }

                $conn->close();
                ?>

                <div class="mb-4 p-4 border border-gray-300 rounded bg-gray-50">
                    <strong>پیام قبلی:</strong> <span><?php echo $current_message; ?></span>
                </div>

                <form action="save_message.php" method="POST">
                    <label for="message" class="block text-right mb-2">متن پیام جدید:</label>
                    <textarea name="message" id="message" required class="border border-gray-300 rounded w-full p-2 mb-4"></textarea>
                    <input type="submit" value="ثبت پیام" class="bg-green-500 text-white font-bold py-2 px-4 rounded hover:bg-green-600 transition duration-200">
                </form>
            </div>

            <!-- List of Phone Numbers -->
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
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        $sql = "SELECT phone_number FROM phone_numbers";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='border-b hover:bg-gray-100 transition duration-200'>";
                                echo "<td class='px-4 py-2 text-right'>" . htmlspecialchars($row['phone_number']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td class='px-4 py-2 text-right'>هیچ شماره‌ای وجود ندارد.</td></tr>";
                        }

                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to toggle the dropdown menu in sidebar
        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownMenu = document.getElementById('dropdownMenu');

        dropdownButton.addEventListener('click', () => {
            dropdownMenu.classList.toggle('hidden');
        });

        // Close the dropdown if the user clicks outside of it
        window.onclick = function(event) {
            if (!event.target.matches('#dropdownButton')) {
                if (!dropdownMenu.classList.contains('hidden')) {
                    dropdownMenu.classList.add('hidden');
                }
            }
        };
    </script>

</body>
</html>
