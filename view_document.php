<?php
session_start();
include "includes/db_connect.php";
include "includes/auth.php";

// Validate and sanitize doc_id
if (!isset($_GET['doc_id']) || !is_numeric($_GET['doc_id'])) {
    die("<div class='error'>❌ Invalid document ID.</div>");
}

$doc_id = intval($_GET['doc_id']);
$user_id = $_SESSION["user_id"];

// Fetch document details from DB
$stmt = $conn->prepare("SELECT doc_type, file_path FROM documents WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    die("<div class='error'>❌ Document not found or access denied.</div>");
}

$stmt->bind_result($doc_type, $file_path);
$stmt->fetch();
$stmt->close();

// File path
$file_full_path = "uploads/" . $file_path;
$file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// Check if file exists
if (!file_exists($file_full_path)) {
    die("<div class='error'>❌ Document file not found on server.</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Document - DigiLocker</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #eee;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #000;
        }
        .error {
            background: #ff4d4d;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            color: #fff;
        }
        a {
            color: #9c88ff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📄 Viewing Document: <?php echo htmlspecialchars($doc_type); ?></h2>

    <div class="document-preview" style="margin: 20px 0;">
        <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png'])): ?>
            <img src="<?php echo htmlspecialchars($file_full_path); ?>" alt="Document Image" style="max-width: 100%; height: auto;">
        <?php elseif ($file_ext === 'pdf'): ?>
            <iframe src="<?php echo htmlspecialchars($file_full_path); ?>" width="100%" height="600px" style="border: none;"></iframe>
        <?php else: ?>
            <p>⚠️ Unsupported file format (<?php echo htmlspecialchars($file_ext); ?>).</p>
        <?php endif; ?>
    </div>

    <p><a href="dashboard.php">⬅️ Back to Dashboard</a></p>
</div>

</body>
</html>
