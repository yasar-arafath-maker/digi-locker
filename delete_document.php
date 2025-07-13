<?php
session_start();
include "includes/db_connect.php";
include "includes/auth.php";

// Validate and sanitize doc_id
if (!isset($_GET['doc_id']) || !is_numeric($_GET['doc_id'])) {
    die("<div class='error'>❌ Invalid request.</div>");
}

$doc_id = intval($_GET['doc_id']);
$user_id = $_SESSION['user_id'];

// Check if the document belongs to the user
$stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    die("<div class='error'>❌ Document not found or access denied.</div>");
}

$stmt->bind_result($file_path);
$stmt->fetch();
$stmt->close();

// Delete file from server
$file_full_path = "uploads/" . $file_path;
if (file_exists($file_full_path)) {
    unlink($file_full_path);
}

// Delete record from database
$stmt = $conn->prepare("DELETE FROM documents WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$stmt->close();

header("Location: dashboard.php");
exit;
?>
