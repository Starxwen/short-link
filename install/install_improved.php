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

    // 创建链接表（改进版）
    $sql = "CREATE TABLE IF NOT EXISTS `go_to_url`( " .
        "`num` INT NOT NULL AUTO_INCREMENT, " .
        "`url` TEXT NOT NULL, " .
        "`short_url` VARCHAR(100) NOT NULL, " .
        "`ip` VARCHAR(50) NOT NULL, " .
        "`add_date` DATETIME NOT NULL, " .
        "`uid` INT DEFAULT 0, " .
        "`click_count` INT DEFAULT 0, " .
        "PRIMARY KEY (`num`), " .
        "UNIQUE KEY `short_url` (`short_url`), " .
        "KEY `uid` (`uid`)" .
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    echo '正在创建链接表……<br />';
    $retval = mysqli_query($conn, $sql);
    if (!$retval) {
        die('链接表创建失败: ' . mysqli_error($conn));
    }

    // 检查并添加缺失的字段（用于升级现有安装）
    $alter_sql = "ALTER TABLE `go_to_url` ADD COLUMN `click_count` INT DEFAULT 0";
    mysqli_query($conn, $alter_sql); // 忽略错误，字段可能已存在

    // 创建用户表（改进版）
    $sql2 = "CREATE TABLE IF NOT EXISTS `users`( " .
        "`uid` INT NOT NULL AUTO_INCREMENT, " .
        "`username` VARCHAR(64) NOT NULL, " .
        "`password` VARCHAR(128) NOT NULL, " .
        "`email` VARCHAR(255) DEFAULT '', " .
        "`ugroup` VARCHAR(32) DEFAULT 'user', " .
        "`email_verified` TINYINT(1) DEFAULT 1, " .
        "`verification_code` VARCHAR(32) DEFAULT '', " .
        "`verification_expires` DATETIME DEFAULT NULL, " .
        "PRIMARY KEY (`uid`), " .
        "UNIQUE KEY `username` (`username`), " .
        "KEY `email` (`email`)" .
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    echo '正在创建用户表……<br />';
    $retval = mysqli_query($conn, $sql2);
    if (!$retval) {
        die('用户表创建失败: ' . mysqli_error($conn));
    }

    // 检查并添加缺失的字段（用于升级现有安装）
    $alter_sqls = [
        "ALTER TABLE `users` MODIFY COLUMN `email` VARCHAR(255) DEFAULT ''",
        "ALTER TABLE `users` ADD COLUMN `email_verified` TINYINT(1) DEFAULT 1",
        "ALTER TABLE `users` ADD COLUMN `verification_code` VARCHAR(32) DEFAULT ''",
        "ALTER TABLE `users` ADD COLUMN `verification_expires` DATETIME DEFAULT NULL"
    ];
    
    foreach ($alter_sqls as $alter_sql) {
        mysqli_query($conn, $alter_sql); // 忽略错误，字段可能已存在
    }

    // 创建系统设置表
    $sql3 = "CREATE TABLE IF NOT EXISTS `settings`( " .
        "`id` INT NOT NULL AUTO_INCREMENT, " .
        "`setting_key` VARCHAR(100) NOT NULL, " .
        "`setting_value` TEXT, " .
        "`setting_name` VARCHAR(100), " .
        "`category` VARCHAR(50) DEFAULT 'general', " .
        "`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, " .
        "PRIMARY KEY (`id`), " .
        "UNIQUE KEY `setting_key` (`setting_key`)" .
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    echo '正在创建系统设置表……<br />';
    $retval = mysqli_query($conn, $sql3);
    if (!$retval) {
        die('系统设置表创建失败: ' . mysqli_error($conn));
    }

    // 插入默认系统设置
    $default_settings = [
        ['site_name', '星跃短链接', '网站名称', 'general'],
        ['site_url', $my_url, '网站URL', 'general'],
        ['allow_registration', '1', '允许用户注册', 'registration'],
        ['email_verification_required', '0', '需要邮箱验证', 'registration'],
        ['smtp_host', '', 'SMTP服务器', 'email'],
        ['smtp_port', '587', 'SMTP端口', 'email'],
        ['smtp_username', '', 'SMTP用户名', 'email'],
        ['smtp_password', '', 'SMTP密码', 'email'],
        ['smtp_encryption', 'tls', '加密方式', 'email'],
        ['email_from_address', '', '发件人邮箱', 'email'],
        ['email_from_name', '', '发件人名称', 'email']
    ];

    echo '正在插入默认系统设置……<br />';
    foreach ($default_settings as $setting) {
        $insert_setting_sql = "INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_name`, `category`) " .
            "VALUES ('" . addslashes($setting[0]) . "', '" . addslashes($setting[1]) . "', '" . 
            addslashes($setting[2]) . "', '" . addslashes($setting[3]) . "') " .
            "ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)";
        
        if (!mysqli_query($conn, $insert_setting_sql)) {
            echo '注意：设置项 ' . $setting[0] . ' 插入失败或已存在<br />';
        }
    }

    // 插入管理员用户
    $insert_admin_sql = "INSERT INTO `users` (`username`, `password`, `email`, `ugroup`, `email_verified`) " .
        "VALUES ('$admin_username', '$admin_password', 'admin@localhost', 'admin', 1) " .
        "ON DUPLICATE KEY UPDATE `password` = '$admin_password'";
    
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
    <a href="index.html">前往安装向导</a>
</body>

</html>
<?php
// 结束非POST请求的处理
exit;
?>