-- database.sql
-- Jalankan di phpMyAdmin atau via mysql CLI

CREATE DATABASE IF NOT EXISTS db_nasabah;
USE db_nasabah;

CREATE TABLE IF NOT EXISTS nasabah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(100) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    penghasilan BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
