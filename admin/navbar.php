<!-- NAVBAR ADMIN -->
<nav class="navbar navbar-expand-lg" style="background:#1A1A1A; border-bottom:1px solid #2A2A2A;">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="dashboard.php" style="font-family:'Syne',sans-serif; color:#fff; font-size:1.4rem; letter-spacing:-0.5px;">
            Rental<span style="color:#FF5722;">Ku</span>
            <span class="badge ms-2" style="background:#FF5722; font-size:.6rem; vertical-align:middle;">ADMIN</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin"
                style="color:#777;"><i class="bi bi-list fs-4"></i></button>
        <div class="collapse navbar-collapse" id="navAdmin">
            <ul class="navbar-nav me-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-3 <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>"
                       href="dashboard.php" style="color:#aaa;">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-3 <?= basename($_SERVER['PHP_SELF']) === 'mobil.php' ? 'active' : '' ?>"
                       href="mobil.php" style="color:#aaa;">
                        <i class="bi bi-car-front me-1"></i>Kelola Mobil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-3 <?= basename($_SERVER['PHP_SELF']) === 'penyewaan.php' ? 'active' : '' ?>"
                       href="penyewaan.php" style="color:#aaa;">
                        <i class="bi bi-clipboard-check me-1"></i>Penyewaan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-3 <?= basename($_SERVER['PHP_SELF']) === 'laporan.php' ? 'active' : '' ?>"
                       href="laporan.php" style="color:#aaa;">
                        <i class="bi bi-bar-chart me-1"></i>Laporan
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span style="color:#777; font-size:.85rem;"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['nama']) ?></span>
                <a href="../logout.php" class="btn btn-sm" style="background:#2A2A2A; color:#FF5722; border:1px solid #3A3A3A;">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>
<style>
    .nav-link.active { background:#FF572220 !important; color:#FF5722 !important; }
    .nav-link:hover:not(.active) { background:#222 !important; color:#fff !important; }
</style>
