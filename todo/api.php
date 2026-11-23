<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

function sendJson($code, $payload) {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

function validDate($d) {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}

function validTime($t) {
    return (bool)preg_match('/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', $t);
}

function saveBase64Photo($base64Str) {
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }
    $parts = explode(',', $base64Str);
    if (!isset($parts[1])) {
        return false;
    }
    $bin = base64_decode($parts[1], true);
    if ($bin === false) {
        return false;
    }
    $fileName = uniqid('foto_') . '.png';
    if (file_put_contents('uploads/' . $fileName, $bin) === false) {
        return false;
    }
    return 'uploads/' . $fileName;
}

if (!$conn) {
    sendJson(500, ['status' => 500, 'error' => 'Internal Server Error: Database connection failed']);
}

$headers = function_exists('getallheaders') ? getallheaders() : [];
$clientKey = '';
foreach ($headers as $k => $v) {
    if (strtolower($k) === 'x-api-key') {
        $clientKey = trim($v);
    }
}
if ($clientKey === '' && isset($_SERVER['HTTP_X_API_KEY'])) {
    $clientKey = trim($_SERVER['HTTP_X_API_KEY']);
}

if ($clientKey === '' || $clientKey !== $apiKey) {
    sendJson(401, ['status' => 401, 'error' => 'Unauthorized: Invalid or missing X-API-Key']);
}

$method = $_SERVER['REQUEST_METHOD'];
$allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
if (!in_array($method, $allowedMethods)) {
    header('Allow: GET, POST, PUT, PATCH, DELETE');
    sendJson(405, ['status' => 405, 'error' => 'Method Not Allowed']);
}

$rawBody = file_get_contents('php://input');
$jsonBody = json_decode($rawBody, true);
if (!is_array($jsonBody)) {
    $jsonBody = [];
}

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            sendJson(400, ['status' => 400, 'error' => 'Bad Request: Invalid ID parameter']);
        }
        $stmt = mysqli_prepare($conn, "SELECT id, tgl, nama, jam, jam_notif, foto, selesai FROM todos WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        if (!$row) {
            sendJson(404, ['status' => 404, 'error' => 'Not Found: Todo does not exist']);
        }
        $row['id'] = (int)$row['id'];
        $row['selesai'] = (int)$row['selesai'];
        sendJson(200, ['status' => 200, 'data' => $row]);
    }

    $resAll = mysqli_query($conn, "SELECT id, tgl, nama, jam, jam_notif, foto, selesai FROM todos ORDER BY id ASC");
    if (!$resAll) {
        sendJson(500, ['status' => 500, 'error' => 'Internal Server Error: Query failed']);
    }
    $list = [];
    while ($row = mysqli_fetch_assoc($resAll)) {
        $row['id'] = (int)$row['id'];
        $row['selesai'] = (int)$row['selesai'];
        $list[] = $row;
    }
    sendJson(200, ['status' => 200, 'data' => $list]);
}

if ($method === 'POST') {
    $tgl = trim($_POST['tgl'] ?? ($jsonBody['tgl'] ?? ''));
    $nama = trim($_POST['nama'] ?? ($jsonBody['nama'] ?? ''));
    $jam = trim($_POST['jam'] ?? ($jsonBody['jam'] ?? ''));
    $jamNotif = trim($_POST['jam_notif'] ?? ($jsonBody['jam_notif'] ?? ''));
    $fotoBase64 = trim($_POST['fotoBase64'] ?? ($jsonBody['fotoBase64'] ?? ''));
    $selesai = isset($_POST['selesai']) ? (int)$_POST['selesai'] : (isset($jsonBody['selesai']) ? (int)$jsonBody['selesai'] : 0);
    $selesai = $selesai === 1 ? 1 : 0;

    if ($tgl === '' || $nama === '' || $jam === '' || $jamNotif === '') {
        sendJson(400, ['status' => 400, 'error' => 'Bad Request: tgl, nama, jam, and jam_notif are required']);
    }
    if (!validDate($tgl) || !validTime($jam) || !validTime($jamNotif)) {
        sendJson(400, ['status' => 400, 'error' => 'Bad Request: Invalid date or time format']);
    }

    $pathFoto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            sendJson(400, ['status' => 400, 'error' => 'Bad Request: File upload error']);
        }
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowedExt)) {
            sendJson(400, ['status' => 400, 'error' => 'Bad Request: Unsupported image format']);
        }
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        $fileName = uniqid('foto_') . '.' . $ext;
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $fileName)) {
            sendJson(500, ['status' => 500, 'error' => 'Internal Server Error: Failed to save uploaded file']);
        }
        $pathFoto = 'uploads/' . $fileName;
    } elseif ($fotoBase64 !== '') {
        if (strpos($fotoBase64, 'data:image/') !== 0) {
            sendJson(400, ['status' => 400, 'error' => 'Bad Request: Invalid base64 image data']);
        }
        $saved = saveBase64Photo($fotoBase64);
        if ($saved === false) {
            sendJson(400, ['status' => 400, 'error' => 'Bad Request: Failed to decode base64 image']);
        }
        $pathFoto = $saved;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO todos (tgl, nama, jam, jam_notif, foto, selesai) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssssi", $tgl, $nama, $jam, $jamNotif, $pathFoto, $selesai);
    if (!mysqli_stmt_execute($stmt)) {
        sendJson(500, ['status' => 500, 'error' => 'Internal Server Error: Failed to create todo']);
    }

    $newId = mysqli_insert_id($conn);
    $stmtGet = mysqli_prepare($conn, "SELECT id, tgl, nama, jam, jam_notif, foto, selesai FROM todos WHERE id = ?");
    mysqli_stmt_bind_param($stmtGet, "i", $newId);
    mysqli_stmt_execute($stmtGet);
    $resNew = mysqli_stmt_get_result($stmtGet);
    $createdRow = mysqli_fetch_assoc($resNew);
    $createdRow['id'] = (int)$createdRow['id'];
    $createdRow['selesai'] = (int)$createdRow['selesai'];

    sendJson(201, [
        'status' => 201,
        'message' => 'Created: Todo successfully added',
        'data' => $createdRow
    ]);
}

if ($method === 'PUT' || $method === 'PATCH') {
    $rawId = $_GET['id'] ?? ($jsonBody['id'] ?? null);
    $id = filter_var($rawId, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        sendJson(400, ['status' => 400, 'error' => 'Bad Request: Valid ID is required']);
    }

    $stmtCheck = mysqli_prepare($conn, "SELECT * FROM todos WHERE id = ?");
    mysqli_stmt_bind_param($stmtCheck, "i", $id);
    mysqli_stmt_execute($stmtCheck);
    $resCheck = mysqli_stmt_get_result($stmtCheck);
    $existing = mysqli_fetch_assoc($resCheck);
    if (!$existing) {
        sendJson(404, ['status' => 404, 'error' => 'Not Found: Todo does not exist']);
    }

    if ($method === 'PUT') {
        $tgl = trim($jsonBody['tgl'] ?? '');
        $nama = trim($jsonBody['nama'] ?? '');
        $jam = trim($jsonBody['jam'] ?? '');
        $jamNotif = trim($jsonBody['jam_notif'] ?? '');
        $selesai = isset($jsonBody['selesai']) ? ((int)$jsonBody['selesai'] === 1 ? 1 : 0) : (int)$existing['selesai'];
        $fotoBase64 = trim($jsonBody['fotoBase64'] ?? '');

        if ($tgl === '' || $nama === '' || $jam === '' || $jamNotif === '') {
            sendJson(400, ['status' => 400, 'error' => 'Bad Request: PUT requires tgl, nama, jam, and jam_notif']);
        }
        if (!validDate($tgl) || !validTime($jam) || !validTime($jamNotif)) {
            sendJson(400, ['status' => 400, 'error' => 'Bad Request: Invalid date or time format']);
        }

        $pathFoto = $existing['foto'];
        if ($fotoBase64 !== '') {
            if (strpos($fotoBase64, 'data:image/') !== 0) {
                sendJson(400, ['status' => 400, 'error' => 'Bad Request: Invalid base64 image data']);
            }
            $saved = saveBase64Photo($fotoBase64);
            if ($saved === false) {
                sendJson(400, ['status' => 400, 'error' => 'Bad Request: Failed to decode base64 image']);
            }
            if ($pathFoto !== '' && file_exists($pathFoto)) {
                unlink($pathFoto);
            }
            $pathFoto = $saved;
        }
    } else {
        $hasField = false;
        $tgl = $existing['tgl'];
        $nama = $existing['nama'];
        $jam = $existing['jam'];
        $jamNotif = $existing['jam_notif'];
        $selesai = (int)$existing['selesai'];
        $pathFoto = $existing['foto'];

        if (array_key_exists('tgl', $jsonBody)) {
            $tgl = trim((string)$jsonBody['tgl']);
            if ($tgl === '' || !validDate($tgl)) {
                sendJson(400, ['status' => 400, 'error' => 'Bad Request: Invalid tgl format']);
            }
            $hasField = true;
        }
        if (array_key_exists('nama', $jsonBody)) {
            $nama = trim((string)$jsonBody['nama']);
            if ($nama === '') {
                sendJson(400, ['status' => 400, 'error' => 'Bad Request: nama cannot be empty']);
            }
            $hasField = true;
        }
        if (array_key_exists('jam', $jsonBody)) {
            $jam = trim((string)$jsonBody['jam']);
            if ($jam === '' || !validTime($jam)) {
                sendJson(400, ['status' => 400, 'error' => 'Bad Request: Invalid jam format']);
            }
            $hasField = true;
        }
        if (array_key_exists('jam_notif', $jsonBody)) {
            $jamNotif = trim((string)$jsonBody['jam_notif']);
            if ($jamNotif === '' || !validTime($jamNotif)) {
                sendJson(400, ['status' => 400, 'error' => 'Bad Request: Invalid jam_notif format']);
            }
            $hasField = true;
        }
        if (array_key_exists('selesai', $jsonBody)) {
            if (!in_array($jsonBody['selesai'], [0, 1, '0', '1', true, false], true)) {
                sendJson(400, ['status' => 400, 'error' => 'Bad Request: selesai must be 0 or 1']);
            }
            $selesai = $jsonBody['selesai'] ? 1 : 0;
            $hasField = true;
        }
        if (array_key_exists('fotoBase64', $jsonBody) && trim((string)$jsonBody['fotoBase64']) !== '') {
            $fotoBase64 = trim((string)$jsonBody['fotoBase64']);
            if (strpos($fotoBase64, 'data:image/') !== 0) {
                sendJson(400, ['status' => 400, 'error' => 'Bad Request: Invalid base64 image data']);
            }
            $saved = saveBase64Photo($fotoBase64);
            if ($saved === false) {
                sendJson(400, ['status' => 400, 'error' => 'Bad Request: Failed to decode base64 image']);
            }
            if ($pathFoto !== '' && file_exists($pathFoto)) {
                unlink($pathFoto);
            }
            $pathFoto = $saved;
            $hasField = true;
        }

        if (!$hasField) {
            sendJson(400, ['status' => 400, 'error' => 'Bad Request: At least one valid field is required for PATCH']);
        }
    }

    $stmtUpd = mysqli_prepare($conn, "UPDATE todos SET tgl = ?, nama = ?, jam = ?, jam_notif = ?, foto = ?, selesai = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmtUpd, "sssssii", $tgl, $nama, $jam, $jamNotif, $pathFoto, $selesai, $id);
    if (!mysqli_stmt_execute($stmtUpd)) {
        sendJson(500, ['status' => 500, 'error' => 'Internal Server Error: Failed to update todo']);
    }

    $stmtGet = mysqli_prepare($conn, "SELECT id, tgl, nama, jam, jam_notif, foto, selesai FROM todos WHERE id = ?");
    mysqli_stmt_bind_param($stmtGet, "i", $id);
    mysqli_stmt_execute($stmtGet);
    $resUpd = mysqli_stmt_get_result($stmtGet);
    $updatedRow = mysqli_fetch_assoc($resUpd);
    $updatedRow['id'] = (int)$updatedRow['id'];
    $updatedRow['selesai'] = (int)$updatedRow['selesai'];

    sendJson(200, [
        'status' => 200,
        'message' => 'OK: Todo successfully updated',
        'data' => $updatedRow
    ]);
}

if ($method === 'DELETE') {
    $rawId = $_GET['id'] ?? ($jsonBody['id'] ?? null);
    $id = filter_var($rawId, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        sendJson(400, ['status' => 400, 'error' => 'Bad Request: Valid ID is required']);
    }

    $stmtCheck = mysqli_prepare($conn, "SELECT foto FROM todos WHERE id = ?");
    mysqli_stmt_bind_param($stmtCheck, "i", $id);
    mysqli_stmt_execute($stmtCheck);
    $resCheck = mysqli_stmt_get_result($stmtCheck);
    $existing = mysqli_fetch_assoc($resCheck);
    if (!$existing) {
        sendJson(404, ['status' => 404, 'error' => 'Not Found: Todo does not exist']);
    }

    if ($existing['foto'] !== '' && file_exists($existing['foto'])) {
        unlink($existing['foto']);
    }

    $stmtDel = mysqli_prepare($conn, "DELETE FROM todos WHERE id = ?");
    mysqli_stmt_bind_param($stmtDel, "i", $id);
    if (!mysqli_stmt_execute($stmtDel)) {
        sendJson(500, ['status' => 500, 'error' => 'Internal Server Error: Failed to delete todo']);
    }

    sendJson(200, [
        'status' => 200,
        'message' => 'OK: Todo successfully deleted'
    ]);
}
