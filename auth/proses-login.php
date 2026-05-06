<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config.php';

$client = new Google\Client();
$client->setClientId('118639840694-uuda9i1n1bc3c216tqufrjirucg3chdv.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/projek-perpus/auth/proses-login.php');
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = mysqli_real_escape_string($koneksi, $google_account_info->email);
        $nama  = mysqli_real_escape_string($koneksi, $google_account_info->name);

        // CEK DOMAIN: email student
        if (strpos($email, '@student.polije.ac.id') === false) {
            echo "<script>
                    alert('Gagal! Anda hanya boleh login menggunakan email @student.polije.ac.id');
                    window.location='login.php';
                  </script>";
            exit();
        }

        // Jika email valid Polije, cek atau daftarkan ke tabel anggota
        $query = mysqli_query($koneksi, "SELECT * FROM anggota WHERE email_anggota = '$email' LIMIT 1");
        
        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_assoc($query);
        } else {
            // Auto Register Member Baru
            $kode_oto = "MHS-" . rand(1000, 9999);
            mysqli_query($koneksi, "INSERT INTO anggota (kode_anggota, nama_anggota, email_anggota, status_anggota) 
                                   VALUES ('$kode_oto', '$nama', '$email', 'active')");
            
            $res = mysqli_query($koneksi, "SELECT * FROM anggota WHERE email_anggota = '$email'");
            $data = mysqli_fetch_assoc($res);
        }

        $_SESSION['id_user'] = $data['id_anggota'];
        $_SESSION['nama']    = $data['nama_anggota'];
        $_SESSION['level']   = 'user';

        header("Location: ../user/mahasiswa.php");
        exit();
    }
}

// login admin
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    // Cek hanya di tabel ADMIN
    $query = mysqli_query($koneksi, "SELECT * FROM admin WHERE email_admin = '$email' LIMIT 1");
    
    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);

        if (password_verify($password, $data['password'])) {
            $_SESSION['id_admin'] = $data['id_admin'];
            $_SESSION['nama']     = $data['nama_admin'];
            $_SESSION['level']    = 'admin';

            mysqli_query($koneksi, "UPDATE admin SET last_login_at = NOW() WHERE id_admin = " . $data['id_admin']);
            header("Location: ../admin/index.php?menu=dashboard");
            exit();
        } else {
            echo "<script>alert('Password Admin salah!'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Akses Admin ditolak atau akun tidak ditemukan!'); window.location='login.php';</script>";
    }
}
?>