<?php
session_start();
include_once 'database.php';

// Fungsi untuk membersihkan input
function clean_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Status task
define('OPEN_STATUS', 'open');
define('CLOSE_STATUS', 'close');

// Cek apakah ada task yang sedang diedit
$edit_task = "";
$edit_id = "";
$button_label = "Tambah";

if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    $query_edit = "SELECT tasklabel FROM tasks WHERE taskid = $edit_id";
    $result_edit = mysqli_query($conn, $query_edit);

    if ($row_edit = mysqli_fetch_assoc($result_edit)) {
        $edit_task = $row_edit['tasklabel'];
        $button_label = "Update";
    }
}

// Proses tambah & update task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    $task = clean_input($_POST['task']);
    $edit_id = filter_input(INPUT_POST, 'edit_id', FILTER_VALIDATE_INT);

    if (!empty($task)) {
        if ($edit_id) {
            // Jika ada ID, lakukan UPDATE
            $stmt = $conn->prepare("UPDATE tasks SET tasklabel = ? WHERE taskid = ?");
            $stmt->bind_param("si", $task, $edit_id);
        } else {
            // Jika tidak ada ID, lakukan INSERT (Tambah tugas baru)
            $tasktime = date("d-m-Y H:i:s");
            $taskstatus = OPEN_STATUS;
            $stmt = $conn->prepare("INSERT INTO tasks (tasklabel, taskstatus, tasktime) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $task, $taskstatus, $tasktime);
        }
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php");
    exit;
}

// Proses hapus task
if (isset($_GET['delete'])) {
    $taskid = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);

    if ($taskid) {
        $stmt = $conn->prepare("DELETE FROM tasks WHERE taskid = ?");
        $stmt->bind_param("i", $taskid);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit;
}
if (isset($_GET['done']) && isset($_GET['status'])) {
    $taskid = filter_input(INPUT_GET, 'done', FILTER_VALIDATE_INT);
    $status = ($_GET['status'] === OPEN_STATUS) ? CLOSE_STATUS : OPEN_STATUS;

    if ($taskid) {
        $stmt = $conn->prepare("UPDATE tasks SET taskstatus = ?, tasktime = NOW() WHERE taskid = ?");
        $stmt->bind_param("si", $status, $taskid);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit;
}

// Ambil semua data task
$q_open = "SELECT * FROM tasks WHERE taskstatus = 'open' ORDER BY taskid DESC";
$run_q_open = mysqli_query($conn, $q_open);

$q_done = "SELECT * FROM tasks WHERE taskstatus = 'close' ORDER BY taskid DESC";
$run_q_done = mysqli_query($conn, $q_done);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f5f5f5;
        display: flex;
        padding-left: 250px;
        height: 100vh;
    }

    .navbar {
        width: 250px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: #4e54c8;
        display: flex;
        flex-direction: column;
        padding-top: 20px;
        box-shadow: 2px 0px 10px rgba(0, 0, 0, 0.2);
    }

    .navbar .logo {
        text-align: center;
        margin-bottom: 20px;
        color: white;
    }

    .navbar a {
        color: white;
        text-decoration: none;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        transition: all 0.3s;
    }

    .navbar a:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .container {
        flex: 1;
        background: white;
        padding: 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
        overflow-y: auto;
    }

    .task-form {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .task-form input {
        flex: 1;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .task-form button {
        padding: 10px;
        background: #4e54c8;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .task-list {
        width: 100%;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
    }

    th {
        background: #4e54c8;
        color: white;
    }
    </style>
</head>

<body>
    <div class="navbar">
        <div class="logo">
            <h2>Menu</h2>
        </div>
        <a href="index.php"><i class="fa fa-home"></i> Home</a>
        <a href="about.php"><i class="fa fa-info-circle"></i> About</a>
        <a href="contact.php"><i class="fa fa-envelope"></i> Contact</a>
        <a href="logout.php" onclick="return confirm('Yakin ingin logout?');">
            <i class="fa fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="container">
        <h1>To-Do List</h1>
        <form class="task-form" action="" method="post">
            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
            <input type="text" name="task" placeholder="Tambahkan atau Edit Task"
                value="<?= htmlspecialchars($edit_task) ?>" required>
            <button type="submit" name="save"> <?= $button_label ?></button>
        </form>

        <h3>Daftar Tugas</h3>
        <table>
            <tr>
                <th>No</th>
                <th>Task</th>
                <th>Status</th>
                <th>Waktu</th>
                <th>Aksi</th>
            </tr>
            <?php $no = 1;
            while ($r = mysqli_fetch_array($run_q_open)) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($r['tasklabel']) ?></td>
                <td><?= $r['taskstatus'] ?></td>
                <td><?= $r['createdat'] ?></td>
                <td>
                    <input type="checkbox" <?= ($r['taskstatus'] === 'close') ? 'checked' : ''; ?>
                        onclick="window.location.href='?done=<?= $r['taskid'] ?>&status=<?= $r['taskstatus'] ?>'">
                    <a href="?edit=<?= $r['taskid'] ?>"><i class="fa fa-edit" style="color: green;"></i></a>
                    <a href="?delete=<?= $r['taskid'] ?>" onclick="return confirm('Yakin ingin menghapus?')">
                        <i class="fa fa-trash" style="color: red;"></i>
                    </a>
                </td>
            </tr>
            <?php } ?>
        </table>
        <h3>Laporan Tugas Selesai</h3>
        <table>
            <tr>
                <th>No</th>
                <th>Task</th>
                <th>Waktu Dibuat</th>
                <th>Selesai</th>
                <th>Aksi</th>
            </tr>
            <?php $no = 1;
            while ($r = mysqli_fetch_array($run_q_done)) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($r['tasklabel']) ?></td>
                <td><?= $r['createdat'] ?></td>
                <td><?= $r['tasktime'] ?></td>
                <td>
                    <a href="?delete=<?= $r['taskid'] ?>" onclick="return confirm('Yakin ingin menghapus task ini?')">
                        <i class="fa fa-trash" style="color: red;"></i>
                    </a>
                </td>
            </tr>
            <?php } ?>

        </table>

    </div>
</body>

</html>