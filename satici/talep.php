<?php
session_start();
require_once __DIR__.'/../configs/database.php';

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: ../auth/giris.php");
    exit();
}

if ($_SESSION['rol'] != 'satici') {
    header("Location: ../index.php");
    exit();
}

$seller_id = $_SESSION['kullanici_id'];

// Handle Accept/Reject form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['kiralik_id'])) {
    $kiralik_id = (int)$_POST['kiralik_id'];
    $action = $_POST['action'];

    // Validate action
    if ($action == 'onaylandi') {
        $status = 'aktif';
    } elseif ($action == 'reddedildi') {
        $status = 'reddedildi';
    } else {
        $_SESSION['error'] = "Geçersiz işlem!";
        header("Location: talep.php");
        exit();
    }

    try {
        // Begin transaction
        $databaseConnection->beginTransaction();

        // Update rental status
        $updateStmt = $databaseConnection->prepare("UPDATE kiralik SET kiralama_durumu = :durum WHERE kiralik_id = :kiralik_id AND satici_id = :seller_id");
        $updateStmt->execute([
            'durum' => $status,
            'kiralik_id' => $kiralik_id,
            'seller_id' => $seller_id
        ]);

        // If approved, update van status to 'kirada'
        if ($status == 'aktif') {
            $vanIdStmt = $databaseConnection->prepare("SELECT van_id FROM kiralik WHERE kiralik_id = :kiralik_id");
            $vanIdStmt->execute(['kiralik_id' => $kiralik_id]);
            $van = $vanIdStmt->fetch(PDO::FETCH_ASSOC);

            if ($van) {
                $updateVanStmt = $databaseConnection->prepare("UPDATE vans SET durum = 'kirada' WHERE van_id = :van_id");
                $updateVanStmt->execute(['van_id' => $van['van_id']]);
            }
        }

        $databaseConnection->commit();
        $_SESSION['success'] = "Talep başarıyla güncellendi!";
    } catch (PDOException $e) {
        $databaseConnection->rollBack();
        $_SESSION['error'] = "Bir hata oluştu: " . $e->getMessage();
    }

    header("Location: talep.php");
    exit();
}

// Fetch rental requests for this seller
$query = "
    SELECT 
        k.*, 
        v.title, v.marka, v.model, v.plate, v.kira_fiyat, v.ilan_numara,
        vi.image_path,
        CONCAT(ku.ad, ' ', ku.soyad) AS musteri_adi,
        ku.telefon AS musteri_telefon,
        DATEDIFF(k.kiralama_bitiş_tarihi, k.kiralama_baslangıç_tarihi) AS gun_sayisi,
        DATEDIFF(k.kiralama_bitiş_tarihi, CURDATE()) AS kalan_gun
    FROM 
        kiralik k
    JOIN 
        vans v ON k.van_id = v.van_id
    LEFT JOIN 
        van_images vi ON v.van_id = vi.van_id AND vi.is_primary = 1
    JOIN 
        kullanici ku ON k.musteri_id = ku.id
    WHERE 
        k.satici_id = :seller_id 
        AND k.kiralama_durumu IN ('beklemede', 'onaylandi', 'aktif')
    ORDER BY 
        k.kiralama_baslangıç_tarihi ASC
";

$stmt = $databaseConnection->prepare($query);
$stmt->execute(['seller_id' => $seller_id]);
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiralama Talepleri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
 <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Kiralama Talepleri</h1>
            
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($rentals)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                    <h5 class="mt-3">Henüz kiralama talebi bulunmuyor</h5>
                    <p class="text-muted">Müşterileriniz tarafından yapılan kiralama talepleri burada görünecektir.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($rentals as $rental): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm h-100">
                            <?php if ($rental['image_path']): ?>
<img src="../<?= htmlspecialchars($rental['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($rental['title']) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-image text-white fs-1"></i>
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($rental['title']) ?></h5>
                                <p class="card-text text-muted mb-1">
                                    <i class="bi bi-car-front"></i> <?= htmlspecialchars($rental['marka']) ?> <?= htmlspecialchars($rental['model']) ?>
                                </p>
                                <p class="card-text text-muted mb-1">
                                    <i class="bi bi-123"></i> İlan No: <?= htmlspecialchars($rental['ilan_numara']) ?>
                                </p>
                                <p class="card-text text-muted mb-1">
                                    <i class="bi bi-credit-card"></i> Plaka: <?= htmlspecialchars($rental['plate']) ?>
                                </p>

                                <hr>

                                <h6 class="fw-bold mt-3">Müşteri Bilgileri</h6>
                                <p class="card-text mb-1">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($rental['musteri_adi']) ?>
                                </p>
                                <p class="card-text mb-1">
                                    <i class="bi bi-telephone"></i> <?= htmlspecialchars($rental['musteri_telefon']) ?>
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
                                    <i class="bi bi-clock"></i> 
                                    <?php if ($rental['kalan_gun'] > 0): ?>
                                        <?= $rental['kalan_gun'] ?> gün kaldı
                                    <?php elseif ($rental['kalan_gun'] == 0): ?>
                                        Bugün bitiyor
                                    <?php else: ?>
                                        <?= abs($rental['kalan_gun']) ?> gün önce bitti
                                    <?php endif; ?>
                                    (<?= $rental['gun_sayisi'] ?> günlük)
                                </p>
                                <p class="card-text mb-1">
                                    <i class="bi bi-cash-stack"></i> Toplam: <?= number_format($rental['kira_fiyat'], 2) ?> ₺
                                </p>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-<?= 
                                        $rental['kiralama_durumu'] == 'beklemede' ? 'warning' : 
                                        ($rental['kiralama_durumu'] == 'aktif' ? 'success' : 
                                        ($rental['kiralama_durumu'] == 'onaylandi' ? 'info' : 'secondary')) ?>">
                                        <?= 
                                            $rental['kiralama_durumu'] == 'beklemede' ? 'Beklemede' : 
                                            ($rental['kiralama_durumu'] == 'aktif' ? 'Aktif' : 
                                            ($rental['kiralama_durumu'] == 'onaylandi' ? 'Onaylandı' : $rental['kiralama_durumu'])) ?>
                                    </span>

                                    <?php if ($rental['kiralama_durumu'] == 'beklemede'): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="kiralik_id" value="<?= $rental['kiralik_id'] ?>">
                                            <button type="submit" name="action" value="onaylandi" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i> Onayla
                                            </button>
                                            <button type="submit" name="action" value="reddedildi" class="btn btn-sm btn-danger ms-1">
                                                <i class="bi bi-x-circle"></i> Reddet
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>