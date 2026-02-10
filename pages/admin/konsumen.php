<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // UPDATE SALDO
    if ($action === 'update') {
        $id    = (int) $_POST['id_konsumen'];
        $saldo = (float) $_POST['saldo'];

        $query = "UPDATE konsumen SET saldo = $saldo WHERE id_konsumen = $id";

        if ($conn->query($query)) {
            setFlashMessage('success', 'Berhasil', 'Saldo konsumen berhasil diperbarui!');
            header("Location: konsumen.php");
            exit;
        } else {
            setFlashMessage('error', 'Gagal', 'Gagal update saldo: ' . $conn->error);
        }
    }

    // HAPUS KONSUMEN
    elseif ($action === 'delete') {
        $id = (int) $_POST['id_konsumen'];

        $cek = $conn->query("SELECT COUNT(*) AS total FROM pesanan WHERE id_konsumen = $id")->fetch_assoc();
        $cek2 = $conn->query("SELECT COUNT(*) AS total FROM transaksi_pc WHERE id_konsumen = $id")->fetch_assoc();

        if ($cek['total'] > 0 || $cek2['total'] > 0) {
            setFlashMessage('error', 'Gagal', 'Konsumen tidak bisa dihapus karena memiliki riwayat transaksi!');
            header("Location: konsumen.php");
            exit;
        } else {
            $query = "DELETE FROM konsumen WHERE id_konsumen = $id";
            if ($conn->query($query)) {
                setFlashMessage('success', 'Berhasil', 'Konsumen berhasil dihapus!');
                header("Location: konsumen.php");
                exit;
            } else {
                setFlashMessage('error', 'Gagal', 'Gagal menghapus konsumen: ' . $conn->error);
            }
        }
    }
}

$konsumen_list = $conn->query("SELECT * FROM konsumen ORDER BY id_konsumen DESC");
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Data Konsumen</title>

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
                    <h2 class="text-3xl font-bold text-white tracking-tight">Data Konsumen</h2>
                    <p class="text-gray-400 mt-1 text-sm">Kelola akun, saldo, dan informasi pelanggan.</p>
                </div>
            </div>

            <div class="bg-dark-surface/60 backdrop-blur-xl border border-dark-border rounded-2xl overflow-hidden shadow-xl">

                <div class="p-5 border-b border-dark-border flex justify-between items-center">
                    <h3 class="font-semibold text-white">Daftar Member Warnet</h3>
                    <div class="relative hidden sm:block">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-500 text-sm">search</span>
                        <input type="text" placeholder="Cari Nama / Username..." class="bg-dark-bg border border-dark-border text-white text-sm rounded-full pl-10 pr-4 py-2 focus:ring-1 focus:ring-primary outline-none placeholder-gray-600 transition-all w-64">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-dark-bg/50 text-gray-400 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="p-4 font-medium">ID</th>
                                <th class="p-4 font-medium">Member</th>
                                <th class="p-4 font-medium">Kontak</th>
                                <th class="p-4 font-medium">Saldo (Rp)</th>
                                <th class="p-4 font-medium text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-border text-sm text-gray-300">
                            <?php if ($konsumen_list->num_rows > 0): ?>
                                <?php while ($k = $konsumen_list->fetch_assoc()): ?>
                                    <?php

                                    $nama_display = isset($k['nama_lengkap']) ? $k['nama_lengkap'] : $k['username'];

                                    $inisial = strtoupper(substr($nama_display, 0, 1));
                                    ?>
                                    <tr class="hover:bg-primary/5 transition-colors group">
                                        <td class="p-4 font-mono text-primary">#<?= $k['id_konsumen']; ?></td>

                                        <td class="p-4 font-medium text-white">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold shadow-lg">
                                                    <?= $inisial; ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold"><?= htmlspecialchars($nama_display); ?></div>
                                                    <div class="text-xs text-gray-500">@<?= $k['username']; ?></div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="p-4 text-gray-400">
                                            <div class="flex flex-col gap-1">
                                                <span class="flex items-center gap-1 text-xs">
                                                    <span class="material-symbols-outlined text-[14px]">mail</span> <?= $k['email']; ?>
                                                </span>
                                            </div>
                                        </td>

                                        <td class="p-4">
                                            <form method="POST" class="flex items-center gap-2">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id_konsumen" value="<?= $k['id_konsumen']; ?>">

                                                <div class="relative">
                                                    <span class="absolute left-2 top-1.5 text-gray-500 text-xs">Rp</span>
                                                    <input type="number" name="saldo" value="<?= $k['saldo']; ?>"
                                                        class="w-28 pl-6 pr-2 py-1 bg-dark-bg border border-dark-border rounded-lg text-white text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-mono">
                                                </div>

                                                <button type="submit" class="p-1.5 bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500 hover:text-white transition-all border border-blue-500/20" title="Simpan Saldo">
                                                    <span class="material-symbols-outlined text-[18px]">save</span>
                                                </button>
                                            </form>
                                        </td>

                                        <td class="p-4 text-center">
                                            <form method="POST" id="delete-form-<?= $k['id_konsumen']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_konsumen" value="<?= $k['id_konsumen']; ?>">

                                                <button type="button" onclick="confirmDelete(<?= $k['id_konsumen']; ?>)" class="p-2 rounded-lg text-red-400 hover:bg-red-500 hover:text-white transition-all border border-red-500/20" title="Hapus Member">
                                                    <span class="material-symbols-outlined text-[18px]">person_remove</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-12 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-4xl mb-2 opacity-30">group_off</span>
                                        <p>Belum ada data konsumen.</p>
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
                title: 'Hapus Member?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
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
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    </script>
    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>