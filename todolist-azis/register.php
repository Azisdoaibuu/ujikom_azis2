<?php
session_start();
include_once 'database.php';

// Fungsi untuk membersihkan input
function clean_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Proses register
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $nama = clean_input($_POST['nama']);
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);

    // Pastikan semua input terisi
    if (!empty($nama) && !empty($username) && !empty($password)) {
        // Cek apakah username sudah ada
        $stmt = $conn->prepare("SELECT id FROM tb_user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password sebelum disimpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan user baru dengan password yang di-hash
            $stmt = $conn->prepare("INSERT INTO tb_user (nama, username, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $nama, $username, $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Terjadi kesalahan saat registrasi!";
            }
        }
        $stmt->close();
    } else {
        $error = "Semua kolom harus diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(135deg, #4e54c8, #8f94fb);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .register-container {
        background: white;
        padding: 50px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        text-align: center;
        width: 300px;
    }

    .register-container h2 {
        margin-bottom: 20px;
    }

    .register-container input {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .register-container button {
        width: 100%;
        padding: 10px;
        background: #4e54c8;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .register-container button:hover {
        background: #3b42a1;
    }

    .error {
        color: red;
        margin-top: 10px;
    }
    </style>
</head>

<body>

    <div class="register-container">
        <h2>Register</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="" method="post">
            <input type="text" name="nama" placeholder="Nama Lengkap" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Register</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login</a></p>
    </div>

</body>

</html>