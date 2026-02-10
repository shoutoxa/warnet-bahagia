<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['swal_icon'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['swal_icon']; ?>',
            title: '<?= $_SESSION['swal_title']; ?>',
            text: '<?= $_SESSION['swal_text']; ?>',
            background: '#1e1628', 
            color: '#fff',
            confirmButtonColor: '#7f0df2' 
        });
    </script>
<?php
    unset($_SESSION['swal_icon']);
    unset($_SESSION['swal_title']);
    unset($_SESSION['swal_text']);
endif;
?>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }
</script>