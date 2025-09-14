<?php
// /config/database.php
namespace App\Config;
require_once 'secrets.php';
class Database {
    // Kredensial Database (sesuai setup Laragon kita)
    private $host = "127.0.0.1";
    private $db_name = "php_auth_api";
    private $username = DB_USER;   // <-- Mengambil dari secrets.php
    private $password = DB_PASS;   // <-- Mengambil dari secrets.php
    public $conn;

    /**
     * Method untuk mendapatkan koneksi ke database.
     * Menggunakan PDO dan mengembalikan object koneksi.
     */
    public function getConnection() {
        // Kosongkan koneksi sebelumnya (jika ada)
        $this->conn = null;

        // DSN (Data Source Name) untuk MySQL
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";

        try {
            // Buat instance PDO baru
            $this->conn = new \PDO($dsn, $this->username, $this->password);
            
            // Atur mode error PDO ke "Exception"
            // Ini WAJIB agar error SQL ditangani sebagai exception, bukan PHP warning biasa.
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Atur mode fetch default (opsional, tapi praktik yang baik)
            $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        } catch(\PDOException $exception) {
            // Jika koneksi gagal, tangkap exception dan tampilkan pesan error
            // (Di aplikasi produksi, ini harus di-log, bukan di-echo)
            echo "Connection error: " . $exception->getMessage();
        }

        // Kembalikan object koneksi
        return $this->conn;
    }
}