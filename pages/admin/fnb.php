<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Cek Session
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit;
}

// --- FUNGSI UPLOAD GAMBAR ---
function uploadGambar($file)
{
    $targetDir = "../../assets/images/fnb/";

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // TAMBAH ITEM
    if ($action === 'add') {
        $nama     = $_POST['nama_item'];
        $harga    = (float) $_POST['harga'];
        $stok     = (int) $_POST['stok'];
        $kategori = $_POST['kategori'];

        $gambar = 'default.png';
        if (!empty($_FILES["gambar"]["name"])) {
            $upload = uploadGambar($_FILES["gambar"]);
            if ($upload) $gambar = $upload;
        }

        $query = "INSERT INTO item_fnb (nama_item, harga, stok, kategori, gambar) VALUES ('$nama', $harga, $stok, '$kategori', '$gambar')";

        if ($conn->query($query)) {
            setFlashMessage('success', 'Berhasil', 'Item berhasil ditambahkan!');
            header("Location: fnb.php");
            exit;
        } else {
            setFlashMessage('error', 'Gagal', 'Gagal: ' . $conn->error);
        }
    }

    // UPDATE ITEM 
    elseif ($action === 'update') {
        $id       = (int) $_POST['id_item'];
        $nama     = $_POST['nama_item'];
        $harga    = (float) $_POST['harga'];
        $stok     = (int) $_POST['stok'];
        $kategori = $_POST['kategori'];

        $imgQuery = "";
        if (!empty($_FILES["gambar"]["name"])) {
            $upload = uploadGambar($_FILES["gambar"]);

            if ($upload) {
                $oldData = $conn->query("SELECT gambar FROM item_fnb WHERE id_item=$id")->fetch_assoc();
                $oldFile = "../../assets/images/fnb/" . $oldData['gambar'];

                if ($oldData['gambar'] != 'default.png' && file_exists($oldFile)) {
                    unlink($oldFile); 
                }

                $imgQuery = ", gambar='$upload'";
            }
        }

        $query = "UPDATE item_fnb SET nama_item='$nama', harga=$harga, stok=$stok, kategori='$kategori' $imgQuery WHERE id_item=$id";

        if ($conn->query($query)) {
            setFlashMessage('success', 'Berhasil', 'Item diperbarui!');
            header("Location: fnb.php");
            exit;
        } else {
            setFlashMessage('error', 'Gagal', 'Gagal update: ' . $conn->error);
        }
    }

    // HAPUS ITEM
    elseif ($action === 'delete') {
        $id = (int) $_POST['id_item'];

        $oldData = $conn->query("SELECT gambar FROM item_fnb WHERE id_item=$id")->fetch_assoc();
        $oldFile = "../../assets/images/fnb/" . $oldData['gambar'];

        if ($oldData['gambar'] != 'default.png' && file_exists($oldFile)) {
            unlink($oldFile);
        }

        $query = "DELETE FROM item_fnb WHERE id_item=$id";
        if ($conn->query($query)) {
            setFlashMessage('success', 'Berhasil', 'Item dihapus!');
            header("Location: fnb.php");
            exit;
        }
    }
}

$item_list = $conn->query("SELECT * FROM item_fnb ORDER BY id_item DESC");
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manajemen F&B</title>
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
</head>

<body class="bg-dark-bg text-gray-200 font-sans antialiased h-screen flex overflow-hidden">

    <?php include '../../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative md:ml-64">
        <div class="flex-1 overflow-y-auto p-6 md:p-8 z-10 scroll-smooth">

            <div class="flex justify-between items-end mb-8">
                <h2 class="text-3xl font-bold text-white">Manajemen F&B</h2>
                <button onclick="openModal('add')" class="bg-primary hover:bg-primary/80 text-white px-5 py-2.5 rounded-xl font-bold flex items-center gap-2 transition-all shadow-neon">
                    <span class="material-symbols-outlined">add</span> Tambah Menu
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php while ($i = $item_list->fetch_assoc()): ?>
                    <div class="bg-dark-surface border border-dark-border rounded-2xl p-4 relative group hover:border-primary/50 transition-all flex flex-col h-full">

                        <div class="h-40 w-full bg-dark-bg rounded-xl mb-4 overflow-hidden relative border border-dark-border">
                            <?php
                            $imgSrc = "../../assets/images/fnb/" . $i['gambar'];
                            if (!file_exists($imgSrc)) $imgSrc = "https://via.placeholder.com/300x200?text=No+Image";
                            ?>
                            <img src="<?= $imgSrc; ?>" alt="<?= $i['nama_item']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <span class="absolute top-2 right-2 text-[10px] px-2 py-1 rounded bg-black/60 text-white font-bold backdrop-blur-sm border border-white/10"><?= $i['kategori']; ?></span>
                        </div>

                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white leading-tight mb-1"><?= $i['nama_item']; ?></h3>
                            <div class="flex justify-between items-end mt-2">
                                <div>
                                    <p class="text-xs text-gray-400">Harga</p>
                                    <p class="text-lg font-bold text-neon-cyan">Rp <?= number_format($i['harga'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-400">Stok</p>
                                    <p class="text-sm font-bold text-white"><?= $i['stok']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 mt-4 pt-4 border-t border-dark-border">
                            <button onclick='openModal("edit", <?= json_encode($i); ?>)' class="flex-1 py-2 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white transition-all font-bold text-xs border border-blue-500/20">EDIT</button>
                            <button onclick="hapusItem(<?= $i['id_item']; ?>)" class="flex-1 py-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition-all font-bold text-xs border border-red-500/20">HAPUS</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <div id="modalForm" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-dark-surface border border-dark-border rounded-2xl w-full max-w-md p-6 relative shadow-2xl transform scale-100 max-h-[90vh] overflow-y-auto">

                <h3 id="modalTitle" class="text-xl font-bold text-white mb-6">Tambah Menu Baru</h3>

                <form method="POST" id="formFnb" enctype="multipart/form-data">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id_item" id="itemId">

                    <div class="space-y-4">

                        <div id="previewContainer" class="hidden text-center mb-4">
                            <label class="block text-xs font-bold text-gray-400 mb-2">GAMBAR SAAT INI</label>
                            <img id="previewImage" src="" class="w-32 h-32 object-cover rounded-xl border border-primary mx-auto">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">FOTO PRODUK</label>
                            <input type="file" name="gambar" accept="image/*" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 cursor-pointer bg-dark-bg rounded-xl border border-dark-border">
                            <p class="text-[10px] text-gray-500 mt-1 ml-1">*Biarkan kosong jika tidak ingin mengubah gambar.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">NAMA ITEM</label>
                            <input type="text" name="nama_item" id="namaItem" required class="w-full bg-dark-bg border border-dark-border text-white text-sm rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-gray-600">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">HARGA (Rp)</label>
                                <input type="number" name="harga" id="hargaItem" required class="w-full bg-dark-bg border border-dark-border text-white text-sm rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-gray-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">STOK</label>
                                <input type="number" name="stok" id="stokItem" required class="w-full bg-dark-bg border border-dark-border text-white text-sm rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-gray-600">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">KATEGORI</label>
                            <div class="relative">
                                <select name="kategori" id="kategoriItem" required class="w-full bg-dark-bg border border-dark-border text-white text-sm rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none cursor-pointer">
                                    <option value="Makanan">Makanan</option>
                                    <option value="Minuman">Minuman</option>
                                    <option value="Snack">Snack</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-3 text-gray-500 pointer-events-none">expand_more</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-8">
                        <button type="button" onclick="closeModal()" class="flex-1 py-3 rounded-xl bg-dark-bg border border-dark-border text-gray-400 hover:text-white font-bold transition-all">Batal</button>
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-primary hover:bg-primary/80 text-white font-bold shadow-neon transition-all">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_item" id="deleteId">
    </form>

    <script>
        // Modal Logic
        const modal = document.getElementById('modalForm');
        const formTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const itemId = document.getElementById('itemId');
        const namaInput = document.getElementById('namaItem');
        const hargaInput = document.getElementById('hargaItem');
        const stokInput = document.getElementById('stokItem');
        const kategoriInput = document.getElementById('kategoriItem');

        // Element Preview
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');

        function openModal(mode, data = null) {
            modal.classList.remove('hidden');

            if (mode === 'edit' && data) {
                formTitle.innerText = 'Edit Menu F&B';
                formAction.value = 'update';
                itemId.value = data.id_item;
                namaInput.value = data.nama_item;
                hargaInput.value = data.harga;
                stokInput.value = data.stok;
                kategoriInput.value = data.kategori;

                previewContainer.classList.remove('hidden');
                previewImage.src = "../../assets/images/fnb/" + data.gambar;
            } else {
                formTitle.innerText = 'Tambah Menu Baru';
                formAction.value = 'add';
                document.getElementById('formFnb').reset();

                previewContainer.classList.add('hidden');
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function hapusItem(id) {
            Swal.fire({
                title: 'Hapus Item?',
                text: "Item menu akan dihapus permanen!",
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
                    document.getElementById('deleteId').value = id;
                    document.getElementById('deleteForm').submit();
                }
            })
        }
    </script>
    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>