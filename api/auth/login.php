<?php
// /api/auth/login.php

// === Headers ===
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // HARUS MENGIZINKAN POST DAN OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// === Include Autoloader Composer ===
require_once __DIR__ . '/../../vendor/autoload.php';

// === Gunakan Class yang Diperlukan ===
use App\Config\Database;
use App\Models\User;
use Firebase\JWT\JWT;

// === Include file konfigurasi inti  ===
require_once __DIR__ . '/../../config/core.php';

// === PENANGANAN PREFLIGHT (CORS) ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit();
}

// === Cek Metode HTTP ===
// GUARD HARUS MENGECEK POST!
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Metode tidak diizinkan."]);
    exit();
}

// === Inisialisasi Database & Objek User ===
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// === Baca Data dari Request Body ===
$data = json_decode(file_get_contents("php://input"));

// === Validasi Input Sederhana ===
if (
    !isset($data->email) ||
    !isset($data->password) ||
    empty(trim($data->email)) ||
    empty(trim($data->password))
) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Data tidak lengkap. Email dan password diperlukan."]);
    exit();
}

// === Proses Login (LOGIKA LOGIN YANG BENAR) ===

// 1. Set email user dan coba cari di database
$user->email = $data->email;
$userFound = $user->findByEmail(); 

// 2. Verifikasi User dan Password
if ($userFound) {
    // User ditemukan. Verifikasi password.
    
    if (password_verify($data->password, $user->password)) {
        // === LOGIN BERHASIL ===
        // Buat JWT.
        
        $payload = [
            'iss' => $issuer,
            'aud' => $audience,
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire, // Variabel dari core.php
            'data' => [ 
                'id' => $user->id,
                'email' => $user->email
            ]
        ];

        // Generate JWT
        try {
            $jwt = JWT::encode($payload, $secret_key, 'HS256');

            http_response_code(200); // OK
            echo json_encode([
                "message" => "Login berhasil.",
                "jwt" => $jwt,
                "expiresIn" => $expire
            ]);

        } catch (Exception $e) {
             http_response_code(500); // Internal Server Error
             echo json_encode(["message" => "Gagal membuat token.", "error" => $e->getMessage()]);
        }

    } else {
        // === LOGIN GAGAL (PASSWORD SALAH) ===
        http_response_code(401); // 401 Unauthorized
        echo json_encode(["message" => "Login gagal. Email atau password salah."]);
    }
} else {
    // === LOGIN GAGAL (EMAIL TIDAK DITEMUKAN) ===
    http_response_code(401); // 401 Unauthorized
    echo json_encode(["message" => "Login gagal. Email atau password salah."]);
}
?>