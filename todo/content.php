<main>
<?php if ($menu === 'personal'): ?>
    <section>
        <ul id="personalList"></ul>
    </section>
    <aside>
        <h2>Add Task</h2>
        <form id="personalForm">
            <p>Date</p>
            <input type="date" required>
            <p>Task Name</p>
            <input type="text" required>
            <p>Time</p>
            <input type="time" required>
            <p>Notification Time</p>
            <input type="time" required>
            <p>Take Photo</p>
            <video id="kamera" width="100%" autoplay></video>
            <button type="button" id="btnSnap" style="margin-bottom: 10px;">Capture</button>
            <canvas id="kanvas" style="display: none;"></canvas>
            <img id="hasilFoto" style="display: none; width: 100%; margin-bottom: 10px;">
            <button type="submit">Save</button>
        </form>
        <button type="button" id="btnTema">Theme Change</button>
    </aside>
<?php else: ?>
    <?php
    $editData = null;
    if (isset($_GET['edit'])) {
        $editId = (int)$_GET['edit'];
        $stmtEdit = mysqli_prepare($conn, "SELECT * FROM todos WHERE id = ?");
        mysqli_stmt_bind_param($stmtEdit, "i", $editId);
        mysqli_stmt_execute($stmtEdit);
        $resEdit = mysqli_stmt_get_result($stmtEdit);
        $editData = mysqli_fetch_assoc($resEdit);
    }
    $resAll = mysqli_query($conn, "SELECT * FROM todos ORDER BY id ASC");
    ?>
    <section>
        <ul>
            <?php while ($row = mysqli_fetch_assoc($resAll)): ?>
                <li class="<?php echo (int)$row['selesai'] === 1 ? 'completed' : ''; ?>">
                    <form action="update.php" method="POST" class="inline-form">
                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                        <input type="hidden" name="toggle" value="1">
                        <input type="hidden" name="selesai" value="<?php echo (int)$row['selesai'] === 1 ? 0 : 1; ?>">
                        <input type="checkbox" onchange="this.form.submit()" <?php echo (int)$row['selesai'] === 1 ? 'checked' : ''; ?>>
                    </form>
                    <span>
                        <?php echo htmlspecialchars($row['tgl']); ?> |
                        <?php echo htmlspecialchars($row['nama']); ?> |
                        <?php echo htmlspecialchars(substr($row['jam'], 0, 5)); ?> |
                        Notif: <?php echo htmlspecialchars(substr($row['jam_notif'], 0, 5)); ?>
                    </span>
                    <?php if ($row['foto'] !== ''): ?>
                        <img src="<?php echo htmlspecialchars($row['foto']); ?>" style="max-height: 100px; display: block; margin: 10px 0;">
                    <?php endif; ?>
                    <a href="index.php?menu=shared&edit=<?php echo (int)$row['id']; ?>" class="btn-link">Edit</a>
                    <form action="delete.php" method="POST" class="inline-form">
                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                        <button type="submit">Delete</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    </section>
    <aside>
        <h2><?php echo $editData ? 'Edit Task' : 'Add Task'; ?></h2>
        <form action="<?php echo $editData ? 'update.php' : 'create.php'; ?>" method="POST" enctype="multipart/form-data">
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?php echo (int)$editData['id']; ?>">
            <?php endif; ?>
            <p>Date</p>
            <input type="date" name="tgl" value="<?php echo $editData ? htmlspecialchars($editData['tgl']) : ''; ?>" required>
            <p>Task Name</p>
            <input type="text" name="nama" value="<?php echo $editData ? htmlspecialchars($editData['nama']) : ''; ?>" required>
            <p>Time</p>
            <input type="time" name="jam" value="<?php echo $editData ? htmlspecialchars(substr($editData['jam'], 0, 5)) : ''; ?>" required>
            <p>Notification Time</p>
            <input type="time" name="jam_notif" value="<?php echo $editData ? htmlspecialchars(substr($editData['jam_notif'], 0, 5)) : ''; ?>" required>
            <p>Upload Photo</p>
            <input type="file" name="foto" accept="image/*">
            <p>Take Photo</p>
            <video id="kamera" width="100%" autoplay></video>
            <button type="button" id="btnSnap" style="margin-bottom: 10px;">Capture</button>
            <canvas id="kanvas" style="display: none;"></canvas>
            <input type="hidden" name="fotoBase64" id="fotoBase64">
            <?php if ($editData && $editData['foto'] !== ''): ?>
                <img id="hasilFoto" src="<?php echo htmlspecialchars($editData['foto']); ?>" style="display: block; width: 100%; margin-bottom: 10px;">
            <?php else: ?>
                <img id="hasilFoto" style="display: none; width: 100%; margin-bottom: 10px;">
            <?php endif; ?>
            <button type="submit"><?php echo $editData ? 'Update' : 'Save'; ?></button>
        </form>
        <button type="button" id="btnTema">Theme Change</button>
    </aside>
<?php endif; ?>
</main>
