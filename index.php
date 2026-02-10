<?php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($role == 'admin') {
        $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");
        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_assoc($query);
            if (password_verify($password, $data['password'])) {
                $_SESSION['user'] = $data['nama_admin'];
                $_SESSION['role'] = 'admin';
                $_SESSION['id_admin'] = $data['id_admin'];
                $_SESSION['user_id'] = $data['id_admin'];
                $_SESSION['nama'] = $data['nama_admin'];
                $_SESSION['username'] = $data['username'];
                header("Location: pages/admin/dashboard.php");
                exit;
            } else {
                $error = "Password admin salah!";
            }
        } else {
            $error = "Username admin tidak ditemukan!";
        }
    } else {
        $query = mysqli_query($conn, "SELECT * FROM konsumen WHERE username='$username'");
        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_assoc($query);
            if (password_verify($password, $data['password'])) {
                $_SESSION['user'] = $data['username'];
                $_SESSION['role'] = 'konsumen';
                $_SESSION['id_konsumen'] = $data['id_konsumen'];
                $_SESSION['user_id'] = $data['id_konsumen'];
                $_SESSION['username'] = $data['username'];
                header("Location: pages/konsumen/dashboard.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}
?>

<!DOCTYPE html>
<html class="dark" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Warnet Bahagia Login</title>
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
                <h1 class="text-3xl font-bold tracking-tight text-white neon-text-primary text-center">Warnet Bahagia</h1>
                <p class="text-[#ab9cba] text-sm font-medium text-center">Welcome back, Gamer. Ready to grind?</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-200 text-sm p-3 rounded-xl text-center">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form class="flex flex-col gap-5 mt-2" action="" method="POST">

                <div class="bg-[#141118] p-1.5 rounded-xl border border-white/5 relative isolate">
                    <!-- Sliding Background Animation -->
                    <div id="roleSlider" class="absolute top-1.5 left-1.5 bottom-1.5 w-[calc(50%_-_0.5rem)] bg-primary rounded-lg shadow-[0_0_15px_rgba(127,13,242,0.5)] transition-all duration-300 ease-in-out z-0 translate-x-0"></div>

                    <div class="grid grid-cols-2 gap-1 relative z-10">
                        <label class="cursor-pointer group">
                            <input checked="" class="sr-only" name="role" type="radio" value="user" onchange="updateRole('user')" />
                            <div id="labelUser" class="flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold text-white transition-colors duration-300 hover:text-white">
                                <span class="material-symbols-outlined text-[18px]">person</span>
                                <span>Konsumen</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input class="sr-only" name="role" type="radio" value="admin" onchange="updateRole('admin')" />
                            <div id="labelAdmin" class="flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold text-[#ab9cba] transition-colors duration-300 hover:text-white">
                                <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                                <span>Admin</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#ab9cba] ml-1">Username</label>
                    <div class="group relative flex items-center rounded-xl bg-[#141118] border border-[#302839] transition-all duration-300 neon-border-focus">
                        <div class="flex items-center justify-center pl-4 pr-2 text-[#6b5a7a] group-focus-within:text-cyan-400 transition-colors">
                            <span class="material-symbols-outlined">alternate_email</span>
                        </div>
                        <input name="username" class="w-full bg-transparent border-0 text-white placeholder-[#5a4d66] focus:ring-0 text-base h-12 py-2 pl-1 pr-4 rounded-xl" placeholder="Enter your username" type="text" required />
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#ab9cba]">Password</label>
                    </div>
                    <div class="group relative flex items-center rounded-xl bg-[#141118] border border-[#302839] transition-all duration-300 neon-border-focus">
                        <div class="flex items-center justify-center pl-4 pr-2 text-[#6b5a7a] group-focus-within:text-cyan-400 transition-colors">
                            <span class="material-symbols-outlined">lock</span>
                        </div>
                        <input name="password" id="passwordInput" class="w-full bg-transparent border-0 text-white placeholder-[#5a4d66] focus:ring-0 text-base h-12 py-2 pl-1 pr-4 rounded-xl" placeholder="Enter your password" type="password" required />
                        <button type="button" onclick="togglePassword()" class="pr-4 pl-2 text-[#5a4d66] hover:text-white transition-colors focus:outline-none">
                            <span class="material-symbols-outlined text-[20px]" id="eyeIcon">visibility_off</span>
                        </button>
                    </div>
                </div>

                <button name="login" class="mt-4 w-full h-12 bg-primary text-white text-base font-bold rounded-xl neon-button transition-all duration-300 transform active:scale-[0.98] flex items-center justify-center gap-2" type="submit">
                    <span>LOGIN</span>
                    <span class="material-symbols-outlined text-[20px]">login</span>
                </button>
            </form>

            <div class="text-center pt-2 border-t border-white/5 mt-2">
                <p class="text-sm text-[#ab9cba]">
                    Belum punya akun?
                    <a class="font-bold text-pink-500 hover:text-pink-400 transition-colors drop-shadow-[0_0_8px_rgba(236,72,153,0.6)] ml-1 inline-flex items-center gap-0.5" href="register.php">
                        Daftar Sekarang
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function updateRole(role) {
            const slider = document.getElementById('roleSlider');
            const labelUser = document.getElementById('labelUser');
            const labelAdmin = document.getElementById('labelAdmin');

            if (role === 'admin') {
                slider.classList.remove('translate-x-0');
                slider.classList.add('translate-x-[calc(100%_+_0.25rem)]');
                labelUser.classList.replace('text-white', 'text-[#ab9cba]');
                labelAdmin.classList.replace('text-[#ab9cba]', 'text-white');
            } else {
                slider.classList.add('translate-x-0');
                slider.classList.remove('translate-x-[calc(100%_+_0.25rem)]');
                labelUser.classList.replace('text-[#ab9cba]', 'text-white');
                labelAdmin.classList.replace('text-white', 'text-[#ab9cba]');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const checked = document.querySelector('input[name="role"]:checked');
            if (checked) updateRole(checked.value);
        });

        function togglePassword() {
            var passwordInput = document.getElementById("passwordInput");
            var eyeIcon = document.getElementById("eyeIcon");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.innerText = "visibility";
            } else {
                passwordInput.type = "password";
                eyeIcon.innerText = "visibility_off";
            }
        }
    </script>
</body>

</html>