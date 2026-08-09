<?php
session_start();
require_once __DIR__ . '/../configs/database.php';

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: ../index.php");
    exit();
}

// Define brand and model options
$markalar = ['Ford', 'Mercedes', 'Volkswagen', 'Nissan', 'Toyota'];
$modeller = ['Transit', 'Sprinter', 'Transporter', 'NV350', 'Hiace'];

// Get filter parameters safely
$filterMarka = isset($_GET['marka']) ? trim($_GET['marka']) : '';
$filterModel = isset($_GET['model']) ? trim($_GET['model']) : '';
$filterMaxPrice = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';
$filterYear = isset($_GET['year']) ? trim($_GET['year']) : '';

// Build the SQL query with filters
$sql = "SELECT v.*, 
               (SELECT COUNT(*) FROM begeni WHERE van_id = v.van_id) AS like_count,
               (SELECT COUNT(*) FROM yorumlar WHERE van_id = v.van_id) AS comment_count
        FROM vans v 
        WHERE v.is_sold = 0";
$params = [];

if ($filterMarka !== '' && in_array($filterMarka, $markalar)) {
    $sql .= " AND v.marka = :marka";
    $params[':marka'] = $filterMarka;
}

if ($filterModel !== '' && in_array($filterModel, $modeller)) {
    $sql .= " AND v.model = :model";
    $params[':model'] = $filterModel;
}

if ($filterMaxPrice !== '' && is_numeric($filterMaxPrice)) {
    $sql .= " AND v.kira_fiyat <= :max_price";
    $params[':max_price'] = (float)$filterMaxPrice;
}

if ($filterYear !== '' && is_numeric($filterYear)) {
    $sql .= " AND v.yil = :year";
    $params[':year'] = (int)$filterYear;
}

$sql .= " ORDER BY v.ilan_numara DESC";

// Execute the query
$stmt = $databaseConnection->prepare($sql);
$stmt->execute($params);
$searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Arama Sonuçları | VANLIFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="navbar_styles.css" />
    <link rel="stylesheet" href="dashboard_styles.css" />
</head>
<body class="bg-light m-0 p-0  ">
    <?php include 'navbar.php'; ?>

    <div class="container py-5 mt-5 my-5">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-black fw-bold">Arama Sonuçları</h2>
                <p class="text-muted">
                    <?php 
                    $filterText = [];
                    if ($filterMarka) $filterText[] = "Marka: $filterMarka";
                    if ($filterModel) $filterText[] = "Model: $filterModel";
                    if ($filterMaxPrice) $filterText[] = "Maksimum Fiyat: $filterMaxPrice ₺";
                    if ($filterYear) $filterText[] = "Yıl: $filterYear";
                    
echo "Filtreler: " . (empty($filterText) ? "Tüm vanlar" : implode(", ", $filterText));
                    ?>
                </p>
            </div>
        </div>

        <?php if (count($searchResults) === 0): ?>
            <div class="alert alert-warning">
                Aradığınız kriterlere uygun van bulunamadı.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($searchResults as $van): 
                    // Fetch images for the van
                    $imageStmt = $databaseConnection->prepare(
                        "SELECT * FROM van_images WHERE van_id = ? ORDER BY is_primary DESC"
                    );
                    $imageStmt->execute([$van['van_id']]);
                    $vanImages = $imageStmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm bg-black text-white van-card transform-on-hover">
                            <?php if (count($vanImages) > 0): ?>
                                <div id="carousel-<?= $van['van_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php foreach ($vanImages as $index => $image): ?>
                                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                <img src="../<?= htmlspecialchars($image['image_path']) ?>" class="d-block w-100 van-img" alt="<?= htmlspecialchars($van['title']) ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($vanImages) > 1): ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $van['van_id'] ?>" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $van['van_id'] ?>" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
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
                                    <span><i class="bi bi-heart-fill text-danger me-1"></i><?= $van['like_count'] ?></span>
                                    <span><i class="bi bi-chat-left-text-fill text-info me-1"></i><?= $van['comment_count'] ?></span>
                                </div>
                                <a href="./view.php?id=<?= $van['van_id'] ?>" class="btn btn-outline-light btn-sm w-100 van-details-btn">Detayları Gör</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>