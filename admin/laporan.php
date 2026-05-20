<?php
session_start();
require '../includes/auth.php';
cek_admin();
require '../includes/koneksi.php';

$tgl_awal  = $_GET['tgl_awal']  ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$stmt = $conn->prepare("
    SELECT * FROM v_laporan_penyewaan
    WHERE tanggal_sewa BETWEEN ? AND ?
    ORDER BY id_sewa DESC
");
$stmt->bind_param('ss', $tgl_awal, $tgl_akhir);
$stmt->execute();
$rows = $stmt->get_result();

// Hitung total
$total_rows    = 0;
$total_biaya   = 0;
$total_denda   = 0;
$data_rows     = [];
while ($r = $rows->fetch_assoc()) {
    $data_rows[]  = $r;
    $total_biaya += (int)$r['total_biaya'];
    $total_denda += (int)$r['denda'];
    $total_rows++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penyewaan — RentalKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary:#FF5722; --dark:#0D0D0D; --card:#1A1A1A; --border:#2A2A2A; }
        body { background:var(--dark); color:#E8E8E8; font-family:'DM Sans',sans-serif; }
        .card-section { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:1.5rem; }
        .table-dark { --bs-table-bg:#1A1A1A; --bs-table-border-color:#2A2A2A; }
        .form-control { background:#111; border:1px solid var(--border); color:#E8E8E8; border-radius:10px; }
        .form-control:focus { background:#111; border-color:var(--primary); color:#E8E8E8; box-shadow:0 0 0 3px rgba(255,87,34,.15); }
        .btn-orange { background:var(--primary); border:none; color:#fff; border-radius:10px; font-weight:600; }
        .btn-orange:hover { opacity:.85; color:#fff; }
        .badge-disewa  { background:#FF572222; color:#FF5722; border:1px solid #FF572244; }
        .badge-kembali { background:#22c55e22; color:#22c55e; border:1px solid #22c55e44; }
        .section-title { font-family:'Syne',sans-serif; font-weight:700; }
        .stat-mini { background:#111; border:1px solid var(--border); border-radius:12px; padding:1rem 1.2rem; }
        @media print {
            nav, .no-print { display:none !important; }
            body { background:#fff !important; color:#000 !important; }
            .card-section { border:1px solid #ccc !important; }
            table { color:#000 !important; }
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0 section-title" style="font-size:1.8rem;">Laporan Penyewaan</h1>
            <p style="color:#777; margin:0;">Filter berdasarkan rentang tanggal</p>
        </div>
        <button onclick="window.print()" class="btn btn-sm no-print"
            style="background:#2A2A2A;color:#aaa;border:1px solid #3A3A3A; border-radius:10px;">
            <i class="bi bi-printer me-1"></i>Cetak
        </button>
    </div>

    <!-- Filter Form -->
    <div class="card-section mb-4 no-print">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase;">Tanggal Awal</label>
                <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase;">Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-orange px-4">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                <a href="laporan.php" class="btn" style="background:#2A2A2A;color:#aaa;border-radius:10px;">Reset</a>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="stat-mini">
                <div style="color:#777; font-size:.8rem; text-transform:uppercase; margin-bottom:.3rem;">Total Transaksi</div>
                <div style="font-family:'Syne',sans-serif; font-size:1.8rem; font-weight:800;"><?= $total_rows ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="stat-mini">
                <div style="color:#777; font-size:.8rem; text-transform:uppercase; margin-bottom:.3rem;">Total Pendapatan</div>
                <div style="font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:800; color:#22c55e;">
                    Rp <?= number_format($total_biaya,0,',','.') ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="stat-mini">
                <div style="color:#777; font-size:.8rem; text-transform:uppercase; margin-bottom:.3rem;">Total Denda</div>
                <div style="font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:800; color:#f59e0b;">
                    Rp <?= number_format($total_denda,0,',','.') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card-section">
        <div style="margin-bottom:1rem; color:#777; font-size:.85rem;">
            Periode: <strong style="color:#E8E8E8;"><?= $tgl_awal ?></strong> s/d
                     <strong style="color:#E8E8E8;"><?= $tgl_akhir ?></strong>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="font-size:.88rem;">
                <thead style="background:#111;">
                    <tr>
                        <th style="color:#777;">#</th>
                        <th style="color:#777;">Penyewa</th>
                        <th style="color:#777;">Mobil</th>
                        <th style="color:#777;">Jml</th>
                        <th style="color:#777;">Tgl Sewa</th>
                        <th style="color:#777;">Tgl Rencana</th>
                        <th style="color:#777;">Tgl Aktual</th>
                        <th style="color:#777;">Status</th>
                        <th style="color:#777;">Total Biaya</th>
                        <th style="color:#777;">Denda</th>
                        <th style="color:#777;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data_rows)): ?>
                    <tr><td colspan="11" class="text-center py-4" style="color:#555;">
                        Tidak ada data pada periode ini.
                    </td></tr>
                <?php else: ?>
                <?php foreach ($data_rows as $r): ?>
                <tr>
                    <td style="color:#555;"><?= $r['id_sewa'] ?></td>
                    <td><?= htmlspecialchars($r['nama_penyewa']) ?></td>
                    <td><?= htmlspecialchars($r['nama_mobil']) ?></td>
                    <td><?= $r['jumlah_sewa'] ?></td>
                    <td style="color:#aaa;"><?= $r['tanggal_sewa'] ?></td>
                    <td style="color:#aaa;"><?= $r['tanggal_kembali_rencana'] ?></td>
                    <td style="color:#aaa;"><?= $r['tanggal_kembali_aktual'] ?></td>
                    <td>
                        <span class="badge rounded-pill px-2 py-1 <?= $r['status'] === 'disewa' ? 'badge-disewa' : 'badge-kembali' ?>">
                            <?= $r['status'] ?>
                        </span>
                    </td>
                    <td style="color:#22c55e;">Rp <?= number_format($r['total_biaya'],0,',','.') ?></td>
                    <td style="color:<?= $r['denda'] > 0 ? '#f59e0b' : '#555' ?>;">
                        <?= $r['denda'] > 0 ? 'Rp ' . number_format($r['denda'],0,',','.') : '—' ?>
                    </td>
                    <td style="color:#777;"><?= htmlspecialchars($r['keterangan_kembali']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <?php if (!empty($data_rows)): ?>
                <tfoot style="background:#111; border-top:2px solid var(--border);">
                    <tr>
                        <td colspan="8" class="text-end" style="color:#777; font-weight:600;">TOTAL</td>
                        <td style="color:#22c55e; font-weight:700;">Rp <?= number_format($total_biaya,0,',','.') ?></td>
                        <td style="color:#f59e0b; font-weight:700;">Rp <?= number_format($total_denda,0,',','.') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
