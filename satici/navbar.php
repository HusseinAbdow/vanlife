<link rel="stylesheet" href="navbar_styles.css">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top mb-4 py-3 px-2 px-md-4 fs-5">    <div class="container d-flex justify-content-between align-items-center">
        <!-- Brand with icon (far left) -->
        <a class="navbar-brand fw-bold d-flex align-items-center me-4" href="dashboard.php" style="flex: 0 0 auto;">
            <i class="bi bi-truck-front me-2"></i> VANLIFE
        </a>

        <!-- Center nav items container -->
        <div class="d-flex flex-grow-1 justify-content-center">
            <ul class="navbar-nav d-flex flex-row justify-content-center">
                <li class="nav-item mx-2">
                    <a class="nav-link active fw-bold d-flex align-items-center" href="dashboard.php">
                        <i class="bi bi-house-door me-2"></i> Ana Sayfa
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link fw-bold d-flex align-items-center" href="listings/add.php">
                        <i class="bi bi-plus-circle me-2"></i> Van Ekle
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link fw-bold d-flex align-items-center" href="vanlarim.php">
                        <i class="bi bi-car-front me-2"></i> Vanlarım
                    </a>
                </li>
                <!-- Aktif Kiralarım Link for Seller -->
                <li class="nav-item mx-2">
                    <a class="nav-link fw-bold d-flex align-items-center" href="aktif_kiramalar.php">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Aktif Kiralarım
                    </a>
                </li>

                <!-- Dropdown for Destek -->
                <li class="nav-item dropdown mx-2">
                    <a class="nav-link fw-bold dropdown-toggle d-flex align-items-center" href="#" id="destekDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-life-preserver me-2"></i> Destek
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="destekDropdown">
                        <!-- Scroll to FAQ section on the same page -->
                        <li><a class="dropdown-item d-flex align-items-center" href="dashboard.php#faq-section"><i class="bi bi-question-circle me-2"></i> Sıkça Sorulan Sorular</a></li>

                        <!-- Navigate to destek.php -->
                        <li><a class="dropdown-item d-flex align-items-center" href="destek.php"><i class="bi bi-chat-dots me-2"></i> Destek ile İletişime Geç</a></li>
                    </ul>
                </li>
            </ul>
        </div>

        <!-- Profile dropdown with logout (far right) -->
        <div class="dropdown">
            <a class="btn btn-dark dropdown-toggle d-flex align-items-center" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['ad'] ?? 'Profil') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                <li>
                    <form action="../logout.php" method="post">
                        <button class="dropdown-item d-flex align-items-center" type="submit">
                            <i class="bi bi-box-arrow-right me-2"></i> Çıkış Yap
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>