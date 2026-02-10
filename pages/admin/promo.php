<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Cek Session
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit;
}

// --- FUNGSI UPLOAD GAMBAR ---
function uploadPromo($file)
{
    $targetDir = "../../assets/images/promo/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = basename($file["name"]);
    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid() . '.' . $fileType;
    $targetSave = $targetDir . $newFileName;

    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
    if (in_array(strtolower($fileType), $allowTypes)) {
        if (move_uploaded_file($file["tmp_name"], $targetSave)) {
            return $newFileName;
        }
    }
    return false;
}

// HANDLE ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $nama   = $conn->real_escape_string($_POST['nama_promo']);
        $kode   = $conn->real_escape_string($_POST['kode_promo']);
        $target = $conn->real_escape_string($_POST['target']);
        $persen = (int) $_POST['persentase'];
        $min    = (float) $_POST['min_transaksi'];
        $kuota  = (int) $_POST['kuota'];
        $valid  = $conn->real_escape_string($_POST['valid_until']);
        $desk   = $conn->real_escape_string($_POST['deskripsi']);

        $gambar = 'default_promo.png';
        if (!empty($_FILES["gambar"]["name"])) {
            $up = uploadPromo($_FILES["gambar"]);
            if ($up) $gambar = $up;
        }

        $query = "INSERT INTO promo (nama_promo, kode_promo, target, persentase, min_transaksi, kuota, valid_until, deskripsi, gambar) 
                  VALUES ('$nama', '$kode', '$target', '$persen', '$min', '$kuota', '$valid', '$desk', '$gambar')";

        if ($conn->query($query)) {
            setFlashMessage('success', 'Berhasil', 'Promo baru ditambahkan!');
        } else {
            setFlashMessage('error', 'Gagal', $conn->error);
        }
        header("Location: promo.php");
        exit;
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id_promo'];

        $oldData = $conn->query("SELECT gambar FROM promo WHERE id_promo=$id")->fetch_assoc();
        if ($oldData) {
            $oldFile = "../../assets/images/promo/" . $oldData['gambar'];
            if ($oldData['gambar'] != 'default_promo.png' && file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $conn->query("DELETE FROM promo WHERE id_promo = $id");
        setFlashMessage('success', 'Dihapus', 'Promo berhasil dihapus.');
        header("Location: promo.php");
        exit;
    }
}

$promos = $conn->query("SELECT * FROM promo ORDER BY valid_until DESC");
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Promo</title>
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
                        'neon': '0 0 10px rgba(127, 13, 242, 0.5)',
                        'neon-cyan': '0 0 10px rgba(0, 240, 255, 0.5)'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-dark-bg text-gray-200 font-sans antialiased h-screen flex overflow-hidden">

    <?php include '../../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative md:ml-64">
        <div class="flex-1 overflow-y-auto p-6 md:p-8 z-10 scroll-smooth">

            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Manajemen Promo</h2>
                    <p class="text-gray-400 mt-1 text-sm">Buat diskon menarik untuk Billing & F&B.</p>
                </div>
                <button type="button" onclick="openModal()" class="bg-primary hover:bg-primary/80 text-white px-5 py-2.5 rounded-xl font-bold shadow-neon transition-all flex items-center gap-2 hover:scale-105 active:scale-95">
                    <span class="material-symbols-outlined">confirmation_number</span> Tambah Promo
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($p = $promos->fetch_assoc()): ?>
                    <?php
                    $is_expired = ($p['valid_until'] < date('Y-m-d'));
                    $bg_class = $is_expired ? 'opacity-60 grayscale' : '';
                    $status_badge = $is_expired ? '<span class="bg-red-500/20 text-red-400 border border-red-500/30 text-[10px] font-bold px-2 py-1 rounded backdrop-blur-md">EXPIRED</span>' : '<span class="bg-green-500/20 text-green-400 border border-green-500/30 text-[10px] font-bold px-2 py-1 rounded backdrop-blur-md">ACTIVE</span>';
                    ?>
                    <div class="bg-dark-surface border border-dark-border rounded-2xl overflow-hidden group hover:border-primary/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg relative <?= $bg_class ?>">
                        <div class="h-40 bg-dark-bg relative overflow-hidden">
                            <?php if ($p['gambar'] != 'default_promo.png'): ?>
                                <img src="../../assets/images/promo/<?= $p['gambar'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-800 to-gray-900">
                                    <span class="material-symbols-outlined text-6xl text-gray-700 group-hover:text-primary transition-colors">local_activity</span>
                                </div>
                            <?php endif; ?>

                            <div class="absolute inset-0 bg-gradient-to-t from-dark-surface via-transparent to-transparent"></div>

                            <div class="absolute top-3 right-3"><?= $status_badge ?></div>
                            <div class="absolute bottom-3 left-3">
                                <span class="text-4xl font-black text-white drop-shadow-lg tracking-tighter"><?= $p['persentase'] ?>% <span class="text-sm font-medium text-gray-300 tracking-normal">OFF</span></span>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-white text-lg leading-tight"><?= $p['nama_promo'] ?></h3>
                            </div>
                            <p class="text-xs text-gray-400 mb-4 line-clamp-2 h-8"><?= $p['deskripsi'] ?></p>

                            <div class="bg-dark-bg border border-dashed border-dark-border rounded-xl p-3 mb-4 flex flex-col items-center justify-center group-hover:border-primary/30 transition-colors">
                                <span class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Kode Promo</span>
                                <code class="text-xl font-mono font-bold text-secondary tracking-wider"><?= $p['kode_promo'] ?></code>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-xs text-gray-300 mb-5">
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-gray-500 text-sm">category</span> <span class="uppercase"><?= $p['target'] ?></span></div>
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-gray-500 text-sm">payments</span> <span>Min. <?= number_format($p['min_transaksi'] / 1000) ?>k</span></div>
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-gray-500 text-sm">inventory_2</span> <span>Sisa: <?= $p['kuota'] ?></span></div>
                                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-gray-500 text-sm">event</span> <span class="<?= $is_expired ? 'text-red-400' : '' ?>"><?= date('d M', strtotime($p['valid_until'])) ?></span></div>
                            </div>

                            <form method="POST" onsubmit="return confirm('Hapus promo ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_promo" value="<?= $p['id_promo'] ?>">
                                <button class="w-full py-2.5 rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition-all font-bold text-xs border border-red-500/20 flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-sm">delete</span> Hapus Promo
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        </div>
    </main>

    <div id="modalPromo" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-dark-surface border border-dark-border rounded-2xl w-full max-w-lg p-6 relative shadow-2xl transform scale-100 max-h-[90vh] overflow-y-auto">
                <h3 class="text-xl font-bold text-white mb-6">Buat Promo Baru</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1">NAMA PROMO</label>
                            <input type="text" name="nama_promo" required class="w-full bg-dark-bg border border-dark-border rounded-lg px-3 py-2 text-white focus:border-primary outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1">KODE UNIK</label>
                            <input type="text" name="kode_promo" required class="w-full bg-dark-bg border border-dark-border rounded-lg px-3 py-2 text-white focus:border-primary outline-none uppercase" placeholder="CONTOH: MABAR50">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1">TARGET</label>
                            <select name="target" class="w-full bg-dark-bg border border-dark-border rounded-lg px-3 py-2 text-white focus:border-primary outline-none">
                                <option value="billing">Billing (PC)</option>
                                <option value="fnb">F&B (Makanan)</option>
                                <option value="all">Semua</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1">DISKON (%)</label>
                            <input type="number" name="persentase" min="1" max="100" required class="w-full bg-dark-bg border border-dark-border rounded-lg px-3 py-2 text-white focus:border-primary outline-none" placeholder="10">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1">KUOTA</label>
                            <input type="number" name="kuota" required class="w-full bg-dark-bg border border-dark-border rounded-lg px-3 py-2 text-white focus:border-primary outline-none" placeholder="100">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1">MIN. TRANSAKSI (Rp)</label>
                            <input type="number" name="min_transaksi" required class="w-full bg-dark-bg border border-dark-border rounded-lg px-3 py-2 text-white focus:border-primary outline-none" placeholder="50000">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1">BERLAKU SAMPAI</label>
                            <input type="date" name="valid_until" required class="w-full bg-dark-bg border border-dark-border rounded-lg px-3 py-2 text-white focus:border-primary outline-none">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-400 mb-1">DESKRIPSI</label>
                        <textarea name="deskripsi" class="w-full bg-dark-bg border border-dark-border rounded-lg px-3 py-2 text-white focus:border-primary outline-none h-20"></textarea>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-400 mb-1">BANNER (Opsional)</label>
                        <input type="file" name="gambar" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 cursor-pointer bg-dark-bg rounded-lg border border-dark-border">
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal()" class="flex-1 py-2 bg-dark-bg border border-dark-border text-gray-400 rounded-lg">Batal</button>
                        <button type="submit" class="flex-1 py-2 bg-primary text-white font-bold rounded-lg shadow-neon-purple">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalPromo').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modalPromo').classList.add('hidden');
        }
    </script>
    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>