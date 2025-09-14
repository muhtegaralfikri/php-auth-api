<?php
// /models/User.php
namespace App\Models;
class User {
    // Koneksi database dan nama tabel
    private $conn;
    private $table_name = "users";

    // Properti Objek (sesuai kolom tabel)
    public $id;
    public $email;
    public $password;
    public $created_at;
    public $updated_at;

    /**
     * Constructor
     * Menerima object koneksi database ($db) dari luar (Dependency Injection)
     * @param PDO $db - Object koneksi PDO
     */
    public function __construct(\PDO $db) {
        $this->conn = $db;
    }

    /**
     * Method REGISTER user baru
     * Ini mengasumsikan properti $email dan $password (yang sudah di-hash)
     * telah di-set pada object sebelum memanggil method ini.
     */
    public function register() {
        // Query untuk insert record baru
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    email = :email,
                    password = :password";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize input (meskipun PDO menangani escaping, ini lapisan tambahan yang baik)
        // Kita tidak sanitize password karena itu adalah HASH, bukan input string biasa.
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind values
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password); // Password di sini HARUS sudah HASHED

        // Eksekusi query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Method untuk mengecek apakah email sudah ada di database.
     * Digunakan sebelum registrasi.
     */
    public function emailExists() {
        // Query untuk mengecek email
        $query = "SELECT id FROM " . $this->table_name . "
                  WHERE email = :email
                  LIMIT 1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize email
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind email
        $stmt->bindParam(":email", $this->email);

        // Eksekusi query
        $stmt->execute();

        // Cek jumlah baris yang dikembalikan
        $num = $stmt->rowCount();

        // Jika num > 0 berarti email sudah ada
        if ($num > 0) {
            return true;
        }

        return false;
    }

    /**
     * Method untuk mencari user berdasarkan email.
     * Digunakan untuk proses login.
     * Jika user ditemukan, properti object ini akan diisi (termasuk hash password).
     */
    public function findByEmail() {
        // Query untuk mengambil data lengkap user
        $query = "SELECT id, email, password, created_at
                  FROM " . $this->table_name . "
                  WHERE email = :email
                  LIMIT 1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize email
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind email
        $stmt->bindParam(":email", $this->email);

        // Eksekusi query
        $stmt->execute();

        // Cek apakah user ditemukan
        $num = $stmt->rowCount();

        if ($num > 0) {
            // Ambil detail user
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Set properti object dengan data dari database
            $this->id = $row['id'];
            $this->password = $row['password']; // Ini adalah HASH password dari DB
            $this->created_at = $row['created_at'];
            // (kita tidak perlu set $this->email karena itu sudah dipakai untuk mencari)

            return true; // User ditemukan
        }

        return false; // User tidak ditemukan
    }
}