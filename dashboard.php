<?php
session_start();
include "includes/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user documents
$stmt = $conn->prepare("SELECT id, doc_type, file_path FROM documents WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>📂 My DigiLocker Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #f2f0fa;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 90%;
            max-width: 900px;
            margin: auto;
            padding: 20px;
        }
        .doc-card {
            background: #fff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(130, 90, 190, 0.2);
        }
        img.document {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        a.button {
            display: inline-block;
            padding: 6px 14px;
            margin: 10px 4px 0;
            border-radius: 5px;
            background-color: #6a4dad;
            color: white;
            text-decoration: none;
        }
        a.button:hover {
            background-color: #5b3a99;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>👋 Welcome to Your DigiLocker Dashboard</h2>
        <a href="logout.php" class="button">🚪 Logout</a>
        <a href="upload_document.php" class="button">📤 Upload New Document</a>
        <hr>

        <?php
        if ($result->num_rows === 0) {
            echo "<p>⚠️ No documents uploaded yet.</p>";
        } else {
            while ($row = $result->fetch_assoc()) {
                $doc_id = $row['id'];
                $doc_type = htmlspecialchars($row['doc_type']);
                $file_path = htmlspecialchars($row['file_path']);
                echo "
                <div class='doc-card'>
                    <h3>📄 $doc_type</h3>
                    <img src='$file_path' alt='Document Image' class='document'><br>
                    <a class='button' href='view_document.php?doc_id=$doc_id'>🔍 View</a>
                    <a class='button' href='delete_document.php?doc_id=$doc_id' onclick='return confirm(\"Are you sure you want to delete this document?\");'>🗑️ Delete</a>
                </div>";
            }
        }
        ?>
    </div>
</body>
</html>
