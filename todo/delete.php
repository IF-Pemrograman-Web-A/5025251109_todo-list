<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = mysqli_prepare($conn, "SELECT foto FROM todos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);

    if ($row && $row['foto'] !== '' && file_exists($row['foto'])) {
        unlink($row['foto']);
    }

    $del = mysqli_prepare($conn, "DELETE FROM todos WHERE id = ?");
    mysqli_stmt_bind_param($del, "i", $id);
    mysqli_stmt_execute($del);
}

header("Location: index.php?menu=shared");
exit;
