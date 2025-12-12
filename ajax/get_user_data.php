<?php
session_start();
include '../config.php';

// 检查用户是否已登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    die(json_encode(['error' => '鉴权失败']));
}

header("Content-Type: application/json; charset=utf-8");

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$perPage = isset($_POST['perPage']) ? intval($_POST['perPage']) : 10;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$offset = ($page - 1) * $perPage;

// 确保用户只能访问自己的数据
if ($user_id != $_SESSION['user_id']) {
    die(json_encode(['error' => '权限不足']));
}

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die(json_encode(['error' => '连接失败']));
}

// 获取用户自己的短链接数据
$sql = "SELECT num, url, short_url, add_date FROM go_to_url WHERE uid = $user_id ORDER BY add_date DESC LIMIT $offset, $perPage";
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
    'rows' => $rows,
    'totalPages' => $totalPages
]);
?>