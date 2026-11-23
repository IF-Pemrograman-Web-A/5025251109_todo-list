<?php
$menu = (isset($_GET['menu']) && $_GET['menu'] === 'shared') ? 'shared' : 'personal';
require_once 'header.php';
require_once 'menu-bar.php';
require_once 'content.php';
require_once 'footer.php';
