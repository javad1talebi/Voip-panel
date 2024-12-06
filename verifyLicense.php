<?php
// بررسی لایسنس


// تابع برای دریافت MAC Address
function getMacAddress() {
    $output = shell_exec("getmac");
    if (!$output) {
        return 'خطا: MAC Address پیدا نشد.';
    }
    $lines = explode("\n", $output);
    foreach ($lines as $line) {
        if (preg_match('/^([0-9A-Fa-f]{2}([-:])){5}[0-9A-Fa-f]{2}/', $line, $matches)) {
            return $matches[0];
        }
    }
    return 'خطا: MAC Address معتبر پیدا نشد.';
}

// تابع برای دریافت شناسه CPU
function getCpuId() {
    $output = shell_exec("wmic cpu get ProcessorId 2>&1");
    if (!$output) {
        return 'خطا: شناسه CPU پیدا نشد.';
    }
    $lines = explode("\n", $output);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && preg_match('/^[A-F0-9]{16}$/i', $line)) {
            return $line;
        }
    }
    return 'خطا: شناسه CPU معتبر پیدا نشد.';
}

// تابع برای دریافت آیدی ترکیبی سیستم
function getSystemId() {
    $macAddress = getMacAddress();
    $cpuSerial = getCpuId();
    return $macAddress . $cpuSerial;
}

// تابع برای تولید کلید لایسنس
function generateLicenseKey($systemId) {
    $secretKey = "JavadKey"; // کلید امنیتی ثابت
    return hash('sha256', $systemId . $secretKey);
}

// تابع برای بررسی لایسنس
function verifyLicense() {
    // لایسنس ذخیره شده از فایل
    $storedLicenseKey = file_get_contents("license.key");

    $systemId = getSystemId(); // دریافت آیدی سیستم
    $generatedKey = generateLicenseKey($systemId); // تولید لایسنس جدید

    return $storedLicenseKey === $generatedKey;
}

// بررسی لایسنس
if (!verifyLicense()) {
    die("لایسنس شما نامعتبر است!");
}
?>