<link rel="stylesheet" href="navbar_styles.css">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top py-3 px-2 px-md-4 fs-5">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold d-flex align-items-center me-4" href="dashboard.php">
            <i class="bi bi-truck-front me-2"></i> VANLIFE
        </a>

        <div class="d-flex flex-grow-1 justify-content-center">
            <ul class="navbar-nav d-flex flex-row justify-content-center">
                <li class="nav-item mx-2">
                    <a class="nav-link active fw-bold d-flex align-items-center" href="dashboard.php">
                        <i class="bi bi-house-door me-2"></i> Ana Sayfa
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link fw-bold d-flex align-items-center" href="vanlar.php">
                        <i class="bi bi-car-front me-2"></i> Vanları Keşfet
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link fw-bold d-flex align-items-center" href= "Aktif_Kiralamalar.php">
                        <i class="bi bi-calendar-check me-2"></i> Aktif Kiralamalar
                    </a>
                </li>
                <li class="nav-item dropdown mx-2">
                    <a class="nav-link fw-bold dropdown-toggle d-flex align-items-center" href="#" id="destekDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-life-preserver me-2"></i> Destek
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="destekDropdown">
                        <li><a class="dropdown-item d-flex align-items-center" href="dashboard.php#faq-section"><i class="bi bi-question-circle me-2"></i> SSS</a></li>
                        <li><a class="dropdown-item d-flex align-items-center" href="destek.php"><i class="bi bi-chat-dots me-2"></i> İletişime Geç</a></li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="d-flex align-items-center">
            <form class="d-flex" action="../logout.php" method="post">
                <button class="btn btn-outline-light fw-bold d-flex align-items-center" type="submit">
                    <i class="bi bi-box-arrow-right me-2"></i> Çıkış Yap
                </button>
            </form>
        </div>
    </div>
</nav>
