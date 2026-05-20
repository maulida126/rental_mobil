<?php
session_start();
require '../includes/auth.php';
cek_penyewa();
require '../includes/koneksi.php';

$id_user = $_SESSION['id_user'];

$rows = $conn->query("
    SELECT p.id_sewa, m.nama_mobil, m.harga_sewa, p.jumlah_sewa,
           p.tanggal_sewa, p.tanggal_kembali_rencana, p.status,
           k.tanggal_kembali AS tgl_aktual, k.denda, k.keterangan,
           (m.harga_sewa * p.jumlah_sewa
               * DATEDIFF(p.tanggal_kembali_rencana, p.tanggal_sewa)) AS total_biaya
    FROM penyewaan p
    JOIN mobil m ON p.id_mobil = m.id_mobil
    LEFT JOIN pengembalian k ON p.id_sewa = k.id_sewa
    WHERE p.id_user = $id_user
    ORDER BY p.id_sewa DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Sewa — RentalKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary:#FF5722; --dark:#0D0D0D; --card:#1A1A1A; --border:#2A2A2A; }
        body { background:var(--dark); color:#E8E8E8; font-family:'DM Sans',sans-serif; }
        .card-section { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:1.5rem; }
        .table-dark { --bs-table-bg:#1A1A1A; --bs-table-border-color:#2A2A2A; }
        .badge-disewa  { background:#FF572222; color:#FF5722; border:1px solid #FF572244; }
        .badge-kembali { background:#22c55e22; color:#22c55e; border:1px solid #22c55e44; }
        .section-title { font-family:'Syne',sans-serif; font-weight:700; }
        .empty-state { text-align:center; padding:4rem 2rem; color:#555; }
        .empty-state .icon { font-size:3rem; margin-bottom:1rem; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h1 class="mb-0 section-title" style="font-size:1.8rem;">Riwayat Sewa</h1>
        <p style="color:#777;">Semua transaksi penyewaan Anda</p>
    </div>

    <div class="card-section">
        <?php if ($rows->num_rows === 0): ?>
        <div class="empty-state">
            <div class="icon">🚗</div>
            <div style="font-size:1.1rem; margin-bottom:.5rem; color:#777;">Belum ada riwayat sewa</div>
            <a href="dashboard.php" class="btn btn-sm mt-2"
               style="background:#FF5722;color:#fff;border:none;border-radius:10px;font-weight:600;">
                Sewa Mobil Sekarang
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="font-size:.88rem;">
                <thead style="background:#111;">
                    <tr>
                        <th style="color:#777;">#</th>
                        <th style="color:#777;">Mobil</th>
                        <th style="color:#777;">Jml</th>
                        <th style="color:#777;">Tgl Sewa</th>
                        <th style="color:#777;">Rencana Kembali</th>
                        <th style="color:#777;">Tgl Kembali</th>
                        <th style="color:#777;">Status</th>
                        <th style="color:#777;">Total Biaya</th>
                        <th style="color:#777;">Denda</th>
                        <th style="color:#777;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($r = $rows->fetch_assoc()): ?>
                <tr>
                    <td style="color:#555;"><?= $r['id_sewa'] ?></td>
                    <td><strong><?= htmlspecialchars($r['nama_mobil']) ?></strong></td>
                    <td><?= $r['jumlah_sewa'] ?></td>
                    <td style="color:#aaa;"><?= $r['tanggal_sewa'] ?></td>
                    <td style="color:#aaa;"><?= $r['tanggal_kembali_rencana'] ?? '-' ?></td>
                    <td style="color:#aaa;"><?= $r['tgl_aktual'] ?? '—' ?></td>
                    <td>
                        <span class="badge rounded-pill px-3 py-1
                            <?= $r['status'] === 'disewa' ? 'badge-disewa' : 'badge-kembali' ?>">
                            <?= $r['status'] === 'disewa' ? 'Disewa' : 'Dikembalikan' ?>
                        </span>
                    </td>
                    <td style="color:#22c55e;">Rp <?= number_format($r['total_biaya'],0,',','.') ?></td>
                    <td style="color:<?= ($r['denda'] ?? 0) > 0 ? '#f59e0b' : '#555' ?>;">
                        <?= ($r['denda'] ?? 0) > 0 ? 'Rp '.number_format($r['denda'],0,',','.') : '—' ?>
                    </td>
                    <td style="color:#777;"><?= htmlspecialchars($r['keterangan'] ?? '—') ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
