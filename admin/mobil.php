<?php
session_start();
require '../includes/auth.php';
cek_admin();
require '../includes/koneksi.php';

$msg = $err = '';

// TAMBAH MOBIL
if (isset($_POST['tambah'])) {
    $nama    = trim($_POST['nama_mobil']);
    $jumlah  = (int)$_POST['jumlah'];
    $kondisi = trim($_POST['kondisi']);
    $harga   = (int)str_replace(['.', ','], '', $_POST['harga_sewa']);

    $stmt = $conn->prepare("INSERT INTO mobil (nama_mobil, jumlah, kondisi, harga_sewa) VALUES (?,?,?,?)");
    $stmt->bind_param('sisi', $nama, $jumlah, $kondisi, $harga);
    $stmt->execute() ? $msg = "Mobil berhasil ditambahkan!" : $err = "Gagal menambahkan mobil.";
}

// EDIT MOBIL
if (isset($_POST['edit'])) {
    $id      = (int)$_POST['id_mobil'];
    $nama    = trim($_POST['nama_mobil']);
    $jumlah  = (int)$_POST['jumlah'];
    $kondisi = trim($_POST['kondisi']);
    $harga   = (int)$_POST['harga_sewa'];

    $stmt = $conn->prepare("UPDATE mobil SET nama_mobil=?, jumlah=?, kondisi=?, harga_sewa=? WHERE id_mobil=?");
    $stmt->bind_param('sisis', $nama, $jumlah, $kondisi, $harga, $id);
    
    if ($stmt->execute()) {
        $msg = "Berhasil! Rows affected: " . $stmt->affected_rows;
    } else {
        $err = "Error: " . $stmt->error . " | errno: " . $stmt->errno;
    }
}

// HAPUS MOBIL
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM mobil WHERE id_mobil=$id")
        ? $msg = "Mobil berhasil dihapus."
        : $err = "Gagal menghapus (mungkin masih ada penyewaan aktif).";
}

$mobils = $conn->query("SELECT *, status_mobil(jumlah) AS status FROM mobil ORDER BY id_mobil DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mobil — RentalKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary:#FF5722; --dark:#0D0D0D; --card:#1A1A1A; --border:#2A2A2A; }
        body { background:var(--dark); color:#E8E8E8; font-family:'DM Sans',sans-serif; }
        .card-section { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:1.5rem; }
        .table-dark { --bs-table-bg:#1A1A1A; --bs-table-border-color:#2A2A2A; }
        .form-control, .form-select {
            background:#111; border:1px solid var(--border); color:#E8E8E8; border-radius:10px;
        }
        .form-control:focus, .form-select:focus {
            background:#111; border-color:var(--primary); color:#E8E8E8;
            box-shadow:0 0 0 3px rgba(255,87,34,.15);
        }
        .form-select option { background:#1A1A1A; }
        .btn-orange { background:var(--primary); border:none; color:#fff; border-radius:10px; font-weight:600; }
        .btn-orange:hover { opacity:.85; color:#fff; }
        .badge-tersedia    { background:#22c55e22; color:#22c55e; border:1px solid #22c55e44; }
        .badge-tidak       { background:#ef444422; color:#ef4444; border:1px solid #ef444444; }
        .modal-content { background:#1A1A1A; border:1px solid #2A2A2A; }
        .modal-header { border-color:#2A2A2A; }
        .modal-footer { border-color:#2A2A2A; }
        .section-title { font-family:'Syne',sans-serif; font-weight:700; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0 section-title" style="font-size:1.8rem;">Kelola Mobil</h1>
            <p style="color:#777; margin:0;">Tambah, edit, dan hapus data armada</p>
        </div>
        <button class="btn btn-orange px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg me-2"></i>Tambah Mobil
        </button>
    </div>

    <?php if ($msg): ?><div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= $msg ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= $err ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div><?php endif; ?>

    <div class="card-section">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead style="background:#111;">
                    <tr>
                        <th style="color:#777;">#</th>
                        <th style="color:#777;">Nama Mobil</th>
                        <th style="color:#777;">Jumlah</th>
                        <th style="color:#777;">Kondisi</th>
                        <th style="color:#777;">Harga/Hari</th>
                        <th style="color:#777;">Status</th>
                        <th style="color:#777;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($m = $mobils->fetch_assoc()): ?>
                <tr>
                    <td style="color:#555;"><?= $m['id_mobil'] ?></td>
                    <td><strong><?= htmlspecialchars($m['nama_mobil']) ?></strong></td>
                    <td><?= $m['jumlah'] ?> unit</td>
                    <td><?= htmlspecialchars($m['kondisi']) ?></td>
                    <td>Rp <?= number_format($m['harga_sewa'],0,',','.') ?></td>
                    <td>
                        <span class="badge rounded-pill px-3 py-1 <?= $m['status'] === 'Tersedia' ? 'badge-tersedia' : 'badge-tidak' ?>">
                            <?= $m['status'] ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm me-1"
                            style="background:#3b82f622;color:#3b82f6;border:1px solid #3b82f644;"
                            onclick='openEdit(<?= json_encode($m) ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="?hapus=<?= $m['id_mobil'] ?>"
                           class="btn btn-sm"
                           style="background:#ef444422;color:#ef4444;border:1px solid #ef444444;"
                           onclick="return confirm('Hapus mobil ini?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title section-title"><i class="bi bi-plus-circle me-2" style="color:#FF5722;"></i>Tambah Mobil</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php include '_form_mobil.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm" style="background:#2A2A2A;color:#aaa;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-orange btn-sm px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id_mobil" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title section-title"><i class="bi bi-pencil-square me-2" style="color:#3b82f6;"></i>Edit Mobil</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase; letter-spacing:.05em;">Nama Mobil</label>
                        <input type="text" name="nama_mobil" id="edit_nama" class="form-control" placeholder="cth. Toyota Avanza" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase; letter-spacing:.05em;">Jumlah Unit</label>
                            <input type="number" name="jumlah" id="edit_jumlah" class="form-control" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase; letter-spacing:.05em;">Harga Sewa/Hari</label>
                            <input type="number" name="harga_sewa" id="edit_harga" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase; letter-spacing:.05em;">Kondisi</label>
                        <select name="kondisi" id="edit_kondisi" class="form-select" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                            <option value="Dalam Perbaikan">Dalam Perbaikan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm" style="background:#2A2A2A;color:#aaa;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-orange btn-sm px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEdit(data) {
    document.getElementById('edit_id').value     = data.id_mobil;
    document.getElementById('edit_nama').value   = data.nama_mobil;
    document.getElementById('edit_jumlah').value = data.jumlah;
    document.getElementById('edit_harga').value  = data.harga_sewa;
    document.getElementById('edit_kondisi').value = data.kondisi;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
</body>
</html>