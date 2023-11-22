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
//创建链接表
$sql = "CREATE TABLE go_to_url( " .
    "num INT NOT NULL AUTO_INCREMENT, " .
    "url VARCHAR(450) NOT NULL, " .
    "short_url VARCHAR(450) NOT NULL, " .
    "ip VARCHAR(50) NOT NULL, " .
    "add_date DATE, " .
    "uid DATE, " .
    "PRIMARY KEY ( num ))ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
//创建用户表
$sql2 = "CREATE TABLE go_to_url( " .
    "uid INT NOT NULL AUTO_INCREMENT, " .
    "username VARCHAR(64) NOT NULL, " .
    "password VARCHAR(128) NOT NULL, " .
    "email VARCHAR(32) NOT NULL, " .
    "ugroup VARCHAR(32) NOT NULL, " .
    "PRIMARY KEY ( uid ))ENGINE=InnoDB DEFAULT CHARSET=utf8; ";

mysqli_select_db($conn, $dbname);
echo '新建数据表，正在创建链接表……<br />';
$retval = mysqli_query($conn, $sql);
if (!$retval) {
    die('链接表创建失败: ' . mysqli_error($conn));
}

echo '新建数据表，正在创建用户表……<br />';
$retval = mysqli_query($conn, $sql2);
if (!$retval) {
    die('用户表创建失败: ' . mysqli_error($conn));
}

echo "数据表创建成功！您现在可以开始使用了。\n";
@file_put_contents("install.lock", '已安装');

echo "安装已经完成，返回即可使用<br />";

echo '<a href="/">返回首页</a> <a href="/admin.php">后台管理</a>';

mysqli_close($conn);
?>