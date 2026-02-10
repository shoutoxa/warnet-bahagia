<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Cek Session Admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../index.php');
}

// Logic: Update Status Laporan
if (isset($_GET['action']) && $_GET['action'] == 'selesai' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE bantuan_teknis SET status='selesai' WHERE id_bantuan=$id");
    redirect("bantuan.php");
}

// Logic: Hapus Laporan
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM bantuan_teknis WHERE id_bantuan=$id");
    redirect("bantuan.php");
}

// Fetch Data Bantuan
$query = $conn->query("
    SELECT bt.*, k.username AS nama_lengkap
    FROM bantuan_teknis bt 
    JOIN konsumen k ON bt.id_konsumen = k.id_konsumen 
    ORDER BY bt.id_bantuan DESC
");

?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bantuan Teknis</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Spline+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Spline Sans', 'sans-serif'],
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
                        'neon-purple': '0 0 15px rgba(127, 13, 242, 0.4)',
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1b1022;
        }

        ::-webkit-scrollbar-thumb {
            background: #4a3b54;
            border-radius: 4px;
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

        <header class="md:hidden h-16 bg-dark-surface border-b border-dark-border flex items-center justify-between px-4 z-10">
            <span class="text-lg font-bold text-white">Warnet<span class="text-primary">Admin</span></span>
            <button class="text-white"><span class="material-symbols-outlined">menu</span></button>
        </header>

        <div class="flex-1 overflow-y-auto p-6 md:p-10 z-10 scroll-smooth">

            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Pusat Bantuan</h2>
                    <p class="text-gray-400 mt-1 text-sm">Kelola laporan masalah dan tiket bantuan dari konsumen.</p>
                </div>
                <div class="hidden md:block">
                    <div class="px-4 py-2 bg-dark-surface rounded-full border border-dark-border flex items-center gap-2 text-sm text-gray-300">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        Admin Online
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-dark-surface border border-dark-border p-5 rounded-2xl flex items-center gap-4 hover:border-primary/50 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">inbox</span>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs uppercase font-semibold">Total Laporan</p>
                        <h3 class="text-2xl font-bold text-white">
                            <?php echo $query->num_rows; ?>
                        </h3>
                    </div>
                </div>
            </div>

            <div class="bg-dark-surface/60 backdrop-blur-xl border border-dark-border rounded-2xl overflow-hidden shadow-xl">
                <div class="p-6 border-b border-dark-border flex justify-between items-center">
                    <h3 class="font-semibold text-white">Daftar Tiket Masuk</h3>
                    <div class="relative hidden sm:block">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-500 text-sm">search</span>
                        <input type="text" placeholder="Cari tiket..." class="bg-dark-bg border border-dark-border text-white text-sm rounded-full pl-10 pr-4 py-2 focus:ring-1 focus:ring-primary outline-none placeholder-gray-600 transition-all w-64">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-dark-bg/50 text-gray-400 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="p-4 font-medium">ID</th>
                                <th class="p-4 font-medium">Konsumen</th>
                                <th class="p-4 font-medium">Jenis & Deskripsi</th>
                                <th class="p-4 font-medium text-center">Status</th>
                                <th class="p-4 font-medium text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-border text-sm text-gray-300">
                            <?php if ($query->num_rows > 0): ?>
                                <?php while ($row = $query->fetch_assoc()): ?>
                                    <tr class="hover:bg-primary/5 transition-colors group">
                                        <td class="p-4 font-mono text-primary">#<?= $row['id_bantuan']; ?></td>
                                        <td class="p-4 font-medium text-white">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-[10px] text-white font-bold">
                                                    <?= substr($row['nama_lengkap'], 0, 1); ?>
                                                </div>
                                                <?= htmlspecialchars($row['nama_lengkap']); ?>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="font-bold text-primary mb-1"><?= htmlspecialchars($row['jenis_bantuan']); ?></div>
                                            <div class="text-gray-400 text-xs"><?= htmlspecialchars($row['deskripsi']); ?></div>
                                        </td>
                                        <td class="p-4 text-center">
                                            <?php if ($row['status'] == 'menunggu'): ?>
                                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-pulse"></span>
                                                    Pending
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20">
                                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                                    Selesai
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if ($row['status'] == 'menunggu'): ?>
                                                    <a href="bantuan.php?action=selesai&id=<?= $row['id_bantuan']; ?>"
                                                        class="p-2 rounded-lg bg-green-500/10 text-green-500 hover:bg-green-500 hover:text-white transition-all border border-green-500/20"
                                                        title="Tandai Selesai">
                                                        <span class="material-symbols-outlined text-[18px]">done</span>
                                                    </a>
                                                <?php endif; ?>

                                                <a href="bantuan.php?action=hapus&id=<?= $row['id_bantuan']; ?>"
                                                    onclick="return confirm('Hapus laporan ini?')"
                                                    class="p-2 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all border border-red-500/20"
                                                    title="Hapus">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-500 flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined text-4xl mb-2 opacity-30">inbox</span>
                                        Belum ada laporan bantuan teknis.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</body>

</html>