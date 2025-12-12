<?php
header("content-type:text/html;charset=utf-8");

// 数据库更新脚本 - 为现有短链接系统添加用户功能
// 此脚本用于更新已部署的系统

include 'config.php';

echo "<h2>星跃短链接系统 - 数据库更新脚本</h2>";
echo "<p>此脚本将为现有系统添加完整的用户功能</p>";

// 连接数据库
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
if (!$conn) {
    die('数据库连接失败: ' . mysqli_error($conn));
}

mysqli_query($conn, "set names utf8");
mysqli_select_db($conn, $dbname);

echo "<h3>步骤1: 检查现有表结构</h3>";

// 检查users表是否存在
$users_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($users_table_check) > 0) {
    echo "<p style='color: green;'>✓ users表已存在</p>";
    
    // 检查users表结构
    $users_columns = mysqli_query($conn, "SHOW COLUMNS FROM users");
    $existing_columns = [];
    while ($row = mysqli_fetch_assoc($users_columns)) {
        $existing_columns[] = $row['Field'];
    }
    
    // 检查并添加缺失的字段
    if (!in_array('email', $existing_columns) && !in_array('mail', $existing_columns)) {
        $alter_sql = "ALTER TABLE users ADD COLUMN `email` VARCHAR(100) DEFAULT '' AFTER `password`";
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p style='color: green;'>✓ 已添加email字段到users表</p>";
        } else {
            echo "<p style='color: red;'>✗ 添加email字段失败: " . mysqli_error($conn) . "</p>";
        }
    } elseif (in_array('mail', $existing_columns) && !in_array('email', $existing_columns)) {
        // 如果存在mail字段但不存在email字段，将mail字段重命名为email
        $alter_sql = "ALTER TABLE users CHANGE COLUMN `mail` `email` VARCHAR(100) DEFAULT ''";
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p style='color: green;'>✓ 已将mail字段重命名为email字段</p>";
        } else {
            echo "<p style='color: red;'>✗ 重命名mail字段失败: " . mysqli_error($conn) . "</p>";
        }
    } elseif (in_array('mail', $existing_columns) && in_array('email', $existing_columns)) {
        // 如果同时存在mail和email字段，删除mail字段
        $alter_sql = "ALTER TABLE users DROP COLUMN `mail`";
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p style='color: green;'>✓ 已删除重复的mail字段，保留email字段</p>";
        } else {
            echo "<p style='color: orange;'>⚠ 删除mail字段失败，但系统仍可正常工作</p>";
        }
    }
    
    if (!in_array('created_at', $existing_columns)) {
        $alter_sql = "ALTER TABLE users ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `ugroup`";
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p style='color: green;'>✓ 已添加created_at字段到users表</p>";
        } else {
            echo "<p style='color: red;'>✗ 添加created_at字段失败: " . mysqli_error($conn) . "</p>";
        }
    }
    
    if (!in_array('last_login', $existing_columns)) {
        $alter_sql = "ALTER TABLE users ADD COLUMN `last_login` DATETIME NULL AFTER `created_at`";
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p style='color: green;'>✓ 已添加last_login字段到users表</p>";
        } else {
            echo "<p style='color: red;'>✗ 添加last_login字段失败: " . mysqli_error($conn) . "</p>";
        }
    }
    
    if (!in_array('status', $existing_columns)) {
        $alter_sql = "ALTER TABLE users ADD COLUMN `status` TINYINT DEFAULT 1 COMMENT '1:正常 0:禁用' AFTER `last_login`";
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p style='color: green;'>✓ 已添加status字段到users表</p>";
        } else {
            echo "<p style='color: red;'>✗ 添加status字段失败: " . mysqli_error($conn) . "</p>";
        }
    }
} else {
    echo "<p style='color: orange;'>⚠ users表不存在，将创建新表</p>";
    
    // 创建users表
    $create_users_sql = "CREATE TABLE `users` (
        `uid` INT NOT NULL AUTO_INCREMENT,
        `username` VARCHAR(64) NOT NULL,
        `password` VARCHAR(128) NOT NULL,
        `email` VARCHAR(100) DEFAULT '',
        `ugroup` VARCHAR(32) DEFAULT 'user',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `last_login` DATETIME NULL,
        `status` TINYINT DEFAULT 1 COMMENT '1:正常 0:禁用',
        PRIMARY KEY (`uid`),
        UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    if (mysqli_query($conn, $create_users_sql)) {
        echo "<p style='color: green;'>✓ users表创建成功</p>";
    } else {
        echo "<p style='color: red;'>✗ users表创建失败: " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>步骤2: 检查go_to_url表结构</h3>";

// 检查go_to_url表
$goto_columns = mysqli_query($conn, "SHOW COLUMNS FROM go_to_url");
$existing_goto_columns = [];
while ($row = mysqli_fetch_assoc($goto_columns)) {
    $existing_goto_columns[] = $row['Field'];
}

if (!in_array('uid', $existing_goto_columns)) {
    $alter_sql = "ALTER TABLE go_to_url ADD COLUMN `uid` INT DEFAULT 0 AFTER `add_date`";
    if (mysqli_query($conn, $alter_sql)) {
        echo "<p style='color: green;'>✓ 已添加uid字段到go_to_url表</p>";
    } else {
        echo "<p style='color: red;'>✗ 添加uid字段失败: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ go_to_url表已包含uid字段</p>";
}

echo "<h3>步骤3: 检查管理员账户</h3>";

// 检查管理员账户是否存在
$admin_check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$admin_username' AND ugroup = 'admin'");
if (mysqli_num_rows($admin_check) == 0) {
    // 检查users表是否有email字段
    $users_columns = mysqli_query($conn, "SHOW COLUMNS FROM users");
    $existing_columns = [];
    while ($row = mysqli_fetch_assoc($users_columns)) {
        $existing_columns[] = $row['Field'];
    }
    
    // 重新获取更新后的字段列表
    $users_columns = mysqli_query($conn, "SHOW COLUMNS FROM users");
    $existing_columns = [];
    while ($row = mysqli_fetch_assoc($users_columns)) {
        $existing_columns[] = $row['Field'];
    }
    
    // 根据表结构动态构建插入语句
    if (in_array('email', $existing_columns)) {
        $insert_admin_sql = "INSERT INTO `users` (`username`, `password`, `email`, `ugroup`) VALUES ('$admin_username', '$admin_password', 'admin@localhost', 'admin')";
    } else {
        $insert_admin_sql = "INSERT INTO `users` (`username`, `password`, `ugroup`) VALUES ('$admin_username', '$admin_password', 'admin')";
    }
    
    if (mysqli_query($conn, $insert_admin_sql)) {
        echo "<p style='color: green;'>✓ 管理员账户创建成功</p>";
    } else {
        echo "<p style='color: red;'>✗ 管理员账户创建失败: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ 管理员账户已存在</p>";
}

echo "<h3>步骤4: 创建用户会话表</h3>";

// 创建用户会话表（可选，用于更好的会话管理）
$session_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'user_sessions'");
if (mysqli_num_rows($session_table_check) == 0) {
    $create_session_sql = "CREATE TABLE `user_sessions` (
        `session_id` VARCHAR(128) NOT NULL,
        `user_id` INT NOT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `user_agent` TEXT,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `expires_at` DATETIME NOT NULL,
        PRIMARY KEY (`session_id`),
        KEY `user_id` (`user_id`),
        KEY `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    if (mysqli_query($conn, $create_session_sql)) {
        echo "<p style='color: green;'>✓ user_sessions表创建成功</p>";
    } else {
        echo "<p style='color: red;'>✗ user_sessions表创建失败: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ user_sessions表已存在</p>";
}

echo "<h3>更新完成！</h3>";
echo "<p style='color: green;'><strong>数据库更新成功完成！</strong></p>";
echo "<p>现在系统支持以下功能：</p>";
echo "<ul>";
echo "<li>用户注册和登录</li>";
echo "<li>用户管理自己的短链接</li>";
echo "<li>管理员管理所有短链接</li>";
echo "<li>会话管理</li>";
echo "</ul>";
echo "<p><strong>重要提示：</strong></p>";
echo "<ol>";
echo "<li>请删除此更新脚本文件以确保安全</li>";
echo "<li>请确保register.php和user_panel.php文件已创建</li>";
echo "<li>测试用户注册和登录功能</li>";
echo "</ol>";

mysqli_close($conn);
?>