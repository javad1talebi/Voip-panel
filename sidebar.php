<?php
$currentPage = basename($_SERVER['PHP_SELF']); // Get the current page name
?>
<!-- Sidebar -->
<div class="flex h-screen">
    <div class="w-64 bg-gray-800 text-white flex flex-col shadow-lg">
        <div class="p-4 text-center bg-gradient-to-r from-blue-600 to-blue-800 rounded-t-lg">
            <h1 class="text-2xl font-bold">پنل مدیریتی</h1>
        </div>
        <nav class="mt-6 flex-1">
            <?php if ($currentPage !== 'sms_panel.php') : ?>
                <form action="/voip/sms_panel/sms_panel.php" method="POST">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                    <button type="submit" class="flex items-center py-3 px-4 hover:bg-blue-700 transition duration-200 ease-in-out rounded-md">
                        <i class="fas fa-sms ml-2 text-lg"></i>
                        پنل پیامکی
                    </button>
                </form>
            <?php else : ?>
                <div class="flex items-center py-3 px-4 bg-blue-600 rounded-md">
                    <i class="fas fa-sms ml-2 text-lg"></i>
                    پنل پیامکی
                </div>
            <?php endif; ?>
        </nav>
        <div class="p-4 bg-gradient-to-r from-blue-600 to-blue-800 text-center rounded-b-lg">
            <a href="#" class="text-sm text-gray-300 hover:text-white transition duration-200">خروج</a>
        </div>
    </div>

