<?php
session_start();
require '../includes/auth.php';
cek_admin();
require '../includes/koneksi.php';

// Stats
$total_mobil   = $conn->query("SELECT COUNT(*) AS c FROM mobil")->fetch_assoc()['c'];
$total_sewa    = $conn->query("SELECT COUNT(*) AS c FROM penyewaan WHERE status='disewa'")->fetch_assoc()['c'];
$total_kembali = $conn->query("SELECT COUNT(*) AS c FROM penyewaan WHERE status='dikembalikan'")->fetch_assoc()['c'];
$total_denda   = $conn->query("SELECT COALESCE(SUM(denda),0) AS c FROM pengembalian")->fetch_assoc()['c'];

// Penyewaan terbaru
$recent = $conn->query("
    SELECT p.id_sewa, u.nama AS penyewa, m.nama_mobil, p.jumlah_sewa,
           p.tanggal_sewa, p.status
    FROM penyewaan p
    JOIN user u ON p.id_user = u.id_user
    JOIN mobil m ON p.id_mobil = m.id_mobil
    ORDER BY p.id_sewa DESC LIMIT 8
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — RentalKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary:#FF5722; --dark:#0D0D0D; --card:#1A1A1A; --border:#2A2A2A; }
        body { background:var(--dark); color:#E8E8E8; font-family:'DM Sans',sans-serif; }
        .stat-card {
            background:var(--card); border:1px solid var(--border); border-radius:16px;
            padding:1.5rem; transition:transform .2s;
        }
        .stat-card:hover { transform:translateY(-3px); }
        .stat-icon {
            width:48px; height:48px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.4rem;
        }
        .stat-num { font-family:'Syne',sans-serif; font-size:2rem; font-weight:800; line-height:1; }
        .table-dark { --bs-table-bg:#1A1A1A; --bs-table-border-color:#2A2A2A; }
        .badge-disewa    { background:#FF572222; color:#FF5722; border:1px solid #FF572244; }
        .badge-kembali   { background:#22c55e22; color:#22c55e; border:1px solid #22c55e44; }
        .section-title { font-family:'Syne',sans-serif; font-weight:700; font-size:1.1rem; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h1 class="mb-1" style="font-family:'Syne',sans-serif; font-size:1.8rem;">Dashboard</h1>
        <p style="color:#777;">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</p>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="stat-icon" style="background:#FF572222;">
                        <i class="bi bi-car-front" style="color:#FF5722;"></i>
                    </div>
                    <span style="color:#777; font-size:.85rem;">Total Mobil</span>
                </div>
                <div class="stat-num"><?= $total_mobil ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="stat-icon" style="background:#3b82f622;">
                        <i class="bi bi-key" style="color:#3b82f6;"></i>
                    </div>
                    <span style="color:#777; font-size:.85rem;">Sedang Disewa</span>
                </div>
                <div class="stat-num"><?= $total_sewa ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="stat-icon" style="background:#22c55e22;">
                        <i class="bi bi-check-circle" style="color:#22c55e;"></i>
                    </div>
                    <span style="color:#777; font-size:.85rem;">Dikembalikan</span>
                </div>
                <div class="stat-num"><?= $total_kembali ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="stat-icon" style="background:#f59e0b22;">
                        <i class="bi bi-cash-stack" style="color:#f59e0b;"></i>
                    </div>
                    <span style="color:#777; font-size:.85rem;">Total Denda</span>
                </div>
                <div class="stat-num" style="font-size:1.4rem;">Rp <?= number_format($total_denda,0,',','.') ?></div>
            </div>
        </div>
    </div>

    <!-- Recent rentals -->
    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="section-title"><i class="bi bi-clock-history me-2" style="color:#FF5722;"></i>Penyewaan Terbaru</span>
            <a href="penyewaan.php" class="btn btn-sm" style="background:#FF572220; color:#FF5722; border:none;">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="border-radius:10px; overflow:hidden;">
                <thead style="background:#111;">
                    <tr>
                        <th style="color:#777; font-weight:500;">#</th>
                        <th style="color:#777; font-weight:500;">Penyewa</th>
                        <th style="color:#777; font-weight:500;">Mobil</th>
                        <th style="color:#777; font-weight:500;">Jumlah</th>
                        <th style="color:#777; font-weight:500;">Tanggal Sewa</th>
                        <th style="color:#777; font-weight:500;">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $recent->fetch_assoc()): ?>
                    <tr>
                        <td style="color:#555;"><?= $row['id_sewa'] ?></td>
                        <td><?= htmlspecialchars($row['penyewa']) ?></td>
                        <td><?= htmlspecialchars($row['nama_mobil']) ?></td>
                        <td><?= $row['jumlah_sewa'] ?> unit</td>
                        <td style="color:#aaa;"><?= $row['tanggal_sewa'] ?></td>
                        <td>
                            <span class="badge rounded-pill px-3 py-1
                                <?= $row['status'] === 'disewa' ? 'badge-disewa' : 'badge-kembali' ?>">
                                <?= $row['status'] === 'disewa' ? 'Disewa' : 'Dikembalikan' ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
