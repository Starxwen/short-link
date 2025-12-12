<?php
header("content-type:text/html;charset=utf-8");

// 检查是否已经安装
if (file_exists("install.lock") && trim(file_get_contents("install.lock")) === '已安装') {
    die('该程序已经安装过了，如需重新安装请删除install.lock文件');
}

// 如果是POST请求，处理配置写入
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取表单数据
    $dbhost = $_POST['dbhost'] ?? 'localhost';
    $dbuser = $_POST['dbuser'] ?? 'short_url';
    $dbpass = $_POST['dbpass'] ?? '';
    $dbname = $_POST['dbname'] ?? 'short_url';
    $admin_username = $_POST['admin_username'] ?? 'admin';
    $admin_password = md5($_POST['admin_password'] ?? '123456');
    $my_url = $_POST['my_url'] ?? '';

    // 验证必要字段
    if (empty($dbhost) || empty($dbuser) || empty($dbname) || empty($my_url)) {
        die('错误：请填写所有必填字段');
    }

    // 验证URL格式
    if (!filter_var($my_url, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\/.+\/$/', $my_url)) {
        die('错误：网站地址格式不正确，必须以 http:// 或 https:// 开头，且以 / 结尾');
    }

    // 生成配置文件内容
    $config_content = "<?php\n";
    $config_content .= "//此文件为配置文件\n";
    $config_content .= "session_start();\n";
    $config_content .= "//请务必在运行install.php之前，填写本文件，删除install目录后，必须保留本文件，否则程序无法运行\n";
    $config_content .= "\$dbhost = '" . addslashes($dbhost) . "'; // mysql服务器主机地址\n";
    $config_content .= "\$dbuser = '" . addslashes($dbuser) . "'; // mysql用户名\n";
    $config_content .= "\$dbpass = '" . addslashes($dbpass) . "'; // mysql密码\n";
    $config_content .= "\$dbname = '" . addslashes($dbname) . "'; //mysql数据库名称\n";
    $config_content .= "\$admin_username = '" . addslashes($admin_username) . "'; //admin管理面板的用户名，后面会更新到数据库里\n";
    $config_content .= "\$admin_password = '" . addslashes($admin_password) . "'; //admin管理面板的密码，需要填入密码的md5值\n";
    $config_content .= "\$my_url = '" . addslashes($my_url) . "'; //当前项目根目录网址，例如：http://xxx.com/short_url/、http://aaa.top/、https://b.cn/、http://a.b.com/s/，记得要加http/https协议，末尾加\"/\"\n";
    $config_content .= "?>";

    // 写入配置文件
    $config_file = '../config.php';
    if (file_put_contents($config_file, $config_content) === false) {
        die('错误：无法写入配置文件，请检查目录权限');
    }

    // 包含配置文件进行数据库操作
    include $config_file;

    // 连接数据库
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass);

    if (!$conn) {
        // 删除已创建的配置文件
        unlink($config_file);
        die('数据库连接失败: ' . mysqli_connect_error());
    }

    echo '数据库连接成功，开始新建数据表……<br />';

    // 创建数据库（如果不存在）
    if (!mysqli_select_db($conn, $dbname)) {
        $create_db_sql = "CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
        if (!mysqli_query($conn, $create_db_sql)) {
            die('数据库创建失败: ' . mysqli_error($conn));
        }
        mysqli_select_db($conn, $dbname);
        echo '数据库创建成功<br />';
    }

    // 创建链接表
    $sql = "CREATE TABLE IF NOT EXISTS `go_to_url`( " .
        "`num` INT NOT NULL AUTO_INCREMENT, " .
        "`url` TEXT NOT NULL, " .
        "`short_url` VARCHAR(100) NOT NULL, " .
        "`ip` VARCHAR(50) NOT NULL, " .
        "`add_date` DATETIME NOT NULL, " .
        "`uid` INT DEFAULT 0, " .
        "PRIMARY KEY (`num`), " .
        "UNIQUE KEY `short_url` (`short_url`)" .
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    echo '正在创建链接表……<br />';
    $retval = mysqli_query($conn, $sql);
    if (!$retval) {
        die('链接表创建失败: ' . mysqli_error($conn));
    }

    // 创建用户表（修正表名和字段）
    $sql2 = "CREATE TABLE IF NOT EXISTS `users`( " .
        "`uid` INT NOT NULL AUTO_INCREMENT, " .
        "`username` VARCHAR(64) NOT NULL, " .
        "`password` VARCHAR(128) NOT NULL, " .
        "`email` VARCHAR(32) DEFAULT '', " .
        "`ugroup` VARCHAR(32) DEFAULT 'user', " .
        "PRIMARY KEY (`uid`), " .
        "UNIQUE KEY `username` (`username`)" .
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    echo '正在创建用户表……<br />';
    $retval = mysqli_query($conn, $sql2);
    if (!$retval) {
        die('用户表创建失败: ' . mysqli_error($conn));
    }

    // 插入管理员用户
    $insert_admin_sql = "INSERT INTO `users` (`username`, `password`, `email`, `ugroup`) VALUES ('$admin_username', '$admin_password', 'admin@localhost', 'admin')";
    if (!mysqli_query($conn, $insert_admin_sql)) {
        echo '注意：管理员用户创建失败或已存在<br />';
    }

    echo "数据表创建成功！<br />";

    // 创建安装锁文件
    if (@file_put_contents("install.lock", '已安装') === false) {
        echo '警告：无法创建安装锁文件，请手动创建install.lock文件并写入"已安装"<br />';
    }

    echo "安装已经完成！<br />";
    echo "您现在可以开始使用了。<br />";

    mysqli_close($conn);

    // 返回成功信息给前端
    echo "success";
    exit;
}

// 如果不是POST请求，显示安装表单
?>
<!DOCTYPE html>
<html>

<head>
    <title>系统安装</title>
</head>

<body>
    <p>请通过安装向导页面进行安装。</p>
    <a href="install.html">前往安装向导</a>
</body>

</html>
<?php
// 结束非POST请求的处理
exit;
?>