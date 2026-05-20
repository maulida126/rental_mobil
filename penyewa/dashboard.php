<?php
session_start();
require '../includes/auth.php';
cek_penyewa();
require '../includes/koneksi.php';

$msg = $err = '';

// PROSES SEWA
if (isset($_POST['sewa'])) {
    $id_user   = $_SESSION['id_user'];
    $id_mobil  = (int)$_POST['id_mobil'];
    $jumlah    = (int)$_POST['jumlah_sewa'];
    $tgl_kembali = $_POST['tanggal_kembali_rencana'];

    $stmt = $conn->prepare("CALL sewa_mobil(?, ?, ?, ?)");
    $stmt->bind_param('iiis', $id_user, $id_mobil, $jumlah, $tgl_kembali);
    if ($stmt->execute()) {
        $msg = "Berhasil menyewa mobil! Silakan cek riwayat sewa Anda.";
    } else {
        // Ambil pesan error dari stored procedure
        $err = $conn->error ?: "Stok mobil tidak mencukupi!";
    }
}

// Ambil data mobil + status
$mobils = $conn->query("SELECT *, status_mobil(jumlah) AS status FROM mobil ORDER BY nama_mobil");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Mobil — RentalKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary:#FF5722; --dark:#0D0D0D; --card:#1A1A1A; --border:#2A2A2A; }
        body { background:var(--dark); color:#E8E8E8; font-family:'DM Sans',sans-serif; }
        .mobil-card {
            background:var(--card); border:1px solid var(--border); border-radius:16px;
            padding:1.4rem; transition:transform .2s, border-color .2s;
        }
        .mobil-card:hover { transform:translateY(-3px); border-color:#FF572244; }
        .mobil-card.unavailable { opacity:.55; }
        .badge-tersedia { background:#22c55e22; color:#22c55e; border:1px solid #22c55e44; }
        .badge-tidak    { background:#ef444422; color:#ef4444; border:1px solid #ef444444; }
        .btn-sewa {
            background:var(--primary); border:none; color:#fff;
            border-radius:10px; font-weight:600; width:100%; padding:.6rem;
            transition:opacity .2s;
        }
        .btn-sewa:disabled { opacity:.4; cursor:not-allowed; }
        .btn-sewa:hover:not(:disabled) { opacity:.85; color:#fff; }
        .modal-content { background:#1A1A1A; border:1px solid #2A2A2A; }
        .modal-header, .modal-footer { border-color:#2A2A2A; }
        .form-control, .form-select { background:#111; border:1px solid var(--border); color:#E8E8E8; border-radius:10px; }
        .form-control:focus, .form-select:focus { background:#111; border-color:var(--primary); color:#E8E8E8; box-shadow:0 0 0 3px rgba(255,87,34,.15); }
        .section-title { font-family:'Syne',sans-serif; font-weight:700; }
        .car-icon { font-size:2rem; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h1 class="mb-0 section-title" style="font-size:1.8rem;">Daftar Mobil</h1>
        <p style="color:#777;">Pilih dan sewa mobil yang tersedia</p>
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

    <div class="row g-3">
    <?php while ($m = $mobils->fetch_assoc()): ?>
        <?php $tersedia = $m['status'] === 'Tersedia'; ?>
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="mobil-card <?= !$tersedia ? 'unavailable' : '' ?>">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="car-icon">🚗</div>
                    <span class="badge rounded-pill px-3 py-1 <?= $tersedia ? 'badge-tersedia' : 'badge-tidak' ?>">
                        <?= $m['status'] ?>
                    </span>
                </div>
                <h6 class="section-title mb-1"><?= htmlspecialchars($m['nama_mobil']) ?></h6>
                <div style="color:#777; font-size:.82rem; margin-bottom:.8rem;">
                    <span><i class="bi bi-tools me-1"></i><?= htmlspecialchars($m['kondisi']) ?></span>
                    &nbsp;·&nbsp;
                    <span><i class="bi bi-stack me-1"></i><?= $m['jumlah'] ?> unit</span>
                </div>
                <div style="font-size:1.1rem; font-weight:700; color:var(--primary); margin-bottom:1rem;">
                    Rp <?= number_format($m['harga_sewa'],0,',','.') ?>
                    <span style="font-size:.75rem; font-weight:400; color:#777;">/hari</span>
                </div>
                <button class="btn btn-sewa"
                    <?= !$tersedia ? 'disabled' : '' ?>
                    onclick='openSewa(<?= json_encode($m) ?>)'>
                    <i class="bi bi-key me-2"></i><?= $tersedia ? 'Sewa Sekarang' : 'Tidak Tersedia' ?>
                </button>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>

<!-- Modal Sewa -->
<div class="modal fade" id="modalSewa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id_mobil" id="s_id_mobil">
                <div class="modal-header">
                    <h5 class="modal-title section-title">
                        <i class="bi bi-key me-2" style="color:var(--primary);"></i>Form Penyewaan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 rounded-3 mb-3" style="background:#111; border:1px solid #2A2A2A;">
                        <div class="section-title" id="s_nama" style="font-size:1.1rem;"></div>
                        <div style="color:#777; font-size:.85rem;">
                            Harga: <span id="s_harga" style="color:var(--primary); font-weight:700;"></span>/hari
                            &nbsp;·&nbsp; Stok: <span id="s_stok"></span> unit
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase;">Jumlah Unit Disewa</label>
                        <input type="number" name="jumlah_sewa" id="s_jumlah" class="form-control"
                               min="1" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase;">Tanggal Rencana Kembali</label>
                        <input type="date" name="tanggal_kembali_rencana" id="s_tgl_kembali" class="form-control" required>
                    </div>
                    <div class="p-3 rounded-3" style="background:#111; border:1px dashed #2A2A2A;">
                        <div style="color:#777; font-size:.82rem; margin-bottom:.2rem;">Estimasi Total Biaya</div>
                        <div id="s_estimasi" style="font-family:'Syne',sans-serif; font-size:1.4rem; font-weight:800; color:#22c55e;">—</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm" style="background:#2A2A2A;color:#aaa;border-radius:10px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="sewa" class="btn btn-sm btn-sewa px-4" style="width:auto;">
                        <i class="bi bi-check-lg me-1"></i>Konfirmasi Sewa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let hargaPerHari = 0;

function openSewa(m) {
    hargaPerHari = m.harga_sewa;
    document.getElementById('s_id_mobil').value  = m.id_mobil;
    document.getElementById('s_nama').textContent = m.nama_mobil;
    document.getElementById('s_harga').textContent = 'Rp ' + parseInt(m.harga_sewa).toLocaleString('id-ID');
    document.getElementById('s_stok').textContent  = m.jumlah;
    document.getElementById('s_jumlah').max        = m.jumlah;

    // default tanggal kembali = besok
    const besok = new Date(); besok.setDate(besok.getDate()+1);
    document.getElementById('s_tgl_kembali').value = besok.toISOString().slice(0,10);
    document.getElementById('s_tgl_kembali').min   = new Date().toISOString().slice(0,10);

    hitungEstimasi();
    new bootstrap.Modal(document.getElementById('modalSewa')).show();
}

function hitungEstimasi() {
    const jumlah = parseInt(document.getElementById('s_jumlah').value) || 1;
    const tglStr = document.getElementById('s_tgl_kembali').value;
    if (!tglStr) { document.getElementById('s_estimasi').textContent = '—'; return; }
    const today = new Date(); today.setHours(0,0,0,0);
    const tgl   = new Date(tglStr);
    const hari  = Math.max(1, Math.ceil((tgl - today) / 86400000));
    const total = jumlah * hari * hargaPerHari;
    document.getElementById('s_estimasi').textContent =
        'Rp ' + total.toLocaleString('id-ID') + ' (' + hari + ' hari)';
}

document.addEventListener('change', e => {
    if (e.target.id === 's_jumlah' || e.target.id === 's_tgl_kembali') hitungEstimasi();
});
document.addEventListener('input', e => {
    if (e.target.id === 's_jumlah') hitungEstimasi();
});
</script>
</body>
</html>
