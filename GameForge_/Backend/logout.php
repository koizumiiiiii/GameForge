<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    session_unset();
    session_destroy();
    header("Location: /Gameforge_/src/Main_Pages/home.php");
    exit();
}
?>