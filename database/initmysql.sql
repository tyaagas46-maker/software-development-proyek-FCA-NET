CREATE DATABASE IF NOT EXISTS fcanetmy;
USE fcanetmy;

-- 1. Table: users
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)               NOT NULL,
    username   VARCHAR(50)                NOT NULL UNIQUE,
    password   VARCHAR(255)               NOT NULL,  -- simpan hash, bukan plain text
    role       ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan',
    created_at TIMESTAMP                  NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Table: paket
CREATE TABLE paket (
    id             INT            AUTO_INCREMENT PRIMARY KEY,
    nama_paket     VARCHAR(50)    NOT NULL,
    kecepatan_mbps INT            NOT NULL CHECK (kecepatan_mbps > 0),
    tarif_standar  DECIMAL(12, 2) NOT NULL CHECK (tarif_standar >= 0)
) ENGINE=InnoDB;

-- 3. Table: pelanggan
CREATE TABLE pelanggan (
    id           INT                       AUTO_INCREMENT PRIMARY KEY,
    id_user      INT                       UNIQUE,
    nama         VARCHAR(100)              NOT NULL,
    no_hp        VARCHAR(20),
    alamat       TEXT,
    id_paket     INT                       NOT NULL,
    tarif_khusus DECIMAL(12, 2)            CHECK (tarif_khusus >= 0),  -- NULL = pakai tarif_standar
    status       ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    CONSTRAINT fk_pelanggan_user  FOREIGN KEY (id_user)  REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_pelanggan_paket FOREIGN KEY (id_paket) REFERENCES paket(id)
) ENGINE=InnoDB;

-- 4. Table: tagihan
CREATE TABLE tagihan (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan   INT NOT NULL,
    periode        DATE NOT NULL,  -- isi tanggal 1 tiap bulan, mis. 2026-11-01
    jumlah_tagihan DECIMAL(12, 2) NOT NULL CHECK (jumlah_tagihan >= 0),
    jatuh_tempo    DATE NOT NULL,
    status         ENUM('belum_bayar', 'kurang_bayar', 'lunas', 'menunggak') NOT NULL DEFAULT 'belum_bayar',
    UNIQUE KEY uq_pelanggan_periode (id_pelanggan, periode),
    CONSTRAINT fk_tagihan_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Table: pembayaran
CREATE TABLE pembayaran (
    id            INT                       AUTO_INCREMENT PRIMARY KEY,
    id_tagihan    INT                       NOT NULL,
    jumlah_bayar  DECIMAL(12, 2)            NOT NULL CHECK (jumlah_bayar > 0),
    metode        ENUM('tunai', 'transfer') NOT NULL,
    tanggal_bayar DATE                      NOT NULL DEFAULT (CURRENT_DATE),
    catatan       TEXT,
    dicatat_oleh  INT,
    CONSTRAINT fk_pembayaran_tagihan FOREIGN KEY (id_tagihan)   REFERENCES tagihan(id) ON DELETE CASCADE,
    CONSTRAINT fk_pembayaran_users   FOREIGN KEY (dicatat_oleh) REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB;

-- Indexes
CREATE INDEX idx_tagihan_periode_status ON tagihan (periode, status);
CREATE INDEX idx_pembayaran_tagihan     ON pembayaran (id_tagihan);


-- Data Contoh --
INSERT INTO paket (nama_paket, kecepatan_mbps, tarif_standar) VALUES
    ('Paket 20/25', 20, 70000),
    ('Paket 25/35', 25, 100000),
    ('Paket 35/50', 35, 130000);


-- Status Pembayaran Query --
SELECT 
    t.id, 
    p.nama, 
    t.periode, 
    t.jumlah_tagihan,
    COALESCE(SUM(b.jumlah_bayar), 0) AS total_bayar,
    CASE
        WHEN COALESCE(SUM(b.jumlah_bayar), 0) >= t.jumlah_tagihan THEN 'lunas'
        WHEN COALESCE(SUM(b.jumlah_bayar), 0) > 0                 THEN 'kurang_bayar'
        WHEN t.jatuh_tempo < CURRENT_DATE                         THEN 'menunggak'
        ELSE 'belum_bayar'
    END AS status_hitung
FROM tagihan t
JOIN pelanggan p ON p.id = t.id_pelanggan
LEFT JOIN pembayaran b ON b.id_tagihan = t.id
GROUP BY 
    t.id, 
    p.nama, 
    t.periode, 
    t.jumlah_tagihan, 
    t.jatuh_tempo;