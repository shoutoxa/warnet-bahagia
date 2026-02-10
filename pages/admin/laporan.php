<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Cek Admin
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit;
}

//LOGIC FILTER TANGGAL
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01'); 
$end_date   = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');  

//QUERY DATA LAPORAN
$query_laporan = mysqli_query($conn, "
    SELECT 
        tanggal,
        SUM(jumlah_transaksi) AS jumlah_pesanan,
        SUM(pendapatan) AS total_pendapatan
    FROM (
        SELECT DATE(waktu_pesanan) AS tanggal, COUNT(id_pesanan) AS jumlah_transaksi, SUM(total_pesanan) AS pendapatan
        FROM pesanan
        WHERE status_pesanan = 'selesai' AND DATE(waktu_pesanan) BETWEEN '$start_date' AND '$end_date'
        GROUP BY DATE(waktu_pesanan)
        
        UNION ALL
        
        SELECT DATE(waktu_selesai) AS tanggal, COUNT(id_transaksi) AS jumlah_transaksi, SUM(total_bayar) AS pendapatan
        FROM transaksi_pc
        WHERE status = 'selesai' AND DATE(waktu_selesai) BETWEEN '$start_date' AND '$end_date'
        GROUP BY DATE(waktu_selesai)
    ) AS gabungan
    GROUP BY tanggal
    ORDER BY tanggal ASC
");

//HITUNG TOTAL KESELURUHAN & PERSIAPKAN DATA CHART
$grand_total_pendapatan = 0;
$grand_total_transaksi = 0;

$chart_labels = []; 
$chart_data   = []; 

$data_harian = []; 
while ($row = mysqli_fetch_assoc($query_laporan)) {
    $grand_total_pendapatan += $row['total_pendapatan'];
    $grand_total_transaksi  += $row['jumlah_pesanan'];

    $chart_labels[] = date('d M', strtotime($row['tanggal']));
    $chart_data[]   = $row['total_pendapatan'];

    $data_harian[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Laporan Keuangan</title>

    <link href="https://fonts.googleapis.com/css2?family=Spline+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Spline Sans', 'sans-serif']
                    },
                    colors: {
                        "primary": "#7f0df2",
                        "secondary": "#00f0ff",
                        "neon-cyan": "#00f0ff",
                        "dark-bg": "#120c18",
                        "dark-surface": "#1e1628",
                        "dark-border": "#352842",
                    },
                    boxShadow: {
                        'neon': '0 0 10px rgba(127, 13, 242, 0.5)',
                        'neon-cyan': '0 0 10px rgba(0, 240, 255, 0.5)',
                        'neon-purple': '0 0 15px rgba(127, 13, 242, 0.4)'
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #1b1022;
        }

        ::-webkit-scrollbar-thumb {
            background: #4a3b54;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #930df2;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-dark-bg text-gray-200 font-sans antialiased h-screen flex overflow-hidden">

    <?php include '../../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative md:ml-64">

        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[-10%] right-[-10%] w-[30%] h-[30%] bg-primary/10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[30%] h-[30%] bg-neon-cyan/5 rounded-full blur-[100px]"></div>
        </div>

        <header class="md:hidden h-16 bg-dark-surface border-b border-dark-border flex items-center justify-between px-4 z-10 sticky top-0">
            <span class="text-lg font-bold text-white">Warnet<span class="text-primary">Admin</span></span>
            <button onclick="toggleSidebar()" class="text-white p-2">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </header>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 z-10 scroll-smooth">

            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Laporan Keuangan</h2>
                    <p class="text-gray-400 mt-1 text-sm">Rekapitulasi pendapatan Billing PC & F&B.</p>
                </div>

                <a href="laporan_download.php?start=<?= $start_date; ?>&end=<?= $end_date; ?>"
                    target="_blank"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl shadow-lg hover:shadow-green-500/20 transition-all flex items-center gap-2 font-semibold text-sm">
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    Download CSV
                </a>
            </div>

            <div class="bg-dark-surface/80 border border-dark-border p-4 rounded-2xl mb-8 shadow-lg">
                <form method="GET" class="flex flex-col md:flex-row items-center gap-4">
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <span class="text-gray-400 text-sm">Dari:</span>
                        <input type="date" name="start" value="<?= $start_date; ?>"
                            class="bg-dark-bg border border-dark-border text-white text-sm rounded-lg px-3 py-2 outline-none focus:border-primary transition-all w-full">
                    </div>
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <span class="text-gray-400 text-sm">Sampai:</span>
                        <input type="date" name="end" value="<?= $end_date; ?>"
                            class="bg-dark-bg border border-dark-border text-white text-sm rounded-lg px-3 py-2 outline-none focus:border-primary transition-all w-full">
                    </div>
                    <button type="submit" class="bg-primary/20 text-primary border border-primary/50 hover:bg-primary hover:text-white px-5 py-2 rounded-lg text-sm font-bold transition-all w-full md:w-auto">
                        Filter Data
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gradient-to-br from-dark-surface to-dark-bg border border-dark-border p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-neon-cyan">payments</span>
                    </div>
                    <p class="text-gray-400 text-sm font-medium mb-1">Total Pendapatan</p>
                    <h3 class="text-3xl font-bold text-white">Rp <?= number_format($grand_total_pendapatan, 0, ',', '.'); ?></h3>
                    <p class="text-xs text-neon-cyan mt-2">Periode: <?= date('d M', strtotime($start_date)); ?> - <?= date('d M Y', strtotime($end_date)); ?></p>
                </div>

                <div class="bg-gradient-to-br from-dark-surface to-dark-bg border border-dark-border p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-primary">receipt_long</span>
                    </div>
                    <p class="text-gray-400 text-sm font-medium mb-1">Total Transaksi Selesai</p>
                    <h3 class="text-3xl font-bold text-white"><?= number_format($grand_total_transaksi); ?> <span class="text-lg font-normal text-gray-500">Pesanan</span></h3>
                    <p class="text-xs text-primary mt-2">Billing & F&B Berhasil</p>
                </div>
            </div>

            <div class="bg-dark-surface/60 backdrop-blur-xl border border-dark-border rounded-2xl p-6 shadow-xl mb-8">
                <h3 class="font-bold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-sm">show_chart</span>
                    Grafik Pendapatan Harian
                </h3>
                <div class="w-full h-64 md:h-80">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>

            <div class="bg-dark-surface/60 backdrop-blur-xl border border-dark-border rounded-2xl overflow-hidden shadow-xl">
                <div class="p-5 border-b border-dark-border">
                    <h3 class="font-semibold text-white">Rincian Per Tanggal</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-dark-bg/50 text-gray-400 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="p-4">Tanggal</th>
                                <th class="p-4 text-center">Jumlah Transaksi</th>
                                <th class="p-4 text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-border text-sm text-gray-300">
                            <?php if (!empty($data_harian)): ?>
                                <?php foreach ($data_harian as $d): ?>
                                    <tr class="hover:bg-primary/5 transition-colors">
                                        <td class="p-4"><?= date('d F Y', strtotime($d['tanggal'])); ?></td>
                                        <td class="p-4 text-center">
                                            <span class="bg-dark-bg border border-dark-border px-3 py-1 rounded-full text-xs font-bold">
                                                <?= $d['jumlah_pesanan']; ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-right font-mono text-neon-cyan">
                                            Rp <?= number_format($d['total_pendapatan'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="p-12 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-4xl mb-2 opacity-30">event_busy</span>
                                        <p>Tidak ada data transaksi pada periode ini.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script>
        // CHART CONFIGURATION
        const ctx = document.getElementById('incomeChart').getContext('2d');

        // Gradient for Chart
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(127, 13, 242, 0.5)'); 
        gradient.addColorStop(1, 'rgba(127, 13, 242, 0.0)');

        const incomeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?= json_encode($chart_data); ?>,
                    borderColor: '#7f0df2',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#00f0ff',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#00f0ff',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(27, 16, 34, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#ccc',
                        borderColor: '#4a3b54',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            color: '#888'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            color: '#888'
                        },
                        beginAtZero: true
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    </script>
    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>