<?php
header("Content-Type: text/plain; charset=utf-8");
session_start();
include '../config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die('鉴权失败');
}

$num = isset($_POST['num']) ? intval($_POST['num']) : 0;

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die('连接失败');
}

$sql = "DELETE FROM go_to_url WHERE num = $num";
if (!mysqli_query($conn, $sql)) {
    die('删除失败: ' . mysqli_error($conn));
}

mysqli_close($conn);

echo 'success';
?>