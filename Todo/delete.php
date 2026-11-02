<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT photo, type FROM todos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['photo']) && file_exists('uploads/' . $row['photo'])) {
            unlink('uploads/' . $row['photo']);
        }
        
        $del_stmt = $conn->prepare("DELETE FROM todos WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        $del_stmt->execute();
        
        $page = $row['type'];
    } else {
        $page = 'personal';
    }
    
    header("Location: index.php?page=" . $page);
    exit();
}
?>