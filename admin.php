<?php
include "includes/db_connect.php";
include "includes/admin_auth.php";

include "phpqrcode/qrlib.php";

// Fetch all users
$users = $conn->query("SELECT id, name, email FROM users ORDER BY id DESC");

// Fetch stats
$totalUsers = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'];
$totalDocs = $conn->query("SELECT COUNT(*) as cnt FROM documents")->fetch_assoc()['cnt'];

// Handle deletion if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user_id'])) {
        $del_id = intval($_POST['delete_user_id']);
        // Delete user documents first
        $stmt = $conn->prepare("DELETE FROM documents WHERE user_id = ?");
        $stmt->bind_param("i", $del_id);
        $stmt->execute();
        $stmt->close();
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $del_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin.php?msg=userdeleted");
        exit;
    }
    if (isset($_POST['delete_doc_id'])) {
        $doc_id = intval($_POST['delete_doc_id']);
        // Delete doc file from disk
        $stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = ?");
        $stmt->bind_param("i", $doc_id);
        $stmt->execute();
        $stmt->bind_result($file_path);
        if ($stmt->fetch()) {
            $full_path = __DIR__ . "/" . $file_path;
            if (file_exists($full_path)) unlink($full_path);
        }
        $stmt->close();
        // Delete from DB
        $stmt = $conn->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->bind_param("i", $doc_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin.php?msg=docdeleted");
        exit;
    }
}

// Fetch all documents with user info
$docs = $conn->query("SELECT d.id, d.doc_type, d.file_path, u.name as user_name FROM documents d JOIN users u ON d.user_id = u.id ORDER BY d.id DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>🛠 Admin Panel - Digi Locker</title>
<link rel="stylesheet" href="css/style.css" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  body {
    background: #4B0082;
    color: #D8BFD8;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 20px;
  }
  h1 {
    text-align: center;
    margin-bottom: 30px;
  }
  .stats {
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 40px;
  }
  th, td {
    border: 1px solid #9370DB;
    padding: 10px;
    text-align: left;
  }
  th {
    background: #7B68EE;
  }
  tr:nth-child(even) {
    background: #6A5ACD;
  }
  button.delete-btn {
    background-color: #BA55D3;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
  }
  button.delete-btn:hover {
    background-color: #9932CC;
  }
  a.logout {
    display: inline-block;
    margin-bottom: 20px;
    background: #9370DB;
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
  }
  a.logout:hover {
    background: #BA55D3;
  }
</style>
</head>
<body>

<a href="logout.php" class="logout">🚪 Logout</a>

<h1>🛠 Admin Panel - Digi Locker</h1>

<div class="stats">
  Total Users: <strong><?php echo $totalUsers; ?></strong> | Total Documents: <strong><?php echo $totalDocs; ?></strong>
</div>

<h2>Registered Users</h2>
<table>
  <thead>
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr>
  </thead>
  <tbody>
    <?php while ($user = $users->fetch_assoc()): ?>
    <tr>
      <td><?php echo $user['id']; ?></td>
      <td><?php echo htmlspecialchars($user['name']); ?></td>
      <td><?php echo htmlspecialchars($user['email']); ?></td>
      <td>
        <form method="post" class="delete-user-form" style="display:inline;">
          <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>" />
          <button type="submit" class="delete-btn" data-name="<?php echo htmlspecialchars($user['name']); ?>">Delete</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<h2>Uploaded Documents</h2>
<table>
  <thead>
    <tr><th>ID</th><th>Type</th><th>User</th><th>File</th><th>Action</th></tr>
  </thead>
  <tbody>
    <?php while ($doc = $docs->fetch_assoc()): ?>
    <tr>
      <td><?php echo $doc['id']; ?></td>
      <td><?php echo htmlspecialchars($doc['doc_type']); ?></td>
      <td><?php echo htmlspecialchars($doc['user_name']); ?></td>
      <td><a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" rel="noopener noreferrer">View</a></td>
      <td>
        <form method="post" class="delete-doc-form" style="display:inline;">
          <input type="hidden" name="delete_doc_id" value="<?php echo $doc['id']; ?>" />
          <button type="submit" class="delete-btn" data-type="<?php echo htmlspecialchars($doc['doc_type']); ?>">Delete</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<script>
document.querySelectorAll('.delete-user-form').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const userName = this.querySelector('button').dataset.name || 'this user';
    Swal.fire({
      title: `Delete ${userName}?`,
      text: "This action cannot be undone!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        this.submit();
      }
    });
  });
});

document.querySelectorAll('.delete-doc-form').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const docType = this.querySelector('button').dataset.type || 'this document';
    Swal.fire({
      title: `Delete document: ${docType}?`,
      text: "This action cannot be undone!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        this.submit();
      }
    });
  });
});

// Show success message after actions
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'userdeleted'): ?>
  Swal.fire('Deleted!', 'User was deleted successfully.', 'success');
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'docdeleted'): ?>
  Swal.fire('Deleted!', 'Document was deleted successfully.', 'success');
<?php endif; ?>
</script>

</body>
</html>

<?php
$conn->close();
?>
