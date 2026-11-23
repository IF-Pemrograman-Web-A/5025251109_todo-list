<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    if (isset($_POST['toggle'])) {
        $selesai = (int)($_POST['selesai'] ?? 0);
        $stmt = mysqli_prepare($conn, "UPDATE todos SET selesai = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $selesai, $id);
        mysqli_stmt_execute($stmt);
        header("Location: index.php?menu=shared");
        exit;
    }

    $tgl = $_POST['tgl'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $jam = $_POST['jam'] ?? '';
    $jamNotif = $_POST['jam_notif'] ?? '';
    $fotoBase64 = $_POST['fotoBase64'] ?? '';

    $stmtLama = mysqli_prepare($conn, "SELECT foto FROM todos WHERE id = ?");
    mysqli_stmt_bind_param($stmtLama, "i", $id);
    mysqli_stmt_execute($stmtLama);
    $resLama = mysqli_stmt_get_result($stmtLama);
    $rowLama = mysqli_fetch_assoc($resLama);
    $pathFoto = $rowLama ? $rowLama['foto'] : '';

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            if ($pathFoto !== '' && file_exists($pathFoto)) {
                unlink($pathFoto);
            }
            $namaFile = uniqid('foto_') . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $namaFile)) {
                $pathFoto = 'uploads/' . $namaFile;
            }
        }
    } elseif ($fotoBase64 !== '' && strpos($fotoBase64, 'data:image/') === 0) {
        if ($pathFoto !== '' && file_exists($pathFoto)) {
            unlink($pathFoto);
        }
        $bagian = explode(',', $fotoBase64);
        if (isset($bagian[1])) {
            $namaFile = uniqid('foto_') . '.png';
            if (file_put_contents('uploads/' . $namaFile, base64_decode($bagian[1])) !== false) {
                $pathFoto = 'uploads/' . $namaFile;
            }
        }
    }

    $stmt = mysqli_prepare($conn, "UPDATE todos SET tgl = ?, nama = ?, jam = ?, jam_notif = ?, foto = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssssi", $tgl, $nama, $jam, $jamNotif, $pathFoto, $id);
    mysqli_stmt_execute($stmt);
}

header("Location: index.php?menu=shared");
exit;
