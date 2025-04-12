<?php
session_start();
include_once 'database.php';

// Fungsi untuk membersihkan input
function clean_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Jika sudah login, arahkan ke index.php
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Proses login
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Ambil data user berdasarkan username
        $stmt = $conn->prepare("SELECT id, nama, username, password, role FROM tb_user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) { // Gunakan password_verify
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                header("Location: index.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
        $stmt->close();
    } else {
        $error = "Username dan password harus diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Arial', sans-serif;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background: linear-gradient(135deg, #4e54c8, #8f94fb);
    }

    .login-container {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        text-align: center;
        width: 350px;
    }

    .login-container h2 {
        margin-bottom: 20px;
        color: #333;
    }

    .login-container input {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        transition: 0.3s;
    }

    .login-container input:focus {
        border-color: #4e54c8;
        box-shadow: 0 0 8px rgba(78, 84, 200, 0.5);
    }

    .login-container button {
        width: 100%;
        padding: 12px;
        background: #4e54c8;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: 0.3s;
    }

    .login-container button:hover {
        background: #3b42a1;
    }

    .error {
        color: red;
        margin-bottom: 15px;
    }

    .register-link {
        margin-top: 10px;
        font-size: 14px;
    }

    .register-link a {
        color: #4e54c8;
        text-decoration: none;
    }

    .register-link a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <p class="register-link">Belum punya akun? <a href="register.php">Daftar</a></p>
    </div>

</body>

</html>