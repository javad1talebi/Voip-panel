#!/usr/bin/php -q
<?php
include 'phpagi.php';

$servername = "localhost";
$username = "root";
$password = "123";
$dbname = "voip_db";

$agi = new AGI();
$callID = $argv[1];


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    die("create_table_sql sms_settings: " . $conn->error);
}


$sms_settings_sql = "SELECT uname, pass, `from` FROM sms_settings LIMIT 1";
$sms_settings_result = $conn->query($sms_settings_sql);

if ($sms_settings_result === false) {
    die("sms_settings_result sms_settings: " . $conn->error);
}

if ($sms_settings_result->num_rows === 0) {
    $default_sms_sql = "
        INSERT INTO sms_settings (uname, pass, `from`) 
        VALUES ('default_user', 'default_pass', '+9850002040000000')
    ";
    if (!$conn->query($default_sms_sql)) {
        die("default_sms_sql sms_settings: " . $conn->error);
    }
    $sms_settings_result = $conn->query($sms_settings_sql);
}

if ($sms_settings_result->num_rows > 0) {
    $sms_settings = $sms_settings_result->fetch_assoc();
} else {
    die("sms_settings ");
}

$fetch_message_sql = "SELECT message FROM messages ORDER BY created_at DESC LIMIT 1";
$message_result = $conn->query($fetch_message_sql);

if ($message_result === false) {
    die("message_result: " . $conn->error);
}

if ($message_result->num_rows > 0) {
    $message_row = $message_result->fetch_assoc();
    $message = $message_row['message'];
} else {
    die(" messages .");
}



if(preg_match("/^09[0-9]{9}$/", $callID)) {

 $sql = "SELECT phone_number FROM phone_numbers WHERE phone_number = $callID"; 
  $result = $conn->query($sql); 
  if ($result->num_rows > 0) {
  $agi->verbose('number vojod darad');
  }
  else{
  $url = "https://ippanel.com/services.jspd";
    
    $rcpt_nm = array($callID);
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
    curl_setopt($handler, CURLOPT_POSTFIELDS, http_build_query($param));                       
    curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
    
    // Disable SSL verification (only for testing, not recommended for production)
    curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, 0);
    
    $response2 = curl_exec($handler);
    
    if (curl_errno($handler)) {
        // Handle cURL error
        echo 'cURL Error: ' . curl_error($handler);
    } else {
        $response2 = json_decode($response2, true);
       $agi->verbose('sms ersal shod'); 
}
    $insert_sql = "INSERT INTO phone_numbers (phone_number) VALUES ($callID)"; 
       if ($conn->query($insert_sql) === TRUE) {
    echo "New record created successfully";
        } else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

  }
}
else{
$agi->verbose('number vojod nadarad');
}






?>