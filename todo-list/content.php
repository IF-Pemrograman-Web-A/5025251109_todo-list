<?php
$page = isset($_GET['page']) && $_GET['page'] === 'shared' ? 'shared' : 'personal';

$stmt = $conn->prepare("SELECT * FROM todos WHERE type = ? ORDER BY task_date ASC, task_time ASC");
$stmt->bind_param("s", $page);
$stmt->execute();
$result = $stmt->get_result();
?>

<section class="content">
    <h2><?php echo ucfirst($page); ?> Tasks</h2>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li>
                <input type="checkbox" <?php echo $row['is_completed'] ? 'checked' : ''; ?>>
                <span>
                    <?php echo htmlspecialchars($row['task_date']); ?> | 
                    <?php echo htmlspecialchars($row['task_name']); ?> | 
                    <?php echo htmlspecialchars($row['task_time']); ?>
                </span>
                <?php if (!empty($row['photo']) && file_exists('uploads/' . $row['photo'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" style="max-height: 100px; display: block; margin: 10px 0;">
                <?php endif; ?>
                <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete task?')">Delete</a>
            </li>
        <?php endwhile; ?>
    </ul>
</section>

<aside class="task-form">
    <h2>Add Task (<?php echo ucfirst($page); ?>)</h2>
    <form action="process.php" method="POST">
        <input type="hidden" name="type" value="<?php echo $page; ?>">
        <p>Date</p>
        <input type="date" name="date" required>
        <p>Task Name</p>
        <input type="text" name="task_name" required>
        <p>Time</p>
        <input type="time" name="time" required>
        <p>Notification Time</p>
        <input type="time" name="notif_time">
        
        <p>Take Photo</p>
        <video id="kamera" width="100%" autoplay muted playsinline></video>
        <button type="button" id="btnSnap" style="margin-bottom: 10px;">Capture</button>
        <canvas id="kanvas" style="display: none;"></canvas>
        <img id="hasilFoto" style="display: none; width: 100%; margin-bottom: 10px;">
        
        <input type="hidden" name="photo_base64" id="photo_base64">

        <button type="submit">Save</button>
    </form>
    <button id="btnTema">Theme Change</button>
</aside>