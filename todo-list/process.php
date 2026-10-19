<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $date = $_POST['date'];
    $task_name = $_POST['task_name'];
    $time = $_POST['time'];
    $notif_time = !empty($_POST['notif_time']) ? $_POST['notif_time'] : NULL;
    
    $photo_name = NULL;
    
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    if (!empty($_POST['photo_base64'])) {
        $img = $_POST['photo_base64'];
        $image_parts = explode(";base64,", $img);
        
        if (count($image_parts) == 2) {
            $image_base64 = base64_decode($image_parts[1]);
            $photo_name = time() . '_' . uniqid() . '.png';
            $destination = 'uploads/' . $photo_name;
            file_put_contents($destination, $image_base64);
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO todos (type, task_date, task_name, task_time, notif_time, photo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $type, $date, $task_name, $time, $notif_time, $photo_name);
    $stmt->execute();
    
    header("Location: index.php?page=" . $type);
    exit();
}
?>