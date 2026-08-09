<?php
session_start();
if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] != 'satici') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Destek | VANLIFE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="navbar_styles.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="bg-dark text-white p-5 rounded shadow text-center">
            <h1 class="mb-4">Destek ile İletişime Geçin</h1>
            <p class="fs-5">Herhangi bir sorunuz ya da geri bildiriminiz mi var? Aşağıdaki form aracılığıyla bizimle iletişime geçebilirsiniz.</p>
        </div>

        <div class="bg-white p-5 mt-4 rounded shadow-sm">
            <form>
                <div class="mb-3">
                    <label for="name" class="form-label">Ad Soyad</label>
                    <input type="text" class="form-control" id="name" placeholder="Adınızı girin" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">E-Posta</label>
                    <input type="email" class="form-control" id="email" placeholder="E-posta adresinizi girin" required>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Mesajınız</label>
                    <textarea class="form-control" id="message" rows="6" placeholder="Bize iletmek istediğiniz mesaj..." required></textarea>
                </div>

                <button type="submit" class="btn btn-dark px-4 py-2">Gönder</button>
            </form>
        </div>
    </div>

   <div class="class mt-5">
  <?php include '../footer.php'; ?>

</div>
</body>
</html>
