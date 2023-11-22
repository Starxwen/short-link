<?php
header("content-type:text/html;charset=utf-8");

include '../config.php';

// 添加判断是否已经安装的逻辑
if (file_exists("install.lock") && trim(file_get_contents("install.lock")) === '已安装') {
    die('该程序已经安装过了，如需重新安装请删除install.lock文件');
}

$conn = mysqli_connect($dbhost, $dbuser, $dbpass);

if (!$conn) {
    die('数据库连接失败: ' . mysqli_error($conn));
}

echo '数据库连接成功，开始新建数据表……<br />';

$sql = "CREATE TABLE go_to_url( " .
    "num INT NOT NULL AUTO_INCREMENT, " .
    "url VARCHAR(450) NOT NULL, " .
    "ip VARCHAR(50) NOT NULL, " .
    "add_date DATE, " .
    "PRIMARY KEY ( num ))ENGINE=InnoDB DEFAULT CHARSET=utf8; ";

mysqli_select_db($conn, $dbname);

$retval = mysqli_query($conn, $sql);
if (!$retval) {
    die('数据表创建失败: ' . mysqli_error($conn));
}

echo "数据表创建成功！您现在可以开始使用了。\n";
@file_put_contents("install.lock", '已安装');

echo "安装已经完成，返回即可使用\n";
echo '<a href="/">返回首页</a> <a href="/admin.php">后台管理</a>';

mysqli_close($conn);
?>