<?php
// 1. Memulai session di bagian paling atas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Hubungkan dengan file koneksi database kamu
include 'koneksi.php'; 

// 3. DETEKSI OTOMATIS JALUR DASHBOARD
// Memastikan arah redirect benar, baik dashboard ditaruh di folder 'admin/' atau folder utama
$dashboard_path = "dashboard.php";
if (is_dir('admin') && file_exists('admin/dashboard.php')) {
    $dashboard_path = "admin/dashboard.php";
}

// 4. PROTEKSI HALAMAN: Jika user SUDAH login, jangan biarkan masuk ke halaman login lagi
if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    header("Location: " . $dashboard_path);
    exit;
}

$alert_message = "";
$alert_type = "";

// 5. MEMPROSES FORM POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // LOGIKA REGISTRASI (SIGN UP)
    if (isset($_POST['register'])) {
        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        $name  = mysqli_real_escape_string($koneksi, $name);
        $email = mysqli_real_escape_string($koneksi, $email);

        // Validasi apakah email sudah terdaftar
        $check_email = mysqli_query($koneksi, "SELECT email FROM users WHERE email = '$email'");
        
        if (mysqli_num_rows($check_email) > 0) {
            $alert_message = "Email sudah terdaftar! Silakan gunakan email lain.";
            $alert_type = "danger";
        } else {
            // Enkripsi password sebelum disimpan ke database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query_reg = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
            
            if (mysqli_query($koneksi, $query_reg)) {
                $alert_message = "Akun berhasil dibuat! Silakan masuk melalui menu Sign In.";
                $alert_type = "success";
            } else {
                $alert_message = "Gagal mendaftar: " . mysqli_error($koneksi);
                $alert_type = "danger";
            }
        }

    // LOGIKA MASUK (SIGN IN)
    } elseif (isset($_POST['login'])) {
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        $email = mysqli_real_escape_string($koneksi, $email);

        $query_login = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
        
        if (mysqli_num_rows($query_login) === 1) {
            $user_data = mysqli_fetch_assoc($query_login);

            // Verifikasi password terenkripsi
            if (password_verify($password, $user_data['password'])) {
                
                // Simpan data user ke dalam array session utama
                $_SESSION['user'] = [
                    'id'    => $user_data['id'],
                    'name'  => $user_data['name'],
                    'email' => $user_data['email']
                ];

                // Alihkan langsung ke dashboard tujuan
                header("Location: " . $dashboard_path);
                exit;
            } else {
                $alert_message = "Password yang Anda masukkan salah!";
                $alert_type = "danger";
            }
        } else {
            $alert_message = "Email tidak ditemukan!";
            $alert_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Login & Register Page</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body{
            background-color: #323234;
            background: linear-gradient(to right, #585454, #6d6f72);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 100vh;
        }

        .alert {
            padding: 12px 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            width: 768px;
            max-width: 100%;
            text-align: center;
        }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        .container{
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
        }

        .container p{
            font-size: 14px;
            line-height: 20px;
            letter-spacing: 0.3px;
            margin: 20px 0;
        }

        .container span{
            font-size: 12px;
        }

        .container a{
            color: #333;
            font-size: 13px;
            text-decoration: none;
            margin: 15px 0 10px;
        }

        .container button{
            background-color: #a8562d;
            color: #fff;
            font-size: 12px;
            padding: 10px 45px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 10px;
            cursor: pointer;
        }

        .container button.hidden{
            background-color: transparent;
            border-color: #fff;
        }

        .container form{
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
        }

        .container input{
            background-color: #eee;
            border: none;
            margin: 8px 0;
            padding: 10px 15px;
            font-size: 13px;
            border-radius: 8px;
            width: 100%;
            outline: none;
        }

        .form-container{
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in{
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.active .sign-in{
            transform: translateX(100%);
        }

        .sign-up{
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.active .sign-up{
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: move 0.6s;
        }

        @keyframes move{
            0%, 49.99%{
                opacity: 0;
                z-index: 1;
            }
            50%, 100%{
                opacity: 1;
                z-index: 5;
            }
        }

        .social-icons{
            margin: 20px 0;
        }

        .social-icons a{
            border: 1px solid #ccc;
            border-radius: 20%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 3px;
            width: 40px;
            height: 40px;
        }

        .toggle-container{
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: all 0.6s ease-in-out;
            border-radius: 150px 0 0 100px;
            z-index: 1000;
        }

        .container.active .toggle-container{
            transform: translateX(-100%);
            border-radius: 0 150px 100px 0;
        }

        .toggle{
            background-color: #a84a2d;
            height: 100%;
            background: linear-gradient(to right, #e29a2e, #e45d19);
            color: #fff;
            position: relative;
            left: -100%;
            width: 200%;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .container.active .toggle{
            transform: translateX(50%);
        }

        .toggle-panel{
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 30px;
            text-align: center;
            top: 0;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .toggle-left{
            transform: translateX(-200%);
        }

        .container.active .toggle-left{
            transform: translateX(0);
        }

        .toggle-right{
            right: 0;
            transform: translateX(0);
        }

        .container.active .toggle-right{
            transform: translateX(200%);
        }

        /* Responsive Mobile CSS */
        @media (max-width:768px){
            .alert { width: 95%; max-width: 420px; }
            .container{ width: 95%; max-width: 420px; min-height: auto; border-radius: 24px; overflow: hidden; }
            .toggle-container{ display: none !important; }
            .form-container{ position: relative !important; left: 0 !important; top: 0 !important; width: 100 !important; height: auto !important; transform: none !important; opacity: 1 !important; transition: none !important; animation: none !important; z-index: auto !important; }
            .sign-in{ display: block; }
            .sign-up{ display: none; }
            .container.active .sign-in{ display: none; }
            .container.active .sign-up{ display: block; }
            .container form{ padding: 40px 24px; }
            .container form p{ display: block; text-align: center; margin-top: 20px; }
            .container form p a{ margin-left: 5px; color: #a8562d; font-weight: 600; }
        }
        @media (max-width:480px){ .container{ border-radius: 20px; } }
    </style>
</head>

<body>

    <!-- Area Alert Notifikasi -->
    <?php if(!empty($alert_message)): ?>
        <div class="alert alert-<?php echo $alert_type; ?>">
            <?php echo $alert_message; ?>
        </div>
    <?php endif; ?>

    <div class="container" id="container">
        <!-- FORM REGISTER (SIGN UP) -->
        <div class="form-container sign-up">
            <form action="" method="POST">
                <h1>Create Account</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                <span>or use your email for registration</span>
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="register">Sign Up</button>
                <p>Already have an account?<a href="#" id="show-login"> Sign In</a></p>
            </form>
        </div>

        <!-- FORM LOGIN (SIGN IN) -->
        <div class="form-container sign-in">
            <form action="" method="POST">
                <h1>Sign In</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                <span>or use your email password</span>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Sign In</button>
                <p>Don't have an account?<a href="#" id="show-register"> Create Account</a></p>
            </form>
        </div>

        <!-- PANEL TOGGLE SLIDE ANIMASI (DESKTOP ONLY) -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your personal details to use all of site features</p>
                    <button class="hidden" id="login" type="button">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Friend!</h1>
                    <p>Register with your personal details to use all of site features</p>
                    <button class="hidden" id="register" type="button">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mengambil Element DOM
        const container = document.getElementById('container');
        const registerBtn = document.getElementById('register');
        const loginBtn = document.getElementById('login');
        const showRegister = document.getElementById("show-register");
        const showLogin = document.getElementById("show-login");

        // Event handler klik panel desktop
        registerBtn.onclick = () => { container.classList.add("active"); };
        loginBtn.onclick = () => { container.classList.remove("active"); };

        // Event handler klik teks tautan mobile
        showRegister.onclick = function(e){
            e.preventDefault();
            container.classList.add("active");
        };

        showLogin.onclick = function(e){
            e.preventDefault();
            container.classList.remove("active");
        };
    </script>
</body>
</html>