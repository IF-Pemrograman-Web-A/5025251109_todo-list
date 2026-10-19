<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List Webpage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>ToDo List</h1>
    </header>
    <main style="display: flex; gap: 20px;">
        <?php include 'menu-bar.php'; ?>
        <?php include 'content.php'; ?>
    </main>
    <footer>
        <p>Copyright Marveilleux</p>
    </footer>
    <script src="script.js"></script>
</body>
</html>