<?php
session_start();
require_once __DIR__ . '/../configs/database.php';

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: ../index.php");
    exit();
}

$customer_id = $_SESSION['kullanici_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Geçersiz van ID.");
}
$van_id = (int)$_GET['id'];

// Fetch van details and seller contact info
$queryVan = "
    SELECT v.*, u.telefon, u.ad as satici_ad, u.soyad as satici_soyad
    FROM vans v
    JOIN kullanici u ON v.satici_id = u.id
    WHERE v.van_id = :van_id AND v.is_sold = 0
";
$stmtVan = $databaseConnection->prepare($queryVan);
$stmtVan->execute([':van_id' => $van_id]);
$van = $stmtVan->fetch(PDO::FETCH_ASSOC);

if (!$van) {
    die("Van bulunamadı veya satışta değil.");
}

// Check van availability
$vanAvailable = ($van['durum'] === 'bosta');

// Fetch ALL images for this van
$imageQuery = $databaseConnection->prepare("
    SELECT image_path 
    FROM van_images 
    WHERE van_id = :van_id 
    ORDER BY is_primary DESC
");
$imageQuery->execute([':van_id' => $van_id]);
$images = $imageQuery->fetchAll(PDO::FETCH_ASSOC);

// If no images, use default
if (empty($images)) {
    $images = [['image_path' => 'default-van.jpg']];
} else {
    // Prepend '../' to all image paths
    foreach ($images as &$image) {
        $image['image_path'] = "../" . $image['image_path'];
    }
    unset($image);
}

// Function to get any existing rental for this van by this customer
function getCustomerRental($db, $van_id, $customer_id) {
    $stmt = $db->prepare("
        SELECT k.*, TIMESTAMPDIFF(HOUR, k.olusturma_tarihi, NOW()) as hours_since_creation
        FROM kiralik k
        WHERE k.van_id = :van_id 
          AND k.musteri_id = :musteri_id
          AND k.kiralama_durumu IN ('beklemede', 'onaylandi', 'aktif')
        ORDER BY k.kiralik_id DESC
        LIMIT 1
    ");
    $stmt->execute([':van_id' => $van_id, ':musteri_id' => $customer_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to calculate rental cost
function calculateRentalCost($start_date, $end_date, $daily_price) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $days = $interval->days;
    
    // Minimum rental is 1 day
    if ($days < 1) {
        $days = 1;
    }
    
    return $days * floatval($daily_price);
}

// Check if user liked this van
$stmt = $databaseConnection->prepare("SELECT 1 FROM begeni WHERE kullanici_id = :kullanici_id AND van_id = :van_id");
$stmt->execute([':kullanici_id' => $customer_id, ':van_id' => $van_id]);
$liked = (bool)$stmt->fetch();

// Fetch the number of likes
$stmt = $databaseConnection->prepare("SELECT COUNT(*) AS like_count FROM begeni WHERE van_id = :van_id");
$stmt->execute([':van_id' => $van_id]);
$likeCount = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

// Check for any existing rental (pending, approved, or active)
$existingRental = getCustomerRental($databaseConnection, $van_id, $customer_id);

// Check if rental can be cancelled (pending status)
$canCancel = ($existingRental && $existingRental['kiralama_durumu'] === 'beklemede');

// Fetch all reviews for this van
$reviewsQuery = $databaseConnection->prepare("
    SELECT y.*, u.ad, u.soyad 
    FROM yorumlar y
    JOIN kullanici u ON y.kullanici_id = u.id
    WHERE y.van_id = :van_id
    ORDER BY y.olusturma_tarihi DESC
");
$reviewsQuery->execute([':van_id' => $van_id]);
$reviews = $reviewsQuery->fetchAll(PDO::FETCH_ASSOC);

// Calculate average rating
$avgRatingQuery = $databaseConnection->prepare("
    SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
    FROM yorumlar 
    WHERE van_id = :van_id AND rating IS NOT NULL
");
$avgRatingQuery->execute([':van_id' => $van_id]);
$ratingData = $avgRatingQuery->fetch(PDO::FETCH_ASSOC);
$averageRating = round($ratingData['avg_rating'] ?? 0, 1);
$reviewCount = $ratingData['review_count'] ?? 0;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'like':
            $stmt = $databaseConnection->prepare("SELECT 1 FROM begeni WHERE kullanici_id = :kullanici_id AND van_id = :van_id");
            $stmt->execute([':kullanici_id' => $customer_id, ':van_id' => $van_id]);
            if (!$stmt->fetch()) {
                $stmt = $databaseConnection->prepare("INSERT INTO begeni (begeni_id, kullanici_id, van_id) VALUES (UUID(), :kullanici_id, :van_id)");
                $stmt->execute([':kullanici_id' => $customer_id, ':van_id' => $van_id]);
            }
            header("Location: view.php?id=" . $van_id);
            exit();

        case 'unlike':
            $stmt = $databaseConnection->prepare("DELETE FROM begeni WHERE kullanici_id = :kullanici_id AND van_id = :van_id");
            $stmt->execute([':kullanici_id' => $customer_id, ':van_id' => $van_id]);
            header("Location: view.php?id=" . $van_id);
            exit();

        case 'rent':
            // Check if van is available
            if (!$vanAvailable) {
                $message = "Bu van şu anda kiralanmış durumda.";
                break;
            }

            // Check for existing rental request
            if ($existingRental) {
                $message = match($existingRental['kiralama_durumu']) {
                    'beklemede' => "Zaten bir kiralama talebiniz bekliyor.",
                    'onaylandi', 'aktif' => "Bu van için zaten aktif bir kiralama işleminiz var.",
                    default => "Bu van için zaten bir talebiniz var."
                };
                break;
            }

            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            
            if (!$start_date || !$end_date || strtotime($start_date) > strtotime($end_date)) {
                $message = "Geçerli bir tarih aralığı giriniz.";
                break;
            }

            // Check if dates are in the past
            if (strtotime($start_date) < strtotime('today') || strtotime($end_date) < strtotime('today')) {
                $message = "Geçmiş tarihli kiralama yapılamaz.";
                break;
            }

            // Calculate the total cost here in PHP instead of relying on form input
            $total_cost = calculateRentalCost($start_date, $end_date, $van['kira_fiyat']);

            // Check for overlapping rentals (active or approved)
            $stmt = $databaseConnection->prepare("
                SELECT 1 FROM kiralik 
                WHERE van_id = :van_id 
                AND kiralama_durumu IN ('aktif', 'onaylandi')
                AND (
                    (:start_date BETWEEN kiralama_baslangıç_tarihi AND kiralama_bitiş_tarihi)
                    OR (:end_date BETWEEN kiralama_baslangıç_tarihi AND kiralama_bitiş_tarihi)
                    OR (kiralama_baslangıç_tarihi BETWEEN :start_date AND :end_date)
                )
            ");
            $stmt->execute([
                ':van_id' => $van_id,
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);

            if ($stmt->fetch()) {
                $message = "Bu van seçilen tarihlerde müsait değil.";
            } else {
                try {
                    $databaseConnection->beginTransaction();
                    
                    // Insert new rental request with calculated total cost
                    $stmt = $databaseConnection->prepare("
                        INSERT INTO kiralik (
                            van_id, satici_id, musteri_id, kiralama_baslangıç_tarihi, 
                            kiralama_bitiş_tarihi, kira_tutari, kiralama_durumu
                        ) VALUES (
                            :van_id, :satici_id, :musteri_id, :start_date, 
                            :end_date, :kira_tutari, 'beklemede'
                        )
                    ");
                    $stmt->execute([
                        ':van_id' => $van_id,
                        ':satici_id' => $van['satici_id'],
                        ':musteri_id' => $customer_id,
                        ':start_date' => $start_date,
                        ':end_date' => $end_date,
                        ':kira_tutari' => $total_cost
                    ]);
                    
                    // Update van status to 'kirada' (even if pending)
                    $updateStmt = $databaseConnection->prepare("
                        UPDATE vans SET durum = 'kirada' WHERE van_id = :van_id
                    ");
                    $updateStmt->execute([':van_id' => $van_id]);
                    
                    $databaseConnection->commit();
                    header("Location: view.php?id=" . $van_id);
                    exit();
                } catch (Exception $e) {
                    $databaseConnection->rollBack();
                    $message = "Kiralama işlemi sırasında bir hata oluştu: " . $e->getMessage();
                }
            }
            break;

        case 'cancel_request':
            if (!$existingRental || $existingRental['kiralama_durumu'] !== 'beklemede') {
                $message = "Bu kiralama talebi iptal edilemez.";
                break;
            }

            $reason = $_POST['reason'] ?? '';
            $other_reason = $_POST['other_reason'] ?? '';

            if (empty($reason)) {
                $message = "Lütfen bir iptal nedeni seçin.";
                break;
            }

            try {
                $databaseConnection->beginTransaction();
                
                // Insert cancellation request
                $stmt = $databaseConnection->prepare("
                    INSERT INTO talep_iptal (
                        kiralik_id, musteri_id, neden, diger_aciklama
                    ) VALUES (
                        :kiralik_id, :musteri_id, :neden, :diger_aciklama
                    )
                ");
                $stmt->execute([
                    ':kiralik_id' => $existingRental['kiralik_id'],
                    ':musteri_id' => $customer_id,
                    ':neden' => $reason,
                    ':diger_aciklama' => $other_reason
                ]);
                
                // Update rental status to 'reddedildi'
                $updateStmt = $databaseConnection->prepare("
                    UPDATE kiralik SET kiralama_durumu = 'reddedildi' 
                    WHERE kiralik_id = :kiralik_id
                ");
                $updateStmt->execute([':kiralik_id' => $existingRental['kiralik_id']]);
                
                // Update van status back to 'bosta'
                $updateVanStmt = $databaseConnection->prepare("
                    UPDATE vans SET durum = 'bosta' WHERE van_id = :van_id
                ");
                $updateVanStmt->execute([':van_id' => $van_id]);
                
                $databaseConnection->commit();
                header("Location: view.php?id=" . $van_id);
                exit();
            } catch (Exception $e) {
                $databaseConnection->rollBack();
                $message = "İptal işlemi sırasında bir hata oluştu: " . $e->getMessage();
            }
            break;

        case 'add_review':
            $review_text = $_POST['review_text'] ?? '';
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;

            if (empty($review_text)) {
                $message = "Lütfen bir yorum yazın.";
                break;
            }

            // Check if user has already reviewed this van
            $checkReview = $databaseConnection->prepare("
                SELECT 1 FROM yorumlar 
                WHERE kullanici_id = :kullanici_id AND van_id = :van_id
            ");
            $checkReview->execute([
                ':kullanici_id' => $customer_id,
                ':van_id' => $van_id
            ]);

            if ($checkReview->fetch()) {
                $message = "Bu van için zaten bir yorumunuz var.";
                break;
            }

            // Insert new review
            $stmt = $databaseConnection->prepare("
                INSERT INTO yorumlar (
                    kullanici_id, van_id, yorum_metni, rating
                ) VALUES (
                    :kullanici_id, :van_id, :yorum_metni, :rating
                )
            ");
            $stmt->execute([
                ':kullanici_id' => $customer_id,
                ':van_id' => $van_id,
                ':yorum_metni' => $review_text,
                ':rating' => $rating
            ]);

            header("Location: view.php?id=" . $van_id);
            exit();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title><?=htmlspecialchars($van['marka'] . ' ' . $van['model'])?> - Detaylar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="view_styles.css" rel="stylesheet" />
    <style>
        .rating-star {
            color: #ddd;
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        .rating-star:hover, .rating-star.active {
            color: #ffc107;
        }
        .price-calculator {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
        }
        .total-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #28a745;
        }
        #rentalForm {
            transition: all 0.3s ease;
        }
        .carousel-container {
            height: 400px;
            overflow: hidden;
            border-radius: 8px;
        }
        .carousel-inner {
            height: 100%;
        }
        .carousel-item {
            height: 100%;
        }
        .carousel-item img {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
<!-- Include the navbar -->
<?php include 'navbar.php'; ?>

<div class="container mt-5 pt-5">
    <?php if ($message): ?>
        <div class="alert alert-warning"><?=htmlspecialchars($message)?></div>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm border-light">
        <div class="row g-0">
            <!-- Image Carousel Column -->
            <div class="col-md-5 p-3">
                <div class="carousel-container">
                    <div id="vanCarousel" class="carousel slide h-100" data-bs-ride="carousel">
                        <div class="carousel-inner h-100">
                            <?php foreach ($images as $index => $image): ?>
                                <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="<?= htmlspecialchars($image['image_path']) ?>" class="d-block w-100" alt="Van Resmi">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#vanCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#vanCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Van Details Column -->
            <div class="col-md-7">
                <div class="card-body">
                    <h3 class="card-title text-black"><?=htmlspecialchars($van['marka'] . ' ' . $van['model'])?></h3>
                    
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
                    <p class="card-text"><strong>Üretim Yılı:</strong> <?=htmlspecialchars($van['yil'])?></p>
                    <p class="card-text"><strong>Yakıt Tipi:</strong> <?=htmlspecialchars($van['yakit'])?></p>
                    <p class="card-text"><strong>Motor Gücü:</strong> <?=htmlspecialchars($van['motor_gucu'])?></p>
                    <p class="card-text"><strong>Renk:</strong> <?=htmlspecialchars($van['renk'])?></p>
                    <p class="card-text"><strong>Vites Tipi:</strong> <?=htmlspecialchars($van['vites'])?></p>
                    <p class="card-text"><strong>Plaka:</strong> <?=htmlspecialchars($van['plate'])?></p>
                    <p class="card-text"><strong>Kira Fiyatı (Günlük):</strong> <?=htmlspecialchars($van['kira_fiyat'])?> ₺</p>

                    <!-- Like/Unlike button -->
                    <form method="post" class="d-inline">
                        <?php if ($liked): ?>
                            <button type="submit" name="action" value="unlike" class="btn btn-danger" title="Beğeniyi Kaldır">
                                <i class="bi bi-heart-fill"></i> Beğen
                            </button>
                        <?php else: ?>
                            <button type="submit" name="action" value="like" class="btn btn-outline-danger" title="Beğen">
                                <i class="bi bi-heart"></i> Beğen
                            </button>
                        <?php endif; ?>
                    </form>

                    <!-- Rental form or status -->
                    <div class="mt-3">
                        <?php if ($existingRental): ?>
                            <div class="alert alert-<?= 
                                match($existingRental['kiralama_durumu']) {
                                    'beklemede' => 'info',
                                    'onaylandi', 'aktif' => 'success',
                                    default => 'secondary'
                                }
                            ?>">
                                Kiralama durumu: <strong><?= 
                                    match($existingRental['kiralama_durumu']) {
                                        'beklemede' => 'Onay bekliyor',
                                        'onaylandi' => 'Onaylandı (başlangıç tarihini bekliyor)',
                                        'aktif' => 'Aktif kiralama',
                                        default => 'Bilinmeyen durum'
                                    }
                                ?></strong>
                                <br>
                                <small class="text-muted">Toplam Tutar: <?=htmlspecialchars($existingRental['kira_tutari'])?> ₺</small>
                            </div>

                            <?php if ($existingRental['kiralama_durumu'] === 'beklemede'): ?>
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="action" value="cancel" />
                                    <button type="button" class="btn btn-warning" id="cancelButton">İptal Et</button>
                                </form>

                                <!-- Cancellation Reason Form (hidden initially) -->
                                <div id="cancellationForm" style="display: none;">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="cancel_request" />
                                        <div class="form-group">
                                            <label for="reason">İptal Nedeni</label>
                                            <select name="reason" id="reason" class="form-control" required>
                                                <option value="">Bir neden seçin...</option>
                                                <option value="fiyat_yuksek">Fiyat çok yüksek</option>
                                                <option value="plan_degisti">Planım değişti</option>
                                                <option value="diğer">Diğer</option>
                                            </select>
                                        </div>
                                        <div class="form-group mt-2">
                                            <label for="additional_info">Ekstra Açıklama</label>
                                            <textarea name="other_reason" id="additional_info" class="form-control"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-danger mt-3">İptal Talebini Gönder</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php elseif (!$vanAvailable): ?>
                            <div class="alert alert-warning">
                                Bu van şu anda kiralanmış durumda.
                            </div>
                        <?php else: ?>
                            <!-- Show rental form -->
                            <div class="price-calculator mb-3">
                                <h6 class="mb-3">Kiralama Hesaplayıcısı</h6>
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label for="calc_start_date" class="form-label">Başlangıç Tarihi</label>
                                        <input type="date" id="calc_start_date" class="form-control" 
                                               min="<?= date('Y-m-d') ?>" onchange="calculatePrice()" />
                                    </div>
                                    <div class="col-md-5">
                                        <label for="calc_end_date" class="form-label">Bitiş Tarihi</label>
                                        <input type="date" id="calc_end_date" class="form-control" 
                                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>" onchange="calculatePrice()" />
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                                        <div class="text-center">
                                            <small class="text-muted">Gün Sayısı</small>
                                            <div id="dayCount" class="fw-bold">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <div class="total-price" id="totalPrice">Toplam: - ₺</div>
                                    <small class="text-muted">(Günlük <?=htmlspecialchars($van['kira_fiyat'])?> ₺)</small>
                                </div>
                            </div>

                            <form method="post" id="rentalForm" style="display: none;">
                                <input type="hidden" name="action" value="rent" />
                                <input type="hidden" name="start_date" id="final_start_date" />
                                <input type="hidden" name="end_date" id="final_end_date" />
                                <input type="hidden" name="total_cost" id="final_total_cost" />
                                
                                <div class="alert alert-info">
                                    <h6>Kiralama Onayı</h6>
                                    <p class="mb-2">
                                        <strong>Tarih Aralığı:</strong> <span id="dateRange">-</span><br>
                                        <strong>Gün Sayısı:</strong> <span id="confirmDays">-</span><br>
                                        <strong>Toplam Tutar:</strong> <span id="confirmTotal" class="text-success fw-bold">- ₺</span>
                                    </p>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success">Kiralama Talebini Onayla</button>
                                        <button type="button" class="btn btn-secondary" onclick="resetForm()">İptal Et</button>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Müşteri Yorumları</h5>
        </div>
        <div class="card-body">
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-2">
                            <strong><?= htmlspecialchars($review['ad'] . ' ' . $review['soyad']) ?></strong>
                            <small class="text-muted"><?= date('d.m.Y H:i', strtotime($review['olusturma_tarihi'])) ?></small>
                        </div>
                        <?php if (!empty($review['rating'])): ?>
                            <div class="mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= $review['rating'] ? '-fill' : '' ?> text-warning"></i>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                        <p><?= nl2br(htmlspecialchars($review['yorum_metni'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Henüz yorum yapılmamış.</p>
            <?php endif; ?>
            
            <!-- Add Review Form -->
            <div class="mt-4">
                <h6>Yorum Yap</h6>
                <form method="post">
                    <input type="hidden" name="action" value="add_review" />
                    <div class="mb-3">
                        <label class="form-label">Değerlendirme</label>
                        <div class="rating-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star rating-star" data-rating="<?= $i ?>"></i>
                            <?php endfor; ?>
                            <input type="hidden" name="rating" id="rating-value" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="review_text" class="form-label">Yorumunuz</label>
                        <textarea class="form-control" id="review_text" name="review_text" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Gönder</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("cancelButton")?.addEventListener("click", function () {
        document.getElementById("cancellationForm").style.display = "block";
    });

    // Rating star selection
    document.querySelectorAll('.rating-star').forEach(star => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.getAttribute('data-rating'));
            document.getElementById('rating-value').value = rating;
            
            // Update star display
            document.querySelectorAll('.rating-star').forEach(s => {
                const sRating = parseInt(s.getAttribute('data-rating'));
                if (sRating <= rating) {
                    s.classList.add('active');
                    s.classList.add('bi-star-fill');
                    s.classList.remove('bi-star');
                } else {
                    s.classList.remove('active');
                    s.classList.remove('bi-star-fill');
                    s.classList.add('bi-star');
                }
            });
        });
        
        // Hover effect
        star.addEventListener('mouseover', () => {
            const rating = parseInt(star.getAttribute('data-rating'));
            document.querySelectorAll('.rating-star').forEach(s => {
                const sRating = parseInt(s.getAttribute('data-rating'));
                if (sRating <= rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
        
        star.addEventListener('mouseout', () => {
            const currentRating = parseInt(document.getElementById('rating-value').value || 0);
            document.querySelectorAll('.rating-star').forEach(s => {
                const sRating = parseInt(s.getAttribute('data-rating'));
                if (currentRating === 0) {
                    s.classList.remove('active');
                } else if (sRating <= currentRating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
    });

    // Calculate total price based on dates selected
    function calculatePrice() {
        const startDate = document.getElementById('calc_start_date').value;
        const endDate = document.getElementById('calc_end_date').value;

        if (!startDate || !endDate) return;

        const start = new Date(startDate);
        const end = new Date(endDate);

        const dayCount = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1; // Including the end date
        const dailyPrice = <?= $van['kira_fiyat'] ?>;  // The daily price of the van
        
        const totalPrice = dayCount * dailyPrice;
        
        document.getElementById('dayCount').innerText = dayCount;
        document.getElementById('totalPrice').innerText = `Toplam: ${totalPrice} ₺`;
        document.getElementById('confirmDays').innerText = dayCount;
        document.getElementById('confirmTotal').innerText = `${totalPrice} ₺`;

        document.getElementById('final_start_date').value = startDate;
        document.getElementById('final_end_date').value = endDate;
        document.getElementById('final_total_cost').value = totalPrice;
    }

    // Reset form fields
    function resetForm() {
        document.getElementById('rentalForm').style.display = 'none';
    }

    // Show rental form when dates are valid
    document.getElementById('calc_end_date')?.addEventListener('change', () => {
        const startDate = document.getElementById('calc_start_date').value;
        const endDate = document.getElementById('calc_end_date').value;

        if (startDate && endDate) {
            document.getElementById('rentalForm').style.display = 'block';
        }
    });
</script>

<div class="class mt-5">
  <?php include '../footer.php'; ?>

</div>
</body>
</html>