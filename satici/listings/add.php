<?php
session_start();
require_once __DIR__.'/../../configs/database.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'satici') {
    header("Location: ../../login.php");
    exit();
}

// Create upload directory if it doesn't exist
$uploadDir = __DIR__ . '/../../uploads/vans/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$seller_id = $_SESSION['kullanici_id'];
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Validate main form fields
    $title = trim($_POST['title'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $engine_power = trim($_POST['engine_power'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $transmission = trim($_POST['transmission'] ?? '');
    $fuel_type = trim($_POST['fuel_type'] ?? '');
    $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $plate = trim($_POST['plate'] ?? '');
    $status = trim($_POST['status'] ?? 'bosta'); // Default is available

    // Basic validation
    if (empty($title)) $errors[] = "Başlık gereklidir";
    if (empty($brand)) $errors[] = "Marka gereklidir";
    if (empty($model)) $errors[] = "Model gereklidir";
    if (empty($year)) $errors[] = "Yıl gereklidir";
    if (empty($engine_power)) $errors[] = "Motor gücü gereklidir";
    if (empty($color)) $errors[] = "Renk gereklidir";
    if (empty($transmission)) $errors[] = "Vites türü gereklidir";
    if (empty($fuel_type)) $errors[] = "Yakıt türü gereklidir";
    if ($price <= 0) $errors[] = "Geçerli bir fiyat girin";
    if (empty($plate)) $errors[] = "Plaka gereklidir";
    if (empty($status)) $errors[] = "Durum gereklidir";

    // Validate uploaded images
    $uploadedImages = count(array_filter($_FILES['images']['name'] ?? []));
    if ($uploadedImages < 1) {
        $errors[] = "En az 1 resim yüklemelisiniz";
    } elseif ($uploadedImages > 10) {
        $errors[] = "Maksimum 10 resim yükleyebilirsiniz";
    }

    if (empty($errors)) {
        try {
            $databaseConnection->beginTransaction();

            // Insert van details
            $stmt = $databaseConnection->prepare(
                "INSERT INTO vans (satici_id, title, marka, model, yil, motor_gucu, renk, vites, yakit, kira_fiyat, plate, durum) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$seller_id, $title, $brand, $model, $year, $engine_power, $color, $transmission, $fuel_type, $price, $plate, $status]);
            $van_id = $databaseConnection->lastInsertId();

            // Upload images
            $primarySet = false;
            foreach ($_FILES['images']['name'] as $i => $name) {
                if (!empty($name) && $_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $fileExt = pathinfo($name, PATHINFO_EXTENSION);
                    $newFilename = "van_{$van_id}_" . uniqid() . ".{$fileExt}";
                    $targetPath = $uploadDir . $newFilename;

                    if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetPath)) {
                        $isPrimary = $primarySet ? 0 : 1;
                        $primarySet = true;

                        $stmt = $databaseConnection->prepare(
                            "INSERT INTO van_images (van_id, image_path, is_primary) 
                             VALUES (?, ?, ?)"
                        );
                        $stmt->execute([$van_id, 'uploads/vans/' . $newFilename, $isPrimary]);
                    }
                }
            }

            $databaseConnection->commit();
            $success = true;

            // ✅ Move header BEFORE any output
            header("Refresh:2; url=../dashboard.php");
        } catch (Exception $e) {
            $databaseConnection->rollBack();
            $errors[] = "Hata oluştu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <base href="/Vanlife/satici/" />
    <title>Yeni Van Ekle | VANLIFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="addstyles.css">
    <link rel="stylesheet" href="../navbar_styles.css">
</head>
<body class="bg-dark text-white">
    <?php include '../navbar.php'; ?>

    <div class="container py-5 mt-5">
        <h2 class="mb-4 text-center">Yeni Van Ekle</h2>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> Van başarıyla eklendi! Yönlendiriliyorsunuz...
            </div>
        <?php elseif (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Başlık</label>
                <input type="text" name="title" class="form-control bg-secondary text-white" required>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Marka</label>
                    <input type="text" name="brand" class="form-control bg-secondary text-white" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control bg-secondary text-white" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Plaka</label>
                    <input type="text" name="plate" class="form-control bg-secondary text-white" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Yıl</label>
                    <input type="text" name="year" class="form-control bg-secondary text-white" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Motor Gücü</label>
                    <input type="text" name="engine_power" class="form-control bg-secondary text-white" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Renk</label>
                    <input type="text" name="color" class="form-control bg-secondary text-white" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Vites Türü</label>
                    <select name="transmission" class="form-control bg-secondary text-white" required>
                        <option value="manuel">Manuel</option>
                        <option value="otomatik">Otomatik</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Yakıt Türü</label>
                    <select name="fuel_type" class="form-control bg-secondary text-white" required>
                        <option value="petrol">Petrol</option>
                        <option value="dizel">Dizel</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-control bg-secondary text-white" required>
                        <option value="bosta">Boşta</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Fiyat (₺)</label>
                <input type="number" step="0.01" name="price" class="form-control bg-secondary text-white" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Resimler (en fazla 10 adet)</label>
                <input type="file" name="images[]" class="form-control bg-secondary text-white" multiple required accept="image/*">
                <small class="text-muted">Ctrl / Cmd tuşuyla birden fazla dosya seçebilirsiniz.</small>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="submit" class="btn btn-primary">Van Ekle</button>
                <a href="../dashboard.php" class="btn btn-secondary">İptal</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
