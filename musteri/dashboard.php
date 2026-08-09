<?php
session_start();
require_once __DIR__ . '/../configs/database.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'musteri') {
    header("Location: ../index.php");
    exit();
}
$customer_id = $_SESSION['kullanici_id'];

// Fetch customer's existing rentals (unchanged)
$query = "
    SELECT k.*, v.title AS van_title, v.marka, v.model
    FROM kiralik k
    JOIN vans v ON k.van_id = v.van_id
    WHERE k.musteri_id = :customer_id
";
$stmt = $databaseConnection->prepare($query);
$stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -- Search form options --
$markalar = ['Ford', 'Mercedes', 'Volkswagen', 'Nissan', 'Toyota'];
$modeller = ['Transit', 'Sprinter', 'Transporter', 'NV350', 'Hiace'];

// Removed year query since you want input type number for year

// Capture filter inputs from GET safely
$filterMarka = isset($_GET['marka']) ? trim($_GET['marka']) : '';
$filterModel = isset($_GET['model']) ? trim($_GET['model']) : '';
$filterMaxPrice = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';
$filterYear = isset($_GET['year']) ? trim($_GET['year']) : '';

// Build SQL query dynamically with filters for search results
$sql = "SELECT * FROM vans WHERE is_sold = 0";
$params = [];

if ($filterMarka !== '' && in_array($filterMarka, $markalar)) {
    $sql .= " AND marka = :marka";
    $params[':marka'] = $filterMarka;
}

if ($filterModel !== '' && in_array($filterModel, $modeller)) {
    $sql .= " AND model = :model";
    $params[':model'] = $filterModel;
}

if ($filterMaxPrice !== '' && is_numeric($filterMaxPrice)) {
    $sql .= " AND CAST(kira_fiyat AS DECIMAL(10,2)) <= :max_price";
    $params[':max_price'] = $filterMaxPrice;
}

if ($filterYear !== '' && is_numeric($filterYear)) {
    // Filter by year as number (int)
    $sql .= " AND yil = :year";
    $params[':year'] = $filterYear;
}

$sql .= " ORDER BY ilan_numara DESC";

$searchStmt = $databaseConnection->prepare($sql);
$searchStmt->execute($params);
$searchResults = $searchStmt->fetchAll(PDO::FETCH_ASSOC);
  
// Fetch all available vans (not sold)
$allVansStmt = $databaseConnection->prepare("SELECT * FROM vans WHERE is_sold = 0 ORDER BY ilan_numara DESC");
$allVansStmt->execute();
$allVans = $allVansStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle video selection in PHP instead of JavaScript
$videos = [
    'videos/video1.mp4',
    'videos/video2.mp4',
    'videos/video3.mp4',
    'videos/video4.mp4',
    'videos/video5.mp4',
    'videos/video6.mp4'
];
$randomVideo = $videos[array_rand($videos)];

// Handle model select disable/enable logic in PHP
$modelDisabled = empty($filterMarka) ? 'disabled' : '';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Müşteri Paneli | VANLIFE</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="navbar_styles.css" />
        <link rel="stylesheet" href="dashboard_styles.css">

       

</head>
<body class="bg-light m-0 p-0">

    <?php include 'navbar.php'; ?>

 <!-- Updated Hero Section -->
<div class="hero-video position-relative overflow-hidden vh-100">
    <video id="randomVideo" class="object-fit-cover position-absolute top-0 start-0 z-0" autoplay muted loop playsinline src="<?php echo htmlspecialchars($randomVideo); ?>"></video>

    <div class="hero-overlay text-white">
        <div>
            <h1 class="display-3 fw-bold mb-4">Van Life'a Hoş Geldiniz</h1>
            <p class="lead mb-5 fs-4">Hayalinizdeki yolculuk sizi bekliyor. Özgürlüğü keşfedin, yeni yerler görün.</p>
            <div class="hero-buttons">
                <a href="vanlar.php" class="btn btn-mercedes btn-outline-light">Kiralamaya Başla</a>
                <a href="#" class="btn btn-mercedes btn-outline-light">Daha Fazla Bilgi</a>
            </div>
        </div>
    </div>
</div>

    <!-- Search Form -->
<!-- Search Form -->
<div class="container-fluid mt-0 mb-9 bg-black py-5 rounded">
    <form method="GET" action="arasonuclar.php" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label for="marka" class="form-label text-white">Marka</label>
            <select name="marka" id="marka" class="form-select">
                <option value="">Tüm Markalar</option>
                <?php foreach ($markalar as $marka): ?>
                    <option value="<?= htmlspecialchars($marka) ?>" <?= ($filterMarka === $marka) ? 'selected' : '' ?>><?= htmlspecialchars($marka) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label for="model" class="form-label text-white">Model</label>
            <select name="model" id="model" class="form-select">
                <option value="">Tüm Modeller</option>
                <?php foreach ($modeller as $model): ?>
                    <option value="<?= htmlspecialchars($model) ?>" <?= ($filterModel === $model) ? 'selected' : '' ?>><?= htmlspecialchars($model) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label for="max_price" class="form-label text-white">Maksimum Fiyat (₺)</label>
            <input type="number" step="0.01" min="0" class="form-control" name="max_price" id="max_price" placeholder="Örn: 1000" value="<?= htmlspecialchars($filterMaxPrice) ?>">
        </div>

        <div class="col-md-2">
            <label for="year" class="form-label text-white">Yıl</label>
            <input
                type="number"
                name="year"
                id="year"
                class="form-control"
                min="1900"
                max="2100"
                placeholder="Yıl girin"
                value="<?= htmlspecialchars($filterYear) ?>"
            />
        </div>

        <div class="col-md-1 d-grid">
            <button type="submit" class="btn btn-outline-light btn-sm mt-2 w-100 van-details-btn">Ara</button>
        </div>
    </form>
</div>

<!-- Trending Vans Section -->
<div class="container mb-5 mt-5">
<h3 class="text-black mb-4 fw-bold text-uppercase">Trend Vanlar</h3>
    <?php
    // Fetch vans with most likes (top 5)
    $trendingQuery = "
        SELECT v.*, COUNT(b.begeni_id) AS like_count
        FROM vans v
        LEFT JOIN begeni b ON v.van_id = b.van_id
        WHERE v.is_sold = 0
        GROUP BY v.van_id
        ORDER BY like_count DESC
        LIMIT 5
    ";
    $trendingStmt = $databaseConnection->prepare($trendingQuery);
    $trendingStmt->execute();
    $trendingVans = $trendingStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($trendingVans) === 0): ?>
        <p class="bg-black text-white p-3 rounded">Henüz trend van bulunmamaktadır.</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 g-4">
            <?php foreach ($trendingVans as $van): 
                // Fetch primary image for each van
                $imageStmt = $databaseConnection->prepare(
                    "SELECT * FROM van_images WHERE van_id = ? AND is_primary = 1 LIMIT 1"
                );
                $imageStmt->execute([$van['van_id']]);
                $primaryImage = $imageStmt->fetch(PDO::FETCH_ASSOC);
                
                // Fetch like count again for display
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
                                Fiyat: <?= htmlspecialchars($van['kira_fiyat']) ?> ₺
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><i class="bi bi-heart-fill text-danger me-1"></i><?= $likeCount ?></span>
                            </div>
                            <a href="./view.php?id=<?= $van['van_id'] ?>" class="btn btn-outline-light btn-sm w-100 van-details-btn">Detayları Gör</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- All Available Vans Listing -->
<div class="container mb-5">
    <h3 class="text-black mb-3">Mevcut Vanlar</h3>
    <?php if (count($allVans) === 0): ?>
        <p class="bg-black text-white p-3 rounded">Şu anda müsait van bulunmamaktadır.</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php 
            // Show only first 9 vans
            $displayedVans = array_slice($allVans, 0, 9);
            foreach ($displayedVans as $van): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm bg-black text-white van-card transform-on-hover">
                        <?php
                        // Fetch all images for this van
                        $imageStmt = $databaseConnection->prepare(
                            "SELECT * FROM van_images WHERE van_id = ? ORDER BY is_primary DESC"
                        );
                        $imageStmt->execute([$van['van_id']]);
                        $vanImages = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

                        // Fetch like count
                        $likeStmt = $databaseConnection->prepare("SELECT COUNT(*) FROM begeni WHERE van_id = ?");
                        $likeStmt->execute([$van['van_id']]);
                        $likeCount = $likeStmt->fetchColumn();

                        // Fetch comment count
                        $commentStmt = $databaseConnection->prepare("SELECT COUNT(*) FROM yorumlar WHERE van_id = ?");
                        $commentStmt->execute([$van['van_id']]);
                        $commentCount = $commentStmt->fetchColumn();
                        ?>
                        
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
                                <span><i class="bi bi-heart-fill text-danger me-1"></i><?= $likeCount ?></span>
                                <span><i class="bi bi-chat-left-text-fill text-info me-1"></i><?= $commentCount ?></span>
                            </div>
                            <a href="./view.php?id=<?= $van['van_id'] ?>" class="btn btn-outline-light btn-sm w-100 van-details-btn">Detayları Gör</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($allVans) > 9): ?>
            <div class="text-center mt-4">
                <a href="vanlar.php" class="btn btn-outline-dark btn-lg">Daha Fazla Göster <i class="bi bi-arrow-right"></i></a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="container mt-4">
<h2 class="mb-4 fw-bold text-uppercase">Van Satış Rehberi</h2>
    
    <div class="row">
        <!-- Satış Sırasında Güvende Kalma -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 transition-all transform-on-hover">
                <img src="../assets/images/search.png" class="card-img-top" alt="Güvenli Satış">
                <div class="card-body d-flex flex-column">
                    <div>
                        <h5 class="card-title">Kiralık Van Seçme Rehberi</h5>
                        <p class="card-text">İhtiyacınıza en uygun vanı nasıl seçeceğinizi öğrenin. Taşınma, seyahat ya da iş için doğru boyut, yakıt türü ve özellikleri seçmenize yardımcı olacak ipuçları bu rehberde.</p>
                    </div>
                    <p class="card-text mt-auto mb-0 fst-italic">Tavsiye</p>
                </div>
            </div>
        </div>
        
        <!-- Van Fotoğrafı Çekme Rehberi -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 transition-all transform-on-hover">
                <img src="../assets/images/trip.png" class="card-img-top" alt="Van Fotoğrafları">
                <div class="card-body d-flex flex-column">
                    <div>
                        <h5 class="card-title"> Kiralık Van ile Yolculuğa Hazırlık Rehberi</h5>
                        <p class="card-text">Yola çıkmadan önce bilmeniz gereken her şey bu rehberde. Gerekli belgeler, yanınıza almanız gerekenler ve sorunsuz bir yolculuk için öneriler.</p>
                    </div>
                    <p class="card-text mt-auto mb-0 fst-italic">Tavsiye</p>
                </div>
            </div>
        </div>
        
        <!-- Vanı Satışa Hazırlama -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 transition-all transform-on-hover">
                <img src="../assets/images/how_to_prepare_van_for_sale.jpg" class="card-img-top" alt="Van Hazırlık">
                <div class="card-body d-flex flex-column">
                    <div>
                        <h5 class="card-title">Van Kiralarken Güvende Kalma Rehberi</h5>
                        <p class="card-text">Kiralama süresince güvende kalmak için dikkat etmeniz gerekenleri öğrenin. Aracı teslim alırken kontrol etmeniz gerekenler, dolandırıcılıktan korunma yolları ve daha fazlası.</p>
                    </div>
                     <p class="card-text mt-auto mb-0 fst-italic">Tavsiye</p>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Dual Featured Media Sections -->
<div class="container-fluid my-5 py-5">
    <!-- First Section: Media Left, Text Right -->
    <div class="row align-items-center justify-content-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <img src="../assets/images/fast_customer_service.jpg" alt="Featured 1" class="img-fluid rounded shadow w-100" style="max-height: 500px; object-fit: cover;">
          
        </div>
        <div class="col-lg-6">
            <div class="bg-black text-white p-5 rounded shadow">
                <h2 class="mb-4">Müşteri Hizmetlerimizle Tanışın: Her Zaman Yanınızdayız!</h2>
                <p class="fs-5"> VanLife olarak, müşteri memnuniyetini her zaman ön planda tutuyoruz. Sorularınız veya ihtiyaçlarınız ne olursa olsun, uzman ekibimiz size 7/24 yardımcı olmak için burada. Kiralama sürecinizde ya da yolculuğunuzda karşılaşabileceğiniz her türlü konuda yanınızdayız.
Her adımda sizi desteklemek için buradayız, çünkü yolculuğunuz bizim için önemli!</p>
            </div>
        </div>
    </div>

    <!-- Second Section: Text Left, Media Right -->
    <div class="row align-items-center justify-content-center">
        <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
            <img src="../assets/images/van_rental.png" alt="Featured 2" class="img-fluid rounded shadow w-100" style="max-height: 500px; object-fit: cover;">
         </div>

        <div class="col-lg-6 order-lg-1">
            <div class="bg-black text-white p-5 rounded shadow">
                <h2 class="mb-4">En İyi Fiyatlarla En İyi Kiralık Araçlar Burada!</h2>
                <p class="fs-5">VanLife, hayalinizdeki yolculuğa çıkarken sizi en kaliteli araçlarla buluşturuyor! Farklı ihtiyaçlarınıza özel geniş araç seçeneklerimiz ve cazip fiyatlarımızla, en uygun kiralama deneyimini sunuyoruz. Hem konforlu hem de ekonomik araçlar, bütçenize ve seyahat planınıza göre sizin için hazır.
VanLife'ta, sadece araç kiralamıyorsunuz; en iyi teklifleri, en iyi hizmetle birleştiriyorsunuz. Hayalinizdeki yolculuk için en doğru seçim burada!</p>
            </div>
        </div>
    </div>
</div>
<section id="faq-section" class="py-5 bg-light">

    <div class="container">
        <h2 class="text-center mb-5 fs-1 fw-bold text-dark">Sıkça Sorulan Sorular</h2>
        
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="accordion" id="faqAccordion">
                    <!-- FAQ Item 1 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed rounded-3 text-start fs-5 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                Vanlife üzerinden nasıl araç kiralayabilirim?
                            </button>
                        </h3>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Vanlife üzerinden araç kiralamak oldukça basit! Üye olun, istediğiniz aracı seçin, kiralama tarihlerinizi belirleyin ve ödeme işlemini tamamlayın. Kiralama süreci boyunca her aşamayı takip edebilirsiniz.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed rounded-3 text-start fs-5 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Aracımı kiralarken ne gibi güvenlik önlemleri alınmaktadır?
                            </button>
                        </h3>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Vanlife, tüm kiralamalar için güvenli ödeme sistemleri ve sigorta seçenekleri sunar. Ayrıca, araç sahiplerinin güvenilirliği ve kiracının kimlik doğrulaması sürekli olarak denetlenir.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed rounded-3 text-start fs-5 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Aracımı kiralarken sigorta nasıl işliyor?
                            </button>
                        </h3>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Kiralama sırasında araç sahiplerinin sigorta kapsamı doğrulanır. Vanlife, size özel sigorta seçenekleri sunar ve isterseniz kendi sigortanızı kullanabilirsiniz.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed rounded-3 text-start fs-5 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Kiralama talebimi iptal edebilir miyim?
                            </button>
                        </h3>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Evet, kiralama talebinizi "Beklemede" olduğu sürece iptal edebilirsiniz. Bu, aracın henüz kiralama sürecine başlanmadan önce yapabileceğiniz bir işlemdir. Fakat, kiralama onaylandıktan sonra iptal işlemi yapılamaz.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed rounded-3 text-start fs-5 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Kiralamada ödeme nasıl yapılır?
                            </button>
                        </h3>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Vanlife platformu üzerinden güvenli bir ödeme sistemi ile işlem yapabilirsiniz. Ödeme, kiralama tarihleri onaylandığında yapılır ve araç sahibi ile kiracı arasında güvenli bir şekilde aktarılır.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 6 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header" id="headingSix">
                            <button class="accordion-button collapsed rounded-3 text-start fs-5 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                Kiralama sırasında aracımda herhangi bir hasar oluşursa ne olur?
                            </button>
                        </h3>
                        <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Kiralama sırasında aracınızda herhangi bir hasar oluşursa, sigorta kapsamında tazminat alabilirsiniz. Vanlife, tüm hasar süreçlerini hızlı bir şekilde çözüme kavuşturur ve araç sahipleri ile kiracılar arasındaki anlaşmazlıkları yönetir.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 7 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header" id="headingSeven">
                            <button class="accordion-button collapsed rounded-3 text-start fs-5 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                Aracın temizliğini nasıl yapmalıyım?
                            </button>
                        </h3>
                        <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Aracınızı temiz ve bakımlı bir şekilde teslim etmek, araç sahipleri için önemlidir. Aracın temizliği ve bakımı, kiralama sürecinizin sorunsuz geçmesini sağlar ve olumlu yorumlar almanıza yardımcı olur.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 8 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header" id="headingEight">
                            <button class="accordion-button collapsed rounded-3 text-start fs-5 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                Kiralama sırasında değişiklik yapabilir miyim?
                            </button>
                        </h3>
                        <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Kiralama talebinizi yaptıktan sonra, tarih veya araç detaylarında değişiklik yapmak isterseniz, araç sahibine bildirerek yeni bir anlaşma yapabilirsiniz. Değişiklikler karşılıklı onayla yapılır.
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>
<div class="class mt-5">
  <?php include '../footer.php'; ?>

</div>
</body>
</html>