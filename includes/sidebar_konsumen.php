<?php

$current_page = basename($_SERVER['PHP_SELF']);

function isActive($page_name, $current)
{
    $active_class = "bg-primary/20 text-white rounded-xl border border-primary/20 shadow-neon-purple transition-all";
    $inactive_class = "text-gray-400 hover:bg-dark-border hover:text-white rounded-xl transition-all group";

    return ($page_name == $current) ? $active_class : $inactive_class;
}

if (!isset($user) && isset($_SESSION['id_konsumen'])) {
    $uid_sidebar = $_SESSION['id_konsumen'];
    $query_sidebar = mysqli_query($conn, "SELECT * FROM konsumen WHERE id_konsumen = '$uid_sidebar'");
    if ($query_sidebar) {
        $user = mysqli_fetch_assoc($query_sidebar);
    }
}

$display_nama = $user['nama_lengkap'] ?? 'Konsumen';
$display_user = $user['username'] ?? 'user';
$display_inisial = strtoupper(substr($display_user, 0, 1));
?>

<style>
    @keyframes pageEnter {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.99);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    main {
        animation: pageEnter 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    }
</style>

<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-dark-surface border-r border-dark-border flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-50">

    <div class="h-20 flex items-center justify-center border-b border-dark-border">
        <h1 class="text-xl font-bold tracking-wider text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-3xl">sports_esports</span>
            WARNET<span class="text-primary">BAHAGIA</span>
        </h1>
    </div>

    <div class="p-6 flex flex-col items-center border-b border-dark-border bg-primary/5">
        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary to-secondary p-[2px] mb-3 shadow-neon-purple">
            <div class="w-full h-full rounded-full bg-dark-surface flex items-center justify-center">
                <span class="text-2xl font-bold text-white"><?= $display_inisial; ?></span>
            </div>
        </div>
        <h3 class="text-white font-bold text-center leading-tight mb-1"><?= $display_nama; ?></h3>
        <p class="text-xs text-secondary">@<?= $display_user; ?></p>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 px-4 space-y-2">
        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Main Menu</p>
        <a href="dashboard.php" class="flex items-center px-4 py-3 <?= isActive('dashboard.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 <?= ($current_page != 'dashboard.php') ? 'group-hover:text-white' : '' ?>">dashboard</span>
            Home
        </a>

        <a href="booking.php" class="flex items-center px-4 py-3 <?= isActive('booking.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 <?= ($current_page != 'booking.php') ? 'group-hover:text-secondary' : '' ?>">calendar_clock</span>
            Booking PC
        </a>

        <a href="fnb.php" class="flex items-center px-4 py-3 <?= isActive('fnb.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 <?= ($current_page != 'fnb.php') ? 'group-hover:text-yellow-400' : '' ?>">fastfood</span>
            Pesan Makan
        </a>

        <?php if ($current_page == 'keranjang.php'): ?>
            <script>
                document.querySelector('a[href="fnb.php"]').className = "<?= isActive('fnb.php', 'fnb.php') ?> flex items-center px-4 py-3";
            </script>
        <?php endif; ?>

        <a href="topup.php" class="flex items-center px-4 py-3 <?= isActive('topup.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 <?= ($current_page != 'topup.php') ? 'group-hover:text-green-400' : '' ?>">account_balance_wallet</span>
            Top Up Saldo
        </a>

        <a href="riwayat.php" class="flex items-center px-4 py-3 <?= isActive('riwayat.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 <?= ($current_page != 'riwayat.php') ? 'group-hover:text-blue-400' : '' ?>">history</span>
            Riwayat
        </a>
        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Support</p>
        <a href="bantuan.php" class="flex items-center px-4 py-3 <?= isActive('bantuan.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 <?= ($current_page != 'bantuan.php') ? 'group-hover:text-red-400' : '' ?>">support_agent</span>
            Bantuan
        </a>

    </nav>

    <div class="p-4">
        <a href="../../logout.php" class="flex items-center justify-center w-full py-3 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white transition-all font-bold text-sm">
            <span class="material-symbols-outlined mr-2 text-lg">logout</span>
            Logout
        </a>
    </div>
</aside>

<div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/70 z-40 hidden md:hidden backdrop-blur-sm"></div>