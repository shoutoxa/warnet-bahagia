<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // TAMBAH PC
    if ($action === 'add') {
        $id_admin = $_SESSION['id_admin'];
        $query = "INSERT INTO pc (status_pc, id_admin) VALUES ('tersedia', '$id_admin')";

        if ($conn->query($query)) {
            setFlashMessage('success', 'Berhasil', 'Unit PC berhasil ditambahkan!');
            header("Location: pc.php");
            exit;
        } else {
            setFlashMessage('error', 'Gagal', 'Gagal: ' . $conn->error);
        }
    }

    // UPDATE STATUS PC
    elseif ($action === 'update') {
        $id = (int)$_POST['id_pc'];
        $status = $_POST['status_pc'];

        $query = "UPDATE pc SET status_pc='$status' WHERE id_pc = $id";

        if ($conn->query($query)) {
            setFlashMessage('success', 'Berhasil', 'Status PC berhasil diupdate!');
            header("Location: pc.php");
            exit;
        } else {
            setFlashMessage('error', 'Gagal', 'Gagal: ' . $conn->error);
        }
    }

    // HAPUS PC
    elseif ($action === 'delete') {
        $id = (int)$_POST['id_pc'];
        $cek = $conn->query("SELECT status_pc FROM pc WHERE id_pc='$id'")->fetch_assoc();
        if ($cek['status_pc'] == 'digunakan') {
            setFlashMessage('error', 'Gagal', 'PC sedang digunakan, tidak bisa dihapus!');
            header("Location: pc.php");
            exit;
        } else {
            $query = "DELETE FROM pc WHERE id_pc = $id";
            if ($conn->query($query)) {
                setFlashMessage('success', 'Berhasil', 'PC berhasil dihapus!');
                header("Location: pc.php");
                exit;
            } else {
                setFlashMessage('error', 'Gagal', 'Gagal hapus: ' . $conn->error);
            }
        }
    }
}

// AMBIL DATA PC
$pc_list = $conn->query("
    SELECT p.*, k.username 
    FROM pc p 
    LEFT JOIN konsumen k ON p.id_konsumen = k.id_konsumen 
    ORDER BY p.id_pc ASC
");
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manajemen PC</title>

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
                    <h2 class="text-3xl font-bold text-white tracking-tight">Manajemen Unit PC</h2>
                    <p class="text-gray-400 mt-1 text-sm">Monitor dan kelola status komputer warnet.</p>
                </div>
                <form method="POST" id="addPCForm">
                    <input type="hidden" name="action" value="add">
                    <button type="button" onclick="confirmAdd()" class="bg-primary hover:bg-primary/80 text-white px-5 py-2.5 rounded-xl shadow-neon transition-all flex items-center gap-2 font-semibold">
                        <span class="material-symbols-outlined">add_to_queue</span>
                        Tambah Unit Auto
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if ($pc_list->num_rows > 0): ?>
                    <?php while ($pc = $pc_list->fetch_assoc()): ?>
                        <?php
                        $status = $pc['status_pc'];

                        $bg_color = 'bg-dark-surface border-dark-border';
                        $icon_color = 'text-gray-600';
                        $status_badge = 'bg-gray-700 text-gray-300';
                        $icon = 'desktop_windows';
                        $label_status = $status;

                        if ($status == 'tersedia') {
                            $bg_color = 'bg-dark-surface border-green-500/30 shadow-lg shadow-green-500/5';
                            $icon_color = 'text-green-500';
                            $status_badge = 'bg-green-500/10 text-green-400 border border-green-500/20';
                            $icon = 'check_circle';
                            $label_status = 'Ready';
                        } elseif ($status == 'digunakan') {
                            $bg_color = 'bg-dark-surface border-primary/50 shadow-neon';
                            $icon_color = 'text-primary animate-pulse';
                            $status_badge = 'bg-primary/10 text-primary border border-primary/20';
                            $icon = 'sports_esports';
                            $label_status = 'In Game';
                        } elseif ($status == 'rusak') {
                            $bg_color = 'bg-dark-surface border-red-500/30';
                            $icon_color = 'text-red-500';
                            $status_badge = 'bg-red-500/10 text-red-400 border border-red-500/20';
                            $icon = 'build';
                            $label_status = 'Maintenance';
                        }
                        ?>

                        <div class="border rounded-2xl p-5 relative transition-all group <?= $bg_color; ?>">

                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-white">PC-<?= $pc['id_pc']; ?></h3>
                                    <p class="text-xs text-gray-500 mt-1">Standard Gaming Rig</p>
                                </div>
                                <span class="material-symbols-outlined text-4xl <?= $icon_color; ?>"><?= $icon; ?></span>
                            </div>

                            <div class="mb-6 flex justify-between items-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?= $status_badge; ?>">
                                    <?= $label_status; ?>
                                </span>
                                <?php if ($status == 'digunakan' && $pc['username']): ?>
                                    <span class="text-xs text-gray-400 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">person</span>
                                        <?= $pc['username']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="flex gap-2 border-t border-dark-border pt-4">
                                <button onclick='openModal("edit", <?= json_encode($pc); ?>)'
                                    class="flex-1 py-2 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white transition-all text-xs font-bold border border-blue-500/20">
                                    STATUS
                                </button>
                                <form method="POST" class="flex-1" id="delete-form-<?= $pc['id_pc']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_pc" value="<?= $pc['id_pc']; ?>">
                                    <button type="button" onclick="confirmDelete(<?= $pc['id_pc']; ?>)" class="w-full py-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition-all text-xs font-bold border border-red-500/20">
                                        HAPUS
                                    </button>
                                </form>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full py-12 text-center text-gray-500 border-2 border-dashed border-dark-border rounded-2xl">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">desktop_access_disabled</span>
                        <p>Belum ada unit PC terdaftar.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <div id="modalForm" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>

        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-dark-surface border border-dark-border rounded-2xl w-full max-w-sm p-6 relative shadow-2xl transform transition-all scale-100">

                <h3 class="text-xl font-bold text-white mb-6">Ubah Status PC</h3>

                <form method="POST" id="formPC">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_pc" id="pcId">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">STATUS SAAT INI</label>
                            <div class="relative">
                                <select name="status_pc" id="statusPc" class="w-full bg-dark-bg border border-dark-border text-white text-sm rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none cursor-pointer">
                                    <option value="tersedia">Tersedia (Ready)</option>
                                    <option value="digunakan">Digunakan</option>
                                    <option value="rusak">Rusak / Maintenance</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3 text-gray-500 pointer-events-none">expand_more</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-8">
                        <button type="button" onclick="closeModal()" class="flex-1 py-3 rounded-xl bg-dark-bg border border-dark-border text-gray-400 hover:text-white hover:bg-gray-800 transition-all font-semibold">Batal</button>
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-primary hover:bg-primary/80 text-white font-bold shadow-neon transition-all">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalForm');
        const pcId = document.getElementById('pcId');
        const statusPc = document.getElementById('statusPc');

        function openModal(mode, data = null) {
            if (mode === 'edit' && data) {
                pcId.value = data.id_pc;
                statusPc.value = data.status_pc; 
                modal.classList.remove('hidden');
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function confirmAdd() {
            Swal.fire({
                title: 'Tambah Unit PC?',
                text: "Sistem akan menambahkan 1 unit PC baru secara otomatis.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7f0df2',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Tambah',
                cancelButtonText: 'Batal',
                background: '#1e1628',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('addPCForm').submit();
                }
            })
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus PC?',
                text: "Data PC akan dihapus permanen!",
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