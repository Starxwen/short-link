<?php
session_start();
include '../config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die(json_encode(['error' => '鉴权失败']));
}

header("Content-Type: application/json; charset=utf-8");

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$perPage = isset($_POST['perPage']) ? intval($_POST['perPage']) : 10;
$offset = ($page - 1) * $perPage;

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die(json_encode(['error' => '连接失败']));
}

// 检查click_count字段是否存在
$check_click_count = mysqli_query($conn, "SHOW COLUMNS FROM `go_to_url` LIKE 'click_count'");
$has_click_count = mysqli_num_rows($check_click_count) > 0;

if ($has_click_count) {
    $sql = "SELECT num, url, short_url, ip, add_date, uid, click_count FROM go_to_url LIMIT $offset, $perPage";
} else {
    $sql = "SELECT num, url, short_url, ip, add_date, uid, 0 as click_count FROM go_to_url LIMIT $offset, $perPage";
}
$result = mysqli_query($conn, $sql);
if (!$result) {
    die(json_encode(['error' => '无法读取数据']));
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

$totalRows = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM go_to_url"));
$totalPages = ceil($totalRows / $perPage);

mysqli_close($conn);

echo json_encode([
    'rows' => $rows,
    'totalPages' => $totalPages
]);
?>