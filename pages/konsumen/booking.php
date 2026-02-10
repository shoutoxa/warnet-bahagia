<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Cek Login
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'konsumen') {
    header("Location: ../../index.php");
    exit;
}

$id_konsumen = $_SESSION['id_konsumen'];

$query_user = mysqli_query($conn, "SELECT * FROM konsumen WHERE id_konsumen = '$id_konsumen'");
$user = mysqli_fetch_assoc($query_user);

$query_active = mysqli_query($conn, "
    SELECT p.*, t.waktu_mulai, t.durasi_jam 
    FROM pc p
    JOIN transaksi_pc t ON p.id_pc = t.id_pc
    WHERE p.id_konsumen = '$id_konsumen' AND p.status_pc = 'digunakan'
    AND t.status = 'berjalan'
    LIMIT 1
");
$active_session = mysqli_fetch_assoc($query_active);

if (isset($_POST['action']) && $_POST['action'] === 'check_promo') {
    header('Content-Type: application/json');

    $kode = mysqli_real_escape_string($conn, $_POST['kode_promo']);
    $durasi = (int)$_POST['durasi'];

    $harga_per_jam = (int) getSetting('harga_per_jam');
    $total_bayar = $durasi * $harga_per_jam;

    $today = date('Y-m-d');

    $q_promo = $conn->query("SELECT * FROM promo WHERE kode_promo = '$kode' AND target IN ('billing', 'all') AND valid_until >= '$today' AND kuota > 0");

    if ($q_promo && $q_promo->num_rows > 0) {
        $data = $q_promo->fetch_assoc();
        if ($total_bayar >= $data['min_transaksi']) {
            $diskon = ($total_bayar * $data['persentase']) / 100;
            echo json_encode(['status' => 'success', 'diskon' => $diskon, 'persen' => $data['persentase'], 'total_awal' => $total_bayar, 'total_akhir' => $total_bayar - $diskon]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Min. transaksi Rp ' . number_format($data['min_transaksi'])]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kode promo tidak valid atau habis']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- PROSES BOOKING ---
    if (isset($_POST['action']) && $_POST['action'] === 'book') {
        if ($active_session) {
            setFlashMessage('error', 'Gagal!', 'Anda sedang menggunakan PC lain. Selesaikan dulu sesi tersebut.');
            header("Location: booking.php");
            exit;
        }

        $pc_id  = (int)$_POST['pc_id'];
        $durasi = (int)$_POST['durasi'];

        $harga_per_jam = (int) getSetting('harga_per_jam');
        $total_bayar   = $durasi * $harga_per_jam;

        if ($user['saldo'] < $total_bayar) {
            setFlashMessage('error', 'Saldo Kurang!', 'Saldo tidak cukup! Total: Rp ' . number_format($total_bayar));
            header("Location: booking.php");
            exit;
        }

        $cek_pc = mysqli_query($conn, "SELECT status_pc FROM pc WHERE id_pc = '$pc_id'")->fetch_assoc();
        if ($cek_pc['status_pc'] != 'tersedia') {
            setFlashMessage('error', 'Gagal!', 'Maaf, PC ini baru saja digunakan orang lain!');
            header("Location: booking.php");
            exit;
        }

        // LOGIKA PROMO BARU
        $diskon = 0;
        $id_promo_pakai = "NULL"; 

        if (!empty($_POST['kode_promo'])) {
            $kode = $_POST['kode_promo'];
            $today = date('Y-m-d');

            $q_promo = $conn->query("SELECT * FROM promo WHERE kode_promo = '$kode' AND target IN ('billing', 'all') AND valid_until >= '$today' AND kuota > 0");

            if ($q_promo->num_rows > 0) {
                $data_promo = $q_promo->fetch_assoc();

                // Cek Min Transaksi
                if ($total_bayar >= $data_promo['min_transaksi']) {
                    $diskon = ($total_bayar * $data_promo['persentase']) / 100;
                    $id_promo_pakai = $data_promo['id_promo'];


                    $conn->query("UPDATE promo SET kuota = kuota - 1 WHERE id_promo = {$data_promo['id_promo']}");
                } else {
                }
            }
        }

        $total_akhir = $total_bayar - $diskon;

        $sisa_saldo = $user['saldo'] - $total_akhir; 
        mysqli_query($conn, "UPDATE konsumen SET saldo = '$sisa_saldo' WHERE id_konsumen = '$id_konsumen'");

        $waktu_mulai = date('Y-m-d H:i:s');

        $query_transaksi = "INSERT INTO transaksi_pc (id_konsumen, id_pc, waktu_mulai, durasi_jam, total_bayar, status) 
                            VALUES ('$id_konsumen', '$pc_id', '$waktu_mulai', '$durasi', '$total_akhir', 'berjalan')";

        if (!mysqli_query($conn, $query_transaksi)) {
            die("Error: " . mysqli_error($conn));
        }

        mysqli_query($conn, "UPDATE pc SET status_pc = 'digunakan', id_konsumen = '$id_konsumen' WHERE id_pc = '$pc_id'");

        setFlashMessage('success', 'Berhasil!', 'Booking Berhasil! Selamat Bermain.');
        header("Location: dashboard.php");
        exit;
    }

    // --- PROSES STOP (LOGOUT PC) ---
    if (isset($_POST['action']) && $_POST['action'] === 'stop') {
        $id_pc      = $_POST['id_pc'];
        $waktu_selesai = date('Y-m-d H:i:s');

        mysqli_query($conn, "UPDATE transaksi_pc SET waktu_selesai = '$waktu_selesai', status = 'selesai' WHERE id_pc = '$id_pc' AND status = 'berjalan'");

        mysqli_query($conn, "UPDATE pc SET status_pc = 'tersedia', id_konsumen = NULL WHERE id_pc = '$id_pc'");

        setFlashMessage('success', 'Sesi Berakhir', 'Terima kasih telah bermain!');
        header("Location: booking.php");
        exit;
    }
}

$pc_list = mysqli_query($conn, "SELECT * FROM pc ORDER BY id_pc ASC");
?>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking PC - Warnet Bahagia</title>

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
                    <h2 class="text-3xl font-bold text-white tracking-tight">Pilih Unit PC</h2>
                    <p class="text-gray-400 mt-1 text-sm">Saldo Anda: <span class="text-green-400 font-bold">Rp <?= number_format($user['saldo'], 0, ',', '.'); ?></span></p>
                </div>

                <div class="flex gap-2">
                    <span class="px-3 py-1 bg-dark-surface border border-green-500/30 text-green-400 rounded-full text-xs font-bold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Ready
                    </span>
                    <span class="px-3 py-1 bg-dark-surface border border-dark-border text-gray-400 rounded-full text-xs font-bold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-gray-500"></span> Digunakan
                    </span>
                </div>
            </div>

            <?php if ($active_session): ?>
                <?php

                $start_time = strtotime($active_session['waktu_mulai']);
                $end_time = $start_time + ($active_session['durasi_jam'] * 3600);
                $remaining_seconds = $end_time - time();
                if ($remaining_seconds < 0) $remaining_seconds = 0;
                ?>
                <div class="bg-gradient-to-r from-primary/20 to-dark-surface border border-primary/50 p-6 md:p-8 rounded-3xl mb-10 shadow-neon-purple relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="absolute right-0 top-0 opacity-10 p-4 pointer-events-none">
                        <span class="material-symbols-outlined text-9xl text-white">timer</span>
                    </div>

                    <div class="relative z-10 flex-1 w-full md:w-auto">
                        <h3 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse shadow-[0_0_10px_#22c55e]"></span>
                            PC-<?= $active_session['id_pc']; ?>
                        </h3>
                        <p class="text-gray-300 text-sm mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-secondary">sports_esports</span>
                            Status: <span class="font-mono text-secondary font-bold tracking-wide">PLAYING</span>
                        </p>

                        <form method="POST">
                            <input type="hidden" name="action" value="stop">
                            <input type="hidden" name="id_pc" value="<?= $active_session['id_pc']; ?>">
                            <button type="button" onclick="confirmStop(this.form)"
                                class="px-6 py-3 bg-red-600/80 hover:bg-red-500 text-white font-bold rounded-xl shadow-lg hover:shadow-red-500/30 transition-all flex items-center gap-2 backdrop-blur-sm border border-red-500/30">
                                <span class="material-symbols-outlined">power_settings_new</span>
                                Stop Sesi
                            </button>
                        </form>
                    </div>

                    <div class="relative z-10 text-center md:text-right w-full md:w-auto bg-dark-bg/30 md:bg-transparent p-4 md:p-0 rounded-2xl border border-white/5 md:border-none">
                        <p class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-1">Sisa Waktu</p>
                        <div id="countdown" class="font-mono text-5xl md:text-6xl font-black text-white tracking-tighter drop-shadow-[0_0_15px_rgba(255,255,255,0.3)]">
                            --:--:--
                        </div>
                    </div>
                </div>

                <script>
                    let timeLeft = <?= $remaining_seconds; ?>;
                    const countdownEl = document.getElementById('countdown');

                    function updateTimer() {
                        if (timeLeft <= 0) {
                            countdownEl.innerText = "Waktu Habis";
                            return;
                        }

                        const hours = Math.floor(timeLeft / 3600);
                        const minutes = Math.floor((timeLeft % 3600) / 60);
                        const seconds = timeLeft % 60;

                        const hDisplay = hours < 10 ? "0" + hours : hours;
                        const mDisplay = minutes < 10 ? "0" + minutes : minutes;
                        const sDisplay = seconds < 10 ? "0" + seconds : seconds;

                        countdownEl.innerText = `${hDisplay}:${mDisplay}:${sDisplay}`;
                        timeLeft--;
                    }

                    setInterval(updateTimer, 1000);
                    updateTimer();
                </script>
            <?php endif; ?>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <?php while ($pc = mysqli_fetch_assoc($pc_list)): ?>
                    <?php

                    $status_pc = $pc['status_pc']; 
                    $is_mine = ($active_session && $active_session['id_pc'] == $pc['id_pc']);

                    $border = 'border-dark-border bg-dark-surface opacity-50';
                    $text = 'text-gray-600';
                    $icon = 'lock';
                    $label = 'BUSY';
                    $cursor = 'cursor-not-allowed';
                    $onclick = '';

                    if ($status_pc == 'tersedia') {
                        $border = 'border-dark-border bg-dark-surface hover:border-green-500/50 hover:shadow-neon-cyan transition-all opacity-100';
                        $text = 'text-gray-400 group-hover:text-green-400';
                        $icon = 'desktop_windows';
                        $label = 'READY';
                        $cursor = 'cursor-pointer';
                        if (!$active_session) {
                            $onclick = 'onclick="openModal(' . $pc['id_pc'] . ')"';
                        } else {
                            $cursor = 'cursor-not-allowed opacity-50';
                        }
                    }

                    if ($is_mine) {
                        $border = 'border-primary shadow-neon-purple bg-primary/10 opacity-100';
                        $text = 'text-primary animate-pulse';
                        $icon = 'sports_esports';
                        $label = 'MY PC';
                        $cursor = 'cursor-default';
                        $onclick = '';
                    }

                    if ($status_pc == 'rusak') {
                        $border = 'border-red-900/50 bg-red-900/10 opacity-60';
                        $text = 'text-red-600';
                        $icon = 'build';
                        $label = 'RUSAK';
                        $cursor = 'cursor-not-allowed';
                        $onclick = '';
                    }
                    ?>

                    <div <?= $onclick; ?> class="group relative p-4 rounded-2xl border-2 <?= $border; ?> <?= $cursor; ?> flex flex-col items-center justify-center gap-3 h-40 transition-all duration-300">

                        <span class="material-symbols-outlined text-4xl <?= $text; ?> transition-colors duration-300"><?= $icon; ?></span>

                        <div class="text-center">
                            <h4 class="text-lg font-bold text-white group-hover:text-secondary transition-colors">PC-<?= $pc['id_pc']; ?></h4>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-black/30 border border-white/10 <?= $text; ?>">
                                <?= $label; ?>
                            </span>
                        </div>

                        <?php if ($status_pc == 'tersedia' && !$active_session): ?>
                            <div class="absolute inset-0 bg-green-600/90 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm transform scale-95 group-hover:scale-100 duration-200">
                                <span class="text-white font-bold tracking-wider flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">touch_app</span> BOOK
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php endwhile; ?>
            </div>

        </div>
    </main>

    <div id="bookingModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>

        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-dark-surface border border-dark-border rounded-2xl w-full max-w-sm p-6 relative shadow-2xl transform scale-100">

                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white">Booking PC-<span id="modalPcNum" class="text-primary"></span></h3>
                    <p class="text-gray-400 text-xs">Pilih durasi main kamu.</p>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="book">
                    <input type="hidden" name="pc_id" id="modalPcId">
                    <input type="hidden" name="durasi" id="inputDurasi">

                    <div class="space-y-3 mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase ml-1">Durasi Paket (Jam)</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" onclick="selectDuration(1)" class="dur-btn py-3 rounded-xl border border-dark-border bg-dark-bg text-gray-300 hover:border-primary hover:text-primary hover:bg-primary/10 transition-all text-sm font-bold">1 Jam</button>
                            <button type="button" onclick="selectDuration(2)" class="dur-btn py-3 rounded-xl border border-dark-border bg-dark-bg text-gray-300 hover:border-primary hover:text-primary hover:bg-primary/10 transition-all text-sm font-bold">2 Jam</button>
                            <button type="button" onclick="selectDuration(3)" class="dur-btn py-3 rounded-xl border border-dark-border bg-dark-bg text-gray-300 hover:border-primary hover:text-primary hover:bg-primary/10 transition-all text-sm font-bold">3 Jam</button>
                            <button type="button" onclick="selectDuration(4)" class="dur-btn py-3 rounded-xl border border-dark-border bg-dark-bg text-gray-300 hover:border-primary hover:text-primary hover:bg-primary/10 transition-all text-sm font-bold">4 Jam</button>
                            <button type="button" onclick="selectDuration(5)" class="dur-btn py-3 rounded-xl border border-dark-border bg-dark-bg text-gray-300 hover:border-primary hover:text-primary hover:bg-primary/10 transition-all text-sm font-bold">5 Jam</button>
                            <input type="number" id="customDur" placeholder="Lainnya" class="w-full bg-dark-bg border border-dark-border rounded-xl px-2 text-center text-white text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary" oninput="selectDuration(this.value)">
                        </div>
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase ml-1 mb-1">Kode Promo (Opsional)</label>
                            <div class="flex gap-2">
                                <input type="text" name="kode_promo" id="inputPromo" class="w-full bg-dark-bg border border-dark-border rounded-xl px-3 py-2 text-white text-sm outline-none focus:border-primary uppercase placeholder-gray-700" placeholder="Punya kode diskon?">
                                <button type="button" onclick="checkPromo()" class="bg-primary/20 text-primary border border-primary/50 px-4 rounded-xl font-bold text-xs hover:bg-primary hover:text-white transition-all">CEK</button>
                            </div>
                            <p id="promoMessage" class="text-[10px] text-gray-500 mt-1 ml-1">*Diskon otomatis dihitung saat proses checkout.</p>
                        </div>
                    </div>

                    <div class="bg-dark-bg p-4 rounded-xl border border-dark-border mb-6">
                        <div class="flex justify-between items-center text-sm mb-1">
                            <span class="text-gray-400">Harga / Jam</span>
                            <span class="text-white">Rp 5.000</span>
                        </div>

                        <div id="rowSubtotal" class="hidden flex justify-between items-center text-sm mb-1">
                            <span class="text-gray-400">Subtotal</span>
                            <span class="text-white" id="valSubtotal">Rp 0</span>
                        </div>
                        <div id="rowDiskon" class="hidden flex justify-between items-center text-sm mb-1">
                            <span class="text-green-400">Potongan Diskon</span>
                            <span class="text-green-400" id="valDiskon">-Rp 0</span>
                        </div>

                        <div class="h-px bg-dark-border my-2"></div>
                        <div class="flex justify-between items-center font-bold">
                            <span class="text-white">Total Bayar</span>
                            <span class="text-secondary text-xl" id="totalPrice">Rp 5.000</span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal()" class="flex-1 py-3 rounded-xl bg-dark-bg border border-dark-border text-gray-400 hover:text-white font-bold transition-all">Batal</button>
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-primary hover:bg-primary/80 text-white font-bold shadow-neon-purple transition-all flex justify-center items-center gap-2">
                            <span class="material-symbols-outlined text-sm">bolt</span>
                            Gas Main!
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal Logic
        const modal = document.getElementById('bookingModal');
        const pcNum = document.getElementById('modalPcNum');
        const pcId = document.getElementById('modalPcId');
        const inputDurasi = document.getElementById('inputDurasi');
        const totalPrice = document.getElementById('totalPrice');
        const customInput = document.getElementById('customDur');

        const pricePerH = <?= getSetting('harga_per_jam'); ?>;

        function openModal(id) {
            pcNum.innerText = id;
            pcId.value = id;
            modal.classList.remove('hidden');
            selectDuration(1);

            document.querySelectorAll('.dur-btn').forEach(btn => {
                btn.classList.remove('border-primary', 'text-primary', 'bg-primary/10');
            });

            document.querySelector('.dur-btn').classList.add('border-primary', 'text-primary', 'bg-primary/10');
            customInput.value = '';
            resetPromoUI(); 
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function selectDuration(jam) {
            if (!jam || jam < 1) jam = 1;
            inputDurasi.value = jam;

            resetPromoUI();

            if (event && event.target.classList.contains('dur-btn')) {
                document.querySelectorAll('.dur-btn').forEach(btn => {
                    btn.classList.remove('border-primary', 'text-primary', 'bg-primary/10');
                });
                event.target.classList.add('border-primary', 'text-primary', 'bg-primary/10');
                customInput.value = '';
            }
        }

        function checkPromo() {
            const kode = document.getElementById('inputPromo').value;
            const durasi = document.getElementById('inputDurasi').value;
            const msg = document.getElementById('promoMessage');

            if (!kode) {
                msg.innerHTML = '<span class="text-red-400">Masukkan kode promo dulu!</span>';
                return;
            }

            const formData = new FormData();
            formData.append('action', 'check_promo');
            formData.append('kode_promo', kode);
            formData.append('durasi', durasi);

            fetch('booking.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        msg.innerHTML = `<span class="text-green-400 font-bold">Kode valid! Hemat ${data.persen}%</span>`;

                        // Tampilkan Baris Diskon
                        document.getElementById('rowSubtotal').classList.remove('hidden');
                        document.getElementById('rowDiskon').classList.remove('hidden');

                        // Update Angka
                        document.getElementById('valSubtotal').innerText = "Rp " + parseInt(data.total_awal).toLocaleString('id-ID');
                        document.getElementById('valDiskon').innerText = "-Rp " + parseInt(data.diskon).toLocaleString('id-ID');
                        document.getElementById('totalPrice').innerText = "Rp " + parseInt(data.total_akhir).toLocaleString('id-ID');
                    } else {
                        msg.innerHTML = `<span class="text-red-400">${data.message}</span>`;
                        resetPromoUI();
                    }
                })
                .catch(err => console.error(err));
        }

        function resetPromoUI() {
            document.getElementById('rowSubtotal').classList.add('hidden');
            document.getElementById('rowDiskon').classList.add('hidden');
            document.getElementById('promoMessage').innerText = '*Diskon otomatis dihitung saat proses checkout.';

            // Hitung Total Normal
            const jam = document.getElementById('inputDurasi').value || 1;
            const total = jam * pricePerH;
            document.getElementById('totalPrice').innerText = "Rp " + total.toLocaleString('id-ID');
        }

        function confirmStop(form) {
            Swal.fire({
                title: 'Berhenti Bermain?',
                text: "Sisa waktu tidak dapat diuangkan kembali.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Stop',
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