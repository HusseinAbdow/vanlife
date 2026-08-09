<?php
session_start();
require_once __DIR__ . '/../configs/database.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'musteri') {
    header("Location: ../index.php");
    exit();
}

$sort = $_GET['sort'] ?? 'recent';
$validSorts = ['recent', 'liked', 'price_asc', 'price_desc'];
$sort = in_array($sort, $validSorts) ? $sort : 'recent';

// Base query
$query = "SELECT v.* FROM vans v WHERE v.is_sold = 0 ";

// Add sorting
switch ($sort) {
    case 'recent':
        $query .= "ORDER BY v.ilan_numara DESC";
        break;
    case 'liked':
        $query = "
            SELECT v.*, COUNT(b.begeni_id) AS like_count 
            FROM vans v
            LEFT JOIN begeni b ON v.van_id = b.van_id
            WHERE v.is_sold = 0
            GROUP BY v.van_id
            ORDER BY like_count DESC
        ";
        break;
    case 'price_asc':
        $query .= "ORDER BY CAST(v.kira_fiyat AS DECIMAL(10,2)) ASC";
        break;
    case 'price_desc':
        $query .= "ORDER BY CAST(v.kira_fiyat AS DECIMAL(10,2)) DESC";
        break;
}

$stmt = $databaseConnection->prepare($query);
$stmt->execute();
$vans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Vanları Keşfet | VANLIFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="navbar_styles.css" />
    <link rel="stylesheet" href="dashboard_styles.css">
     <link rel="stylesheet" href="vanlar_styles.css">


</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container py-5 mt-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold">Vanlarımızı Keşfedin</h1>
                <div class="d-flex justify-content-between align-items-center">
                    <p class="lead mb-0">Hayalinizdeki vanı bulun</p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Sırala: 
                            <?= $sort === 'recent' ? 'En Yeni' : 
                               ($sort === 'liked' ? 'En Beğenilen' : 
                               ($sort === 'price_asc' ? 'Ucuzdan Pahalıya' : 'Pahalıdan Ucuza')) ?>
                        </button>
                        <ul class="dropdown-menu">
    <li><a class="dropdown-item btn btn-outline-light" href="?sort=recent">En Yeni</a></li>
    <li><a class="dropdown-item btn btn-outline-light" href="?sort=liked">En Beğenilen</a></li>
    <li><a class="dropdown-item btn btn-outline-light" href="?sort=price_asc">Ucuzdan Pahalıya</a></li>
    <li><a class="dropdown-item btn btn-outline-light" href="?sort=price_desc">Pahalıdan Ucuza</a></li>
</ul>

                    </div>
                </div>
            </div>
        </div>

        <?php if (count($vans) === 0): ?>
            <div class="alert alert-info">
                Şu anda müsait van bulunmamaktadır.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($vans as $van): ?>
                    <?php
                    // Fetch primary image
                    $imageStmt = $databaseConnection->prepare(
                        "SELECT * FROM van_images WHERE van_id = ? AND is_primary = 1 LIMIT 1"
                    );
                    $imageStmt->execute([$van['van_id']]);
                    $primaryImage = $imageStmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Fetch like count
                    $likeStmt = $databaseConnection->prepare("SELECT COUNT(*) FROM begeni WHERE van_id = ?");
                    $likeStmt->execute([$van['van_id']]);
                    $likeCount = $likeStmt->fetchColumn();
                    ?>
                    
                    <div class="col">
                        <div class="card h-100 shadow-sm bg-black text-white van-card transform-on-hover">
                            <?php if ($primaryImage): ?>
                                <img src="../<?= htmlspecialchars($primaryImage['image_path']) ?>" class="card-img-top van-img" alt="<?= htmlspecialchars($van['title']) ?>">
                            <?php else: ?>
                                <img src="default-van.jpg" class="card-img-top van-img" alt="Resim yok" />
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($van['title']) ?></h5>
                                <p class="card-text van-text-small">
                                    Marka: <?= htmlspecialchars($van['marka']) ?><br>
                                    Model: <?= htmlspecialchars($van['model']) ?><br>
                                    Yıl: <?= htmlspecialchars($van['yil']) ?><br>
                                    Fiyat: <?= htmlspecialchars($van['kira_fiyat']) ?> ₺<br>
                                    Durum: <?= ($van['durum'] === 'bosta') ? 'Müsait' : 'Kirada' ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="bi bi-heart-fill text-danger me-1"></i><?= $likeCount ?></span>
                                </div>
                                <a href="view.php?id=<?= $van['van_id'] ?>" class="btn btn-outline-light btn-sm w-100 van-details-btn">Detayları Gör</a>
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