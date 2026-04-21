<?php
session_start();

if (isset($_POST['email']) && isset($_POST['password'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($email === "user" && $password === "123") {

        $_SESSION['login'] = true;

        header("Location: user/beranda.php");
        exit;

    } else {
        echo "<script>
                alert('Login gagal! Email atau password salah');
                window.location='login.php';
              </script>";
    }

} else {
    header("Location: login.php");
    exit;
}
?>