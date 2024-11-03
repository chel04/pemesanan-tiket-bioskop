<?php
// Class untuk mengelola koneksi ke database
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "pemesanan_tiket_bioskop";
    public $conn;

    // Method untuk menghubungkan ke database
    public function __construct() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Method untuk menutup koneksi database
    public function closeConnection() {
        $this->conn->close();
    }
}

// Class untuk mengelola operasi pemesanan tiket
class Pemesanan {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db->conn;
    }

    // Method untuk menyimpan pemesanan ke database
    public function simpanPemesanan($nama, $film, $kursi, $jumlah, $tanggal, $waktu, $metode_pembayaran, $total_pembayaran) {
        // Ubah format tanggal menjadi 'YYYY-MM-DD' untuk MySQL
        $tanggal_mysql = date('Y-m-d', strtotime($tanggal));

        // Pastikan untuk menghindari serangan injeksi SQL dengan menggunakan prepared statement
        $stmt = $this->conn->prepare("INSERT INTO pemesanan (nama, film, kursi, jumlah, tanggal, waktu, metode_pembayaran, total_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiisss", $nama, $film, $kursi, $jumlah, $tanggal_mysql, $waktu, $metode_pembayaran, $total_pembayaran);

        if ($stmt->execute()) {
            $last_id = $stmt->insert_id;
            $stmt->close();
            return $last_id;
        } else {
            $error_message = "Error: " . $stmt->error;
            $stmt->close();
            return $error_message;
        }
    }

    // Method untuk mengambil semua pemesanan dari database
    public function getSemuaPemesanan() {
        $sql = "SELECT * FROM pemesanan";
        $result = $this->conn->query($sql);
        return $result;
    }
}

// Harga tetap per tiket
$harga = 50000;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pemesanan dan Pembayaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            background-image: url('background5.jpg');
            background-size: cover;
            background-position: center;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            width: 200px;
            margin: 0 auto;
        }
        a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Menggunakan koneksi dan operasi pemesanan
        $database = new Database();
        $pemesanan = new Pemesanan($database);

        // Variabel untuk menyimpan pesan kesalahan
        $pesan_error = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Memeriksa dan menetapkan nilai dari $_POST
            $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
            $film = isset($_POST['film']) ? $_POST['film'] : '';
            $kursi = isset($_POST['kursi']) ? implode(', ', $_POST['kursi']) : '';
            $jumlah_tiket = isset($_POST['jumlah_tiket']) ? intval($_POST['jumlah_tiket']) : 0;
            $tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
            $waktu = isset($_POST['waktu']) ? $_POST['waktu'] : '';
            $metode_pembayaran = isset($_POST['metode_pembayaran']) ? $_POST['metode_pembayaran'] : '';
            $total_pembayaran = $harga * $jumlah_tiket; // Hitung total pembayaran berdasarkan harga per tiket

            // Ubah format tanggal menjadi 'YYYY-MM-DD' untuk MySQL
            $tanggal_mysql = date('Y-m-d', strtotime($tanggal));

            // Menyimpan pemesanan ke database
            $result = $pemesanan->simpanPemesanan($nama, $film, $kursi, $jumlah_tiket, $tanggal_mysql, $waktu, $metode_pembayaran, $total_pembayaran);

            // Cek jika penyimpanan berhasil
            if (is_int($result)) {
                echo "<p>Pemesanan berhasil! ID Pemesanan: " . $result . "</p>";
            } else {
                echo "<p style='color: red;'>Terjadi kesalahan: " . htmlspecialchars($result) . "</p>";
            }
        }
        ?>

        <h1>Detail Pemesanan</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Film</th>
                <th>Kursi</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Metode Pembayaran</th>
                <th>Total Pembayaran</th>
            </tr>
            <tr>
                <td><?php echo isset($result) && is_int($result) ? $result : ''; ?></td>
                <td><?php echo isset($nama) ? htmlspecialchars($nama) : ''; ?></td>
                <td><?php echo isset($film) ? htmlspecialchars($film) : ''; ?></td>
                <td><?php echo isset($kursi) ? htmlspecialchars($kursi) : ''; ?></td>
                <td><?php echo isset($jumlah_tiket) ? $jumlah_tiket : ''; ?></td>
                <td><?php echo isset($tanggal) ? htmlspecialchars($tanggal) : ''; ?></td>
                <td><?php echo isset($waktu) ? htmlspecialchars($waktu) : ''; ?></td>
                <td><?php echo isset($metode_pembayaran) ? htmlspecialchars($metode_pembayaran) : ''; ?></td>
                <td>Rp <?php echo isset($total_pembayaran) ? number_format($total_pembayaran, 2, ',', '.') : ''; ?></td>
            </tr>
        </table>

        <h1>Detail Pembayaran</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Film</th>
                <th>Metode Pembayaran</th>
                <th>Jumlah Bayar</th>
            </tr>
            <tr>
                <td><?php echo isset($result) && is_int($result) ? $result : ''; ?></td>
                <td><?php echo isset($nama) ? htmlspecialchars($nama) : ''; ?></td>
                <td><?php echo isset($film) ? htmlspecialchars($film) : ''; ?></td>
                <td><?php echo isset($metode_pembayaran) ? htmlspecialchars($metode_pembayaran) : ''; ?></td>
                <td>Rp <?php echo isset($total_pembayaran) ? number_format($total_pembayaran, 2, ',', '.') : ''; ?></td>
            </tr>
        </table>
        <a href="tampil_pemesanan.php">Lihat Semua Pemesanan</a>
    </div>
</body>
</html>
