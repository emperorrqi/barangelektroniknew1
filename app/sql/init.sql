CREATE DATABASE IF NOT EXISTS manajemengudang;
USE manajemengudang;

GRANT ALL PRIVILEGES ON *.* TO 'user'@'%' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
-- ============================
-- TABEL MASTER
-- ============================

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL
);

INSERT INTO users (username, password) VALUES (
  'admin',
  '$2y$10$y0PwCPJf4uXjW6dkqJdIieDz.XgChB4G5HgM0WsmhOrJ3eEFvS5bC'
);

CREATE TABLE master_administrasi (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    kode_admin VARCHAR(20) UNIQUE,
    nama_admin VARCHAR(100)
);

CREATE TABLE master_barang_elektronik (
    id_barang INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(20) UNIQUE,
    nama_barang VARCHAR(150),
    spesifikasi VARCHAR(50),
    kategori VARCHAR(50),
    satuan VARCHAR(30) DEFAULT 'Unit',
    stok INT DEFAULT 0
);

CREATE TABLE master_driver (
    id_driver INT AUTO_INCREMENT PRIMARY KEY,
    kode_driver VARCHAR(20) UNIQUE,
    nama_driver VARCHAR(100),
    no_hp VARCHAR(30),
    alamat TEXT
);

CREATE TABLE master_gudang (
    id_gudang INT AUTO_INCREMENT PRIMARY KEY,
    kode_gudang VARCHAR(20) UNIQUE,
    nama_gudang VARCHAR(100),
    lokasi TEXT
);

-- ============================
-- TABEL TRANSAKSI
-- ============================

CREATE TABLE trx_barang_pesanan (
    id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(30),
    tanggal DATE,
    id_barang INT,
    id_gudang INT,
    id_admin INT,
    jumlah INT,
    FOREIGN KEY (id_barang) REFERENCES master_barang_elektronik(id_barang),
    FOREIGN KEY (id_gudang) REFERENCES master_gudang(id_gudang),
    FOREIGN KEY (id_admin) REFERENCES master_administrasi(id_admin)
);

CREATE TABLE trx_surat_jalan (
    id_surat INT AUTO_INCREMENT PRIMARY KEY,
    kode_surat VARCHAR(30),
    tanggal DATE,
    id_driver INT,
    id_gudang INT,
    keterangan TEXT,
    FOREIGN KEY (id_driver) REFERENCES master_driver(id_driver),
    FOREIGN KEY (id_gudang) REFERENCES master_gudang(id_gudang)
);


CREATE TABLE trx_retur (
    id_retur INT AUTO_INCREMENT PRIMARY KEY,
    kode_retur VARCHAR(30),
    tanggal DATE,
    id_barang INT,
    jumlah INT,
    alasan TEXT,
    FOREIGN KEY (id_barang) REFERENCES master_barang_elektronik(id_barang)
);

CREATE TABLE trx_berita_serah_terima (
    id_serah INT AUTO_INCREMENT PRIMARY KEY,
    kode_basterima VARCHAR(30),
    tanggal DATE,
    penerima VARCHAR(100),
    id_barang INT,
    jumlah INT,
    FOREIGN KEY (id_barang) REFERENCES master_barang_elektronik(id_barang)
);

-- ============================
-- TABEL LAPORAN
-- ============================

CREATE TABLE laporan_arsip (
    id_laporan INT AUTO_INCREMENT PRIMARY KEY,
    kode_laporan VARCHAR(30),
    jenis_laporan VARCHAR(50),
    tanggal DATE,
    file_path VARCHAR(255)
);

-- ============================
-- TRIGGER KODE OTOMATIS
-- ============================

DELIMITER //

-- master_administrasi
CREATE TRIGGER trg_kode_admin BEFORE INSERT ON master_administrasi
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(20);
    DECLARE newKode VARCHAR(20);
    DECLARE nomor INT;

    SELECT kode_admin INTO lastKode
    FROM master_administrasi
    ORDER BY id_admin DESC
    LIMIT 1;

    IF lastKode IS NULL THEN
        SET newKode = 'ADM001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,4) AS UNSIGNED) + 1;
        SET newKode = CONCAT('ADM', LPAD(nomor,3,'0'));
    END IF;

    SET NEW.kode_admin = newKode;
END//

-- master_barang_elektronik
CREATE TRIGGER trg_kode_barang BEFORE INSERT ON master_barang_elektronik
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(20);
    DECLARE newKode VARCHAR(20);
    DECLARE nomor INT;

    SELECT kode_barang INTO lastKode
    FROM master_barang_elektronik
    ORDER BY id_barang DESC
    LIMIT 1;

    IF lastKode IS NULL THEN
        SET newKode = 'BRG001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,4) AS UNSIGNED) + 1;
        SET newKode = CONCAT('BRG', LPAD(nomor,3,'0'));
    END IF;

    SET NEW.kode_barang = newKode;
END//

-- master_driver
CREATE TRIGGER trg_kode_driver BEFORE INSERT ON master_driver
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(20);
    DECLARE newKode VARCHAR(20);
    DECLARE nomor INT;

    SELECT kode_driver INTO lastKode
    FROM master_driver
    ORDER BY id_driver DESC
    LIMIT 1;

    IF lastKode IS NULL THEN
        SET newKode = 'DRV001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,4) AS UNSIGNED) + 1;
        SET newKode = CONCAT('DRV', LPAD(nomor,3,'0'));
    END IF;

    SET NEW.kode_driver = newKode;
END//

-- master_gudang
CREATE TRIGGER trg_kode_gudang BEFORE INSERT ON master_gudang
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(20);
    DECLARE newKode VARCHAR(20);
    DECLARE nomor INT;

    SELECT kode_gudang INTO lastKode
    FROM master_gudang
    ORDER BY id_gudang DESC
    LIMIT 1;

    IF lastKode IS NULL THEN
        SET newKode = 'GDN001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,4) AS UNSIGNED) + 1;
        SET newKode = CONCAT('GDN', LPAD(nomor,3,'0'));
    END IF;

    SET NEW.kode_gudang = newKode;
END//

-- trx_barang_pesanan
CREATE TRIGGER trg_kode_pesanan BEFORE INSERT ON trx_barang_pesanan
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(30);
    DECLARE newKode VARCHAR(30);
    DECLARE nomor INT;

    SELECT kode_pesanan INTO lastKode
    FROM trx_barang_pesanan
    ORDER BY id_pesanan DESC
    LIMIT 1;

    IF lastKode IS NULL THEN
        SET newKode = 'PSN001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,4) AS UNSIGNED) + 1;
        SET newKode = CONCAT('PSN', LPAD(nomor,3,'0'));
    END IF;

    SET NEW.kode_pesanan = newKode;
END//

-- trx_surat_jalan
CREATE TRIGGER trg_kode_surat BEFORE INSERT ON trx_surat_jalan
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(30);
    DECLARE newKode VARCHAR(30);
    DECLARE nomor INT;

    SELECT kode_surat INTO lastKode
    FROM trx_surat_jalan
    ORDER BY id_surat DESC
    LIMIT 1;

    IF lastKode IS NULL THEN
        SET newKode = 'SJ001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,3) AS UNSIGNED) + 1;
        SET newKode = CONCAT('SJ', LPAD(nomor,3,'0'));
    END IF;

    SET NEW.kode_surat = newKode;
END//

-- trx_retur
CREATE TRIGGER trg_kode_retur BEFORE INSERT ON trx_retur
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(30);
    DECLARE newKode VARCHAR(30);
    DECLARE nomor INT;

    SELECT kode_retur INTO lastKode
    FROM trx_retur
    ORDER BY id_retur DESC
    LIMIT 1;

    IF lastKode IS NULL THEN
        SET newKode = 'RT001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,3) AS UNSIGNED) + 1;
        SET newKode = CONCAT('RT', LPAD(nomor,3,'0'));
    END IF;

    SET NEW.kode_retur = newKode;
END//

-- trx_berita_serah_terima
CREATE TRIGGER trg_kode_basterima BEFORE INSERT ON trx_berita_serah_terima
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(30);
    DECLARE newKode VARCHAR(30);
    DECLARE nomor INT;

    SELECT kode_basterima INTO lastKode
    FROM trx_berita_serah_terima
    ORDER BY id_serah DESC
    LIMIT 1;

    IF lastKode IS NULL THEN
        SET newKode = 'BST001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,4) AS UNSIGNED) + 1;
        SET newKode = CONCAT('BST', LPAD(nomor,3,'0'));
    END IF;

    SET NEW.kode_basterima = newKode;
END//

DELIMITER ;
