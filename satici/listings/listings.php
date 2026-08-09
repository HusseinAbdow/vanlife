<?php
session_start();

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success text-center">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
?>
