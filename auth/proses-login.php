<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../config.php';

// Konfigurasi Google Client
$client = new Google\Client();
$client->setClientId('118639840694-uuda9i1n1bc3c216tqufrjirucg3chdv.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/update/projek-perpus/auth/proses-login.php');

// FIX SSL: Agar tidak error di localhost (Laragon/XAMPP)
$client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

$client->addScope("email");
$client->addScope("profile");

/**
 * --------------------------------------------------------------
 * PROSES LOGIN GOOGLE (MAHASISWA)
 * --------------------------------------------------------------
 */
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email_asli = $google_account_info->email;
        $nama_asli  = $google_account_info->name; // Nama dari Google biasanya "E41251709 NURUL MA'RIFAH"

        // 1. Validasi Domain Email
        if (strpos($email_asli, '@student.polije.ac.id') === false) {
            echo "<script>alert('Gagal! Gunakan email @student.polije.ac.id'); window.location='login.php';</script>";
            exit();
        }

        // 2. Ambil NIM dari email (sebelum @)
        $pecah_email = explode("@", $email_asli);
        $nim_otomatis = strtoupper($pecah_email[0]); // Hasil: E41251709

        // 3. Bersihkan NAMA (Hapus NIM dari Nama jika ada)
        // Jika nama mengandung NIM di depannya, kita hapus supaya murni nama saja
        $nama_bersih = trim(str_replace($nim_otomatis, "", $nama_asli));
        
        // Escape string untuk keamanan database
        $email_db = mysqli_real_escape_string($koneksi, $email_asli);
        $nama_db  = mysqli_real_escape_string($koneksi, $nama_bersih);
        $nim_db   = mysqli_real_escape_string($koneksi, $nim_otomatis);

        // 4. Cek keberadaan anggota di database
        $query = mysqli_query($koneksi, "SELECT * FROM anggota WHERE email_anggota = '$email_db' LIMIT 1");
        
        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_assoc($query);
            
            // Update jika data lama masih menggunakan format MHS-xxx atau nama belum bersih
            mysqli_query($koneksi, "UPDATE anggota SET 
                kode_anggota = '$nim_db', 
                nama_anggota = '$nama_db' 
                WHERE email_anggota = '$email_db'");
            
            // Ambil ulang data terbaru setelah update
            $res_update = mysqli_query($koneksi, "SELECT * FROM anggota WHERE email_anggota = '$email_db'");
            $data = mysqli_fetch_assoc($res_update);
        } else {
            // Jika belum terdaftar, buat baru dengan NIM sebagai kode_anggota
            $insert = mysqli_query($koneksi, "INSERT INTO anggota (kode_anggota, nama_anggota, email_anggota, status_anggota) 
                                              VALUES ('$nim_db', '$nama_db', '$email_db', 'active')");
            
            if ($insert) {
                $res_baru = mysqli_query($koneksi, "SELECT * FROM anggota WHERE email_anggota = '$email_db'");
                $data = mysqli_fetch_assoc($res_baru);
            } else {
                die("Gagal simpan database: " . mysqli_error($koneksi));
            }
        } 

        // 5. Set Session
        $_SESSION['id_user'] = $data['id_anggota'];
        $_SESSION['nama']    = $data['nama_anggota'];
        $_SESSION['nim']     = $data['kode_anggota'];
        $_SESSION['level']   = 'user';

        header("Location: ../user/mahasiswa.php");
        exit();
    } else {
        die("Kesalahan Google: " . $token['error_description']);
    }
}

/**
 * --------------------------------------------------------------
 * PROSES LOGIN ADMIN (MANUAL)
 * --------------------------------------------------------------
 */
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    $query_admin = mysqli_query($koneksi, "SELECT * FROM admin WHERE email_admin = '$email' LIMIT 1");
    
    if (mysqli_num_rows($query_admin) === 1) {
        $data_admin = mysqli_fetch_assoc($query_admin);
        if (password_verify($password, $data_admin['password'])) {
            $_SESSION['id_admin'] = $data_admin['id_admin'];
            $_SESSION['nama']     = $data_admin['nama_admin'];
            $_SESSION['level']    = 'admin';
            mysqli_query($koneksi, "UPDATE admin SET last_login_at = NOW() WHERE id_admin = " . $data_admin['id_admin']);
            header("Location: ../admin/index.php?menu=dashboard");
            exit();
        } else {
            echo "<script>alert('Password Admin salah!'); window.location='login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Akun tidak ditemukan!'); window.location='login.php';</script>";
        exit();
    }
}
?>