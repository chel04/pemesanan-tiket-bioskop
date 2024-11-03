<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "pemesanan_tiket_bioskop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    // Ambil data dari form
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $film = $_POST['film'];
    $kursi = $_POST['kursi'];
    $jumlah = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $total_pembayaran = $_POST['total_pembayaran'];

    // Update data di dalam database
    $sql = "UPDATE pemesanan 
            SET nama = '$nama', film = '$film', kursi = '$kursi', jumlah = $jumlah, tanggal = '$tanggal', waktu = '$waktu', metode_pembayaran = '$metode_pembayaran', total_pembayaran = '$total_pembayaran'
            WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "Data pemesanan berhasil diperbarui!";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();
?>
<a href="tampil_pemesanan.php">Kembali ke Detail Pemesanan</a>
