<?php
class Database {
    private $conn;

    // Constructor untuk inisialisasi koneksi
    public function __construct($host, $username, $password, $dbname) {
        $this->conn = new mysqli($host, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Method untuk menutup koneksi
    public function closeConnection() {
        $this->conn->close();
    }

    // Method untuk melakukan pembayaran
    public function bayarPemesanan($id) {
        $sql = "SELECT * FROM pemesanan WHERE id = $id";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $metode_pembayaran = $row['metode_pembayaran'];
            $jumlah_bayar = $row['total_pembayaran'];

            $sql_bayar = "INSERT INTO pembayaran (id_pemesanan, metode_pembayaran, jumlah_bayar) 
                          VALUES ($id, '$metode_pembayaran', $jumlah_bayar)";
            if ($this->conn->query($sql_bayar) === TRUE) {
                return "Pembayaran berhasil!";
            } else {
                return "Error: " . $sql_bayar . "<br>" . $this->conn->error;
            }
        } else {
            return "Data pemesanan tidak ditemukan.";
        }
    }
}

// Inisialisasi objek Database
$db = new Database("localhost", "root", "", "pemesanan_tiket_bioskop");

// Handle pembayaran jika ada parameter ID yang diberikan
$message = "";
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $message = $db->bayarPemesanan($id);
}

// Menutup koneksi setelah selesai
$db->closeConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pemesanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .center {
            text-align: center;
            margin-top: 20px;
        }
        .message {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
            padding: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Pembayaran Pemesanan</h1>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="center">
        <a href="tampil_pemesanan.php">Kembali ke Detail Pemesanan</a>
    </div>
</body>
</html>
