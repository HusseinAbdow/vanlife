<?php
session_start();
require_once __DIR__ . '/../configs/database.php';

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['rol'] != 'musteri') {
    header("Location: unauthorized.php");
    exit();
}

$customer_id = $_SESSION['kullanici_id'];
$currentDate = date('Y-m-d');

// Check if the database connection is available
if (!isset($databaseConnection)) {
    die("Database connection not found.");
}

try {
    // Query to get active rentals for the customer
    $query = "
    SELECT 
        k.kiralik_id, 
        k.kiralama_baslangıç_tarihi, 
        k.kiralama_bitiş_tarihi, 
        k.kira_tutari, 
        k.kiralama_durumu,
        v.van_id,
        v.title AS van_title, 
        v.marka, 
        v.model, 
        v.plate, 
        COALESCE(vi.image_path, v.image_path) AS image_path,
        v.yil,
        v.motor_gucu,
        v.vites,
        v.yakit,
        CONCAT(u.ad, ' ', u.soyad) AS satici_adi,
        u.telefon AS satici_telefon
    FROM kiralik k
    JOIN vans v ON k.van_id = v.van_id
    JOIN kullanici u ON k.satici_id = u.id
    LEFT JOIN van_images vi ON (v.van_id = vi.van_id AND vi.is_primary = 1)
    WHERE k.musteri_id = :customer_id 
    AND k.kiralama_durumu = 'aktif'
    ORDER BY k.kiralama_baslangıç_tarihi DESC
";

    // Prepare the query and bind parameters
    $stmt = $databaseConnection->prepare($query);
    $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch active rentals
    $activeRentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Function to calculate the remaining days of the rental
    function getDaysRemaining($endDate, $currentDate) {
        $endDate = new DateTime($endDate);
        $currentDate = new DateTime($currentDate);
        $interval = $endDate->diff($currentDate);
        return $interval->format('%a');
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktif Kiralamalar</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <link rel="stylesheet" href="navbar_styles.css" />

</head>
<body class="bg-light">
        <?php include 'navbar.php'; ?>

<div class="container py-5 mt-5"> <!-- or use mt-6 or mt-7 if you need more space -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Aktif Kiralamalar</h1>
        </div>

        <?php if (count($activeRentals) > 0): ?>
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
    <p class="card-text text-muted mb-1">
        <i class="bi bi-file-earmark-text"></i> Kiralama ID: <?= htmlspecialchars($rental['kiralik_id']) ?>
    </p>
    
    <h5 class="card-title"><?= htmlspecialchars($rental['van_title']) ?></h5>
    <p class="card-text text-muted mb-1">
        <i class="bi bi-car-front"></i> <?= htmlspecialchars($rental['marka']) ?> <?= htmlspecialchars($rental['model']) ?>
    </p>
    <p class="card-text text-muted mb-1">
        <i class="bi bi-123"></i> Plaka: <?= htmlspecialchars($rental['plate']) ?>
    </p>
    <p class="card-text text-muted mb-1">
        <i class="bi bi-calendar"></i> Yıl: <?= isset($rental['yil']) ? htmlspecialchars($rental['yil']) : 'N/A' ?>
    </p>
    <p class="card-text text-muted mb-1">
        <i class="bi bi-gear"></i> <?= isset($rental['motor_gucu']) ? htmlspecialchars($rental['motor_gucu']) : 'N/A' ?> - <?= isset($rental['vites']) ? htmlspecialchars($rental['vites']) : 'N/A' ?>
    </p>
    <p class="card-text text-muted mb-1">
        <i class="bi bi-fuel-pump"></i> <?= isset($rental['yakit']) ? htmlspecialchars($rental['yakit']) : 'N/A' ?>
    </p>

    <hr>

    <h6 class="fw-bold mt-3">Kiralama Detayları</h6>
    <p class="card-text mb-1">
        <i class="bi bi-calendar-check"></i> Başlangıç: <?= date('d.m.Y', strtotime($rental['kiralama_baslangıç_tarihi'])) ?>
    </p>
    <p class="card-text mb-1">
        <i class="bi bi-calendar-x"></i> Bitiş: <?= date('d.m.Y', strtotime($rental['kiralama_bitiş_tarihi'])) ?>
    </p>
    <p class="card-text mb-1">
        <i class="bi bi-clock"></i> Kalan Gün: <?= getDaysRemaining($rental['kiralama_bitiş_tarihi'], $currentDate) ?>
    </p>
    <p class="card-text mb-1">
        <i class="bi bi-cash-stack"></i> Tutar: <?= htmlspecialchars($rental['kira_tutari']) ?>
    </p>

    <hr>

    <h6 class="fw-bold mt-3">Araç Sahibi</h6>
    <p class="card-text mb-1">
        <i class="bi bi-person"></i> <?= htmlspecialchars($rental['satici_adi']) ?>
    </p>
    <p class="card-text mb-1">
        <i class="bi bi-telephone"></i> <?= htmlspecialchars($rental['satici_telefon']) ?>
    </p>

    <div class="mt-3">
        <span class="badge bg-success">Aktif Kiralama</span>
    </div>
</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                    <h5 class="mt-3">Aktif kiralama bulunmuyor</h5>
                    <p class="text-muted">Şu anda aktif olarak kiraladığınız herhangi bir araç bulunmamaktadır.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="class mt-5">
  <?php include '../footer.php'; ?>

</div>
</body>
</html>