<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "pemesanan_tiket_bioskop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil ID pemesanan dari parameter URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus terlebih dahulu data terkait di tabel pembayaran
    $sql_delete_pembayaran = "DELETE FROM pembayaran WHERE id_pemesanan = ?";
    $stmt_pembayaran = $conn->prepare($sql_delete_pembayaran);
    $stmt_pembayaran->bind_param("i", $id);

    if ($stmt_pembayaran->execute()) {
        // Setelah berhasil menghapus pembayaran, lanjutkan untuk menghapus data pemesanan
        $sql_delete_pemesanan = "DELETE FROM pemesanan WHERE id = ?";
        $stmt_pemesanan = $conn->prepare($sql_delete_pemesanan);
        $stmt_pemesanan->bind_param("i", $id);

        if ($stmt_pemesanan->execute()) {
            // Notifikasi penghapusan berhasil
            echo "<script>
                    alert('Data pemesanan berhasil dihapus');
                    window.location.href = 'tampil_pemesanan.php';
                  </script>";
            exit();
        } else {
            echo "Error deleting record: " . $stmt_pemesanan->error;
        }
    } else {
        echo "Error deleting related records: " . $stmt_pembayaran->error;
    }

    $stmt_pembayaran->close();
    $stmt_pemesanan->close();
} else {
    echo "ID pemesanan tidak valid";
}

$conn->close();
?>
