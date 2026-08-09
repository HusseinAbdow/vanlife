<?php
session_start();
require_once __DIR__ . '/../configs/database.php';

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'satici') {
    header("Location: ../index.php");
    exit();
}

$seller_id = $_SESSION['kullanici_id'];

// Fetch seller's active rentals
$query = "
    SELECT k.kiralik_id, k.kiralama_baslangıç_tarihi, k.kiralama_bitiş_tarihi, 
           k.kira_tutari, v.title AS van_title, v.marka, v.model, v.plate, 
           vi.image_path
    FROM kiralik k
    JOIN vans v ON k.van_id = v.van_id
    LEFT JOIN van_images vi ON v.van_id = vi.van_id AND vi.is_primary = 1
    WHERE v.satici_id = :seller_id AND k.kiralama_durumu = 'aktif'
";
$stmt = $databaseConnection->prepare($query);
$stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);

// Handle potential errors in the database query
try {
    $stmt->execute();
    $activeRentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Aktif Kiralamalar | VANLIFE</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="navbar_styles.css" />
    <link rel="stylesheet" href="dash_styles.css" />
</head>
<body class="bg-light m-0 p-0">

    <?php include 'navbar.php'; ?>

    <div class="container mt-5 pt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-dark">Aktif Kiralamalarınız</h2>
        </div>

        <?php if (empty($activeRentals)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                    <h5 class="mt-3">Şu anda aktif kiralamanız bulunmamaktadır.</h5>
                    <p class="text-muted">Müşterileriniz tarafından onaylanmış aktif kiralama işlemleri burada görünür.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($activeRentals as $rental): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm h-100">
                            <?php if ($rental['image_path']): ?>
                                <img src="../<?= htmlspecialchars($rental['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($rental['van_title']) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-image text-white fs-1"></i>
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <span class="badge bg-secondary mb-2">Kiralama ID: #<?= htmlspecialchars($rental['kiralik_id']) ?></span>

                                <h5 class="card-title"><?= htmlspecialchars($rental['van_title']) ?></h5>

                                <p class="card-text text-muted mb-1">
                                    <i class="bi bi-car-front"></i> <?= htmlspecialchars($rental['marka']) ?> <?= htmlspecialchars($rental['model']) ?>
                                </p>
                                <p class="card-text text-muted mb-1">
                                    <i class="bi bi-123"></i> Plaka: <?= htmlspecialchars($rental['plate']) ?>
                                </p>

                                <hr>

                                <?php 
                                $startDate = new DateTime($rental['kiralama_baslangıç_tarihi']);
                                $endDate = new DateTime($rental['kiralama_bitiş_tarihi']);
                                ?>

                                <h6 class="fw-bold mt-3">Kiralama Detayları</h6>
                                <p class="card-text mb-1">
                                    <i class="bi bi-calendar-check"></i> Başlangıç: <?= $startDate->format('d.m.Y') ?>
                                </p>
                                <p class="card-text mb-1">
                                    <i class="bi bi-calendar-x"></i> Bitiş: <?= $endDate->format('d.m.Y') ?>
                                </p>
                                <p class="card-text mb-1">
                                    <i class="bi bi-cash-stack"></i> Ücret: <?= number_format($rental['kira_tutari'], 2, ',', '.') ?> ₺
                                </p>

                                <span class="badge bg-success mt-2">Aktif</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<div class="class mt-5">
  <?php include '../footer.php'; ?>

</div>
 

</body>
</html>
