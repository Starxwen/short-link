<?php
session_start();
include '../config.php';

// 检查是否是管理员或具有管理员权限的用户
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || (!isset($_SESSION['user_group']) || ($_SESSION['user_group'] !== 'admin' && $_SESSION['user_id'] !== 0))) {
    die(json_encode(['error' => '权限不足']));
}

header("Content-Type: application/json; charset=utf-8");

// 获取POST数据
$uid = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$ugroup = isset($_POST['ugroup']) ? trim($_POST['ugroup']) : '';

// 验证数据
if (empty($uid) || empty($username) || empty($ugroup)) {
    die(json_encode(['error' => '用户ID、用户名和用户组是必填的']));
}

// 防止修改管理员账户和自己
if ($uid === 0 || $uid === $_SESSION['user_id']) {
    die(json_encode(['error' => '不能修改此账户']));
}

// 验证用户组
if ($ugroup !== 'admin' && $ugroup !== 'user') {
    die(json_encode(['error' => '无效的用户组']));
}

// 验证邮箱格式（如果邮箱不为空）
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['error' => '无效的邮箱格式']));
}

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die(json_encode(['error' => '连接失败']));
}

// 检查用户名是否已存在（排除当前用户）
$check_sql = "SELECT uid FROM users WHERE username = ? AND uid != ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "si", $username, $uid);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) > 0) {
    die(json_encode(['error' => '用户名已存在']));
}

// 检查邮箱是否已存在（排除当前用户）
$check_email_sql = "SELECT uid FROM users WHERE email = ? AND uid != ?";
$check_email_stmt = mysqli_prepare($conn, $check_email_sql);
mysqli_stmt_bind_param($check_email_stmt, "si", $email, $uid);
mysqli_stmt_execute($check_email_stmt);
$check_email_result = mysqli_stmt_get_result($check_email_stmt);

if (mysqli_num_rows($check_email_result) > 0) {
    die(json_encode(['error' => '邮箱已存在']));
}

// 更新用户信息
$update_sql = "UPDATE users SET username = ?, email = ?, ugroup = ? WHERE uid = ?";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, "sssi", $username, $email, $ugroup, $uid);

if (mysqli_stmt_execute($update_stmt)) {
    echo json_encode(['success' => '用户信息更新成功']);
} else {
    die(json_encode(['error' => '更新失败']));
}

mysqli_close($conn);
?>