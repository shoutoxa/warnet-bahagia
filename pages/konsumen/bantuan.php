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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'kirim') {
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis_bantuan']);
    $desk  = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    if (empty($jenis) || empty($desk)) {
        setFlashMessage('warning', 'Data Tidak Lengkap', 'Jenis dan deskripsi wajib diisi.');
        header("Location: bantuan.php");
        exit;
    } else {
        $query = "INSERT INTO bantuan_teknis (id_konsumen, jenis_bantuan, deskripsi, status)
                  VALUES ('$id_konsumen', '$jenis', '$desk', 'menunggu')";

        if ($conn->query($query)) {
            setFlashMessage('success', 'Terkirim!', 'Tiket bantuan berhasil dikirim! Admin akan segera merespon.');
            header("Location: bantuan.php");
            exit;
        } else {
            setFlashMessage('error', 'Error', 'Gagal mengirim tiket: ' . $conn->error);
            header("Location: bantuan.php");
            exit;
        }
    }
}

$bantuan = $conn->query("
    SELECT *
    FROM bantuan_teknis
    WHERE id_konsumen = '$id_konsumen'
    ORDER BY id_bantuan DESC
");
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantuan - Warnet Bahagia</title>

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

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Pusat Bantuan</h2>
                    <p class="text-gray-400 mt-1 text-sm">Laporkan masalah PC, Jaringan, atau Akun.</p>
                </div>
                <button onclick="openModal()" class="bg-primary hover:bg-primary/80 text-white px-5 py-2.5 rounded-xl shadow-neon-purple transition-all flex items-center gap-2 font-semibold">
                    <span class="material-symbols-outlined">add_comment</span>
                    Buat Tiket Baru
                </button>
            </div>

            <div class="space-y-4">
                <?php if ($bantuan->num_rows > 0): ?>
                    <?php while ($b = $bantuan->fetch_assoc()):
                        // Status Config
                        $status = ucfirst($b['status']);
                        $status_class = 'bg-gray-700 text-gray-300 border-gray-600';
                        $icon = 'hourglass_empty';

                        if ($b['status'] == 'menunggu') {
                            $status_class = 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
                            $icon = 'schedule';
                        } elseif ($b['status'] == 'diproses') {
                            $status_class = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                            $icon = 'engineering';
                        } elseif ($b['status'] == 'selesai') {
                            $status_class = 'bg-green-500/10 text-green-400 border-green-500/20';
                            $icon = 'check_circle';
                        }
                    ?>

                        <div class="bg-dark-surface border border-dark-border p-5 rounded-2xl hover:border-primary/30 transition-all group relative overflow-hidden">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-dark-bg flex items-center justify-center border border-dark-border group-hover:border-primary/50 transition-colors">
                                        <span class="material-symbols-outlined text-gray-400 group-hover:text-primary">support</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white"><?= $b['jenis_bantuan']; ?></h3>
                                        <p class="text-xs text-gray-500">ID Tiket: #<?= $b['id_bantuan']; ?></p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold border flex items-center gap-1 <?= $status_class; ?>">
                                    <span class="material-symbols-outlined text-[14px]"><?= $icon; ?></span>
                                    <?= $status; ?>
                                </span>
                            </div>

                            <div class="bg-dark-bg/50 p-4 rounded-xl border border-dark-border mb-3">
                                <p class="text-sm text-gray-300 leading-relaxed">"<?= $b['deskripsi']; ?>"</p>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="py-16 text-center border-2 border-dashed border-dark-border rounded-2xl">
                        <span class="material-symbols-outlined text-6xl text-gray-600 mb-4">sentiment_satisfied</span>
                        <h3 class="text-xl font-bold text-white">Tidak Ada Masalah</h3>
                        <p class="text-gray-500">Kamu belum pernah mengirim laporan bantuan.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <div id="modalForm" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>

        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-dark-surface border border-dark-border rounded-2xl w-full max-w-md p-6 relative shadow-2xl transform transition-all scale-100">

                <h3 class="text-xl font-bold text-white mb-6">Ajukan Bantuan</h3>

                <form method="POST">
                    <input type="hidden" name="action" value="kirim">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">KATEGORI MASALAH</label>
                            <div class="relative">
                                <select name="jenis_bantuan" class="w-full bg-dark-bg border border-dark-border text-white text-sm rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none cursor-pointer" required>
                                    <option value="" disabled selected>Pilih Kategori</option>
                                    <option value="PC & Hardware">PC / Hardware Bermasalah</option>
                                    <option value="Jaringan & Internet">Internet Lambat / Putus</option>
                                    <option value="Billing & Akun">Masalah Billing / Akun</option>
                                    <option value="F&B Order">Pesanan Makanan / Minuman</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3 text-gray-500 pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">DESKRIPSI MASALAH</label>
                            <textarea name="deskripsi" rows="4" required
                                class="w-full bg-dark-bg border border-dark-border text-white text-sm rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-gray-600"
                                placeholder="Ceritakan detail masalah yang kamu alami..."></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-8">
                        <button type="button" onclick="closeModal()" class="flex-1 py-3 rounded-xl bg-dark-bg border border-dark-border text-gray-400 hover:text-white hover:bg-gray-800 transition-all font-bold">Batal</button>
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-primary hover:bg-primary/80 text-white font-bold shadow-neon-purple transition-all">Kirim Tiket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalForm');

        function openModal() {
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }
    </script>
    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>