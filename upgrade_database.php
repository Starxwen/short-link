<?php
header("content-type:text/html;charset=utf-8");

include 'config.php';

// 连接数据库
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);

if (!$conn) {
    die('连接失败: ' . mysqli_error($conn));
}

mysqli_query($conn, "set names utf8");
mysqli_select_db($conn, $dbname);

// 添加类型字段
$alter_sql = "ALTER TABLE `go_to_url` ADD COLUMN `type` VARCHAR(10) DEFAULT 'url' AFTER `url`";

if (mysqli_query($conn, $alter_sql)) {
    echo "成功添加类型字段<br />";
} else {
    // 检查字段是否已存在
    $check_field_sql = "SHOW COLUMNS FROM `go_to_url` LIKE 'type'";
    $result = mysqli_query($conn, $check_field_sql);
    if (mysqli_num_rows($result) > 0) {
        echo "类型字段已存在<br />";
    } else {
        echo "添加类型字段失败: " . mysqli_error($conn) . "<br />";
    }
}

// 为现有数据设置默认类型
$update_sql = "UPDATE `go_to_url` SET `type` = 'url' WHERE `type` IS NULL OR `type` = ''";

if (mysqli_query($conn, $update_sql)) {
    echo "成功为现有数据设置默认类型<br />";
} else {
    echo "设置默认类型失败: " . mysqli_error($conn) . "<br />";
}

mysqli_close($conn);

echo "数据库升级完成！<br />";
echo "<a href='new.php'>返回首页</a>";
?>