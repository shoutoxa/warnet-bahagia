<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Cek Login Konsumen
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'konsumen') {
    header("Location: ../../index.php");
    exit;
}

$id_konsumen = $_SESSION['id_konsumen'];

$query_user = mysqli_query($conn, "SELECT * FROM konsumen WHERE id_konsumen = '$id_konsumen'");
$user = mysqli_fetch_assoc($query_user);

$query_active_pc = mysqli_query($conn, "SELECT * FROM pc WHERE id_konsumen = '$id_konsumen' AND status_pc = 'digunakan' LIMIT 1");
$active_session = mysqli_fetch_assoc($query_active_pc);

$query_order = mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE id_konsumen = '$id_konsumen' AND status_pesanan IN ('pending', 'dibayar')");
$active_orders = mysqli_fetch_assoc($query_order)['total'];
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Warnet Bahagia</title>

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
                        "dark-bg": "#120c18",
                        "dark-surface": "#1e1628",
                        "dark-border": "#352842",
                    },
                    boxShadow: {
                        'neon-purple': '0 0 15px rgba(127, 13, 242, 0.4)',
                        'neon-cyan': '0 0 15px rgba(0, 240, 255, 0.4)'
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
            background: #120c18;
        }

        ::-webkit-scrollbar-thumb {
            background: #352842;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #7f0df2;
        }
    </style>
</head>

<body class="bg-dark-bg text-gray-200 font-sans antialiased h-screen flex overflow-hidden">

    <?php include '../../includes/sidebar_konsumen.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative md:ml-64">
        <div class="absolute top-0 right-0 w-[50%] h-[50%] bg-primary/10 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[30%] h-[30%] bg-secondary/5 blur-[100px] rounded-full pointer-events-none"></div>

        <header class="md:hidden h-16 bg-dark-surface/80 backdrop-blur-md border-b border-dark-border flex items-center justify-between px-4 z-20 sticky top-0">
            <span class="text-lg font-bold text-white">Warnet<span class="text-primary">Bahagia</span></span>
            <button onclick="toggleSidebar()" class="text-white p-2">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </header>

        <div class="flex-1 overflow-y-auto p-6 z-10">

            <div class="mb-8">
                <h2 class="text-3xl font-bold text-white leading-tight">Welcome back, <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary"><?= ucfirst($user['username']); ?></span>!</h2>
                <p class="text-gray-400">Siap untuk push rank hari ini?</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

                <div class="bg-gradient-to-br from-dark-surface to-dark-bg border border-dark-border p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute right-[-10px] top-[-10px] p-4 bg-green-500/10 rounded-full group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-4xl text-green-400">wallet</span>
                    </div>
                    <p class="text-gray-400 text-sm font-medium mb-1">Saldo Digital</p>
                    <h3 class="text-3xl font-bold text-white mb-4">Rp <?= number_format($user['saldo'], 0, ',', '.'); ?></h3>
                    <a href="topup.php" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-bold rounded-lg transition-all shadow-lg hover:shadow-green-500/30">
                        <span class="material-symbols-outlined text-[18px]">add</span> Top Up
                    </a>
                </div>

                <div class="bg-gradient-to-br from-dark-surface to-dark-bg border border-dark-border p-6 rounded-2xl relative overflow-hidden group">
                    <?php if ($active_session): ?>
                        <div class="absolute right-[-10px] top-[-10px] p-4 bg-primary/10 rounded-full animate-pulse">
                            <span class="material-symbols-outlined text-4xl text-primary">sports_esports</span>
                        </div>
                        <p class="text-gray-400 text-sm font-medium mb-1">Status: <span class="text-primary font-bold">In-Game</span></p>
                        <h3 class="text-3xl font-bold text-white mb-4">PC-<?= $active_session['id_pc']; ?></h3>
                        <button class="inline-flex items-center gap-2 px-4 py-2 bg-dark-border text-gray-300 text-sm font-bold rounded-lg cursor-not-allowed opacity-70">
                            Sedang Bermain...
                        </button>
                    <?php else: ?>
                        <div class="absolute right-[-10px] top-[-10px] p-4 bg-gray-800 rounded-full">
                            <span class="material-symbols-outlined text-4xl text-gray-600">desktop_access_disabled</span>
                        </div>
                        <p class="text-gray-400 text-sm font-medium mb-1">Status</p>
                        <h3 class="text-xl font-bold text-white mb-4">Tidak Aktif</h3>
                        <a href="booking.php" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/80 text-white text-sm font-bold rounded-lg transition-all shadow-neon-purple">
                            <span class="material-symbols-outlined text-[18px]">play_arrow</span> Main Sekarang
                        </a>
                    <?php endif; ?>
                </div>

                <div class="bg-gradient-to-br from-dark-surface to-dark-bg border border-dark-border p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute right-[-10px] top-[-10px] p-4 bg-yellow-500/10 rounded-full">
                        <span class="material-symbols-outlined text-4xl text-yellow-400">fastfood</span>
                    </div>
                    <p class="text-gray-400 text-sm font-medium mb-1">Pesanan F&B</p>
                    <?php if ($active_orders > 0): ?>
                        <h3 class="text-3xl font-bold text-white mb-4"><?= $active_orders; ?> <span class="text-lg font-normal text-gray-500">Item</span></h3>
                        <a href="pesanan.php" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white text-sm font-bold rounded-lg transition-all shadow-lg hover:shadow-yellow-500/30">
                            Lihat Status
                        </a>
                    <?php else: ?>
                        <h3 class="text-xl font-bold text-gray-500 mb-4">Kosong</h3>
                        <a href="fnb.php" class="inline-flex items-center gap-2 px-4 py-2 bg-dark-border hover:bg-gray-700 text-white text-sm font-bold rounded-lg transition-all">
                            Pesan Makan
                        </a>
                    <?php endif; ?>
                </div>

            </div>


            <?php
            $today = date('Y-m-d');
            $promo_list = mysqli_query($conn, "SELECT * FROM promo WHERE valid_until >= '$today' AND kuota > 0 ORDER BY valid_until ASC");
            if ($promo_list->num_rows > 0):
            ?>
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-yellow-400">local_fire_department</span> Promo Hot
                    </h3>
                    <div class="flex gap-4 overflow-x-auto pb-4 snap-x">
                        <?php while ($p = $promo_list->fetch_assoc()): ?>
                            <div class="snap-center shrink-0 w-80 bg-dark-surface border border-dark-border rounded-xl overflow-hidden group hover:border-primary/50 transition-all relative">
                                <div class="h-32 bg-gradient-to-r from-primary/20 to-secondary/20 relative">
                                    <?php if ($p['gambar'] != 'default_promo.png'): ?>
                                        <img src="../../assets/images/promo/<?= $p['gambar'] ?>" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity">
                                    <?php endif; ?>
                                    <div class="absolute top-2 right-2 bg-primary text-white text-xs font-bold px-2 py-1 rounded shadow-lg"><?= $p['persentase'] ?>% OFF</div>
                                </div>
                                <div class="p-4">
                                    <h4 class="font-bold text-white mb-1"><?= $p['nama_promo'] ?></h4>
                                    <p class="text-xs text-gray-400 mb-3 line-clamp-1"><?= $p['deskripsi'] ?></p>
                                    <div class="flex justify-between items-center bg-dark-bg p-2 rounded-lg border border-dashed border-dark-border">
                                        <code class="text-secondary font-mono font-bold"><?= $p['kode_promo'] ?></code>
                                        <span class="text-[10px] text-gray-500">Min. <?= number_format($p['min_transaksi'] / 1000) ?>k</span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-white">Ketersediaan PC</h3>
                    <a href="booking.php" class="text-xs text-primary hover:text-white transition-colors">Lihat Semua</a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                    <?php
                    $pc_query = mysqli_query($conn, "SELECT * FROM pc ORDER BY id_pc ASC LIMIT 8");
                    while ($pc = mysqli_fetch_assoc($pc_query)):
                        $is_ready = ($pc['status_pc'] == 'tersedia'); // Sesuai DB: tersedia/digunakan/rusak
                        $bg_status = $is_ready ? 'bg-dark-surface border-dark-border text-gray-400' : 'bg-primary/20 border-primary text-white shadow-neon-purple';
                        $icon = $is_ready ? 'desktop_windows' : 'sports_esports';
                    ?>
                        <div class="p-3 rounded-xl border <?= $bg_status; ?> flex flex-col items-center justify-center text-center transition-all">
                            <span class="material-symbols-outlined text-xl mb-1"><?= $icon; ?></span>
                            <span class="text-xs font-bold">PC-<?= $pc['id_pc']; ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

        </div>
    </main>

    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>