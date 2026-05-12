<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

// KONFIGURASI GOOGLE
$client = new Google\Client();

$client->setClientId('118639840694-uuda9i1n1bc3c216tqufrjirucg3chdv.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/projek-perpus/auth/proses-login.php');

$client->setHttpClient(
    new \GuzzleHttp\Client([
        'verify' => false
    ])
);

$client->addScope("email");
$client->addScope("profile");


/*
|--------------------------------------------------------------------------
| LOGIN GOOGLE MAHASISWA
|--------------------------------------------------------------------------
*/

if (isset($_GET['code'])) {

    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    // Jika token error
    if (isset($token['error'])) {
        die("Kesalahan Google: " . $token['error_description']);
    }

    // Set access token
    $client->setAccessToken($token['access_token']);

    // Ambil data akun Google
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();

    $email_asli = $google_account_info->email;
    $nama_asli  = $google_account_info->name;

    /*
    |--------------------------------------------------------------------------
    | VALIDASI EMAIL POLIJE
    |--------------------------------------------------------------------------
    */

    if (strpos($email_asli, '@student.polije.ac.id') === false) {

        echo "
        <script>
            alert('Gunakan email @student.polije.ac.id');
            window.location='login.php';
        </script>
        ";

        exit();
    }

    /*
    |--------------------------------------------------------------------------
    | AMBIL NIM DARI EMAIL
    |--------------------------------------------------------------------------
    */

    $pecah_email  = explode("@", $email_asli);
    $nim_otomatis = strtoupper($pecah_email[0]);

    // Bersihkan nama
    $nama_bersih = trim(str_replace($nim_otomatis, "", $nama_asli));

    // Escape database
    $email_db = mysqli_real_escape_string($koneksi, $email_asli);
    $nama_db  = mysqli_real_escape_string($koneksi, $nama_bersih);
    $nim_db   = mysqli_real_escape_string($koneksi, $nim_otomatis);

    /*
    |--------------------------------------------------------------------------
    | CEK APAKAH SUDAH ADA DI DATABASE
    |--------------------------------------------------------------------------
    */

 $query = mysqli_query(
        $koneksi,
        "SELECT * FROM anggota WHERE email = '$email_db' OR nim = '$nim_db' LIMIT 1"
    );

    // Kalau sudah ada (berdasarkan email atau nim)
    if (mysqli_num_rows($query) > 0) {

        $data = mysqli_fetch_assoc($query);

    } else {

        /*
        |--------------------------------------------------------------------------
        | AUTO REGISTER
        |--------------------------------------------------------------------------
        */

        mysqli_query(
            $koneksi,
            "INSERT INTO anggota (nama, nama_anggota, nim, email)
            VALUES ('$nama_db', '$nama_db', '$nim_db', '$email_db')"
        );

        // Ambil akun yang baru dibuat
        $query_baru = mysqli_query(
            $koneksi,
            "SELECT * FROM anggota WHERE email = '$email_db' LIMIT 1"
        );

        $data = mysqli_fetch_assoc($query_baru);
    }
    /*
    |--------------------------------------------------------------------------
    | SESSION LOGIN
    |--------------------------------------------------------------------------
    */

    $_SESSION['id_user']    = $data['id_anggota'];
    $_SESSION['id_anggota'] = $data['id_anggota'];
    $_SESSION['nama']       = $data['nama'];
    $_SESSION['nim']        = $data['nim'];
    $_SESSION['jurusan']    = $data['jurusan'] ?? '';
    $_SESSION['level']      = 'user';

    header("Location: ../user/mahasiswa.php");
    exit();
}



/*
|--------------------------------------------------------------------------
| LOGIN ADMIN MANUAL
|--------------------------------------------------------------------------
*/

if (isset($_POST['login']) || isset($_POST['login_admin'])) {

    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    $query_admin = mysqli_query(
        $koneksi,
        "SELECT * FROM admin WHERE email = '$email' LIMIT 1"
    );

    if (mysqli_num_rows($query_admin) === 1) {

        $data_admin = mysqli_fetch_assoc($query_admin);

        if (password_verify($password, $data_admin['password'])) {

            $_SESSION['id_admin'] = $data_admin['id_admin'];
            $_SESSION['nama']     = $data_admin['nama'];
            $_SESSION['jabatan']  = $data_admin['jabatan_admin'] ?? 'Admin Perpustakaan';
            $_SESSION['level']    = 'admin';

            mysqli_query(
                $koneksi,
                "UPDATE admin 
                 SET last_login_at = NOW() 
                 WHERE id_admin = " . $data_admin['id_admin']
            );

            header("Location: ../admin/index.php?menu=dashboard");
            exit();

        } else {

            echo "
            <script>
                alert('Password Admin salah!');
                window.location='login.php';
            </script>
            ";

            exit();
        }

    } else {

        echo "
        <script>
            alert('Akun Admin tidak ditemukan!');
            window.location='login.php';
        </script>
        ";

        exit();
    }
}
?>
