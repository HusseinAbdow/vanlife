<?php
session_start();
require_once __DIR__.'/../../configs/database.php';

if (!isset($_GET['id'])) {
    echo "Van ID is missing.";
    exit();
}

$van_id = $_GET['id'];
$seller_id = $_SESSION['kullanici_id'];

// Fetch van data
$queryVan = $databaseConnection->prepare(
    "SELECT * FROM vans WHERE van_id = ? AND satici_id = ?"
);
$queryVan->execute([$van_id, $seller_id]);
$van = $queryVan->fetch(PDO::FETCH_ASSOC);

if (!$van) {
    echo "Van not found.";
    exit();
}

// Fetch images
$imagesQuery = $databaseConnection->prepare(
    "SELECT * FROM van_images WHERE van_id = ? ORDER BY is_primary DESC"
);
$imagesQuery->execute([$van_id]);
$images = $imagesQuery->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $marka = $_POST['marka'];
    $model = $_POST['model'];
    $kira_fiyat = $_POST['kira_fiyat'];
    $plate = $_POST['plate'];
    $yil = $_POST['yil'];
    $motor_gucu = $_POST['motor_gucu'];
    $renk = $_POST['renk'];
    $vites = $_POST['vites'];
    $yakit = $_POST['yakit'];
    $durum = $_POST['durum'];

    $updateStmt = $databaseConnection->prepare(
        "UPDATE vans SET title = ?, marka = ?, model = ?, kira_fiyat = ?, plate = ?, yil = ?, motor_gucu = ?, renk = ?, vites = ?, yakit = ?, durum = ? WHERE van_id = ?"
    );
    $updateStmt->execute([
        $title, $marka, $model, $kira_fiyat, $plate, $yil,
        $motor_gucu, $renk, $vites, $yakit, $durum, $van_id
    ]);

    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === 0) {
        $imageTmpName = $_FILES['new_image']['tmp_name'];
        $imageName = basename($_FILES['new_image']['name']);
        $imagePath = '../../uploads/vans/' . $imageName;

        if (move_uploaded_file($imageTmpName, $imagePath)) {
            $insertImage = $databaseConnection->prepare(
                "INSERT INTO van_images (van_id, image_path, is_primary) VALUES (?, ?, ?)"
            );
            $insertImage->execute([$van_id, 'uploads/vans/' . $imageName, 0]);
        }
    }

    // Set a success message and redirect to the dashboard after 2 seconds
    $success_message = "Van details updated successfully!";
    header("Refresh:0; url=../../satici/dashboard.php"); // Redirect to dashboard
    exit();
}

// Handle image deletion
if (isset($_GET['delete_image_id'])) {
    $delete_image_id = $_GET['delete_image_id'];

    $imageQuery = $databaseConnection->prepare(
        "SELECT * FROM van_images WHERE id = ? AND van_id = ?"
    );
    $imageQuery->execute([$delete_image_id, $van_id]);
    $image = $imageQuery->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        $imagePath = '../../' . $image['image_path'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $deleteImageStmt = $databaseConnection->prepare(
            "DELETE FROM van_images WHERE id = ?"
        );
        $deleteImageStmt->execute([$delete_image_id]);

        header("Location: edit.php?id=$van_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <base href="/Vanlife/satici/" />
    <title>Van Düzenle | VANLIFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="edit_styles.css">
    <link rel="stylesheet" href="../navbar_styles.css">
</head>
<body class="bg-dark text-white">

<?php include __DIR__ . '/../navbar.php'; ?>

<div class="container py-4">
    <h2 class="mb-4"><?= htmlspecialchars($van['title']) ?> - Düzenle</h2>

    <div class="row">
        <!-- Image Carousel -->
        <div class="col-md-6 mb-4">
            <div id="carouselVan" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php if ($images): ?>
                        <?php foreach ($images as $index => $image): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
<img src="../<?= htmlspecialchars($image['image_path']) ?>" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="Van Image">
                                <div class="carousel-caption d-none d-md-block">
                                    <!-- SIL button for deleting individual image -->
                                   <a href="listings/edit.php?id=<?= $van_id ?>&delete_image_id=<?= $image['id'] ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Bu resmi silmek istediğinize emin misiniz?');">
                                        Sil
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="carousel-item active">
                            <img src="../../uploads/vans/default.jpg" class="d-block w-100" alt="Default Image">
                        </div>
                    <?php endif; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselVan" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                    <span class="visually-hidden">Önceki</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselVan" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                    <span class="visually-hidden">Sonraki</span>
                </button>
            </div>
            <!-- Delete Whole Listing -->
            <div class="mt-3">
                <a href="listings/delete.php?id=<?= $van_id ?>" class="btn btn-outline-danger w-100"
                   onclick="return confirm('Bu ilanı tamamen silmek istediğinize emin misiniz?')">Tüm İlanı Sil</a>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="col-md-6">
            <h4>Van Detayları</h4>
            <form action="listings/edit.php?id=<?= $van_id ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Başlık</label>
                    <input type="text" class="form-control" id="title" name="title"
                           value="<?= htmlspecialchars($van['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="marka" class="form-label">Marka</label>
                    <input type="text" class="form-control" id="marka" name="marka"
                           value="<?= htmlspecialchars($van['marka']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="model" class="form-label">Model</label>
                    <input type="text" class="form-control" id="model" name="model"
                           value="<?= htmlspecialchars($van['model']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="kira_fiyat" class="form-label">Fiyat</label>
                    <input type="number" step="0.01" class="form-control" id="kira_fiyat" name="kira_fiyat"
                           value="<?= htmlspecialchars($van['kira_fiyat']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="plate" class="form-label">Plaka</label>
                    <input type="text" class="form-control" id="plate" name="plate"
                           value="<?= htmlspecialchars($van['plate']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="yil" class="form-label">Yıl</label>
                    <input type="text" class="form-control" id="yil" name="yil"
                           value="<?= htmlspecialchars($van['yil']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="motor_gucu" class="form-label">Motor Gücü</label>
                    <input type="text" class="form-control" id="motor_gucu" name="motor_gucu"
                           value="<?= htmlspecialchars($van['motor_gucu']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="renk" class="form-label">Renk</label>
                    <input type="text" class="form-control" id="renk" name="renk"
                           value="<?= htmlspecialchars($van['renk']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="vites" class="form-label">Vites</label>
                    <input type="text" class="form-control" id="vites" name="vites"
                           value="<?= htmlspecialchars($van['vites']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="yakit" class="form-label">Yakıt</label>
                    <input type="text" class="form-control" id="yakit" name="yakit"
                           value="<?= htmlspecialchars($van['yakit']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="durum" class="form-label">Durum</label>
                    <input type="text" class="form-control" id="durum" name="durum"
                           value="<?= htmlspecialchars($van['durum']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="new_image" class="form-label">Yeni Resim Yükle</label>
                    <input type="file" class="form-control" id="new_image" name="new_image" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary w-100">Güncelle</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
