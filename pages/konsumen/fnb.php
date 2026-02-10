<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'konsumen') {
    header("Location: ../../index.php");
    exit;
}

$id_konsumen = $_SESSION['id_konsumen'];

// HANDLE TAMBAH KE KERANJANG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_cart') {
    $id_item = (int)$_POST['id_item'];
    $jumlah = (int)$_POST['jumlah'];
    $harga = (float)$_POST['harga'];
    $total_harga = $jumlah * $harga;

    // Cek stok 
    $cek_stok = $conn->query("SELECT stok FROM item_fnb WHERE id_item = $id_item")->fetch_assoc();
    if ($cek_stok['stok'] < $jumlah) {
        setFlashMessage('error', 'Stok Habis!', 'Stok tidak mencukupi untuk pesanan ini.');
        header("Location: fnb.php");
        exit;
    } else {

        $cek_cart = $conn->query("SELECT * FROM keranjang WHERE id_item = '$id_item' AND status = 'aktif' AND id_konsumen = '$id_konsumen'");

        if ($cek_cart->num_rows > 0) {
            $cart = $cek_cart->fetch_assoc();
            $new_jumlah = $cart['qty'] + $jumlah;
            $new_total = $new_jumlah * $harga;
            $conn->query("UPDATE keranjang SET qty = '$new_jumlah', total_harga = '$new_total' WHERE id_keranjang = '{$cart['id_keranjang']}'");
        } else {
            $conn->query("INSERT INTO keranjang (id_item, qty, total_harga, status, id_konsumen) VALUES ('$id_item', '$jumlah', '$total_harga', 'aktif', '$id_konsumen')");
        }
        setFlashMessage('success', 'Berhasil!', 'Item berhasil masuk keranjang!');
        header("Location: fnb.php");
        exit;
    }
}

// FILTER KATEGORI
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'Semua';
$where = "";
if ($kategori != 'Semua') {
    $where = "WHERE kategori = '$kategori'";
}

$items = $conn->query("SELECT * FROM item_fnb $where ORDER BY nama_item ASC");

// HITUNG JUMLAH KERANJANG 
$cart_count = $conn->query("SELECT SUM(qty) as total FROM keranjang WHERE status = 'aktif' AND id_konsumen = '$id_konsumen'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F&B - Warnet Bahagia</title>
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
        <a href="keranjang.php" class="absolute bottom-8 right-8 z-50 bg-secondary hover:bg-cyan-400 text-black p-4 rounded-full shadow-lg hover:scale-110 transition-transform flex items-center justify-center group">
            <span class="material-symbols-outlined text-3xl">shopping_cart</span>
            <?php if ($cart_count > 0): ?>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold w-6 h-6 flex items-center justify-center rounded-full animate-bounce"><?= $cart_count ?></span>
            <?php endif; ?>
        </a>

        <header class="md:hidden h-16 bg-dark-surface/80 backdrop-blur-md border-b border-dark-border flex items-center justify-between px-4 z-20 sticky top-0">
            <span class="text-lg font-bold text-white">Warnet<span class="text-primary">Bahagia</span></span>
            <button onclick="toggleSidebar()" class="text-white p-2"><span class="material-symbols-outlined">menu</span></button>
        </header>

        <div class="flex-1 overflow-y-auto p-6 z-10">

            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Kantin Gaming</h2>
                    <p class="text-gray-400 mt-1 text-sm">Isi tenaga buat lanjut push rank!</p>
                </div>

                <div class="flex gap-2 overflow-x-auto pb-2 w-full md:w-auto">
                    <?php
                    $cats = ['Semua', 'Makanan', 'Minuman', 'Snack'];
                    foreach ($cats as $c):
                        $active = ($kategori == $c) ? 'bg-primary text-white shadow-neon-purple' : 'bg-dark-surface text-gray-400 hover:bg-dark-border';
                    ?>
                        <a href="?kategori=<?= $c ?>" class="px-4 py-2 rounded-full text-sm font-bold transition-all whitespace-nowrap <?= $active ?>"><?= $c ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <?php while ($item = $items->fetch_assoc()): ?>
                    <?php
                    $folder = "../../assets/images/fnb/";
                    $gambar = $item['gambar'];
                    $path   = $folder . $gambar;

                    if (!empty($gambar) && $gambar != 'default.png' && file_exists($path)) {
                        $show_image = true;
                        $image_src = $path;
                    } else {
                        $show_image = false;
                    }
                    ?>

                    <div class="bg-dark-surface border border-dark-border rounded-2xl overflow-hidden group hover:border-primary/50 transition-all flex flex-col h-full shadow-lg">

                        <div class="h-40 bg-dark-bg flex items-center justify-center relative overflow-hidden border-b border-dark-border">
                            <?php if ($show_image): ?>
                                <img src="<?= $image_src; ?>" alt="<?= $item['nama_item']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <span class="material-symbols-outlined text-6xl text-gray-700 group-hover:text-primary transition-colors duration-300">restaurant</span>
                            <?php endif; ?>

                            <span class="absolute top-2 right-2 bg-black/70 backdrop-blur-md px-2 py-1 rounded-lg text-[10px] text-white font-bold border border-white/10 shadow-sm">
                                Stok: <?= $item['stok'] ?>
                            </span>
                        </div>

                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="font-bold text-white mb-1 line-clamp-1 leading-tight"><?= $item['nama_item'] ?></h3>
                            <p class="text-[10px] text-gray-400 mb-3 bg-dark-bg inline-block px-2 py-0.5 rounded w-fit border border-dark-border uppercase tracking-wider"><?= $item['kategori'] ?></p>

                            <div class="mt-auto flex justify-between items-center">
                                <span class="text-secondary font-bold text-sm">Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                                <button onclick="openOrderModal(<?= htmlspecialchars(json_encode($item)) ?>)"
                                    class="w-8 h-8 rounded-lg bg-primary hover:bg-primary/80 flex items-center justify-center text-white transition-all shadow-lg hover:shadow-primary/50 group-hover:scale-105 active:scale-95">
                                    <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        </div>
    </main>

    <div id="orderModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-dark-surface border border-dark-border rounded-2xl w-full max-w-sm p-6 relative shadow-2xl transform scale-100">
                <h3 class="text-xl font-bold text-white mb-1" id="modalItemName">Nama Item</h3>
                <p class="text-gray-400 text-sm mb-6" id="modalItemPrice">Rp 0</p>

                <form method="POST">
                    <input type="hidden" name="action" value="add_cart">
                    <input type="hidden" name="id_item" id="modalItemId">
                    <input type="hidden" name="harga" id="modalItemPriceVal">

                    <div class="flex items-center justify-between bg-dark-bg p-2 rounded-xl border border-dark-border mb-6">
                        <button type="button" onclick="adjustQty(-1)" class="w-10 h-10 rounded-lg bg-dark-surface hover:bg-dark-border text-white flex items-center justify-center transition-all"><span class="material-symbols-outlined">remove</span></button>
                        <input type="number" name="jumlah" id="inputQty" value="1" min="1" class="bg-transparent text-center text-white font-bold w-16 outline-none appearance-none" readonly>
                        <button type="button" onclick="adjustQty(1)" class="w-10 h-10 rounded-lg bg-dark-surface hover:bg-dark-border text-white flex items-center justify-center transition-all"><span class="material-symbols-outlined">add</span></button>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal()" class="flex-1 py-3 rounded-xl bg-dark-bg border border-dark-border text-gray-400 hover:text-white font-bold">Batal</button>
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-primary hover:bg-primary/80 text-white font-bold shadow-neon-purple">Masuk Keranjang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('orderModal');
        const itemName = document.getElementById('modalItemName');
        const itemPrice = document.getElementById('modalItemPrice');
        const itemId = document.getElementById('modalItemId');
        const itemPriceVal = document.getElementById('modalItemPriceVal');
        const inputQty = document.getElementById('inputQty');
        let maxStok = 0;

        function openOrderModal(item) {
            itemName.innerText = item.nama_item;
            itemPrice.innerText = "Rp " + parseInt(item.harga).toLocaleString('id-ID');
            itemId.value = item.id_item;
            itemPriceVal.value = item.harga;
            maxStok = parseInt(item.stok);
            inputQty.value = 1;
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function adjustQty(change) {
            let val = parseInt(inputQty.value) + change;
            if (val < 1) val = 1;
            if (val > maxStok) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Terbatas',
                    text: 'Maksimal stok tersedia: ' + maxStok,
                    confirmButtonColor: '#7f0df2',
                    background: '#1e1628',
                    color: '#fff'
                });
                val = maxStok;
            }
            inputQty.value = val;
        }
    </script>
    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>