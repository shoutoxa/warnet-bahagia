<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Cek Session Admin (Disamakan dengan file lain)
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    die('Akses ditolak: Anda bukan Admin.');
}

$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end   = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Set Header untuk Download CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Laporan_Warnet_' . $start . '_sd_' . $end . '.csv');

// Buka Output Stream
$output = fopen('php://output', 'w');

// HEADER KOLOM CSV
fputcsv($output, ['No', 'Tanggal', 'Jumlah Transaksi', 'Total Pendapatan (Rp)']);

// QUERY DATA
$query = mysqli_query($conn, "
    SELECT 
        tanggal,
        SUM(jumlah_transaksi) AS jumlah_pesanan,
        SUM(pendapatan) AS total_pendapatan
    FROM (
        SELECT DATE(waktu_pesanan) AS tanggal, COUNT(id_pesanan) AS jumlah_transaksi, SUM(total_pesanan) AS pendapatan
        FROM pesanan
        WHERE status_pesanan = 'selesai' AND DATE(waktu_pesanan) BETWEEN '$start' AND '$end'
        GROUP BY DATE(waktu_pesanan)
        UNION ALL
        SELECT DATE(waktu_selesai) AS tanggal, COUNT(id_transaksi) AS jumlah_transaksi, SUM(total_bayar) AS pendapatan
        FROM transaksi_pc
        WHERE status = 'selesai' AND DATE(waktu_selesai) BETWEEN '$start' AND '$end'
        GROUP BY DATE(waktu_selesai)
    ) AS gabungan
    GROUP BY tanggal
    ORDER BY tanggal ASC
");

$no = 1;
// ISI DATA CSV
while ($row = mysqli_fetch_assoc($query)) {
    fputcsv($output, [
        $no++,
        $row['tanggal'],
        $row['jumlah_pesanan'],
        $row['total_pendapatan']
    ]);
}

fclose($output);
exit;
