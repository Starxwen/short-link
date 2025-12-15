<?php
session_start();
include '../config.php';

// 检查是否是管理员或具有管理员权限的用户
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || (!isset($_SESSION['user_group']) || ($_SESSION['user_group'] !== 'admin' && $_SESSION['user_id'] !== 0))) {
    die(json_encode(['error' => '权限不足']));
}

header("Content-Type: application/json; charset=utf-8");

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$perPage = isset($_POST['perPage']) ? intval($_POST['perPage']) : 10;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$offset = ($page - 1) * $perPage;

if ($user_id <= 0) {
    die(json_encode(['error' => '无效的用户ID']));
}

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die(json_encode(['error' => '连接失败']));
}

// 获取用户信息
$user_sql = "SELECT username, email, ugroup FROM users WHERE uid = $user_id";
$user_result = mysqli_query($conn, $user_sql);
if (!$user_result || mysqli_num_rows($user_result) == 0) {
    mysqli_close($conn);
    die(json_encode(['error' => '用户不存在']));
}
$user_info = mysqli_fetch_assoc($user_result);

// 获取用户的短链接数据
$sql = "SELECT num, url, short_url, ip, add_date FROM go_to_url WHERE uid = $user_id ORDER BY add_date DESC LIMIT $offset, $perPage";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die(json_encode(['error' => '无法读取数据']));
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

// 获取用户短链接总数
$totalRows = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM go_to_url WHERE uid = $user_id"));
$totalPages = ceil($totalRows / $perPage);

mysqli_close($conn);

echo json_encode([
    'user_info' => $user_info,
    'rows' => $rows,
    'totalPages' => $totalPages
]);
?>