<?php
header("content-type:text/html;charset=utf-8");

// 检查配置文件是否存在
if (!file_exists('../config.php')) {
    die('错误：配置文件不存在，请确保系统已正确安装');
}

// 包含配置文件
include '../config.php';

// 连接数据库
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$conn) {
    die('数据库连接失败: ' . mysqli_connect_error());
}

echo '数据库连接成功，开始升级数据库结构……<br />';

// 升级 go_to_url 表
echo '<h3>升级 go_to_url 表</h3>';

// 检查 click_count 字段是否存在
$check_click_count = mysqli_query($conn, "SHOW COLUMNS FROM `go_to_url` LIKE 'click_count'");
if (mysqli_num_rows($check_click_count) == 0) {
    $add_click_count = "ALTER TABLE `go_to_url` ADD COLUMN `click_count` INT DEFAULT 0";
    if (mysqli_query($conn, $add_click_count)) {
        echo '✓ 添加 click_count 字段成功<br />';
    } else {
        echo '✗ 添加 click_count 字段失败: ' . mysqli_error($conn) . '<br />';
    }
} else {
    echo '✓ click_count 字段已存在<br />';
}

// 检查 uid 索引是否存在
$check_uid_index = mysqli_query($conn, "SHOW INDEX FROM `go_to_url` WHERE Key_name = 'uid'");
if (mysqli_num_rows($check_uid_index) == 0) {
    $add_uid_index = "ALTER TABLE `go_to_url` ADD KEY `uid` (`uid`)";
    if (mysqli_query($conn, $add_uid_index)) {
        echo '✓ 添加 uid 索引成功<br />';
    } else {
        echo '✗ 添加 uid 索引失败: ' . mysqli_error($conn) . '<br />';
    }
} else {
    echo '✓ uid 索引已存在<br />';
}

// 升级 users 表
echo '<h3>升级 users 表</h3>';

// 检查并修改 email 字段长度
$check_email = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'email'");
if ($email_row = mysqli_fetch_assoc($check_email)) {
    if ($email_row['Type'] != 'varchar(255)') {
        $modify_email = "ALTER TABLE `users` MODIFY COLUMN `email` VARCHAR(255) DEFAULT ''";
        if (mysqli_query($conn, $modify_email)) {
            echo '✓ 修改 email 字段长度成功<br />';
        } else {
            echo '✗ 修改 email 字段长度失败: ' . mysqli_error($conn) . '<br />';
        }
    } else {
        echo '✓ email 字段长度正确<br />';
    }
}

// 检查 email_verified 字段是否存在
$check_email_verified = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'email_verified'");
if (mysqli_num_rows($check_email_verified) == 0) {
    $add_email_verified = "ALTER TABLE `users` ADD COLUMN `email_verified` TINYINT(1) DEFAULT 1";
    if (mysqli_query($conn, $add_email_verified)) {
        echo '✓ 添加 email_verified 字段成功<br />';
    } else {
        echo '✗ 添加 email_verified 字段失败: ' . mysqli_error($conn) . '<br />';
    }
} else {
    echo '✓ email_verified 字段已存在<br />';
}

// 检查 verification_code 字段是否存在
$check_verification_code = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'verification_code'");
if (mysqli_num_rows($check_verification_code) == 0) {
    $add_verification_code = "ALTER TABLE `users` ADD COLUMN `verification_code` VARCHAR(32) DEFAULT ''";
    if (mysqli_query($conn, $add_verification_code)) {
        echo '✓ 添加 verification_code 字段成功<br />';
    } else {
        echo '✗ 添加 verification_code 字段失败: ' . mysqli_error($conn) . '<br />';
    }
} else {
    echo '✓ verification_code 字段已存在<br />';
}

// 检查 verification_expires 字段是否存在
$check_verification_expires = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'verification_expires'");
if (mysqli_num_rows($check_verification_expires) == 0) {
    $add_verification_expires = "ALTER TABLE `users` ADD COLUMN `verification_expires` DATETIME DEFAULT NULL";
    if (mysqli_query($conn, $add_verification_expires)) {
        echo '✓ 添加 verification_expires 字段成功<br />';
    } else {
        echo '✗ 添加 verification_expires 字段失败: ' . mysqli_error($conn) . '<br />';
    }
} else {
    echo '✓ verification_expires 字段已存在<br />';
}

// 检查 email 索引是否存在
$check_email_index = mysqli_query($conn, "SHOW INDEX FROM `users` WHERE Key_name = 'email'");
if (mysqli_num_rows($check_email_index) == 0) {
    $add_email_index = "ALTER TABLE `users` ADD KEY `email` (`email`)";
    if (mysqli_query($conn, $add_email_index)) {
        echo '✓ 添加 email 索引成功<br />';
    } else {
        echo '✗ 添加 email 索引失败: ' . mysqli_error($conn) . '<br />';
    }
} else {
    echo '✓ email 索引已存在<br />';
}

// 创建 settings 表
echo '<h3>创建/升级 settings 表</h3>';

$check_settings_table = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
if (mysqli_num_rows($check_settings_table) == 0) {
    // 创建 settings 表
    $create_settings = "CREATE TABLE `settings`( " .
        "`id` INT NOT NULL AUTO_INCREMENT, " .
        "`setting_key` VARCHAR(100) NOT NULL, " .
        "`setting_value` TEXT, " .
        "`setting_name` VARCHAR(100), " .
        "`category` VARCHAR(50) DEFAULT 'general', " .
        "`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, " .
        "PRIMARY KEY (`id`), " .
        "UNIQUE KEY `setting_key` (`setting_key`)" .
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    if (mysqli_query($conn, $create_settings)) {
        echo '✓ 创建 settings 表成功<br />';
        
        // 插入默认设置
        insert_default_settings($conn);
    } else {
        echo '✗ 创建 settings 表失败: ' . mysqli_error($conn) . '<br />';
    }
} else {
    echo '✓ settings 表已存在<br />';
    
    // 检查并添加缺失的字段
    $check_category = mysqli_query($conn, "SHOW COLUMNS FROM `settings` LIKE 'category'");
    if (mysqli_num_rows($check_category) == 0) {
        $add_category = "ALTER TABLE `settings` ADD COLUMN `category` VARCHAR(50) DEFAULT 'general'";
        if (mysqli_query($conn, $add_category)) {
            echo '✓ 添加 category 字段成功<br />';
        } else {
            echo '✗ 添加 category 字段失败: ' . mysqli_error($conn) . '<br />';
        }
    } else {
        echo '✓ category 字段已存在<br />';
    }
    
    // 插入缺失的默认设置
    insert_default_settings($conn);
}

// 更新现有设置的值
echo '<h3>更新系统设置</h3>';
$update_site_url = "UPDATE `settings` SET `setting_value` = '$my_url' WHERE `setting_key` = 'site_url'";
if (mysqli_query($conn, $update_site_url)) {
    echo '✓ 更新网站URL设置成功<br />';
} else {
    echo '✗ 更新网站URL设置失败: ' . mysqli_error($conn) . '<br />';
}

echo '<h3>数据库升级完成！</h3>';
echo '<p>所有缺失的字段和表已添加，系统现在应该能够正常使用所有功能。</p>';
echo '<p><a href="../">返回首页</a></p>';

mysqli_close($conn);

// 插入默认设置的函数
function insert_default_settings($conn) {
    global $my_url;
    
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
            echo '注意：设置项 ' . $setting[0] . ' 插入失败<br />';
        }
    }
    echo '✓ 默认设置插入完成<br />';
}
?>