<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Cek Login
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'konsumen') {
    header("Location: ../../index.php");
    exit;
}

$id_konsumen = $_SESSION['id_konsumen'];

$query_billing = mysqli_query($conn, "
    SELECT * FROM transaksi_pc 
    WHERE id_konsumen = '$id_konsumen' 
    ORDER BY waktu_mulai DESC LIMIT 10
");

$query_pesanan = mysqli_query($conn, "
    SELECT * FROM pesanan 
    WHERE id_konsumen = '$id_konsumen' 
    ORDER BY waktu_pesanan DESC 
    LIMIT 20
");
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Aktivitas - Warnet Bahagia</title>

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

        <header class="md:hidden h-16 bg-dark-surface/80 backdrop-blur-md border-b border-dark-border flex items-center justify-between px-4 z-20 sticky top-0">
            <span class="text-lg font-bold text-white">Warnet<span class="text-primary">Bahagia</span></span>
            <button onclick="toggleSidebar()" class="text-white p-2">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </header>

        <div class="flex-1 overflow-y-auto p-6 z-10">

            <div class="mb-8">
                <h2 class="text-3xl font-bold text-white tracking-tight">Riwayat Aktivitas</h2>
                <p class="text-gray-400 mt-1 text-sm">Catatan jejak gaming dan jajanan kamu.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="bg-dark-surface/60 backdrop-blur-xl border border-dark-border rounded-2xl overflow-hidden flex flex-col max-h-[calc(100vh-180px)]">
                    <div class="p-5 border-b border-dark-border flex items-center gap-3 bg-dark-surface sticky top-0 z-10">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <span class="material-symbols-outlined">sports_esports</span>
                        </div>
                        <h3 class="font-bold text-white">Log Permainan</h3>
                    </div>

                    <div class="overflow-y-auto p-4 space-y-3">
                        <?php if ($query_billing && mysqli_num_rows($query_billing) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($query_billing)): ?>
                                <?php
                                // Hitung Durasi (Jika sudah selesai)
                                $durasi_text = "Sedang Berjalan";
                                $status_color = "text-yellow-400";
                                $border_active = "border-yellow-500/30";

                                if ($row['waktu_selesai'] && $row['status'] == 'selesai') {
                                    $start = new DateTime($row['waktu_mulai']);
                                    $end   = new DateTime($row['waktu_selesai']);
                                    $diff  = $start->diff($end);

                                    // Format durasi (contoh: 2j 15m)
                                    $jam = $diff->h + ($diff->days * 24);
                                    $menit = $diff->i;
                                    $durasi_text = ($jam > 0 ? $jam . "j " : "") . $menit . "m";

                                    $status_color = "text-gray-400";
                                    $border_active = "border-dark-border";
                                }
                                ?>
                                <div class="p-4 rounded-xl border <?= $border_active; ?> bg-dark-bg hover:bg-dark-surface transition-colors flex justify-between items-center group">
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col items-center justify-center w-12 h-12 rounded-lg bg-dark-surface border border-dark-border">
                                            <span class="text-xs text-gray-500 font-bold">PC</span>
                                            <span class="text-lg font-bold text-white"><?= $row['id_pc']; ?></span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1"><?= date('d M Y, H:i', strtotime($row['waktu_mulai'])); ?></p>
                                            <p class="text-sm font-bold text-white"><?= $durasi_text; ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-primary">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></p>
                                        <span class="text-[10px] uppercase font-bold <?= $status_color; ?>"><?= $row['status']; ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-10 text-gray-500">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">videogame_asset_off</span>
                                <p>Belum ada riwayat bermain.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-dark-surface/60 backdrop-blur-xl border border-dark-border rounded-2xl overflow-hidden flex flex-col max-h-[calc(100vh-180px)]">
                    <div class="p-5 border-b border-dark-border flex items-center gap-3 bg-dark-surface sticky top-0 z-10">
                        <div class="p-2 bg-secondary/10 rounded-lg text-secondary">
                            <span class="material-symbols-outlined">fastfood</span>
                        </div>
                        <h3 class="font-bold text-white">Riwayat Jajan</h3>
                    </div>

                    <div class="overflow-y-auto p-4 space-y-3">
                        <?php if (mysqli_num_rows($query_pesanan) > 0): ?>
                            <?php while ($ord = mysqli_fetch_assoc($query_pesanan)): ?>
                                <?php
                                $status = strtolower($ord['status_pesanan']);
                                $icon_bg = 'bg-gray-800';
                                $icon_col = 'text-gray-400';

                                if ($status == 'selesai') {
                                    $icon_bg = 'bg-green-900/20';
                                    $icon_col = 'text-green-500';
                                } elseif ($status == 'pending' || $status == 'diproses') {
                                    $icon_bg = 'bg-yellow-900/20';
                                    $icon_col = 'text-yellow-500';
                                } elseif ($status == 'batal') {
                                    $icon_bg = 'bg-red-900/20';
                                    $icon_col = 'text-red-500';
                                }
                                ?>
                                <div class="p-4 rounded-xl border border-dark-border bg-dark-bg hover:bg-dark-surface transition-colors flex justify-between items-center">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full <?= $icon_bg; ?> flex items-center justify-center">
                                            <span class="material-symbols-outlined <?= $icon_col; ?> text-xl">receipt</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-white">Order #<?= $ord['id_pesanan']; ?></p>
                                            <p class="text-xs text-gray-500"><?= date('d M Y, H:i', strtotime($ord['waktu_pesanan'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-secondary">Rp <?= number_format($ord['total_pesanan'], 0, ',', '.'); ?></p>
                                        <span class="text-[10px] uppercase font-bold <?= $icon_col; ?>"><?= $ord['status_pesanan']; ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-10 text-gray-500">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">no_meals</span>
                                <p>Belum ada riwayat pesanan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>