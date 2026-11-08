-- Active: 1793625810291@@127.0.0.1@5432@fcanet
CREATE DATABASE fcanet;

-- Tipe enum (pengganti ENUM di MySQL)
CREATE TYPE role_user        AS ENUM ('admin', 'pelanggan');
CREATE TYPE status_pelanggan AS ENUM ('aktif', 'nonaktif');
CREATE TYPE status_tagihan   AS ENUM ('belum_bayar', 'kurang_bayar', 'lunas', 'menunggak');
CREATE TYPE metode_bayar     AS ENUM ('tunai', 'transfer');

CREATE TABLE users (
    id         INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama       VARCHAR(100) NOT NULL,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,          -- simpan hash, bukan plain text
    role       role_user    NOT NULL DEFAULT 'pelanggan',
    created_at TIMESTAMPTZ  NOT NULL DEFAULT now()
);

CREATE TABLE paket (
    id             INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama_paket     VARCHAR(50)   NOT NULL,
    kecepatan_mbps INT           NOT NULL CHECK (kecepatan_mbps > 0),
    tarif_standar  NUMERIC(12,2) NOT NULL CHECK (tarif_standar >= 0)
);

CREATE TABLE pelanggan (
    id           INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_user      INT UNIQUE REFERENCES users(id) ON DELETE SET NULL,
    nama         VARCHAR(100) NOT NULL,
    no_hp        VARCHAR(20),
    alamat       TEXT,
    id_paket     INT NOT NULL REFERENCES paket(id),
    tarif_khusus NUMERIC(12,2) CHECK (tarif_khusus >= 0),  -- NULL = pakai tarif_standar
    status       status_pelanggan NOT NULL DEFAULT 'aktif'
);

CREATE TABLE tagihan (
    id             INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_pelanggan   INT NOT NULL REFERENCES pelanggan(id) ON DELETE CASCADE,
    periode        DATE NOT NULL,              -- isi tanggal 1 tiap bulan, mis. 2026-11-01
    jumlah_tagihan NUMERIC(12,2) NOT NULL CHECK (jumlah_tagihan >= 0),
    jatuh_tempo    DATE NOT NULL,
    status         status_tagihan NOT NULL DEFAULT 'belum_bayar',
    UNIQUE (id_pelanggan, periode)             -- cegah tagihan ganda per bulan
);

CREATE TABLE pembayaran (
    id            INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_tagihan    INT NOT NULL REFERENCES tagihan(id) ON DELETE CASCADE,
    jumlah_bayar  NUMERIC(12,2) NOT NULL CHECK (jumlah_bayar > 0),
    metode        metode_bayar  NOT NULL,
    tanggal_bayar DATE NOT NULL DEFAULT CURRENT_DATE,
    catatan       TEXT,
    dicatat_oleh  INT REFERENCES users(id)
);

CREATE INDEX idx_tagihan_periode_status ON tagihan (periode, status);
CREATE INDEX idx_pembayaran_tagihan     ON pembayaran (id_tagihan);


-- Data Contoh --
INSERT INTO paket (nama_paket, kecepatan_mbps, tarif_standar) VALUES
    ('Paket 20/25', 20, 70000),
    ('Paket 25/35', 25, 100000),
    ('Paket 35/50', 35, 130000);


-- Status Pembayaran --
SELECT t.id, p.nama, t.periode, t.jumlah_tagihan,
    COALESCE(SUM(b.jumlah_bayar), 0) AS total_bayar,
    CASE
        WHEN COALESCE(SUM(b.jumlah_bayar),0) >= t.jumlah_tagihan THEN 'lunas'
        WHEN COALESCE(SUM(b.jumlah_bayar),0) > 0                 THEN 'kurang_bayar'
        WHEN t.jatuh_tempo < CURRENT_DATE                        THEN 'menunggak'
        ELSE 'belum_bayar'
    END AS status_hitung
FROM tagihan t
JOIN pelanggan p ON p.id = t.id_pelanggan
LEFT JOIN pembayaran b ON b.id_tagihan = t.id
GROUP BY t.id, p.nama;