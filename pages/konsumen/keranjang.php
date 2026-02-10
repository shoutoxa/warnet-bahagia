<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'konsumen') {
    header("Location: ../../index.php");
    exit;
}

$id_konsumen = $_SESSION['id_konsumen'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'delete') {
        $id_keranjang = $_POST['id_keranjang'];
        $conn->query("DELETE FROM keranjang WHERE id_keranjang = '$id_keranjang'");
    }

    if ($_POST['action'] === 'checkout') {
        $cart = $conn->query("SELECT * FROM keranjang WHERE status = 'aktif' AND id_konsumen = '$id_konsumen'");
        $total_bayar = 0;
        $items_to_process = [];

        while ($item = $cart->fetch_assoc()) {
            $total_bayar += $item['total_harga'];
            $items_to_process[] = $item;
        }

        $saldo = $conn->query("SELECT saldo FROM konsumen WHERE id_konsumen = '$id_konsumen'")->fetch_assoc()['saldo'];

        if ($saldo < $total_bayar) {
            setFlashMessage('error', 'Gagal Checkout!', 'Saldo tidak cukup! Total: Rp ' . number_format($total_bayar));
            header("Location: keranjang.php");
            exit;
        } else {
            $new_saldo = $saldo - $total_bayar;
            $conn->query("UPDATE konsumen SET saldo = '$new_saldo' WHERE id_konsumen = '$id_konsumen'");

            $waktu = date('Y-m-d H:i:s');
            $conn->query("INSERT INTO pesanan (id_konsumen, waktu_pesanan, total_pesanan, status_pesanan) VALUES ('$id_konsumen', '$waktu', '$total_bayar', 'pending')");
            $id_pesanan_baru = $conn->insert_id;

            foreach ($items_to_process as $item) {
                $id_item = $item['id_item'];
                $qty = $item['qty'];
                $harga_satuan = $item['harga']; 
                $subtotal = $item['total_harga'];

                $conn->query("INSERT INTO detail_pesanan (id_pesanan, id_item, qty, harga_satuan, subtotal) 
                              VALUES ('$id_pesanan_baru', '$id_item', '$qty', '$harga_satuan', '$subtotal')");

                $conn->query("UPDATE item_fnb SET stok = stok - {$item['qty']} WHERE id_item = {$item['id_item']}");

                $conn->query("DELETE FROM keranjang WHERE id_keranjang = {$item['id_keranjang']}");
            }

            setFlashMessage('success', 'Pesanan Diterima!', 'Mohon tunggu pesanan diantar ke meja Anda.');
            header("Location: pesanan.php");
            exit;
        }
    }
}

$cart_items = $conn->query("
    SELECT k.*, i.nama_item, i.harga, i.kategori 
    FROM keranjang k 
    JOIN item_fnb i ON k.id_item = i.id_item 
    WHERE k.status = 'aktif' AND k.id_konsumen = '$id_konsumen'
");

$total_all = 0;
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Warnet Bahagia</title>
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
</head>

<body class="bg-dark-bg text-gray-200 font-sans antialiased h-screen flex overflow-hidden">

    <?php include '../../includes/sidebar_konsumen.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative md:ml-64">
        <header class="md:hidden h-16 bg-dark-surface/80 backdrop-blur-md border-b border-dark-border flex items-center justify-between px-4 z-20 sticky top-0">
            <span class="text-lg font-bold text-white">Warnet<span class="text-primary">Bahagia</span></span>
            <button onclick="toggleSidebar()" class="text-white p-2"><span class="material-symbols-outlined">menu</span></button>
        </header>

        <div class="flex-1 overflow-y-auto p-6 z-10">
            <div class="mb-8 flex items-center gap-4">
                <a href="fnb.php" class="w-10 h-10 rounded-full bg-dark-surface border border-dark-border flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h2 class="text-3xl font-bold text-white">Keranjang Saya</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <?php if ($cart_items->num_rows > 0): ?>
                        <?php while ($row = $cart_items->fetch_assoc()):
                            $total_all += $row['total_harga'];
                        ?>
                            <div class="bg-dark-surface border border-dark-border p-4 rounded-2xl flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-lg bg-dark-bg flex items-center justify-center text-gray-500">
                                        <span class="material-symbols-outlined">restaurant</span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white"><?= $row['nama_item']; ?></h4>
                                        <p class="text-sm text-gray-400"><?= $row['qty']; ?> x Rp <?= number_format($row['harga']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="font-bold text-secondary">Rp <?= number_format($row['total_harga']); ?></span>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id_keranjang" value="<?= $row['id_keranjang']; ?>">
                                        <button type="button" onclick="confirmDeleteCart(this.form)" class="text-red-500 hover:text-red-400 p-2"><span class="material-symbols-outlined">delete</span></button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-12 border-2 border-dashed border-dark-border rounded-2xl text-gray-500">
                            <span class="material-symbols-outlined text-4xl mb-2">production_quantity_limits</span>
                            <p>Keranjang kosong. Yuk pesan makan!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-dark-surface border border-dark-border p-6 rounded-2xl sticky top-6">
                        <h3 class="text-xl font-bold text-white mb-6">Ringkasan Pesanan</h3>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-400">Total Harga</span>
                            <span class="text-2xl font-bold text-white">Rp <?= number_format($total_all, 0, ',', '.'); ?></span>
                        </div>
                        <div class="h-px bg-dark-border w-full mb-6"></div>

                        <?php if ($total_all > 0): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="checkout">
                                <button type="button" onclick="confirmCheckout(this.form)"
                                    class="w-full py-4 bg-green-600 hover:bg-green-500 text-white font-bold rounded-xl shadow-lg hover:shadow-green-500/20 transition-all flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined">payments</span>
                                    Bayar Sekarang
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="w-full py-4 bg-dark-border text-gray-500 font-bold rounded-xl cursor-not-allowed">Keranjang Kosong</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        function confirmCheckout(form) {
            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                text: "Saldo akan terpotong sesuai total belanja.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Bayar Sekarang',
                cancelButtonText: 'Batal',
                background: '#1e1628',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        }

        function confirmDeleteCart(form) {
            Swal.fire({
                title: 'Hapus Item?',
                text: "Item akan dihapus dari keranjang.",
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
                    form.submit();
                }
            })
        }
    </script>
    <?php include '../../includes/footer_scripts.php'; ?>
</body>

</html>