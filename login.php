<?php
session_start();
include './config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $password_md5 = md5($password);

    if ($password_md5 === $admin_password_md5) {
        $_SESSION['logged_in'] = true;
        header('Location: admin.php'); // 重定向到后台管理页面
        exit();
    } else {
        $error = '密码错误';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>登录 - 后台管理</title>
</head>

<body>
    <h2>登录</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label for="password">密码:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">登录</button>
    </form>
</body>

</html>