<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if admin
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit;
}


$total_konsumen = 0;
$q = $conn->query("SELECT COUNT(*) as total FROM konsumen");
if ($q) $total_konsumen = $q->fetch_assoc()['total'];

// Total PC
$total_pc = 0;
$q = $conn->query("SELECT COUNT(*) as total FROM pc");
if ($q) $total_pc = $q->fetch_assoc()['total'];

// PC Terpakai 
$pc_terpakai = 0;
$q = $conn->query("SELECT COUNT(*) as total FROM pc WHERE status_pc = 'digunakan'");
if ($q) $pc_terpakai = $q->fetch_assoc()['total'];

// PC Tersedia
$pc_tersedia = 0;
$q = $conn->query("SELECT COUNT(*) as total FROM pc WHERE status_pc = 'tersedia'");
if ($q) $pc_tersedia = $q->fetch_assoc()['total'];

// Pendapatan Hari Ini 
$today = date('Y-m-d');
$revenue_today = 0;

// 1. Pendapatan F&B
$q1 = $conn->query("SELECT COALESCE(SUM(total_pesanan), 0) as total FROM pesanan WHERE DATE(waktu_pesanan) = '$today' AND status_pesanan = 'selesai'");
$rev_fnb = $q1 ? $q1->fetch_assoc()['total'] : 0;

// 2. Pendapatan Sewa PC 
$q2 = $conn->query("SELECT COALESCE(SUM(total_bayar), 0) as total FROM transaksi_pc WHERE DATE(waktu_mulai) = '$today'");
$rev_pc = $q2 ? $q2->fetch_assoc()['total'] : 0;

$revenue_today = $rev_fnb + $rev_pc;

// Pesanan Pending
$pending_orders = 0;
$q = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status_pesanan = 'pending'");
if ($q) $pending_orders = $q->fetch_assoc()['total'];

// Format Rupiah Helper
function rupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dashboard</title>

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
    </style>
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

        <div class="flex-1 overflow-y-auto p-6 md:p-10 z-10 scroll-smooth">

            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Dashboard Overview</h2>
                    <p class="text-gray-400 mt-1 text-sm">Selamat datang, Administrator!</p>
                </div>
                <div class="hidden md:block">
                    <div class="px-4 py-2 bg-dark-surface rounded-full border border-dark-border flex items-center gap-2 text-sm text-gray-300">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        System Operational
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <div class="bg-dark-surface border border-dark-border p-5 rounded-2xl flex items-center justify-between hover:border-primary/50 transition-all group">
                    <div>
                        <p class="text-gray-400 text-xs uppercase font-semibold mb-1">Total Konsumen</p>
                        <h3 class="text-2xl font-bold text-white group-hover:text-primary transition-colors"><?= $total_konsumen; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                </div>

                <div class="bg-dark-surface border border-dark-border p-5 rounded-2xl flex items-center justify-between hover:border-primary/50 transition-all group">
                    <div>
                        <p class="text-gray-400 text-xs uppercase font-semibold mb-1">PC Tersedia</p>
                        <h3 class="text-2xl font-bold text-white">
                            <span class="text-green-400"><?= $pc_tersedia; ?></span>
                            <span class="text-gray-500 text-lg font-normal">/ <?= $total_pc; ?></span>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-green-500/10 text-green-400 flex items-center justify-center border border-green-500/20">
                        <span class="material-symbols-outlined">computer</span>
                    </div>
                </div>

                <div class="bg-dark-surface border border-dark-border p-5 rounded-2xl flex items-center justify-between hover:border-primary/50 transition-all group">
                    <div>
                        <p class="text-gray-400 text-xs uppercase font-semibold mb-1">Omset Hari Ini</p>
                        <h3 class="text-2xl font-bold text-white group-hover:text-neon-cyan transition-colors"><?= rupiah($revenue_today); ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-primary flex items-center justify-center border border-purple-500/20">
                        <span class="material-symbols-outlined">payments</span>
                    </div>
                </div>

                <div class="bg-dark-surface border border-dark-border p-5 rounded-2xl flex items-center justify-between hover:border-primary/50 transition-all group">
                    <div>
                        <p class="text-gray-400 text-xs uppercase font-semibold mb-1">Pesanan Pending</p>
                        <h3 class="text-2xl font-bold text-white group-hover:text-yellow-400 transition-colors"><?= $pending_orders; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-400 flex items-center justify-center border border-yellow-500/20">
                        <span class="material-symbols-outlined">notifications_active</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="bg-dark-surface/60 backdrop-blur-xl border border-dark-border rounded-2xl overflow-hidden shadow-xl">
                    <div class="p-5 border-b border-dark-border flex justify-between items-center">
                        <h3 class="font-semibold text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-sm">shopping_bag</span>
                            Pesanan F&B Terbaru
                        </h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <?php
                        $recent = $conn->query("SELECT p.*, k.username FROM pesanan p JOIN konsumen k ON p.id_konsumen = k.id_konsumen ORDER BY p.waktu_pesanan DESC LIMIT 5");

                        if ($recent && $recent->num_rows > 0):
                            while ($r = $recent->fetch_assoc()):
                                $status = $r['status_pesanan'];
                                $color = ($status == 'pending') ? 'yellow' : (($status == 'selesai') ? 'green' : 'gray');
                        ?>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-dark-bg border border-dark-border hover:border-primary/30 transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center font-bold text-xs text-white">
                                            <?= substr($r['username'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-white"><?= $r['username']; ?></p>
                                            <p class="text-xs text-gray-500"><?= date('H:i', strtotime($r['waktu_pesanan'])); ?> • <?= rupiah($r['total_pesanan']); ?></p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 rounded text-[10px] uppercase font-bold bg-<?= $color; ?>-500/10 text-<?= $color; ?>-500 border border-<?= $color; ?>-500/20">
                                        <?= ucfirst($status); ?>
                                    </span>
                                </div>
                            <?php endwhile;
                        else: ?>
                            <div class="text-center text-gray-500 py-4 text-sm">Belum ada pesanan terbaru.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-dark-surface/60 backdrop-blur-xl border border-dark-border rounded-2xl overflow-hidden shadow-xl">
                    <div class="p-5 border-b border-dark-border flex justify-between items-center">
                        <h3 class="font-semibold text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-neon-cyan text-sm">desktop_windows</span>
                            Status PC
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <?php
                            $pc_query = $conn->query("SELECT * FROM pc ORDER BY id_pc ASC LIMIT 12");
                            while ($pc = $pc_query->fetch_assoc()):
                                $is_ready = ($pc['status_pc'] == 'tersedia');
                                $bg_class = $is_ready ? 'bg-dark-bg border-dark-border text-gray-400' : 'bg-primary/20 border-primary text-white shadow-neon';
                            ?>
                                <div class="p-3 rounded-xl border <?= $bg_class; ?> flex flex-col items-center justify-center transition-all text-center">
                                    <span class="material-symbols-outlined text-2xl mb-1"><?= $is_ready ? 'tv' : 'sports_esports'; ?></span>
                                    <span class="text-xs font-bold">PC-<?= $pc['id_pc']; ?></span>
                                    <span class="text-[10px] uppercase mt-1 <?= $is_ready ? 'text-green-500' : 'text-neon-cyan animate-pulse'; ?>">
                                        <?= $is_ready ? 'Ready' : 'Main'; ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>