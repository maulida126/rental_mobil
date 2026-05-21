-- ============================================
--   DATABASE RENTAL MOBIL
--   Lengkap dengan Tugas Praktik
-- ============================================

CREATE DATABASE IF NOT EXISTS rental_mobil;
USE rental_mobil;

-- ============================================
-- LANGKAH 2: TABEL USER
-- ============================================
CREATE TABLE user (
    id_user   INT AUTO_INCREMENT PRIMARY KEY,
    nama      VARCHAR(100),
    username  VARCHAR(50) UNIQUE,
    password  VARCHAR(100),
    role      ENUM('admin','penyewa')
);

-- Insert akun default
INSERT INTO user (nama, username, password, role) VALUES
('Administrator', 'admin', MD5('admin123'), 'admin'),
('Budi Santoso',  'budi',  MD5('budi123'),  'penyewa');


-- ============================================
-- LANGKAH 3: TABEL MOBIL
-- ============================================
CREATE TABLE mobil (
    id_mobil   INT AUTO_INCREMENT PRIMARY KEY,
    nama_mobil VARCHAR(100),
    jumlah     INT          DEFAULT 0,
    kondisi    VARCHAR(50),
    harga_sewa INT
);

-- Insert data mobil contoh
INSERT INTO mobil (nama_mobil, jumlah, kondisi, harga_sewa) VALUES
('Toyota Avanza',   5, 'Baik',  250000),
('Honda Brio',      3, 'Baik',  200000),
('Daihatsu Xenia',  4, 'Baik',  230000),
('Mitsubishi Xpander', 2, 'Baik', 350000),
('Suzuki Ertiga',   2, 'Rusak Ringan', 220000);


-- ============================================
-- LANGKAH 4: TABEL PENYEWAAN
-- ============================================
CREATE TABLE penyewaan (
    id_sewa      INT AUTO_INCREMENT PRIMARY KEY,
    id_user      INT,
    id_mobil     INT,
    jumlah_sewa  INT,
    tanggal_sewa DATE,
    tanggal_kembali_rencana DATE,   -- tambahan: rencana kembali
    status       ENUM('disewa','dikembalikan') DEFAULT 'disewa',
    FOREIGN KEY (id_user)  REFERENCES user(id_user),
    FOREIGN KEY (id_mobil) REFERENCES mobil(id_mobil)
);


-- ============================================
-- TUGAS PRAKTIK 1: TABEL PENGEMBALIAN
-- ============================================
CREATE TABLE pengembalian (
    id_kembali        INT AUTO_INCREMENT PRIMARY KEY,
    id_sewa           INT,
    tanggal_kembali   DATE,
    denda             INT DEFAULT 0,   -- dalam rupiah
    keterangan        VARCHAR(200),
    FOREIGN KEY (id_sewa) REFERENCES penyewaan(id_sewa)
);


-- ============================================
-- LANGKAH 5: PROCEDURE SEWA MOBIL
-- ============================================
DELIMITER //
CREATE PROCEDURE sewa_mobil(
    IN p_id_user  INT,
    IN p_id_mobil INT,
    IN p_jumlah   INT,
    IN p_tgl_kembali DATE
)
BEGIN
    -- Cek stok dulu
    DECLARE stok_sekarang INT;
    SELECT jumlah INTO stok_sekarang FROM mobil WHERE id_mobil = p_id_mobil;

    IF stok_sekarang < p_jumlah THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Stok mobil tidak mencukupi!';
    ELSE
        INSERT INTO penyewaan (id_user, id_mobil, jumlah_sewa, tanggal_sewa, tanggal_kembali_rencana, status)
        VALUES (p_id_user, p_id_mobil, p_jumlah, CURDATE(), p_tgl_kembali, 'disewa');

        UPDATE mobil
        SET jumlah = jumlah - p_jumlah
        WHERE id_mobil = p_id_mobil;
    END IF;
END //
DELIMITER ;


-- ============================================
-- TUGAS PRAKTIK 1 & 2: PROCEDURE KEMBALIKAN MOBIL + DENDA
-- ============================================
DELIMITER //
CREATE PROCEDURE kembalikan_mobil(
    IN p_id_sewa        INT,
    IN p_tgl_kembali    DATE,
    IN p_denda_per_hari INT   -- denda per hari keterlambatan (Rp)
)
BEGIN
    DECLARE v_id_mobil       INT;
    DECLARE v_jumlah_sewa    INT;
    DECLARE v_tgl_rencana    DATE;
    DECLARE v_hari_terlambat INT DEFAULT 0;
    DECLARE v_denda          INT DEFAULT 0;

    -- Ambil data penyewaan
    SELECT id_mobil, jumlah_sewa, tanggal_kembali_rencana
    INTO v_id_mobil, v_jumlah_sewa, v_tgl_rencana
    FROM penyewaan
    WHERE id_sewa = p_id_sewa;

    -- Hitung keterlambatan
    IF p_tgl_kembali > v_tgl_rencana THEN
        SET v_hari_terlambat = DATEDIFF(p_tgl_kembali, v_tgl_rencana);
        SET v_denda = v_hari_terlambat * p_denda_per_hari * v_jumlah_sewa;
    END IF;

    -- Catat pengembalian
    INSERT INTO pengembalian (id_sewa, tanggal_kembali, denda, keterangan)
    VALUES (
        p_id_sewa,
        p_tgl_kembali,
        v_denda,
        IF(v_hari_terlambat > 0,
            CONCAT('Terlambat ', v_hari_terlambat, ' hari'),
            'Tepat waktu')
    );

    -- Update status penyewaan
    UPDATE penyewaan SET status = 'dikembalikan' WHERE id_sewa = p_id_sewa;

    -- Kembalikan stok mobil
    UPDATE mobil
    SET jumlah = jumlah + v_jumlah_sewa
    WHERE id_mobil = v_id_mobil;

    -- Kembalikan nilai denda untuk ditampilkan
    SELECT v_denda AS total_denda, v_hari_terlambat AS hari_terlambat;
END //
DELIMITER ;


-- ============================================
-- LANGKAH 6: FUNCTION STATUS MOBIL
-- ============================================
DELIMITER //
CREATE FUNCTION status_mobil(jumlah INT)
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE hasil VARCHAR(20);
    IF jumlah <= 0 THEN
        SET hasil = 'Tidak Tersedia';
    ELSE
        SET hasil = 'Tersedia';
    END IF;
    RETURN hasil;
END //
DELIMITER ;


-- ============================================
-- TUGAS PRAKTIK 3: VIEW LAPORAN PENYEWAAN
-- (Bisa difilter per tanggal dari PHP)
-- ============================================
CREATE VIEW v_laporan_penyewaan AS
SELECT
    p.id_sewa,
    u.nama                          AS nama_penyewa,
    m.nama_mobil,
    m.harga_sewa,
    p.jumlah_sewa,
    p.tanggal_sewa,
    p.tanggal_kembali_rencana,
    p.status,
    (m.harga_sewa * p.jumlah_sewa
        * DATEDIFF(p.tanggal_kembali_rencana, p.tanggal_sewa))
                                    AS total_biaya,
    COALESCE(k.tanggal_kembali, '-') AS tanggal_kembali_aktual,
    COALESCE(k.denda, 0)            AS denda,
    COALESCE(k.keterangan, '-')     AS keterangan_kembali
FROM penyewaan p
JOIN user u        ON p.id_user  = u.id_user
JOIN mobil m       ON p.id_mobil = m.id_mobil
LEFT JOIN pengembalian k ON p.id_sewa = k.id_sewa;


-- ============================================
-- CONTOH QUERY LAPORAN PER TANGGAL
-- (dipakai di PHP dengan parameter $tgl_awal & $tgl_akhir)
-- ============================================
-- SELECT * FROM v_laporan_penyewaan
-- WHERE tanggal_sewa BETWEEN '2025-01-01' AND '2025-12-31';
