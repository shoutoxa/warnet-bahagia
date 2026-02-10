<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'konsumen') {
    header("Location: ../../index.php");
    exit;
}

$id_konsumen = $_SESSION['id_konsumen'];

// AMBIL DATA PESANAN
$query = "SELECT * FROM pesanan WHERE id_konsumen = '$id_konsumen' ORDER BY waktu_pesanan DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Warnet Bahagia</title>
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
                        "dark-border": "#352842"
                    },
                    boxShadow: {
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

        <header class="md:hidden h-16 bg-dark-surface/80 backdrop-blur-md border-b border-dark-border flex items-center justify-between px-4 z-20 sticky top-0">
            <span class="text-lg font-bold text-white">Warnet<span class="text-primary">Bahagia</span></span>
            <button onclick="toggleSidebar()" class="text-white p-2"><span class="material-symbols-outlined">menu</span></button>
        </header>

        <div class="flex-1 overflow-y-auto p-6 z-10">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Status Pesanan</h2>
                    <p class="text-gray-400 mt-1 text-sm">Lacak pesanan makanan & minumanmu.</p>
                </div>
                <a href="fnb.php" class="px-4 py-2 bg-dark-surface border border-dark-border hover:bg-dark-border rounded-lg text-sm text-white transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">add</span> Pesan Lagi
                </a>
            </div>

            <div class="space-y-4">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()):
                        $status = $row['status_pesanan']; 
                        $status_lower = strtolower($status);

                        $bg = 'bg-dark-surface border-dark-border';
                        $icon = 'hourglass_empty';
                        $color = 'text-gray-400';
                        $badge_class = 'bg-gray-700 text-gray-300';

                        if ($status_lower == 'pending') {
                            $bg = 'bg-dark-surface border-yellow-500/30';
                            $icon = 'skillet';
                            $color = 'text-yellow-400';
                            $badge_class = 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20';
                        } elseif ($status_lower == 'dibayar' || $status_lower == 'diproses') {
                            $bg = 'bg-dark-surface border-blue-500/30';
                            $icon = 'soup_kitchen';
                            $color = 'text-blue-400';
                            $badge_class = 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
                        } elseif ($status_lower == 'selesai') {
                            $bg = 'bg-dark-surface border-dark-border';
                            $icon = 'check_circle';
                            $color = 'text-green-500';
                            $badge_class = 'bg-green-500/10 text-green-400 border border-green-500/20';
                        } elseif ($status_lower == 'batal') {
                            $bg = 'bg-dark-surface border-dark-border opacity-70';
                            $icon = 'cancel';
                            $color = 'text-red-500';
                            $badge_class = 'bg-red-500/10 text-red-400 border border-red-500/20';
                        }
                    ?>

                        <div class="p-5 rounded-2xl border <?= $bg ?> transition-all hover:shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-dark-bg border border-dark-border flex items-center justify-center">
                                    <span class="material-symbols-outlined <?= $color ?>"><?= $icon ?></span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-white text-lg">Pesanan #<?= $row['id_pesanan'] ?></h4>
                                    <p class="text-xs text-gray-500"><?= date('d M Y, H:i', strtotime($row['waktu_pesanan'])) ?></p>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row items-end md:items-center gap-4 w-full md:w-auto">
                                <div class="text-right">
                                    <p class="text-xs text-gray-400">Total Biaya</p>
                                    <p class="font-bold text-white text-lg">Rp <?= number_format($row['total_pesanan'], 0, ',', '.') ?></p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?= $badge_class ?>">
                                    <?= $status ?>
                                </span>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="py-16 text-center border-2 border-dashed border-dark-border rounded-2xl">
                        <span class="material-symbols-outlined text-6xl text-gray-600 mb-4">no_meals</span>
                        <h3 class="text-xl font-bold text-white">Belum Ada Pesanan</h3>
                        <p class="text-gray-500 mb-6">Kamu belum memesan makanan apapun.</p>
                        <a href="fnb.php" class="inline-block px-6 py-2 bg-primary hover:bg-primary/80 text-white font-bold rounded-lg transition-all">Pesan Sekarang</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>