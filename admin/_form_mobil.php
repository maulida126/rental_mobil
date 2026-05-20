<div class="mb-3">
    <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase; letter-spacing:.05em;">Nama Mobil</label>
    <input type="text" name="nama_mobil" class="form-control" placeholder="cth. Toyota Avanza" required>
</div>
<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase; letter-spacing:.05em;">Jumlah Unit</label>
        <input type="number" name="jumlah" class="form-control" min="0" placeholder="0" required>
    </div>
    <div class="col-6">
        <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase; letter-spacing:.05em;">Harga Sewa/Hari</label>
        <input type="number" name="harga_sewa" class="form-control" min="0" placeholder="250000" required>
    </div>
</div>
<div class="mb-3">
    <label class="form-label" style="color:#777; font-size:.82rem; text-transform:uppercase; letter-spacing:.05em;">Kondisi</label>
    <select name="kondisi" class="form-select" required>
        <option value="Baik">Baik</option>
        <option value="Rusak Ringan">Rusak Ringan</option>
        <option value="Rusak Berat">Rusak Berat</option>
        <option value="Dalam Perbaikan">Dalam Perbaikan</option>
    </select>
</div>
