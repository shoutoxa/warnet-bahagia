<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Cek Session Admin
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // TAMBAH BILLING
    if ($action === 'add') {
        $durasi = (int) $_POST['durasi_jam'];
        $harga  = (float) $_POST['harga_per_jam'];
        $total  = $durasi * $harga;

        $query = "INSERT INTO billing (durasi_jam, harga_per_jam, total_harga) VALUES ($durasi, $harga, $total)";

        if ($conn->query($query)) {
            echo "<script>alert('Billing berhasil ditambahkan!'); window.location='billing.php';</script>";
        } else {
            echo "<script>alert('Gagal: " . $conn->error . "');</script>";
        }
    }

    // HAPUS BILLING
    elseif ($action === 'delete') {
        $id = (int) $_POST['id_billing'];
        $query = "DELETE FROM billing WHERE id_billing = $id";

        if ($conn->query($query)) {
            echo "<script>alert('Billing berhasil dihapus!'); window.location='billing.php';</script>";
        } else {
            echo "<script>alert('Gagal hapus: " . $conn->error . "');</script>";
        }
    }
}

$billing_list = $conn->query("SELECT * FROM billing ORDER BY id_billing DESC");
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Paket Billing</title>

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

        <div class="flex-1 overflow-y-auto p-6 md:p-8 z-10 scroll-smooth">

            <div class="mb-8">
                <h2 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Paket Billing</h2>
                <p class="text-gray-400 mt-1 text-sm">Atur harga paket bermain per jam.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-1">
                    <div class="bg-dark-surface/80 backdrop-blur-xl border border-dark-border rounded-2xl p-6 shadow-xl sticky top-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined">add_circle</span>
                            </div>
                            <h3 class="text-lg font-bold text-white">Buat Paket Baru</h3>
                        </div>

                        <form method="POST" action="" class="space-y-5">
                            <input type="hidden" name="action" value="add">

                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase text-gray-400 ml-1">Durasi (Jam)</label>
                                <div class="relative group">
                                    <span class="absolute left-4 top-3 text-gray-500 material-symbols-outlined">schedule</span>
                                    <input type="number" name="durasi_jam" required
                                        class="w-full bg-dark-bg border border-dark-border text-white text-sm rounded-xl focus:ring-1 focus:ring-primary focus:border-primary block pl-12 p-3 placeholder-gray-600 transition-all outline-none"
                                        placeholder="Contoh: 1">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase text-gray-400 ml-1">Harga per Jam (Rp)</label>
                                <div class="relative group">
                                    <span class="absolute left-4 top-3 text-gray-500 material-symbols-outlined">attach_money</span>
                                    <input type="number" name="harga_per_jam" required
                                        class="w-full bg-dark-bg border border-dark-border text-white text-sm rounded-xl focus:ring-1 focus:ring-primary focus:border-primary block pl-12 p-3 placeholder-gray-600 transition-all outline-none"
                                        placeholder="Contoh: 5000">
                                </div>
                            </div>

                            <button type="submit" class="w-full py-3 bg-primary hover:bg-primary/80 text-white font-bold rounded-xl shadow-neon transition-all flex items-center justify-center gap-2 mt-4 hover:-translate-y-1">
                                <span class="material-symbols-outlined">save</span>
                                Simpan Paket
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if ($billing_list->num_rows > 0): ?>
                            <?php while ($b = $billing_list->fetch_assoc()): ?>

                                <div class="bg-dark-surface border border-dark-border rounded-2xl p-6 relative group hover:border-primary/50 transition-all hover:shadow-lg hover:shadow-primary/10 flex flex-col justify-between">

                                    <div class="absolute top-4 right-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                        <span class="material-symbols-outlined text-6xl text-white">confirmation_number</span>
                                    </div>

                                    <div>
                                        <div class="flex items-baseline gap-1 mb-1">
                                            <h3 class="text-4xl font-bold text-white"><?= $b['durasi_jam']; ?></h3>
                                            <span class="text-gray-400 font-medium">Jam</span>
                                        </div>
                                        <div class="inline-block px-3 py-1 rounded-full bg-dark-bg text-xs text-gray-400 border border-dark-border mb-4">
                                            Paket Regular
                                        </div>

                                        <div class="space-y-3 mb-6 bg-dark-bg/50 p-3 rounded-xl border border-dashed border-dark-border">
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-gray-400">Harga/Jam</span>
                                                <span class="text-gray-300">Rp <?= number_format($b['harga_per_jam'], 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="h-px bg-dark-border w-full"></div>
                                            <div class="flex items-center justify-between text-lg font-bold text-neon-cyan">
                                                <span>Total</span>
                                                <span>Rp <?= number_format($b['total_harga'], 0, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <button onclick="hapusBilling(<?= $b['id_billing']; ?>)" class="w-full py-2.5 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center gap-2 text-sm font-semibold hover:shadow-lg hover:shadow-red-500/20">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                        Hapus Paket
                                    </button>
                                </div>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-span-2 py-12 flex flex-col items-center justify-center border-2 border-dashed border-dark-border rounded-2xl text-gray-500">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">sentiment_dissatisfied</span>
                                <p>Belum ada paket billing tersedia.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_billing" id="delete_id_billing">
    </form>

    <script>
        // Fungsi Hapus
        function hapusBilling(id) {
            if (confirm('Yakin ingin menghapus paket billing ini?')) {
                document.getElementById('delete_id_billing').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>

    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>