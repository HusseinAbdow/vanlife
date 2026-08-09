<?php
session_start();
require_once __DIR__.'/../../configs/database.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] !== 'satici') {
    header("Location: ../../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Van ID is missing.";
    exit();
}

$van_id = $_GET['id'];
$seller_id = $_SESSION['kullanici_id'];

// Check van ownership
$queryVan = $databaseConnection->prepare(
    "SELECT * FROM vans WHERE van_id = ? AND satici_id = ?"
);
$queryVan->execute([$van_id, $seller_id]);
$van = $queryVan->fetch(PDO::FETCH_ASSOC);

if (!$van) {
    echo "Van not found or unauthorized.";
    exit();
}

// Delete images from filesystem and DB
$imagesQuery = $databaseConnection->prepare(
    "SELECT * FROM van_images WHERE van_id = ?"
);
$imagesQuery->execute([$van_id]);
$images = $imagesQuery->fetchAll(PDO::FETCH_ASSOC);

foreach ($images as $image) {
    $imagePath = '../../' . $image['image_path'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    $deleteImageStmt = $databaseConnection->prepare(
        "DELETE FROM van_images WHERE id = ?"
    );
    $deleteImageStmt->execute([$image['id']]);
}

// Delete van
$deleteVanStmt = $databaseConnection->prepare(
    "DELETE FROM vans WHERE van_id = ?"
);
$deleteVanStmt->execute([$van_id]);

// Redirect immediately to dashboard after deletion
header("Location: ../dashboard.php");
exit();
