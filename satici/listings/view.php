<?php
session_start();
require_once __DIR__.'/../../configs/database.php';

// Check if the 'id' parameter is present in the URL
if (!isset($_GET['id'])) {
    header("Location: ../dashboard.php?error=missing_id");
    exit();
}

$van_id = $_GET['id'];

// Get van details with seller info
$queryVan = $databaseConnection->prepare(
    "SELECT v.*, u.telefon, u.ad as satici_ad, u.soyad as satici_soyad 
     FROM vans v 
     JOIN kullanici u ON v.satici_id = u.id 
     WHERE v.van_id = ?"
);
$queryVan->execute([$van_id]);
$van = $queryVan->fetch(PDO::FETCH_ASSOC);

// If van not found
if (!$van) {
    header("Location: ../dashboard.php?error=van_not_found");
    exit();
}

// Get the likes count
$queryLikes = $databaseConnection->prepare(
    "SELECT COUNT(*) as total_likes FROM begeni WHERE van_id = ?"
);
$queryLikes->execute([$van_id]);
$likes = $queryLikes->fetch(PDO::FETCH_ASSOC)['total_likes'];

// Get the comments for this van with user info
$queryComments = $databaseConnection->prepare(
    "SELECT y.yorum_metni, y.rating, y.olusturma_tarihi, u.ad, u.soyad 
     FROM yorumlar y
     JOIN kullanici u ON y.kullanici_id = u.id
     WHERE y.van_id = ?
     ORDER BY y.olusturma_tarihi DESC"
);
$queryComments->execute([$van_id]);
$comments = $queryComments->fetchAll(PDO::FETCH_ASSOC);

// Calculate average rating
$avgRatingQuery = $databaseConnection->prepare(
    "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
     FROM yorumlar 
     WHERE van_id = ? AND rating IS NOT NULL"
);
$avgRatingQuery->execute([$van_id]);
$ratingData = $avgRatingQuery->fetch(PDO::FETCH_ASSOC);
$averageRating = round($ratingData['avg_rating'] ?? 0, 1);
$reviewCount = $ratingData['review_count'] ?? 0;

// Get active rentals for this van
$queryRentals = $databaseConnection->prepare(
    "SELECT k.*, u.ad as musteri_ad, u.soyad as musteri_soyad, u.telefon as musteri_telefon
     FROM kiralik k
     JOIN kullanici u ON k.musteri_id = u.id
     WHERE k.van_id = ? AND k.kiralama_durumu IN ('beklemede', 'onaylandi', 'aktif')
     ORDER BY k.kiralama_durumu DESC, k.olusturma_tarihi DESC"
);
$queryRentals->execute([$van_id]);
$rentals = $queryRentals->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <base href="/Vanlife/satici/" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($van['marka'] . ' ' . $van['model']) ?> | VANLIFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href ="../navbar_styles.css">
</head>
<body class="bg-light">

    <!-- Navbar Include -->
    <?php include __DIR__ . '/../navbar.php'; ?>
    

    <div class="container mt-5 pt-4">
        <div class="card mb-4 shadow-sm">
            <div class="row g-0">
                <!-- Van Image Carousel -->
                <div class="col-md-6">
                    <div class="carousel-container" style="height: 400px; overflow: hidden; border-radius: 8px;">
                        <div id="carouselVan" class="carousel slide h-100" data-bs-ride="carousel">
                            <div class="carousel-inner h-100">
                                <?php
                                $imagesQuery = $databaseConnection->prepare(
                                    "SELECT * FROM van_images WHERE van_id = ? ORDER BY is_primary DESC"
                                );
                                $imagesQuery->execute([$van_id]);
                                $images = $imagesQuery->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($images as $index => $image):
                                ?>
                                    <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>">
                                        
                                        <img src="../<?= htmlspecialchars($image['image_path']) ?>" class="d-block w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($van['title']) ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($images) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselVan" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselVan" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Van Details -->
                <div class="col-md-6">
                    <div class="card-body">
                        <h3 class="card-title text-black"><?= htmlspecialchars($van['marka'] . ' ' . $van['model']) ?></h3>
                        
                        <!-- Rating display -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= $i <= $averageRating ? '-fill' : '' ?> text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-muted"><?= $averageRating ?> (<?= $reviewCount ?> yorum)</span>
                            </div>
                        </div>

                        <!-- Van details -->
                        <div class="row">
                            <div class="col-md-6">
                                <p class="card-text"><strong>İlan No:</strong> <?= htmlspecialchars($van['ilan_numara']) ?></p>
                                <p class="card-text"><strong>Marka:</strong> <?= htmlspecialchars($van['marka']) ?></p>
                                <p class="card-text"><strong>Model:</strong> <?= htmlspecialchars($van['model']) ?></p>
                                <p class="card-text"><strong>Yıl:</strong> <?= htmlspecialchars($van['yil']) ?></p>
                                <p class="card-text"><strong>Renk:</strong> <?= htmlspecialchars($van['renk']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="card-text"><strong>Plaka:</strong> <?= htmlspecialchars($van['plate']) ?></p>
                                <p class="card-text"><strong>Yakıt:</strong> <?= htmlspecialchars($van['yakit']) ?></p>
                                <p class="card-text"><strong>Vites:</strong> <?= htmlspecialchars($van['vites']) ?></p>
                                <p class="card-text"><strong>Motor Gücü:</strong> <?= htmlspecialchars($van['motor_gucu']) ?></p>
                                <p class="card-text"><strong>Günlük Fiyat:</strong> <?= number_format($van['kira_fiyat'], 2) ?> ₺</p>
                            </div>
                        </div>

                        <!-- Status badge -->
                        <div class="mt-3">
                            <span class="badge bg-<?= $van['durum'] === 'bosta' ? 'success' : 'danger' ?>">
                                <?= $van['durum'] === 'bosta' ? 'Müsait' : 'Kirada' ?>
                            </span>
                        </div>

                        <!-- Likes count -->
                        <div class="mt-3">
                            <span class="text-muted"><i class="bi bi-heart-fill text-danger"></i> <?= $likes ?> beğeni</span>
                        </div>

                        <!-- Edit button -->
                        <div class="mt-4">
                            <a href="edit.php?id=<?= $van_id ?>" class="btn btn-dark">
                                <i class="bi bi-pencil-square"></i> İlanı Düzenle
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rental Requests Section -->
        <?php if (!empty($rentals)): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-black text-white">
                    <h5 class="mb-0">Kiralama Talepleri</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($rentals as $rental): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6>
                                        <?= htmlspecialchars($rental['musteri_ad'] . ' ' . $rental['musteri_soyad']) ?>
                                        <small class="text-muted">(<?= htmlspecialchars($rental['musteri_telefon']) ?>)</small>
                                    </h6>
                                    <p class="mb-1">
                                        <strong>Tarih:</strong> 
                                        <?= date('d.m.Y', strtotime($rental['kiralama_baslangıç_tarihi'])) ?> - 
                                        <?= date('d.m.Y', strtotime($rental['kiralama_bitiş_tarihi'])) ?>
                                    </p>
                                    <p class="mb-1"><strong>Tutar:</strong> <?= number_format($rental['kira_tutari'], 2) ?> ₺</p>
                                </div>
                                <div>
                                    <span class="badge bg-<?= 
                                        match($rental['kiralama_durumu']) {
                                            'beklemede' => 'warning',
                                            'onaylandi' => 'info',
                                            'aktif' => 'success',
                                            default => 'secondary'
                                        }
                                    ?>">
                                        <?= 
                                            match($rental['kiralama_durumu']) {
                                                'beklemede' => 'Onay Bekliyor',
                                                'onaylandi' => 'Onaylandı',
                                                'aktif' => 'Aktif',
                                                default => $rental['kiralama_durumu']
                                            }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if ($rental['kiralama_durumu'] === 'beklemede'): ?>
                                <div class="mt-2 d-flex gap-2">
                                    <form method="post" action="process_rental.php" class="d-inline">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="kiralik_id" value="<?= $rental['kiralik_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Onayla</button>
                                    </form>
                                    <form method="post" action="process_rental.php" class="d-inline">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="kiralik_id" value="<?= $rental['kiralik_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Reddet</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Reviews Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Müşteri Yorumları</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between mb-2">
                                <strong><?= htmlspecialchars($comment['ad'] . ' ' . $comment['soyad']) ?></strong>
                                <small class="text-muted"><?= date('d.m.Y H:i', strtotime($comment['olusturma_tarihi'])) ?></small>
                            </div>
                            <?php if (!empty($comment['rating'])): ?>
                                <div class="mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= $i <= $comment['rating'] ? '-fill' : '' ?> text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                            <p><?= nl2br(htmlspecialchars($comment['yorum_metni'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">Henüz yorum yapılmamış.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>