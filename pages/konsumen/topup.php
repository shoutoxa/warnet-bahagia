<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'konsumen') {
    header("Location: ../../index.php");
    exit;
}

$id_konsumen = $_SESSION['id_konsumen'];

// HANDLE TOP UP (SIMULASI)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'topup') {
    $nominal = (float)$_POST['nominal'];
    $metode = $_POST['metode']; 

    if ($nominal < 5000) {
        setFlashMessage('warning', 'Perhatian', 'Minimal Top Up adalah Rp 5.000');
        header("Location: topup.php");
        exit;
    } else {
        $conn->query("UPDATE konsumen SET saldo = COALESCE(saldo, 0) + $nominal WHERE id_konsumen = '$id_konsumen'");

        setFlashMessage('success', 'Top Up Berhasil!', 'Saldo bertambah Rp ' . number_format($nominal, 0, ',', '.'));
        header("Location: dashboard.php");
        exit;
    }
}

// AMBIL SALDO TERBARU
$user_saldo = $conn->query("SELECT COALESCE(saldo, 0) as saldo FROM konsumen WHERE id_konsumen = '$id_konsumen'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Up - Warnet Bahagia</title>
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
                        'neon-green': '0 0 15px rgba(0, 255, 128, 0.4)',
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
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-green-500/10 blur-[120px] rounded-full"></div>
        </div>

        <header class="md:hidden h-16 bg-dark-surface/80 backdrop-blur-md border-b border-dark-border flex items-center justify-between px-4 z-20 sticky top-0">
            <span class="text-lg font-bold text-white">Warnet<span class="text-primary">Bahagia</span></span>
            <button onclick="toggleSidebar()" class="text-white p-2"><span class="material-symbols-outlined">menu</span></button>
        </header>

        <div class="flex-1 overflow-y-auto p-6 z-10">

            <div class="max-w-2xl mx-auto">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-white mb-2">Isi Saldo (Top Up)</h2>
                    <p class="text-gray-400 text-sm">Metode instan untuk menambah jam bermain.</p>
                </div>

                <div class="bg-gradient-to-r from-green-900/40 to-dark-surface border border-green-500/30 p-6 rounded-2xl mb-8 flex flex-col items-center justify-center relative overflow-hidden shadow-neon-green">
                    <div class="absolute top-0 right-0 p-6 opacity-20">
                        <span class="material-symbols-outlined text-8xl text-green-500">account_balance_wallet</span>
                    </div>
                    <p class="text-green-400 font-bold tracking-widest uppercase text-xs mb-2">Saldo Anda Saat Ini</p>
                    <h3 class="text-4xl md:text-5xl font-bold text-white">Rp <?= number_format($user_saldo['saldo'], 0, ',', '.'); ?></h3>
                </div>

                <div class="bg-dark-surface border border-dark-border p-6 rounded-2xl shadow-xl">
                    <form method="POST">
                        <input type="hidden" name="action" value="topup">

                        <label class="block text-sm font-bold text-gray-400 mb-3 ml-1">PILIH NOMINAL</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                            <button type="button" onclick="setNominal(10000)" class="nominal-btn py-3 rounded-xl border border-dark-border bg-dark-bg hover:border-green-500 hover:text-green-400 hover:bg-green-500/10 transition-all font-bold text-sm">
                                10.000
                            </button>
                            <button type="button" onclick="setNominal(20000)" class="nominal-btn py-3 rounded-xl border border-dark-border bg-dark-bg hover:border-green-500 hover:text-green-400 hover:bg-green-500/10 transition-all font-bold text-sm">
                                20.000
                            </button>
                            <button type="button" onclick="setNominal(50000)" class="nominal-btn py-3 rounded-xl border border-dark-border bg-dark-bg hover:border-green-500 hover:text-green-400 hover:bg-green-500/10 transition-all font-bold text-sm">
                                50.000
                            </button>
                            <button type="button" onclick="setNominal(100000)" class="nominal-btn py-3 rounded-xl border border-dark-border bg-dark-bg hover:border-green-500 hover:text-green-400 hover:bg-green-500/10 transition-all font-bold text-sm">
                                100.000
                            </button>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-400 mb-2 ml-1">ATAU INPUT MANUAL (Rp)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-gray-500 font-bold">Rp</span>
                                <input type="number" name="nominal" id="inputNominal" required min="5000"
                                    class="w-full bg-dark-bg border border-dark-border text-white text-lg font-bold rounded-xl pl-12 pr-4 py-3 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all placeholder-gray-600"
                                    placeholder="0">
                            </div>
                            <p class="text-xs text-gray-500 mt-2 ml-1">*Minimal top up Rp 5.000</p>
                        </div>

                        <div class="mb-8">
                            <label class="block text-sm font-bold text-gray-400 mb-3 ml-1">METODE PEMBAYARAN</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="metode" value="qris" class="peer sr-only" checked>
                                    <div class="p-3 rounded-xl border border-dark-border bg-dark-bg peer-checked:border-green-500 peer-checked:bg-green-500/10 transition-all flex items-center gap-2">
                                        <span class="material-symbols-outlined text-gray-400 peer-checked:text-green-500">qr_code_scanner</span>
                                        <span class="text-sm font-bold text-gray-300 peer-checked:text-white">QRIS</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="metode" value="cash" class="peer sr-only">
                                    <div class="p-3 rounded-xl border border-dark-border bg-dark-bg peer-checked:border-green-500 peer-checked:bg-green-500/10 transition-all flex items-center gap-2">
                                        <span class="material-symbols-outlined text-gray-400 peer-checked:text-green-500">payments</span>
                                        <span class="text-sm font-bold text-gray-300 peer-checked:text-white">Tunai (Kasir)</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 bg-green-600 hover:bg-green-500 text-white font-bold rounded-xl shadow-lg hover:shadow-green-500/20 transition-all flex items-center justify-center gap-2 text-lg">
                            <span class="material-symbols-outlined">check_circle</span>
                            Konfirmasi Top Up
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </main>

    <script>
        function setNominal(amount) {
            document.getElementById('inputNominal').value = amount;
        }
    </script>
    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>