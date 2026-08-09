<?php
require_once(__DIR__ . '/configs/database.php');
session_start();

$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = htmlspecialchars($_POST['email']);
  $sifre = htmlspecialchars($_POST['sifre']);

  $stmt = $databaseConnection->prepare("SELECT * FROM kullanici WHERE email = ?");
  $stmt->execute([$email]);
  $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
    $_SESSION['kullanici_id'] = $kullanici['id'];
    $_SESSION['email'] = $kullanici['email'];
    $_SESSION['rol'] = $kullanici['rol'];

    switch ($kullanici['rol']) {
      case 'admin':
        header("Location: admin/dashboard.php");
        break;
      case 'satici':
        header("Location: satici/dashboard.php");
        break;
      case 'musteri':
        header("Location: musteri/dashboard.php");
        break;
      default:
        header("Location: login.php");
        break;
    }
    exit();
  } else {
    $hata = "Geçersiz email veya şifre.";
  }
}
?>

<!DOCTYPE html>
<html lang="tr">
  <head>
    <meta charset="UTF-8" />
    <title>VAN LIFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="login_styles.css">
  </head>
  <body>
    <div class="container">
      <div class="col-md-6 offset-md-3 mt-5 login-container">
        <h2 class="text-center login-title">Kontrol Panelinize Giriş Yapın</h2>

        <?php if (!empty($hata)) {
          echo '<div class="text-danger mb-3">'.$hata.'</div>';
        } ?>

        <form method="post" action="">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" placeholder="email@example.com" required>
          </div>

          <div class="mb-3">
            <label for="sifre" class="form-label">Şifre</label>
            <input type="password" class="form-control" name="sifre" required>
          </div>

          <button type="submit" class="btn btn-login w-100">Giriş Yap</button>
        </form>

        <div class="mt-3 text-center link">
          <a href="registration.php">Hesabınız yok mu? Kayıt olun</a>
        </div>
      </div>
    </div>
  </body>
</html>
