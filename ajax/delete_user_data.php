<?php
header("Content-Type: text/plain; charset=utf-8");
session_start();
include '../config.php';

// 检查用户是否已登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    die('鉴权失败');
}

$num = isset($_POST['num']) ? intval($_POST['num']) : 0;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

// 确保用户只能删除自己的数据
if ($user_id != $_SESSION['user_id']) {
    die('权限不足');
}

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die('连接失败');
}

// 先检查这条记录是否属于当前用户
$check_sql = "SELECT uid FROM go_to_url WHERE num = $num";
$check_result = mysqli_query($conn, $check_sql);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    mysqli_close($conn);
    die('记录不存在');
}

$row = mysqli_fetch_assoc($check_result);
if ($row['uid'] != $user_id) {
    mysqli_close($conn);
    die('权限不足');
}

// 删除记录
$sql = "DELETE FROM go_to_url WHERE num = $num AND uid = $user_id";
if (!mysqli_query($conn, $sql)) {
    mysqli_close($conn);
    die('删除失败: ' . mysqli_error($conn));
}

mysqli_close($conn);

echo 'success';
?>