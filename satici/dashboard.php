<?php
session_start();
require_once __DIR__.'/../configs/database.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'satici') {
    header("Location: index.php");
    exit();
}

$seller_id = $_SESSION['kullanici_id'];

// Prepare and execute stats query
$statsStmt = $databaseConnection->prepare(
    "SELECT
        (SELECT COUNT(*) FROM vans WHERE satici_id = :seller_id AND is_sold = 0) AS total_vans,
        (SELECT COUNT(*) FROM kiralik WHERE satici_id = :seller_id AND kiralama_durumu IN ('beklemede', 'aktif')) AS pending_requests
    "
);
$statsStmt->execute(['seller_id' => $seller_id]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Fetch vans for this seller (not sold) with like and comment counts
$vansStmt = $databaseConnection->prepare(
    "SELECT v.*, vi.image_path,
            (SELECT COUNT(*) FROM begeni WHERE van_id = v.van_id) AS like_count,
            (SELECT COUNT(*) FROM yorumlar WHERE van_id = v.van_id) AS comment_count
     FROM vans v
     LEFT JOIN van_images vi ON v.van_id = vi.van_id AND vi.is_primary = 1
     WHERE v.satici_id = :seller_id AND v.is_sold = 0
     ORDER BY v.van_id DESC"
);
$vansStmt->execute(['seller_id' => $seller_id]);
$vans = $vansStmt->fetchAll(PDO::FETCH_ASSOC);

// Video paths relative to the seller directory
$videos = [
    '../musteri/videos/video1.mp4',
    '../musteri/videos/video2.mp4',
    '../musteri/videos/video3.mp4',
    '../musteri/videos/video4.mp4',
    '../musteri/videos/video5.mp4',
    '../musteri/videos/video6.mp4'
];
$randomVideo = $videos[array_rand($videos)];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Satıcı Paneli | VANLIFE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    <link rel= "stylesheet" href ="dash_styles.css">
    <style>
        body {
            padding-top: 56px; /* Add padding to body to account for fixed navbar */
        }
        .video-container {
            margin-top: -56px; /* Pull video up to overlap with navbar */
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <!-- Hero Video Section -->
    <div class="position-relative vh-100 overflow-hidden video-container">
        <video autoplay muted loop playsinline class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover z-0">
            <source src="<?php echo htmlspecialchars($randomVideo); ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>

        <div class="position-relative z-1 h-100 d-flex align-items-center justify-content-center text-center bg-dark bg-opacity-50 p-4 text-white">
            <div>
                <h1 class="display-3 fw-bold mb-4">Van Life kiracı Paneli</h1>
                <p class="lead mb-5 fs-4">Vanlarınızı güvenle kiraya verin, ek gelir elde edin.</p>
                
                <!-- Stats in Hero Section -->
                <div class="row g-3 mt-5">
                    <div class="col-md-6">
                        <a href="talep.php" class="text-decoration-none">
                            <div class="bg-dark bg-opacity-75 p-3 rounded text-center position-relative">
                                <i class="bi bi-bell fs-1 text-danger mb-2"></i>
                                <h5 class="text-white-50">Talep Bildirimi</h5>
                                <h3 class="fw-bold text-white"><?= $stats['pending_requests'] ?></h3>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="bg-dark bg-opacity-75 p-3 rounded text-center">
                            <i class="bi bi-truck fs-1 text-info mb-2"></i>
                            <h5 class="text-white-50">Toplam Van</h5>
                            <h3 class="fw-bold"><?= $stats['total_vans'] ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="listings/add.php" class="btn btn-light btn-lg px-4 py-3 fw-bold">
                        <i class="bi bi-plus-circle me-2"></i> Yeni Van Ekle
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <h3 class="mb-3">Kiralık Vanlarınız</h3>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if(!empty($vans)): ?>
                <?php foreach($vans as $van): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <a href="listings/view.php?id=<?= $van['van_id'] ?>">
                            <div id="carouselVan<?= $van['van_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner ratio ratio-16x9">
                                    <?php 
                                        $images = $databaseConnection->prepare(
                                            "SELECT * FROM van_images WHERE van_id = ? ORDER BY is_primary DESC"
                                        );
                                        $images->execute([$van['van_id']]);
                                        $vanImages = $images->fetchAll(PDO::FETCH_ASSOC);
                                    ?>

                                    <?php foreach ($vanImages as $index => $image): ?>
                                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                            <img src="../<?= htmlspecialchars($image['image_path']) ?>" class="d-block w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($van['title']) ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (count($vanImages) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselVan<?= $van['van_id'] ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselVan<?= $van['van_id'] ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </a>

                        <div class="card-body bg-dark text-white">
                            <h5 class="card-title"><?= htmlspecialchars($van['title']) ?></h5>
                            <p class="text-white-50 mb-1"><?= htmlspecialchars($van['marka'].' '.$van['model']) ?></p>
                            <p class="text-white-50 mb-1"><?= htmlspecialchars($van['yil']) ?> - <?= htmlspecialchars($van['yakit']) ?></p>
                            <p class="text-white-50"><?= htmlspecialchars($van['vites']) ?> vites</p>
                        </div>

                        <div class="card-footer bg-dark text-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-success">₺<?= number_format($van['kira_fiyat'], 2) ?></span>
                            <div class="d-flex gap-2">
                                <span class="badge bg-danger">
                                    <i class="bi bi-heart-fill"></i> <?= $van['like_count'] ?>
                                </span>
                                <span class="badge bg-primary">
                                    <i class="bi bi-chat-left-text"></i> <?= $van['comment_count'] ?>
                                </span>
                            </div>
                            <a href="listings/edit.php?id=<?= $van['van_id'] ?>" class="btn btn-sm btn-outline-light">
                                <i class="bi bi-pencil"></i> Düzenle
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center bg-white rounded-3 p-5">
                        <i class="bi bi-truck text-muted display-5"></i>
                        <h5 class="mt-3">Henüz van eklemediniz</h5>
                        <a href="listings/add.php" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle me-2"></i> İlk Vanınızı Ekleyin
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="container mt-4">
            <h2 class="mb-4">Van Satış Rehberi</h2>
            
            <div class="row">
                <!-- Satış Sırasında Güvende Kalma -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="../assets/images/stay_safe.jpg" class="card-img-top" alt="Güvenli Satış">
                        <div class="card-body d-flex flex-column">
                            <div>
                                <h5 class="card-title">Van Satarken Güvende Kalma Rehberi</h5>
                                <p class="card-text">İlk kez bir van satıyorsanız muhtemelen birçok sorunuz vardır. Bu rehber, satış sürecinde güvende kalmanız için dikkat etmeniz gerekenleri içerir.</p>
                            </div>
                            <p class="card-text mt-auto mb-0 fst-italic">Tavsiye</p>
                        </div>
                    </div>
                </div>
                
                <!-- Van Fotoğrafı Çekme Rehberi -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="../assets/images/how__to_take_pic.jpg" class="card-img-top" alt="Van Fotoğrafları">
                        <div class="card-body d-flex flex-column">
                            <div>
                                <h5 class="card-title">Vanınızın Fotoğraflarını Çekme Rehberi</h5>
                                <p class="card-text">İlanınızda insanların ilk dikkat ettiği şey vanınızın fotoğraflarıdır. Bu rehber, dikkat çekici fotoğraflar çekmenize yardımcı olacaktır.</p>
                            </div>
                            <p class="card-text mt-auto mb-0 fst-italic">Tavsiye</p>
                        </div>
                    </div>
                </div>
                
                <!-- Vanı Satışa Hazırlama -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="../assets/images/how_to_prepare_van_for_sale.jpg" class="card-img-top" alt="Van Hazırlık">
                        <div class="card-body d-flex flex-column">
                            <div>
                                <h5 class="card-title">Vanınızı Satışa Hazırlama Rehberi</h5>
                                <p class="card-text">Biraz çaba vanınıza değer katabilir veya alıcıların fiyat kırmak için bahane bulmalarını engelleyebilir.</p>
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
                    <div>
                        <img src="../assets/images/advice.jpeng.webp" alt="Featured 1" class="img-fluid rounded shadow w-100" style="max-height: 500px; object-fit: cover;">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-dark text-white p-5 rounded shadow">
                        <h2 class="mb-4">Sizin Yolculuğunuz, Bizim Misyonumuz</h2>
                        <p class="fs-5">Vanlife, aracınızı güvenle kiraya verebilmeniz için en iyi müşterileri özenle seçer. 
                            Hızlı destek ekibimiz her zaman yanınızda ve her adımda size rehberlik etmeye hazır. 
                            Gelirinizi artırırken huzurla paylaşım yapmanın keyfini çıkarın!</p>
                    </div>
                </div>
            </div>

            <!-- Second Section: Text Left, Media Right -->
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                    <div>
                        <img src="../assets/images/build.jpg" alt="Featured 2" class="img-fluid rounded shadow w-100" style="max-height: 500px; object-fit: cover;">
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <div class="bg-dark text-white p-5 rounded shadow">
                        <h2 class="mb-4">Başarınız İçin Buradayız</h2>
                        <p class="fs-5">Vanlife olarak, minibüsünüzü en doğru kişilerle buluşturmanız için yanınızdayız. Size güven veren süreçler, hızlı müşteri desteği ve güçlü bir platform sunuyoruz.</p>
                    </div>
                </div>
            </div>
        </div>

        <section id="faq-section" class="py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-5">Sıkça Sorulan Sorular</h2>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">
                        <div class="accordion" id="faqAccordion">
                            <!-- FAQ Item 1 -->
                            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                                <h3 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        Neden Vanlife üzerinden aracımı kiraya vermeliyim?
                                    </button>
                                </h3>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Vanlife, aracınızı kullanmadığınız zamanlarda pasif gelir elde etmenizi sağlar. Güvenilir kiracılarla sizi buluşturur, güvenli ödeme ve sigorta seçenekleri sunar.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 2 -->
                            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                                <h3 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Aracımı kiraya vermek için ne yapmalıyım?
                                    </button>
                                </h3>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Üye olun, araç bilgilerinizi girin, fotoğraflar yükleyin, uygunluk takviminizi ve fiyatınızı belirleyin. Tüm işlemleri kolayca yönetebileceğiniz bir panelimiz var.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 3 -->
                            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                                <h3 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        Aracımı kiraya vermek için sigorta gerekiyor mu?
                                    </button>
                                </h3>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Evet. Vanlife, dilerseniz size özel sigorta seçenekleri sunar. Kendi sigortanızı kullanmak isterseniz, kiralama kapsamını içerdiğinden emin olmalısınız.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 4 -->
                            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                                <h3 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        Aracımı kiraya vererek ne kadar kazanabilirim?
                                    </button>
                                </h3>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Kazançlar; aracınızın tipi, konumu ve ne kadar süreyle kiraya verdiğinize göre değişir. Fiyatı siz belirlersiniz, Vanlife yalnızca küçük bir komisyon alır.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 5 -->
                            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                                <h3 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                        Kimler aracımı kiralayabilir?
                                    </button>
                                </h3>
                                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Tüm kiracılar geçerli bir ehliyete sahip olmalı, kimlik doğrulamasından geçmeli ve yaş/sürücülük geçmişi kriterlerini karşılamalıdır.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 6 -->
                            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                                <h3 class="accordion-header" id="headingSix">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                        Aracımın hangi günlerde kiralanabilir olduğunu seçebilir miyim?
                                    </button>
                                </h3>
                                <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Elbette! Takviminizi tamamen siz kontrol edersiniz. Müsait olmadığınız günleri engelleyebilir, yalnızca uygun olduğunuz günleri açık bırakabilirsiniz.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 7 -->
                            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                                <h3 class="accordion-header" id="headingSeven">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                        Kiralama sırasında aracım zarar görürse ne olur?
                                    </button>
                                </h3>
                                <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Vanlife, hasar durumlarında size yardımcı olacak sigorta ve destek süreçleri sunar. Kiracılar, zararları karşılamakla yükümlüdür ve destek ekibimiz süreci yönetir.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 8 -->
                            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                                <h3 class="accordion-header" id="headingEight">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                        Kiralama öncesi aracı temizlemem gerekiyor mu?
                                    </button>
                                </h3>
                                <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Evet. Aracınızı temiz, bakımlı ve güvenli bir şekilde teslim etmek hem olumlu yorumlar almanızı sağlar hem de daha fazla kiralama şansı sunar.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 9 -->
                            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                                <h3 class="accordion-header" id="headingNine">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
                                        Gelen kiralama talebini reddedebilir miyim?
                                    </button>
                                </h3>
                                <div id="collapseNine" class="accordion-collapse collapse" aria-labelledby="headingNine" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Evet, hiçbir kiralama talebini kabul etmek zorunda değilsiniz. Ayarlarınızdan manuel onay sistemini aktif hale getirebilir ve talepleri tek tek değerlendirebilirsiniz.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        
    </div>
<?php include '../footer.php'; ?>

</body>
</html>