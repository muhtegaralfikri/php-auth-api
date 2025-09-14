<?php
// /api/auth/register.php

// === Headers ===
// Atur header untuk memberitahu klien bahwa respons adalah JSON
header("Content-Type: application/json; charset=UTF-8");
// Atur header CORS (Cross-Origin Resource Sharing)
// '*' mengizinkan akses dari domain manapun. Untuk produksi, ganti dengan domain frontend Anda.
header("Access-Control-Allow-Origin: *");
// Tentukan metode HTTP yang diizinkan untuk endpoint ini
header("Access-Control-Allow-Methods: POST, OPTIONS");
// Tentukan header kustom yang diizinkan (misalnya untuk otentikasi atau content-type)
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle CORS Preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // 204 No Content (Ini adalah respons standar untuk preflight yang sukses)
    exit(); // Hentikan eksekusi. Jangan lanjut ke logika database.
}
// === Cek Metode HTTP ===
// Endpoint ini hanya menerima request POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika bukan POST, kirim respons 405 Method Not Allowed
    http_response_code(405);
    echo json_encode(["message" => "Metode tidak diizinkan."]);
    exit(); // Hentikan eksekusi skrip
}
// Include Autoloader Composer
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Config\Database;
use App\Models\User;
// === Inisialisasi Database & Objek User ===
$database = new Database();
$db = $database->getConnection(); // Dapatkan koneksi PDO

$user = new User($db); // Injeksi koneksi DB ke model User

// === Baca Data dari Request Body ===
// API menerima JSON mentah, bukan form-data (bukan $_POST)
$data = json_decode(file_get_contents("php://input"));

// === Validasi Input ===

// 1. Cek apakah data JSON valid dan berisi email serta password
if (
    !isset($data->email) ||
    !isset($data->password) ||
    empty(trim($data->email)) ||
    empty(trim($data->password))
) {
    // Jika data tidak lengkap, kirim 400 Bad Request
    http_response_code(400);
    echo json_encode(["message" => "Data tidak lengkap. Email dan password diperlukan."]);
    exit();
}

// 2. Validasi format email
if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    // Jika format email salah
    http_response_code(400);
    echo json_encode(["message" => "Format email tidak valid."]);
    exit();
}

// 3. Validasi kekuatan password (minimal 8 karakter)
if (strlen($data->password) < 8) {
    // Jika password terlalu pendek
    http_response_code(400);
    echo json_encode(["message" => "Password minimal harus 8 karakter."]);
    exit();
}

// 4. Cek apakah email sudah ada (menggunakan method dari Model)
$user->email = $data->email; // Set properti email di object user
if ($user->emailExists()) {
    // Jika email sudah terdaftar, kirim 409 Conflict
    http_response_code(409);
    echo json_encode(["message" => "Email sudah terdaftar."]);
    exit();
}

// === Proses Registrasi (Jika Semua Validasi Lolos) ===

try {
    // Set properti user (email sudah di-set di atas)
    
    // HASHING PASSWORD: Ini bagian paling penting dari keamanan registrasi.
    // Kita gunakan BCRYPT, standar PHP.
    $user->password = password_hash($data->password, PASSWORD_BCRYPT);

    // Panggil method register() dari Model
    if ($user->register()) {
        // Jika registrasi sukses, kirim 201 Created
        http_response_code(201);
        echo json_encode(["message" => "Registrasi berhasil."]);
    } else {
        // Jika gagal (error internal saat query), kirim 503 Service Unavailable
        http_response_code(503);
        echo json_encode(["message" => "Gagal melakukan registrasi (Internal Server Error)."]);
    }
} catch (Exception $e) {
    // Menangkap exception lain (misalnya jika koneksi DB putus tiba-tiba)
    http_response_code(500);
    echo json_encode(["message" => "Terjadi kesalahan server: " . $e->getMessage()]);
}

?>