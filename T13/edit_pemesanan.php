<?php
class Pemesanan {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getPemesananById($id) {
        $sql = "SELECT * FROM pemesanan WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function updatePemesanan($id, $nama, $film, $kursi, $jumlah, $tanggal, $waktu, $metode_pembayaran, $total_pembayaran) {
        $sql = "UPDATE pemesanan 
                SET nama = ?, film = ?, kursi = ?, jumlah = ?, tanggal = ?, waktu = ?, metode_pembayaran = ?, total_pembayaran = ?
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssiisssi", $nama, $film, $kursi, $jumlah, $tanggal, $waktu, $metode_pembayaran, $total_pembayaran, $id);

        if ($stmt->execute()) {
            return true;
        } else {
            echo "Error updating record: " . $stmt->error;
            return false;
        }
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "pemesanan_tiket_bioskop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pemesanan = new Pemesanan($conn);

$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    $dataPemesanan = $pemesanan->getPemesananById($id);
    if (!$dataPemesanan) {
        echo "Data pemesanan tidak ditemukan.";
        exit;
    }
    // Ambil data yang akan ditampilkan di form
    $nama = $dataPemesanan['nama'];
    $film = $dataPemesanan['film'];
    $kursi = $dataPemesanan['kursi'];
    $jumlah = $dataPemesanan['jumlah'];
    $tanggal = $dataPemesanan['tanggal'];
    $waktu = $dataPemesanan['waktu'];
    $metode_pembayaran = $dataPemesanan['metode_pembayaran'];
    $total_pembayaran = $dataPemesanan['total_pembayaran'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $film = $_POST['film'];
    $kursi = $_POST['kursi'];
    $jumlah = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];
    $metode_pembayaran = $_POST['metode_pembayaran']; // Ambil nilai dari dropdown
    $total_pembayaran = $_POST['total_pembayaran'];

    // Update data di dalam database menggunakan objek Pemesanan
    if ($pemesanan->updatePemesanan($id, $nama, $film, $kursi, $jumlah, $tanggal, $waktu, $metode_pembayaran, $total_pembayaran)) {
        // Redirect ke halaman tampil_pemesanan.php setelah berhasil update
        header("Location: tampil_pemesanan.php");
        exit();
    }
}

$pemesanan->closeConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pemesanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('background5.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"], select {
            width: calc(100% - 20px);
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        a.button {
            display: inline-block;
            text-decoration: none;
            background-color: #f44336;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            font-size: 16px;
            margin-top: 10px;
        }
        a.button:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Pemesanan</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <label for="nama">Nama:</label>
            <input type="text" id="nama" name="nama" value="<?php echo isset($nama) ? htmlspecialchars($nama) : ''; ?>"><br>
            
            <label for="film">Film:</label>
            <input type="text" id="film" name="film" value="<?php echo isset($film) ? htmlspecialchars($film) : ''; ?>"><br>
            
            <label for="kursi">Kursi:</label>
            <input type="text" id="kursi" name="kursi" value="<?php echo isset($kursi) ? htmlspecialchars($kursi) : ''; ?>"><br>
            
            <label for="jumlah">Jumlah:</label>
            <input type="text" id="jumlah" name="jumlah" value="<?php echo isset($jumlah) ? htmlspecialchars($jumlah) : ''; ?>"><br>
            
            
            <label for="waktu">Waktu:</label>
            <input type="time" id="waktu" name="waktu" value="<?php echo isset($waktu) ? date('H:i', strtotime($waktu)) : ''; ?>"><br>
            
            <label for="metode_pembayaran">Metode Pembayaran:</label>
            <select id="metode_pembayaran" name="metode_pembayaran">
                <option value="CASH" <?php echo ($metode_pembayaran == 'CASH') ? 'selected' : ''; ?>>CASH</option>
                <option value="BCA" <?php echo ($metode_pembayaran == 'BCA') ? 'selected' : ''; ?>>BCA</option>
                <option value="MANDIRI" <?php echo ($metode_pembayaran == 'MANDIRI') ? 'selected' : ''; ?>>MANDIRI</option>
                <option value="BNI" <?php echo ($metode_pembayaran == 'BNI') ? 'selected' : ''; ?>>BNI</option>
            </select><br>
            
            <label for="total_pembayaran">Total Pembayaran:</label>
            <input type="text" id="total_pembayaran" name="total_pembayaran" value="<?php echo isset($total_pembayaran) ? htmlspecialchars($total_pembayaran) : ''; ?>"><br>
            
            <input type="submit" name="submit" value="Simpan">
        </form>
        <a href="tampil_pemesanan.php" class="button">Batal</a>
    </div>
</body>
</html>
