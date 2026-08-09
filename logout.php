<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();

    // Redirect to satici/login.php
    header("Location: login.php");
    exit();
} else {
    // Block direct access
    header("Location: login.php");
    exit();
}
