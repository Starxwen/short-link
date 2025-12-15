<?php
session_start();
include '../config.php';

// 检查是否是管理员或具有管理员权限的用户
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || (!isset($_SESSION['user_group']) || ($_SESSION['user_group'] !== 'admin' && $_SESSION['user_id'] !== 0))) {
    die(json_encode(['error' => '权限不足']));
}

header("Content-Type: application/json; charset=utf-8");

$uid = isset($_POST['uid']) ? intval($_POST['uid']) : 0;

if ($uid <= 0) {
    die(json_encode(['error' => '无效的用户ID']));
}

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die(json_encode(['error' => '连接失败']));
}

// 获取用户名
$sql = "SELECT username FROM users WHERE uid = $uid";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die(json_encode(['error' => '无法读取数据']));
}

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode(['username' => $row['username']]);
} else {
    echo json_encode(['error' => '用户不存在']);
}

mysqli_close($conn);
?>