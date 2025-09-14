<?php
// /api/users/profile.php

// === Headers ===
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Izinkan akses dari mana saja
header("Access-Control-Allow-Methods: GET"); // Endpoint ini hanya GET
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// === Include Dependencies ===
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/core.php'; //  butuh ini untuk $secret_key

// === Gunakan Class JWT ===
use Firebase\JWT\JWT;
use Firebase\JWT\Key; // Ini penting untuk versi v6+ library php-jwt

// === Cek Metode HTTP ===
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["message" => "Metode tidak diizinkan."]);
    exit();
}

// === Proses Validasi Token ===

$jwt = null;
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null; // Cek header Authorization

if ($authHeader) {
    // Format header adalah "Bearer <token>"
    //  perlu pisahkan kata "Bearer" dari token-nya
    $arr = explode(" ", $authHeader);
    if (isset($arr[1])) {
        $jwt = $arr[1];
    }
}

// Jika token tidak ada di header
if (!$jwt) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Akses ditolak. Token tidak disediakan."]);
    exit();
}

// Jika token ada,  coba validasi (decode)
try {
    // JWT::decode akan otomatis mengecek signature DAN expiry time (exp)
    // Jika gagal (kadaluarsa, signature salah, dll), dia akan melempar Exception.
    //  gunakan object Key baru sesuai standar library v6+
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

    // === TOKEN VALID ===
    // Jika lolos sampai sini, berarti token valid.
    //  bisa kirim kembali data yang ada di dalam token ($decoded->data)
    http_response_code(200); // OK
    echo json_encode([
        "message" => "Akses diizinkan.",
        "data" => $decoded->data // Ini adalah data (id, email) yang  masukkan saat login
    ]);

} catch (Exception $e) {
    // === TOKEN TIDAK VALID ===
    // Tangkap SEMUA jenis exception dari JWT (ExpiredException, SignatureInvalidException, dll)
    http_response_code(401); // Unauthorized
    echo json_encode([
        "message" => "Akses ditolak. Token tidak valid.",
        "error" => $e->getMessage() // Tampilkan pesan error (bagus untuk debugging)
    ]);
}
?>