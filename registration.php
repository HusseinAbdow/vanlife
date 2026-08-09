<?php
require_once(__DIR__ . '/configs/database.php');

if (isset($_POST['submit'])) {
  $hata = false;
  $hata_email = "";
  $hata_sifre = "";

  $email = htmlspecialchars($_POST['email']);
  $ad = htmlspecialchars($_POST['ad']);
  $soyad = htmlspecialchars($_POST['soyad']);
  $sehir = htmlspecialchars($_POST['sehir']);
  $adres = htmlspecialchars($_POST['adres']);
  $telefon = htmlspecialchars($_POST['telefon']);
  $sifre = htmlspecialchars($_POST['sifre']);
  $sifre_tekrar = htmlspecialchars($_POST['sifre_tekrar']);
  $rol = htmlspecialchars($_POST['rol']);

  if (!empty($email) && !empty($sifre) && !empty($sifre_tekrar)) {
    $kontrolEmail = $databaseConnection->prepare('SELECT email FROM kullanici WHERE email=?');
    $kontrolEmail->execute([$email]);

    if ($kontrolEmail->rowCount() >= 1) {
      $hata = true;
      $hata_email = 'Bu email adresi zaten kayıtlı';
    } else {
      if ($sifre == $sifre_tekrar) {
        $hashedPassword = password_hash($sifre, PASSWORD_DEFAULT);

        $ekleKullanici = $databaseConnection->prepare(
          'INSERT INTO kullanici (email, ad, soyad, sehir, adres, telefon, sifre, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $ekleKullanici->execute([$email, $ad, $soyad, $sehir, $adres, $telefon, $hashedPassword, $rol]);

        header("Location: login.php?kayit=1");
        exit();
      } else {
        $hata = true;
        $hata_sifre = 'Şifreler uyuşmuyor';
      }
    }
  } else {
    $hata = true;
    $hata_email = 'Email gerekli';
    $hata_sifre = 'Şifre gerekli';
  }
}
?>

<!DOCTYPE html>
<html lang="tr">
  <head>
    <meta charset="UTF-8" />
    <title>Kayıt Ol | VAN LIFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="registration_styles.css">
  </head>
  <body>
    <div class="container">
      <div class="col-md-6 offset-md-3 mt-5 registration-container">
        <h2 class="text-center registration-title">Kayıt Ol</h2>

        <form method="post">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
            <?php if (!empty($hata_email)) echo '<div class="text-danger mt-1">'.$hata_email.'</div>'; ?>
          </div>

          <div class="mb-3">
            <label for="ad" class="form-label">Ad</label>
            <input type="text" name="ad" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="soyad" class="form-label">Soyad</label>
            <input type="text" name="soyad" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="sehir" class="form-label">Şehir</label>
            <input type="text" name="sehir" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="adres" class="form-label">Adres</label>
            <textarea name="adres" class="form-control" required></textarea>
          </div>

          <div class="mb-3">
            <label for="telefon" class="form-label">Telefon</label>
            <input type="text" name="telefon" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="sifre" class="form-label">Şifre</label>
            <input type="password" name="sifre" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="sifre_tekrar" class="form-label">Şifreyi Tekrarla</label>
            <input type="password" name="sifre_tekrar" class="form-control" required>
            <?php if (!empty($hata_sifre)) echo '<div class="text-danger mt-1">'.$hata_sifre.'</div>'; ?>
          </div>

          <div class="mb-3">
            <label for="rol" class="form-label">Rol Seç</label>
            <select name="rol" class="form-select" required>
              <option value="musteri">Müşteri</option>
              <option value="satici">Satıcı</option>
            </select>
          </div>

          <button type="submit" name="submit" class="btn btn-register w-100">Kayıt Ol</button>
        </form>

        <div class="mt-3 text-center link">
          <a href="login.php">Zaten hesabınız var mı? Giriş yapın</a>
        </div>
      </div>
    </div>
  </body>
</html>
