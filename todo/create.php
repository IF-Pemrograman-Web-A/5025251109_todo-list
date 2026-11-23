<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tgl = $_POST['tgl'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $jam = $_POST['jam'] ?? '';
    $jamNotif = $_POST['jam_notif'] ?? '';
    $fotoBase64 = $_POST['fotoBase64'] ?? '';
    $pathFoto = '';

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $namaFile = uniqid('foto_') . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $namaFile)) {
                $pathFoto = 'uploads/' . $namaFile;
            }
        }
    } elseif ($fotoBase64 !== '' && strpos($fotoBase64, 'data:image/') === 0) {
        $bagian = explode(',', $fotoBase64);
        if (isset($bagian[1])) {
            $namaFile = uniqid('foto_') . '.png';
            if (file_put_contents('uploads/' . $namaFile, base64_decode($bagian[1])) !== false) {
                $pathFoto = 'uploads/' . $namaFile;
            }
        }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO todos (tgl, nama, jam, jam_notif, foto, selesai) VALUES (?, ?, ?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt, "sssss", $tgl, $nama, $jam, $jamNotif, $pathFoto);
    mysqli_stmt_execute($stmt);
}

header("Location: index.php?menu=shared");
exit;
