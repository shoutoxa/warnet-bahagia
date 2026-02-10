
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function getSetting($key)
{
    global $conn;
    $query = mysqli_query($conn, "SELECT value FROM pengaturan WHERE key_name = '$key'");
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        return $data['value'];
    }
    return 0; 
}

function setFlashMessage($type, $title, $message)
{
    $_SESSION['swal_icon'] = $type;    
    $_SESSION['swal_title'] = $title;
    $_SESSION['swal_text'] = $message;
}


function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}


function setAlert($type, $message)
{
    $swal_type = ($type == 'danger') ? 'error' : $type;
    $title = ($swal_type == 'success') ? 'Berhasil' : (($swal_type == 'error') ? 'Gagal' : 'Info');

    setFlashMessage($swal_type, $title, $message);
}

function getAlert()
{
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}


function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}


function getActiveCartId($conn)
{
    if (!isset($_SESSION['cart_id'])) {
        $conn->query("INSERT INTO keranjang (status) VALUES ('aktif')");
        $_SESSION['cart_id'] = $conn->insert_id;
    }
    return $_SESSION['cart_id'];
}


function getCartItems($conn)
{
    if (!isset($_SESSION['cart_id'])) {
        return [];
    }

    $cart_id = $_SESSION['cart_id'];

    $query = "
        SELECT 
            k.*,
            i.nama_item,
            i.harga AS harga_item,
            b.durasi_jam,
            b.harga_per_jam
        FROM keranjang k
        LEFT JOIN item_fnb i ON k.id_item = i.id_item
        LEFT JOIN billing b ON k.id_billing = b.id_billing
        WHERE k.id_keranjang = $cart_id
          AND k.status = 'aktif'
    ";

    return $conn->query($query);
}


function getCartTotal($conn)
{
    if (!isset($_SESSION['cart_id'])) {
        return 0;
    }

    $cart_id = $_SESSION['cart_id'];

    $result = $conn->query("
        SELECT SUM(total_harga) AS total
        FROM keranjang
        WHERE id_keranjang = $cart_id
          AND status = 'aktif'
    ")->fetch_assoc();

    return $result['total'] ?? 0;
}


function clearCart($conn)
{
    if (isset($_SESSION['cart_id'])) {
        $cart_id = $_SESSION['cart_id'];
        $conn->query("
            UPDATE keranjang
            SET status = 'selesai'
            WHERE id_keranjang = $cart_id
        ");
        unset($_SESSION['cart_id']);
    }
}


function checkoutCart($conn, $user_id)
{
    if (!isset($_SESSION['cart_id'])) {
        return false;
    }

    $cart_id = $_SESSION['cart_id'];
    $total = getCartTotal($conn);

    if ($total <= 0) {
        return false;
    }

    $user = $conn->query("
        SELECT saldo FROM konsumen
        WHERE id_konsumen = $user_id
    ")->fetch_assoc();

    if ($user['saldo'] < $total) {
        setAlert('error', 'Saldo tidak cukup untuk checkout.');
        return false;
    }

    $conn->begin_transaction();

    try {
        $conn->query("
            INSERT INTO pesanan (
                waktu_pesanan,
                status_pesanan,
                total_pesanan,
                id_konsumen,
                id_keranjang
            ) VALUES (
                NOW(),
                'dibayar',
                $total,
                $user_id,
                $cart_id
            )
        ");

        $conn->query("
            UPDATE konsumen
            SET saldo = saldo - $total
            WHERE id_konsumen = $user_id
        ");

        $conn->query("
            UPDATE keranjang
            SET status = 'selesai'
            WHERE id_keranjang = $cart_id
        ");

        $conn->commit();
        unset($_SESSION['cart_id']);

        setAlert('success', 'Checkout berhasil. Pesanan diproses.');
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        setAlert('error', 'Checkout gagal.');
        return false;
    }
}
