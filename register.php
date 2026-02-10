<?php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak sesuai!";
    } else {
        $checkUser = mysqli_query($conn, "SELECT username FROM konsumen WHERE username = '$username'");
        if (mysqli_num_rows($checkUser) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO konsumen (username, password, email, saldo) 
                      VALUES ('$username', '$hashed_password', '$email', 0)";

            if (mysqli_query($conn, $query)) {
                echo "<script>
                        alert('Registrasi Berhasil! Silakan Login.');
                        window.location.href='index.php';
                      </script>";
                exit;
            } else {
                $error = "Terjadi kesalahan: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html class="dark" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Warnet Bahagia Register</title>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Spline+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#7f0df2",
                        "background-light": "#f7f5f8",
                        "background-dark": "#191022",
                    },
                    fontFamily: {
                        "display": ["Spline Sans", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "1rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        /* Custom Neon Effects */
        .neon-text-primary {
            text-shadow: 0 0 10px rgba(127, 13, 242, 0.7), 0 0 20px rgba(127, 13, 242, 0.5);
        }

        .neon-border-focus:focus-within {
            box-shadow: 0 0 10px rgba(34, 211, 238, 0.5), inset 0 0 5px rgba(34, 211, 238, 0.2);
            border-color: #22d3ee;
        }

        .neon-button {
            box-shadow: 0 0 15px rgba(127, 13, 242, 0.5);
        }

        .neon-button:hover {
            box-shadow: 0 0 25px rgba(127, 13, 242, 0.7);
        }

        .bg-grid-pattern {
            background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }
    </style>
</head>

<body class="font-display antialiased text-white bg-background-dark min-h-screen flex flex-col overflow-x-hidden selection:bg-primary selection:text-white">
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-grid-pattern opacity-40"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-primary/20 blur-[120px] rounded-full mix-blend-screen pointer-events-none"></div>
    </div>
    <div class="relative z-10 flex grow flex-col items-center justify-center p-4">
        <div class="w-full max-w-[440px] flex flex-col gap-6 bg-[#211b27]/80 backdrop-blur-xl border border-white/5 p-8 rounded-2xl shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-primary to-transparent opacity-70"></div>

            <div class="flex flex-col items-center gap-2">
                <div class="size-12 mb-2 flex items-center justify-center rounded-xl bg-primary/10 border border-primary/20 shadow-[0_0_15px_rgba(127,13,242,0.3)]">
                    <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">sports_esports</span>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-white neon-text-primary text-center">Buat Akun Baru</h1>
                <p class="text-[#ab9cba] text-sm font-medium text-center">Join the ultimate gaming experience</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-200 text-sm p-3 rounded-xl text-center">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form class="flex flex-col gap-5 mt-2" action="" method="POST">

                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#ab9cba] ml-1">Nama Lengkap</label>
                    <div class="group relative flex items-center rounded-xl bg-[#141118] border border-[#302839] transition-all duration-300 neon-border-focus">
                        <div class="flex items-center justify-center pl-4 pr-2 text-[#6b5a7a] group-focus-within:text-cyan-400 transition-colors">
                            <span class="material-symbols-outlined">badge</span>
                        </div>
                        <input name="nama_lengkap" class="w-full bg-transparent border-0 text-white placeholder-[#5a4d66] focus:ring-0 text-base h-12 py-2 pl-1 pr-4 rounded-xl" placeholder="Nama Lengkap Anda" type="text" required />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#ab9cba] ml-1">Username</label>
                    <div class="group relative flex items-center rounded-xl bg-[#141118] border border-[#302839] transition-all duration-300 neon-border-focus">
                        <div class="flex items-center justify-center pl-4 pr-2 text-[#6b5a7a] group-focus-within:text-cyan-400 transition-colors">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                        <input name="username" class="w-full bg-transparent border-0 text-white placeholder-[#5a4d66] focus:ring-0 text-base h-12 py-2 pl-1 pr-4 rounded-xl" placeholder="Masukan Username" type="text" required />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#ab9cba] ml-1">Email</label>
                    <div class="group relative flex items-center rounded-xl bg-[#141118] border border-[#302839] transition-all duration-300 neon-border-focus">
                        <div class="flex items-center justify-center pl-4 pr-2 text-[#6b5a7a] group-focus-within:text-cyan-400 transition-colors">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                        <input name="email" class="w-full bg-transparent border-0 text-white placeholder-[#5a4d66] focus:ring-0 text-base h-12 py-2 pl-1 pr-4 rounded-xl" placeholder="Masukan Email" type="email" required />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#ab9cba] ml-1">Password</label>
                    <div class="group relative flex items-center rounded-xl bg-[#141118] border border-[#302839] transition-all duration-300 neon-border-focus">
                        <div class="flex items-center justify-center pl-4 pr-2 text-[#6b5a7a] group-focus-within:text-cyan-400 transition-colors">
                            <span class="material-symbols-outlined">lock</span>
                        </div>
                        <input name="password" id="pass1" class="w-full bg-transparent border-0 text-white placeholder-[#5a4d66] focus:ring-0 text-base h-12 py-2 pl-1 pr-4 rounded-xl" placeholder="Masukan Password" type="password" required />
                        <button type="button" onclick="togglePass('pass1')" class="pr-4 pl-2 text-[#5a4d66] hover:text-white transition-colors focus:outline-none">
                            <span class="material-symbols-outlined text-[20px]" id="icon-pass1">visibility_off</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#ab9cba] ml-1">Konfirmasi Password</label>
                    <div class="group relative flex items-center rounded-xl bg-[#141118] border border-[#302839] transition-all duration-300 neon-border-focus">
                        <div class="flex items-center justify-center pl-4 pr-2 text-[#6b5a7a] group-focus-within:text-cyan-400 transition-colors">
                            <span class="material-symbols-outlined">lock_reset</span>
                        </div>
                        <input name="confirm_password" id="pass2" class="w-full bg-transparent border-0 text-white placeholder-[#5a4d66] focus:ring-0 text-base h-12 py-2 pl-1 pr-4 rounded-xl" placeholder="Ulangi Password" type="password" required />
                        <button type="button" onclick="togglePass('pass2')" class="pr-4 pl-2 text-[#5a4d66] hover:text-white transition-colors focus:outline-none">
                            <span class="material-symbols-outlined text-[20px]" id="icon-pass2">visibility_off</span>
                        </button>
                    </div>
                </div>

                <button name="register" class="mt-4 w-full h-12 bg-primary text-white text-base font-bold rounded-xl neon-button transition-all duration-300 transform active:scale-[0.98] flex items-center justify-center gap-2" type="submit">
                    <span>DAFTAR SEKARANG</span>
                    <span class="material-symbols-outlined text-[20px]">person_add</span>
                </button>
            </form>

            <div class="text-center pt-2 border-t border-white/5 mt-2">
                <p class="text-sm text-[#ab9cba]">
                    Sudah punya akun?
                    <a class="font-bold text-pink-500 hover:text-pink-400 transition-colors drop-shadow-[0_0_8px_rgba(236,72,153,0.6)] ml-1 inline-flex items-center gap-0.5" href="index.php">
                        Login Di Sini
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePass(id) {
            var input = document.getElementById(id);
            var icon = document.getElementById('icon-' + id);
            if (input.type === "password") {
                input.type = "text";
                icon.innerText = "visibility";
            } else {
                input.type = "password";
                icon.innerText = "visibility_off";
            }
        }
    </script>
</body>

</html>