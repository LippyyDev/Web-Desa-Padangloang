<?php
// Test file untuk memastikan PHPMailer bisa di-load
require __DIR__ . '/../vendor/autoload.php';

try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    echo "OK - PHPMailer loaded successfully!";
} catch (Exception $e) {
    echo "ERROR - " . $e->getMessage();
}


