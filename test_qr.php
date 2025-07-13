<?php
require_once 'phpqrcode/qrlib.php';

// Folder to save QR codes
$folder = "uploads";

// Create folder if it doesn't exist
if (!file_exists($folder)) {
    mkdir($folder, 0777, true);
}

$filename = $folder . '/test_qr.png';

// Data to encode in QR
$data = "https://example.com";  // Change to your URL or data

// Generate QR code
QRcode::png($data, $filename, QR_ECLEVEL_L, 5);

echo "<h2>QR Code Generated:</h2>";
echo "<img src='$filename' alt='QR Code'>";
?>
