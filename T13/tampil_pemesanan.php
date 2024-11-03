<?php

class Database {
    private $conn;

    public function __construct($host, $username, $password, $dbname) {
        $this->conn = new mysqli($host, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getAllPemesanan($search = "") {
        $sql = "SELECT id, nama, film, kursi, jumlah, waktu, metode_pembayaran, total_pembayaran 
                FROM pemesanan
                WHERE status_pembayaran = 0";
        if ($search) {
            $sql .= " AND (nama LIKE '%$search%' OR film LIKE '%$search%' OR kursi LIKE '%$search%')";
        }
        $result = $this->conn->query($sql);
        return $result;
    }

    public function getAllPembayaran($search = "") {
        $sql = "SELECT pembayaran.id, pembayaran.id_pemesanan, pembayaran.metode_pembayaran, pembayaran.jumlah_bayar, pemesanan.nama, pemesanan.film 
                FROM pembayaran 
                JOIN pemesanan ON pembayaran.id_pemesanan = pemesanan.id";
        if ($search) {
            $sql .= " WHERE (pemesanan.nama LIKE '%$search%' OR pemesanan.film LIKE '%$search%')";
        }
        $result = $this->conn->query($sql);
        return $result;
    }

    public function updateStatusPembayaran($id_pemesanan) {
        $sql_update = "UPDATE pemesanan SET status_pembayaran = 1 WHERE id = $id_pemesanan";

        if ($this->conn->query($sql_update) === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

class Pemesanan {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getAllPemesanan($search = "") {
        return $this->db->getAllPemesanan($search);
    }

    public function updateStatusPembayaran($id_pemesanan) {
        return $this->db->updateStatusPembayaran($id_pemesanan);
    }
}

class Pembayaran {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getAllPembayaran($search = "") {
        return $this->db->getAllPembayaran($search);
    }
}

// Inisialisasi objek Database
$db = new Database("localhost", "root", "", "pemesanan_tiket_bioskop");

// Inisialisasi objek Pemesanan dan Pembayaran
$pemesanan = new Pemesanan($db);
$pembayaran = new Pembayaran($db);

// Handle pembayaran
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id_pemesanan = $_GET['id'];
    
    // Lakukan pembayaran otomatis
    $success = $pemesanan->updateStatusPembayaran($id_pemesanan);
    
    if ($success) {
        // Refresh data pemesanan setelah pembayaran berhasil
        $result_pemesanan = $pemesanan->getAllPemesanan();
    } else {
        echo "Gagal melakukan pembayaran.";
    }
}

// Menggunakan objek untuk mengambil data
$search = isset($_GET['search']) ? $_GET['search'] : "";
$result_pemesanan = $pemesanan->getAllPemesanan($search);
$result_pembayaran = $pembayaran->getAllPembayaran($search);

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
            background-image: url('background5.jpg'); /* Menambahkan background image */
            background-size: cover;
            background-position: center;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #ffffff; /* Warna teks judul putih */
        }
        table {
            width: 55%; /* Sesuaikan lebar tabel */
            margin: 0 auto; /* Menengahkan tabel di tengah halaman */
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        table td {
            background-color: #fff;
        }
        table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .add-button {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
            margin-top: 10px;
        }
        .add-button:hover {
            background-color: #45a049;
        }
        .center {
            text-align: center;
        }
        .search-box {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-box input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-box input[type="submit"] {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-box input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Detail Pemesanan Yang Harus Dibayar</h1>
    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Cari pemesanan..." value="<?php echo htmlspecialchars($search); ?>">
            <input type="submit" value="Cari">
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Film</th>
                <th>Kursi</th>
                <th>Jumlah</th>
                <th>Waktu</th>
                <th>Metode Pembayaran</th>
                <th>Total Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <!-- Isi tabel pemesanan yang harus dibayar -->
            <?php
            if ($result_pemesanan && $result_pemesanan->num_rows > 0) {
                while($row = $result_pemesanan->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['nama']}</td>
                            <td>{$row['film']}</td>
                            <td>{$row['kursi']}</td>
                            <td>{$row['jumlah']}</td>
                            <td>{$row['waktu']}</td>
                            <td>{$row['metode_pembayaran']}</td>
                            <td>Rp " . number_format($row['total_pembayaran'], 2, ',', '.') . "</td>
                            <td>
                                <a href='edit_pemesanan.php?id={$row['id']}'>Edit</a> |
                                <a href='hapus_pemesanan.php?id={$row['id']}'>Hapus</a> |
                                <a href='bayar.php?id={$row['id']}'>Bayar</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='9'>Tidak ada data pemesanan yang harus dibayar.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="center">
        <a href="index.html" class="add-button">Tambah Pemesanan</a>
    </div>

    <h1>Detail Pembayaran Tiket Yang Sudah Dibayar</h1>
    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Cari pembayaran..." value="<?php echo htmlspecialchars($search); ?>">
            <input type="submit" value="Cari">
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Pemesanan</th>
                <th>Metode Pembayaran</th>
                <th>Jumlah Bayar</th>
                <th>Nama Pemesan</th>
                <th>Film</th>
            </tr>
        </thead>
        <tbody>
            <!-- Isi tabel pembayaran -->
            <?php
            if ($result_pembayaran && $result_pembayaran->num_rows > 0) {
                while($row = $result_pembayaran->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['id_pemesanan']}</td>
                            <td>{$row['metode_pembayaran']}</td>
                            <td>Rp " . number_format($row['jumlah_bayar'], 2, ',', '.') . "</td>
                            <td>{$row['nama']}</td>
                            <td>{$row['film']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Tidak ada data pembayaran.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
// Menutup koneksi database
$db->closeConnection();
?>
