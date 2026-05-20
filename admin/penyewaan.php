<?php
session_start();
require '../includes/auth.php';
cek_admin();
require '../includes/koneksi.php';

$msg = $err = '';

// PROSES PENGEMBALIAN (Tugas Praktik 1 & 2)
if (isset($_POST['kembalikan'])) {
    $id_sewa     = (int)$_POST['id_sewa'];
    $tgl_kembali = $_POST['tanggal_kembali'];
    $denda_hari  = (int)$_POST['denda_per_hari'];

    $stmt = $conn->prepare("CALL kembalikan_mobil(?, ?, ?)");
    $stmt->bind_param('isi', $id_sewa, $tgl_kembali, $denda_hari);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $info   = $result->fetch_assoc();
        $msg    = "Mobil berhasil dikembalikan! "
                . ($info['hari_terlambat'] > 0
                    ? "Terlambat {$info['hari_terlambat']} hari. Denda: Rp " . number_format($info['total_denda'],0,',','.')
                    : "Tepat waktu. Tidak ada denda.");
    } else {
        $err = "Gagal proses pengembalian: " . $conn->error;
    }
}

// Filter
$filter_status = $_GET['status'] ?? '';
$where = $filter_status ? "WHERE p.status='$filter_status'" : '';

$rows = $conn->query("
    SELECT p.*, u.nama AS penyewa, m.nama_mobil, m.harga_sewa,
           p.tanggal_kembali_rencana,
           k.tanggal_kembali AS tgl_aktual, k.denda, k.keterangan
    FROM penyewaan p
    JOIN user u  ON p.id_user  = u.id_user
    JOIN mobil m ON p.id_mobil = m.id_mobil
    LEFT JOIN pengembalian k ON p.id_sewa = k.id_sewa
    $where
    ORDER BY p.id_sewa DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penyewaan — RentalKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary:#FF5722; --dark:#0D0D0D; --card:#1A1A1A; --border:#2A2A2A; }
        body { background:var(--dark); color:#E8E8E8; font-family:'DM Sans',sans-serif; }
        .card-section { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:1.5rem; }
        .table-dark { --bs-table-bg:#1A1A1A; --bs-table-border-color:#2A2A2A; }
        .form-control, .form-select { background:#111; border:1px solid var(--border); color:#E8E8E8; border-radius:10px; }
        .form-control:focus, .form-select:focus { background:#111; border-color:var(--primary); color:#E8E8E8; box-shadow:0 0 0 3px rgba(255,87,34,.15); }
        .form-select option { background:#1A1A1A; }
        .btn-orange { background:var(--primary); border:none; color:#fff; border-radius:10px; font-weight:600; }
        .btn-orange:hover { opacity:.85; color:#fff; }
        .badge-disewa  { background:#FF572222; color:#FF5722; border:1px solid #FF572244; }
        .badge-kembali { background:#22c55e22; color:#22c55e; border:1px solid #22c55e44; }
        .modal-content { background:#1A1A1A; border:1px solid #2A2A2A; }
        .modal-header, .modal-footer { border-color:#2A2A2A; }
        .section-title { font-family:'Syne',sans-serif; font-weight:700; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h1 class="mb-0 section-title" style="font-size:1.8rem;">Data Penyewaan</h1>
        <p style="color:#777; margin:0;">Kelola transaksi sewa & pengembalian</p>
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3">
        <i class="bi bi-check-circle me-2"></i><?= $msg ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($err): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $err ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card-section mb-3">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <label style="color:#777; font-size:.85rem;">Filter Status:</label>
            <select name="status" class="form-select form-select-sm" style="width:auto;">
                <option value="">Semua</option>
                <option value="disewa"       <?= $filter_status === 'disewa'       ? 'selected' : '' ?>>Disewa</option>
                <option value="dikembalikan" <?= $filter_status === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
            </select>
            <button type="submit" class="btn btn-sm btn-orange px-3">Filter</button>
            <a href="penyewaan.php" class="btn btn-sm" style="background:#2A2A2A;color:#aaa;">Reset</a>
        </form>
    </div>

    <div class="card-section">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead style="background:#111;">
                    <tr>
                        <th style="color:#777;">#</th>
                        <th style="color:#777;">Penyewa</th>
                        <th style="color:#777;">Mobil</th>
                        <th style="color:#777;">Jml</th>
                        <th style="color:#777;">Tgl Sewa</th>
                        <th style="color:#777;">Tgl Rencana</th>
                        <th style="color:#777;">Status</th>
                        <th style="color:#777;">Denda</th>
                        <th style="color:#777;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($r = $rows->fetch_assoc()): ?>
                <tr>
                    <td style="color:#555;"><?= $r['id_sewa'] ?></td>
                    <td><?= htmlspecialchars($r['penyewa']) ?></td>
                    <td><?= htmlspecialchars($r['nama_mobil']) ?></td>
                    <td><?= $r['jumlah_sewa'] ?></td>
                    <td style="color:#aaa; font-size:.88rem;"><?= $r['tanggal_sewa'] ?></td>
                    <td style="color:#aaa; font-size:.88rem;"><?= $r['tanggal_kembali_rencana'] ?? '-' ?></td>
                    <td>
                        <span class="badge rounded-pill px-3 py-1 <?= $r['status'] === 'disewa' ? 'badge-disewa' : 'badge-kembali' ?>">
                            <?= $r['status'] === 'disewa' ? 'Disewa' : 'Dikembalikan' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($r['denda'] > 0): ?>
                            <span style="color:#f59e0b;">Rp <?= number_format($r['denda'],0,',','.') ?></span>
                        <?php else: ?>
                            <span style="color:#555;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['status'] === 'disewa'): ?>
                        <button class="btn btn-sm"
                            style="background:#22c55e22;color:#22c55e;border:1px solid #22c55e44;"
                            onclick="openKembali(<?= $r['id_sewa'] ?>, '<?= $r['tanggal_kembali_rencana'] ?>')">
                            <i class="bi bi-arrow-return-left me-1"></i>Kembalikan
                        </button>
                        <?php else: ?>
                            <span style="color:#555; font-size:.82rem;"><?= $r['tgl_aktual'] ?><br>
                            <span style="color:#777;"><?= htmlspecialchars($r['keterangan'] ?? '') ?></span></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Kembalikan -->
<div class="modal fade" id="modalKembali" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id_sewa" id="k_id_sewa">
                <div class="modal-header">
                    <h5 class="modal-title section-title">
                        <i class="bi bi-arrow-return-left me-2" style="color:#22c55e;"></i>Pengembalian Mobil
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase;">Tanggal Kembali Aktual</label>
                        <input type="date" name="tanggal_kembali" id="k_tgl" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase;">Denda per Hari (Rp)</label>
                        <input type="number" name="denda_per_hari" class="form-control" value="50000" min="0">
                        <div style="color:#555; font-size:.8rem; margin-top:.4rem;">
                            Denda dikenakan per unit per hari jika melewati tanggal rencana.
                        </div>
                    </div>
                    <div class="p-3 rounded-3" style="background:#111; border:1px dashed #2A2A2A;">
                        <span style="color:#777; font-size:.85rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Tanggal rencana kembali: <strong id="k_rencana" style="color:#E8E8E8;"></strong>
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm" style="background:#2A2A2A;color:#aaa;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="kembalikan" class="btn btn-sm px-4"
                        style="background:#22c55e;color:#fff;border:none;border-radius:10px;font-weight:600;">
                        Proses Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openKembali(id, rencana) {
    document.getElementById('k_id_sewa').value = id;
    document.getElementById('k_rencana').textContent = rencana || '-';
    document.getElementById('k_tgl').value = new Date().toISOString().slice(0,10);
    new bootstrap.Modal(document.getElementById('modalKembali')).show();
}
</script>
</body>
</html>
