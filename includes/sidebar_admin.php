<?php

$current_page = basename($_SERVER['PHP_SELF']);

function isActive($page_name, $current)
{
    $active_class = "bg-primary/20 text-white shadow-neon rounded-xl border border-primary/20";
    $inactive_class = "text-gray-400 hover:bg-dark-border hover:text-white rounded-xl";

    return ($page_name == $current) ? $active_class : $inactive_class;
}
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
            WARNET<span class="text-primary">ADMIN</span>
        </h1>
    </div>

    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Main Menu</p>

        <a href="dashboard.php" class="flex items-center px-4 py-3 transition-all group <?= isActive('dashboard.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 group-hover:text-primary">dashboard</span> Dashboard
        </a>
        <a href="billing.php" class="flex items-center px-4 py-3 transition-all group <?= isActive('billing.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 group-hover:text-primary">payments</span> Billing & PC
        </a>
        <a href="pesanan.php" class="flex items-center px-4 py-3 transition-all group <?= isActive('pesanan.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 group-hover:text-primary">shopping_cart</span> Pesanan F&B
        </a>
        <a href="fnb.php" class="flex items-center px-4 py-3 transition-all group <?= isActive('fnb.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 group-hover:text-primary">fastfood</span> Menu F&B
        </a>
        <a href="promo.php" class="flex items-center px-4 py-3 transition-all group <?= isActive('promo.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 group-hover:text-primary">local_offer</span> Promo & Diskon
        </a>
        <a href="konsumen.php" class="flex items-center px-4 py-3 transition-all group <?= isActive('konsumen.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 group-hover:text-primary">group</span> Data Konsumen
        </a>
        <a href="laporan.php" class="flex items-center px-4 py-3 transition-all group <?= isActive('laporan.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 group-hover:text-primary">assessment</span> Laporan
        </a>
        <a href="pc.php" class="flex items-center px-4 py-3 transition-all group <?= isActive('pc.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 group-hover:text-primary">computer</span> Data PC
        </a>

        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Support</p>
        <a href="bantuan.php" class="flex items-center px-4 py-3 transition-all group <?= isActive('bantuan.php', $current_page) ?>">
            <span class="material-symbols-outlined mr-3 group-hover:text-primary">support_agent</span> Bantuan Teknis
        </a>
    </nav>

    <div class="p-4 border-t border-dark-border">
        <a href="../../logout.php" class="flex items-center px-4 py-3 text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-xl transition-all">
            <span class="material-symbols-outlined mr-3">logout</span> Logout
        </a>
    </div>
</aside>

<div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden backdrop-blur-sm"></div>