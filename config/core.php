<?php
// /config/core.php
require_once 'secrets.php';
// Aktifkan error reporting untuk development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kredensial JWT
$secret_key = JWT_SECRET_KEY; // <-- Mengambil dari secrets.php
$issuer = "http://php-auth-api.test"; // (Issuer) Siapa yang mengeluarkan token
$audience = "http://localhost:3000";
$issuedAt = time();
$notBefore = $issuedAt;
$expire = $issuedAt + 3600; // Expire dalam 1 Jam