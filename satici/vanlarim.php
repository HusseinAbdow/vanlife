<?php
session_start();
require_once __DIR__.'/../configs/database.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'satici') {
    header("Location: ../index.php");
    exit();
}

$seller_id = $_SESSION['kullanici_id'];

// Fetch all vans for this seller (including sold ones) with like and comment counts
$vansStmt = $databaseConnection->prepare(
    "SELECT v.*, vi.image_path,
            (SELECT COUNT(*) FROM begeni WHERE van_id = v.van_id) AS like_count,
            (SELECT COUNT(*) FROM yorumlar WHERE van_id = v.van_id) AS comment_count,
            (SELECT COUNT(*) FROM kiralik WHERE van_id = v.van_id AND kiralama_durumu IN ('aktif', 'tamamlandi')) AS rental_count
     FROM vans v
     LEFT JOIN van_images vi ON v.van_id = vi.van_id AND vi.is_primary = 1
     WHERE v.satici_id = :seller_id
     ORDER BY v.is_sold ASC, v.van_id DESC"
);
$vansStmt->execute(['seller_id' => $seller_id]);
$vans = $vansStmt->fetchAll(PDO::FETCH_ASSOC);

// Count stats
$totalVans = count($vans);
$activeVans = array_filter($vans, function($van) {
    return $van['is_sold'] == 0;
});
$soldVans = $totalVans - count($activeVans);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Vanlarım | VANLIFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container py-5 mt-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="fw-bold mb-3">Vanlarım</h1>
                
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Toplam Van</h6>
                                        <h3 class="fw-bold mb-0"><?= $totalVans ?></h3>
                                    </div>
                                    <i class="bi bi-truck fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Aktif Van</h6>
                                        <h3 class="fw-bold mb-0"><?= count($activeVans) ?></h3>
                                    </div>
                                    <i class="bi bi-check-circle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Van Listesi</h3>
                    <a href="listings/add.php" class="btn btn-dark">
                        <i class="bi bi-plus-circle me-2"></i> Yeni Van Ekle
                    </a>
                </div>
                
                <!-- Van List Table -->
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Van</th>
                                    <th>Model</th>
                                    <th>Fiyat</th>
                                    <th>Beğeni/Yorum</th>
                                    <th>Kiralama</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($vans)): ?>
                                    <?php foreach($vans as $van): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if($van['image_path']): ?>
                                                    <img src="../<?= htmlspecialchars($van['image_path']) ?>" class="rounded me-3" width="60" height="40" style="object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="rounded me-3 bg-secondary d-flex align-items-center justify-content-center" width="60" height="40">
                                                        <i class="bi bi-truck text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <span><?= htmlspecialchars($van['title']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($van['marka'].' '.$van['model']) ?></td>
                                        <td>₺<?= number_format($van['kira_fiyat'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-danger me-2">
                                                <i class="bi bi-heart-fill"></i> <?= $van['like_count'] ?>
                                            </span>
                                            <span class="badge bg-dark">
                                                <i class="bi bi-chat-left-text"></i> <?= $van['comment_count'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="bi bi-clock-history"></i> <?= $van['rental_count'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($van['is_sold'] == 1): ?>
                                                <span class="badge bg-secondary">Satıldı</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="listings/view.php?id=<?= $van['van_id'] ?>" class="btn btn-sm btn-outline-dark">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="listings/edit.php?id=<?= $van['van_id'] ?>" class="btn btn-sm btn-outline-dark">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="bi bi-truck text-muted display-6 mb-3"></i>
                                            <h5>Henüz van eklemediniz</h5>
                                            <a href="listings/add.php" class="btn btn-dark mt-3">
                                                <i class="bi bi-plus-circle me-2"></i> İlk Vanınızı Ekleyin
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    

    </div>
<div class="class mt-5">
  <?php include '../footer.php'; ?>

</div>

</body>
</html>