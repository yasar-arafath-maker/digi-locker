<?php
include "includes/db_connect.php";
include "includes/auth.php";
require_once 'phpqrcode/qrlib.php'; // Ensure this path is correct

$user_id = $_SESSION["user_id"];
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["document"])) {
    $doc_type = $_POST["doc_type"];

    // Create upload directory if it doesn't exist
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    // Validate file extension
    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
    $original_filename = $_FILES["document"]["name"];
    $file_ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        $error = "❌ Invalid file type. Allowed types: jpg, jpeg, png, pdf.";
    } else {
        // Create unique filename
        $new_file_name = uniqid('doc_') . '.' . $file_ext;
        $target = $upload_dir . $new_file_name;

        if (move_uploaded_file($_FILES["document"]["tmp_name"], $target)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO documents (user_id, doc_type, file_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $doc_type, $new_file_name);
            $stmt->execute();

            $doc_id = $stmt->insert_id;

            // Generate document URL for QR code
            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                . "://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF']) . "/";
            $doc_url = $base_url . "view_document.php?doc_id=" . $doc_id;

            $qr_path = $upload_dir . 'qr_' . $doc_id . '.png';
            QRcode::png($doc_url, $qr_path, QR_ECLEVEL_L, 5);

            $stmt->close();

            // Show success popup
            echo <<<HTML
<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: '✅ Document uploaded successfully!',
        html: '<p>Type: <b>{$doc_type}</b></p>' +
              '<p>Scan QR to access:</p>' +
              '<img src="{$qr_path}" alt="QR Code" style="width:150px; height:150px;">',
        icon: 'success',
        confirmButtonText: 'OK'
    }).then(() => {
        window.location.href = 'dashboard.php';
    });
});
</script>
HTML;
            exit;
        } else {
            $error = "❌ Failed to upload file. Please try again.";
        }
    }
}
?>

<!-- HTML form -->
<link rel="stylesheet" href="css/style.css">
<div class="container">
    <h2>📤 Upload Your Document</h2>

    <?php if (!empty($error)): ?>
        <div class="message" style="color: #e74c3c; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>📄 Document Type</label>
        <select name="doc_type" required>
            <option value="Aadhar">🆔 Aadhar</option>
            <option value="VoterID">🗳️ Voter ID</option>
            <option value="Education">🎓 Education</option>
        </select>

        <label>📁 Select File</label>
        <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required>

        <button type="submit">🚀 Upload</button>
    </form>

    <p><a href="dashboard.php">⬅️ Back to Dashboard</a></p>
</div>
