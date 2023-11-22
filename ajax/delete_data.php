<?php
// 在这里包含你的数据库配置文件
include '../config.php';

// 检查 POST 请求中是否设置了 'num' 参数
if (isset($_POST['num'])) {
    // 对输入进行过滤，防止 SQL 注入
    $num = mysqli_real_escape_string($conn, $_POST['num']);

    // 建立与数据库的连接
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    if (!$conn) {
        die('连接失败: ' . mysqli_error($conn));
    }

    mysqli_query($conn, "set names utf8");

    // 在删除之前检查记录是否存在
    $checkSql = "SELECT * FROM go_to_url WHERE num = '$num'";
    $checkResult = mysqli_query($conn, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        // 记录存在，继续执行删除操作
        $deleteSql = "DELETE FROM go_to_url WHERE num = '$num'";
        $deleteResult = mysqli_query($conn, $deleteSql);

        if ($deleteResult) {
            // 发送成功响应
            echo json_encode(['status' => 'success', 'message' => '删除成功']);
        } else {
            // 发送错误响应
            echo json_encode(['status' => 'error', 'message' => '删除失败']);
        }
    } else {
        // 记录不存在，发送错误响应
        echo json_encode(['status' => 'error', 'message' => '记录不存在']);
    }

    // 关闭数据库连接
    mysqli_close($conn);
} else {
    // 如果 'num' 参数未设置，则发送错误响应
    echo json_encode(['status' => 'error', 'message' => '缺少参数']);
}
?>