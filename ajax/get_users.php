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
$offset = ($page - 1) * $perPage;

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die(json_encode(['error' => '连接失败']));
}

// 获取用户列表
$sql = "SELECT uid, username, email, ugroup FROM users ORDER BY uid DESC LIMIT $offset, $perPage";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die(json_encode(['error' => '无法读取数据']));
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    // 获取每个用户的链接数量
    $count_sql = "SELECT COUNT(*) as url_count FROM go_to_url WHERE uid = " . $row['uid'];
    $count_result = mysqli_query($conn, $count_sql);
    $url_count = 0;
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $url_count = $count_row['url_count'];
    }
    $row['url_count'] = $url_count;
    $rows[] = $row;
}

// 获取用户总数
$totalRows = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
$totalPages = ceil($totalRows / $perPage);

mysqli_close($conn);

echo json_encode([
    'rows' => $rows,
    'totalPages' => $totalPages
]);
?>