<?php
session_start();
include './config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_md5 = md5($password);

    if ($username == $admin_username) {
        if ($password_md5 === $admin_password) {
            $_SESSION['logged_in'] = true;
            header('Location: admin.php'); // 重定向到后台管理页面
            exit();
        } else {
            $error = '密码错误';
        }
    } else {
        $error = '个人登录暂未开启';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>登录 - 短链接</title>
</head>

<body>
    <h2>登录</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label for="username">账户:</label>
        <input type="username" id="username" name="username" required>
        <br><br>
        <label for="password">密码:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <button type="submit">登录</button>
    </form>
</body>

</html>