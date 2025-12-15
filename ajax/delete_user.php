<?php
session_start();
include '../config.php';

// 检查是否是管理员或具有管理员权限的用户
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || (!isset($_SESSION['user_group']) || ($_SESSION['user_group'] !== 'admin' && $_SESSION['user_id'] !== 0))) {
    die('权限不足');
}

header("Content-Type: text/plain; charset=utf-8");

$uid = isset($_POST['uid']) ? intval($_POST['uid']) : 0;

// 防止删除自己
if ($uid == $_SESSION['user_id']) {
    die('不能删除自己的账户');
}

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die('连接失败');
}

// 检查用户是否存在
$check_sql = "SELECT * FROM users WHERE uid = $uid";
$check_result = mysqli_query($conn, $check_sql);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    mysqli_close($conn);
    die('用户不存在');
}

// 开始事务
mysqli_begin_transaction($conn);

try {
    // 删除用户的所有短链接
    $delete_links_sql = "DELETE FROM go_to_url WHERE uid = $uid";
    if (!mysqli_query($conn, $delete_links_sql)) {
        throw new Exception('删除用户链接失败: ' . mysqli_error($conn));
    }

    // 删除用户
    $delete_user_sql = "DELETE FROM users WHERE uid = $uid";
    if (!mysqli_query($conn, $delete_user_sql)) {
        throw new Exception('删除用户失败: ' . mysqli_error($conn));
    }

    // 提交事务
    mysqli_commit($conn);
    mysqli_close($conn);
    
    echo 'success';
} catch (Exception $e) {
    // 回滚事务
    mysqli_rollback($conn);
    mysqli_close($conn);
    die($e->getMessage());
}
?>