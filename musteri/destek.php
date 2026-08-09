<?php
session_start();
require_once __DIR__ . '/../configs/database.php';

if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'musteri') {
    header("Location: ../index.php");
    exit();
}

$customer_id = $_SESSION['kullanici_id'];
$message = '';
$error = '';

// Fetch customer's active rentals for dropdown
$rentalsQuery = "
    SELECT k.kiralik_id, v.title, k.kiralama_durumu 
    FROM kiralik k
    JOIN vans v ON k.van_id = v.van_id
    WHERE k.musteri_id = :customer_id
    ORDER BY k.kiralama_baslangıç_tarihi DESC
";
$rentalsStmt = $databaseConnection->prepare($rentalsQuery);
$rentalsStmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$rentalsStmt->execute();
$rentals = $rentalsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch customer's rented vans for dropdown
$vansQuery = "
    SELECT v.van_id, v.title 
    FROM kiralik k
    JOIN vans v ON k.van_id = v.van_id
    WHERE k.musteri_id = :customer_id
    GROUP BY v.van_id
    ORDER BY v.title
";
$vansStmt = $databaseConnection->prepare($vansQuery);
$vansStmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$vansStmt->execute();
$vans = $vansStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $talep_turu = $_POST['talep_turu'] ?? '';
    $baslik = trim($_POST['baslik'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');
    $kiralik_id = $_POST['kiralik_id'] ?? null;
    $van_id = $_POST['van_id'] ?? null;

    // Validate inputs
    if (empty($talep_turu) ){
        $error = 'Talep türü seçmelisiniz';
    } elseif (empty($baslik)) {
        $error = 'Başlık girmelisiniz';
    } elseif (empty($aciklama)) {
        $error = 'Açıklama girmelisiniz';
    } else {
        // Insert into database
        try {
            $insertQuery = "
                INSERT INTO destek_talepleri 
                (musteri_id, talep_turu, kiralik_id, van_id, baslik, aciklama)
                VALUES 
                (:musteri_id, :talep_turu, :kiralik_id, :van_id, :baslik, :aciklama)
            ";
            $insertStmt = $databaseConnection->prepare($insertQuery);
            $insertStmt->bindParam(':musteri_id', $customer_id, PDO::PARAM_INT);
            $insertStmt->bindParam(':talep_turu', $talep_turu);
            $insertStmt->bindParam(':kiralik_id', $kiralik_id, $kiralik_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $insertStmt->bindParam(':van_id', $van_id, $van_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $insertStmt->bindParam(':baslik', $baslik);
            $insertStmt->bindParam(':aciklama', $aciklama);
            
            if ($insertStmt->execute()) {
                $message = 'Destek talebiniz başarıyla gönderildi. En kısa sürede dönüş yapılacaktır.';
            } else {
                $error = 'Destek talebi gönderilirken bir hata oluştu. Lütfen tekrar deneyin.';
            }
        } catch (PDOException $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Destek Talebi | VANLIFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="navbar_styles.css" />
    <link rel="stylesheet" href="dashboard_styles.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container py-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-black text-white">
                        <h3 class="mb-0">Destek Talebi Oluştur</h3>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                            <div class="text-center mt-3">
                                <a href="dashboard.php" class="btn btn-black">Ana Sayfaya Dön</a>
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="talep_turu" class="form-label">Talep Türü</label>
                                    <select class="form-select" id="talep_turu" name="talep_turu" required>
                                        <option value="">Seçiniz</option>
                                        <option value="kiralik_iptal">Kiralık İptal Talebi</option>
                                        <option value="kiralik_sorunu">Kiralık ile İlgili Sorun</option>
                                        <option value="van_sorunu">Van ile İlgili Sorun</option>
                                        <option value="diger">Diğer</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3" id="kiralikContainer" style="display:none;">
                                    <label for="kiralik_id" class="form-label">İlgili Kiralık</label>
                                    <select class="form-select" id="kiralik_id" name="kiralik_id">
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($rentals as $rental): ?>
                                            <option value="<?= $rental['kiralik_id'] ?>">
                                                #<?= $rental['kiralik_id'] ?> - <?= htmlspecialchars($rental['title']) ?> 
                                                (Durum: <?= $rental['kiralama_durumu'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3" id="vanContainer" style="display:none;">
                                    <label for="van_id" class="form-label">İlgili Van</label>
                                    <select class="form-select" id="van_id" name="van_id">
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($vans as $van): ?>
                                            <option value="<?= $van['van_id'] ?>">
                                                <?= htmlspecialchars($van['title']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="baslik" class="form-label">Başlık</label>
                                    <input type="text" class="form-control" id="baslik" name="baslik" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="aciklama" class="form-label">Açıklama</label>
                                    <textarea class="form-control" id="aciklama" name="aciklama" rows="5" required></textarea>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-dark">Gönder</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide rental and van selects based on complaint type
        document.getElementById('talep_turu').addEventListener('change', function() {
            const type = this.value;
            const rentalContainer = document.getElementById('kiralikContainer');
            const vanContainer = document.getElementById('vanContainer');
            
            rentalContainer.style.display = (type === 'kiralik_iptal' || type === 'kiralik_sorunu') ? 'block' : 'none';
            vanContainer.style.display = (type === 'van_sorunu') ? 'block' : 'none';
        });
    </script>
</body>
</html>