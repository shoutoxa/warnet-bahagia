<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // UPDATE STATUS PESANAN
    if ($action === 'update') {
        $id     = (int) $_POST['id_pesanan'];
        $status = $_POST['status_pesanan'];
        $id_admin = $_SESSION['id_admin']; 

        $query = "UPDATE pesanan SET status_pesanan = '$status', id_admin = '$id_admin' WHERE id_pesanan = $id";

        if ($conn->query($query)) {
            setFlashMessage('success', 'Berhasil', 'Status berhasil diupdate!');
            header("Location: pesanan.php");
            exit;
        } else {
            setFlashMessage('error', 'Gagal', 'Gagal: ' . $conn->error);
        }
    }

    // HAPUS PESANAN
    elseif ($action === 'delete') {
        $id = (int) $_POST['id_pesanan'];

        $query = "DELETE FROM pesanan WHERE id_pesanan = $id";

        if ($conn->query($query)) {
            setFlashMessage('success', 'Berhasil', 'Pesanan dihapus!');
            header("Location: pesanan.php");
            exit;
        } else {
            setFlashMessage('error', 'Gagal', 'Gagal hapus: ' . $conn->error);
        }
    }
}


$query_pesanan = "
    SELECT p.*, k.username, a.nama_admin
    FROM pesanan p
    LEFT JOIN konsumen k ON p.id_konsumen = k.id_konsumen
    LEFT JOIN admin a ON p.id_admin = a.id_admin
    ORDER BY p.waktu_pesanan DESC
";
$pesanan_list = $conn->query($query_pesanan);
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manajemen Pesanan</title>

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

            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Pesanan F&B</h2>
                    <p class="text-gray-400 mt-1 text-sm">Kelola pesanan makanan dan minuman dari pelanggan.</p>
                </div>
                <div class="hidden md:block">
                    <div class="px-4 py-2 bg-dark-surface rounded-full border border-dark-border flex items-center gap-2 text-sm text-gray-300">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        Admin Online
                    </div>
                </div>
            </div>

            <div class="bg-dark-surface/60 backdrop-blur-xl border border-dark-border rounded-2xl overflow-hidden shadow-xl">

                <div class="p-5 border-b border-dark-border flex justify-between items-center">
                    <h3 class="font-semibold text-white">Daftar Pesanan Masuk</h3>
                    <div class="relative hidden sm:block">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-500 text-sm">search</span>
                        <input type="text" placeholder="Cari ID / User..." class="bg-dark-bg border border-dark-border text-white text-sm rounded-full pl-10 pr-4 py-2 focus:ring-1 focus:ring-primary outline-none placeholder-gray-600 transition-all w-64">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-dark-bg/50 text-gray-400 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="p-4 font-medium">ID</th>
                                <th class="p-4 font-medium">Konsumen</th>
                                <th class="p-4 font-medium">Waktu</th>
                                <th class="p-4 font-medium">Total Harga</th>
                                <th class="p-4 font-medium">Status</th>
                                <th class="p-4 font-medium">Diproses Oleh</th>
                                <th class="p-4 font-medium text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-border text-sm text-gray-300">
                            <?php if ($pesanan_list->num_rows > 0): ?>
                                <?php while ($p = $pesanan_list->fetch_assoc()): ?>
                                    <tr class="hover:bg-primary/5 transition-colors group">
                                        <td class="p-4 font-mono text-primary">#<?= $p['id_pesanan']; ?></td>

                                        <td class="p-4 font-medium text-white">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center text-xs font-bold">
                                                    <?= substr($p['username'], 0, 1); ?>
                                                </div>
                                                <?= $p['username']; ?>
                                            </div>
                                        </td>

                                        <td class="p-4 text-gray-400 text-xs">
                                            <?= date('d M H:i', strtotime($p['waktu_pesanan'])); ?>
                                        </td>

                                        <td class="p-4 font-bold text-neon-cyan">
                                            Rp <?= number_format($p['total_pesanan'], 0, ',', '.'); ?>
                                        </td>

                                        <td class="p-4">
                                            <form method="POST" class="inline-block">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id_pesanan" value="<?= $p['id_pesanan']; ?>">

                                                <?php
                                                $status_db = strtolower($p['status_pesanan']);
                                                $bg_color = 'bg-dark-bg';
                                                if ($status_db == 'pending') $bg_color = 'bg-yellow-900/30 text-yellow-500 border-yellow-500/30';
                                                if ($status_db == 'dibayar') $bg_color = 'bg-green-900/30 text-green-500 border-green-500/30';
                                                if ($status_db == 'selesai') $bg_color = 'bg-blue-900/30 text-blue-500 border-blue-500/30';
                                                if ($status_db == 'batal') $bg_color = 'bg-red-900/30 text-red-500 border-red-500/30';
                                                ?>

                                                <select name="status_pesanan" onchange="this.form.submit()"
                                                    class="appearance-none cursor-pointer text-xs font-bold px-3 py-1.5 rounded-full border <?= $bg_color; ?> outline-none focus:ring-1 focus:ring-primary transition-all">
                                                    <option class="bg-dark-bg text-gray-300" value="pending" <?= $status_db == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option class="bg-dark-bg text-gray-300" value="dibayar" <?= $status_db == 'dibayar' ? 'selected' : ''; ?>>Dibayar</option>
                                                    <option class="bg-dark-bg text-gray-300" value="selesai" <?= $status_db == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                    <option class="bg-dark-bg text-gray-300" value="batal" <?= $status_db == 'batal' ? 'selected' : ''; ?>>Batal</option>
                                                </select>
                                            </form>
                                        </td>

                                        <td class="p-4 text-xs text-gray-500">
                                            <?= $p['nama_admin'] ? $p['nama_admin'] : '<span class="italic text-gray-600">-</span>'; ?>
                                        </td>

                                        <td class="p-4 text-center">
                                            <form method="POST" id="delete-order-<?= $p['id_pesanan']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_pesanan" value="<?= $p['id_pesanan']; ?>">

                                                <button type="button" onclick="confirmDelete(<?= $p['id_pesanan']; ?>)" class="p-2 rounded-lg text-red-400 hover:bg-red-500 hover:text-white transition-all border border-red-500/20" title="Hapus Pesanan">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-4xl mb-2 opacity-30">receipt_long</span>
                                        <p>Belum ada pesanan yang masuk.</p>
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
        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Pesanan?',
                text: "Data akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                background: '#1e1628',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-order-' + id).submit();
                }
            })
        }
    </script>
    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>