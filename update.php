<?php
header("content-type:text/html;charset=utf-8");

// 星跃短链接系统 - 统一数据库更新脚本
// 此脚本整合了所有数据库更新功能，包括：
// 1. 用户功能添加
// 2. 邮箱验证功能
// 3. 系统设置表创建

include 'config.php';

echo "<h2>星跃短链接系统 - 统一数据库更新脚本</h2>";
echo "<p>此脚本将为系统添加完整的用户功能、邮箱验证功能和系统设置管理</p>";

// 连接数据库
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
if (!$conn) {
    die('数据库连接失败: ' . mysqli_error($conn));
}

mysqli_query($conn, "set names utf8");
mysqli_select_db($conn, $dbname);

echo "<h3>步骤1: 检查并更新用户表结构</h3>";

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
    
    // 添加其他基础字段
    $basic_fields = [
        'created_at' => "DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `ugroup`",
        'last_login' => "DATETIME NULL AFTER `created_at`",
        'status' => "TINYINT DEFAULT 1 COMMENT '1:正常 0:禁用' AFTER `last_login`"
    ];
    
    foreach ($basic_fields as $field => $definition) {
        if (!in_array($field, $existing_columns)) {
            $alter_sql = "ALTER TABLE users ADD COLUMN `$field` $definition";
            if (mysqli_query($conn, $alter_sql)) {
                echo "<p style='color: green;'>✓ 已添加 $field 字段到users表</p>";
            } else {
                echo "<p style='color: red;'>✗ 添加 $field 字段失败: " . mysqli_error($conn) . "</p>";
            }
        }
    }
    
    // 添加邮箱验证相关字段
    $email_fields = [
        'email_verified' => "TINYINT DEFAULT 0 COMMENT '0:未验证 1:已验证'",
        'verification_code' => "VARCHAR(64) DEFAULT '' COMMENT '邮箱验证码'",
        'verification_expires' => "DATETIME NULL COMMENT '验证码过期时间'"
    ];
    
    foreach ($email_fields as $field => $definition) {
        if (!in_array($field, $existing_columns)) {
            $alter_sql = "ALTER TABLE users ADD COLUMN `$field` $definition";
            if (mysqli_query($conn, $alter_sql)) {
                echo "<p style='color: green;'>✓ 已添加 $field 字段到users表</p>";
            } else {
                echo "<p style='color: red;'>✗ 添加 $field 字段失败: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ $field 字段已存在</p>";
        }
    }
} else {
    echo "<p style='color: orange;'>⚠ users表不存在，将创建新表</p>";
    
    // 创建users表（包含所有字段）
    $create_users_sql = "CREATE TABLE `users` (
        `uid` INT NOT NULL AUTO_INCREMENT,
        `username` VARCHAR(64) NOT NULL,
        `password` VARCHAR(128) NOT NULL,
        `email` VARCHAR(100) DEFAULT '',
        `ugroup` VARCHAR(32) DEFAULT 'user',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `last_login` DATETIME NULL,
        `status` TINYINT DEFAULT 1 COMMENT '1:正常 0:禁用',
        `email_verified` TINYINT DEFAULT 0 COMMENT '0:未验证 1:已验证',
        `verification_code` VARCHAR(64) DEFAULT '' COMMENT '邮箱验证码',
        `verification_expires` DATETIME NULL COMMENT '验证码过期时间',
        PRIMARY KEY (`uid`),
        UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    if (mysqli_query($conn, $create_users_sql)) {
        echo "<p style='color: green;'>✓ users表创建成功</p>";
    } else {
        echo "<p style='color: red;'>✗ users表创建失败: " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>步骤2: 检查并更新go_to_url表结构</h3>";

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

echo "<h3>步骤5: 创建系统设置表</h3>";

// 检查settings表是否存在
$settings_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
if (mysqli_num_rows($settings_table_check) > 0) {
    echo "<p style='color: green;'>✓ settings表已存在</p>";
} else {
    // 创建settings表
    $create_settings_sql = "CREATE TABLE `settings` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `setting_key` VARCHAR(100) NOT NULL,
        `setting_value` TEXT,
        `setting_type` VARCHAR(20) DEFAULT 'text' COMMENT 'text, email, url, number, boolean',
        `setting_name` VARCHAR(100) NOT NULL COMMENT '显示名称',
        `setting_description` TEXT COMMENT '设置描述',
        `category` VARCHAR(50) DEFAULT 'general' COMMENT '设置分类',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`),
        KEY `category` (`category`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    if (mysqli_query($conn, $create_settings_sql)) {
        echo "<p style='color: green;'>✓ settings表创建成功</p>";
    } else {
        echo "<p style='color: red;'>✗ settings表创建失败: " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>步骤6: 插入默认系统设置</h3>";

// 默认设置数据
$default_settings = [
    [
        'setting_key' => 'site_name',
        'setting_value' => '星跃短链接',
        'setting_type' => 'text',
        'setting_name' => '网站名称',
        'setting_description' => '网站的名称，显示在页面标题和各个地方',
        'category' => 'general'
    ],
    [
        'setting_key' => 'site_url',
        'setting_value' => $my_url,
        'setting_type' => 'url',
        'setting_name' => '网站URL',
        'setting_description' => '网站的完整URL地址，必须以/结尾',
        'category' => 'general'
    ],
    [
        'setting_key' => 'smtp_host',
        'setting_value' => '',
        'setting_type' => 'text',
        'setting_name' => 'SMTP服务器',
        'setting_description' => '邮件发送服务器地址，如：smtp.gmail.com',
        'category' => 'email'
    ],
    [
        'setting_key' => 'smtp_port',
        'setting_value' => '587',
        'setting_type' => 'number',
        'setting_name' => 'SMTP端口',
        'setting_description' => '邮件发送服务器端口，通常为587或465',
        'category' => 'email'
    ],
    [
        'setting_key' => 'smtp_username',
        'setting_value' => '',
        'setting_type' => 'email',
        'setting_name' => 'SMTP用户名',
        'setting_description' => '邮件发送服务器的用户名，通常是邮箱地址',
        'category' => 'email'
    ],
    [
        'setting_key' => 'smtp_password',
        'setting_value' => '',
        'setting_type' => 'text',
        'setting_name' => 'SMTP密码',
        'setting_description' => '邮件发送服务器的密码或授权码',
        'category' => 'email'
    ],
    [
        'setting_key' => 'smtp_encryption',
        'setting_value' => 'tls',
        'setting_type' => 'text',
        'setting_name' => 'SMTP加密方式',
        'setting_description' => '邮件发送加密方式，可选：tls, ssl, none',
        'category' => 'email'
    ],
    [
        'setting_key' => 'email_from_address',
        'setting_value' => '',
        'setting_type' => 'email',
        'setting_name' => '发件人邮箱',
        'setting_description' => '系统发送邮件时使用的发件人邮箱地址',
        'category' => 'email'
    ],
    [
        'setting_key' => 'email_from_name',
        'setting_value' => '星跃短链接',
        'setting_type' => 'text',
        'setting_name' => '发件人名称',
        'setting_description' => '系统发送邮件时使用的发件人名称',
        'category' => 'email'
    ],
    [
        'setting_key' => 'email_verification_required',
        'setting_value' => '1',
        'setting_type' => 'boolean',
        'setting_name' => '注册需要邮箱验证',
        'setting_description' => '用户注册时是否需要进行邮箱验证',
        'category' => 'registration'
    ],
    [
        'setting_key' => 'allow_registration',
        'setting_value' => '1',
        'setting_type' => 'boolean',
        'setting_name' => '允许用户注册',
        'setting_description' => '是否允许新用户注册',
        'category' => 'registration'
    ]
];

// 插入默认设置
foreach ($default_settings as $setting) {
    // 检查设置是否已存在
    $check_sql = "SELECT id FROM settings WHERE setting_key = '" . $setting['setting_key'] . "'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) == 0) {
        $insert_sql = "INSERT INTO settings (setting_key, setting_value, setting_type, setting_name, setting_description, category) VALUES (
            '" . $setting['setting_key'] . "',
            '" . $setting['setting_value'] . "',
            '" . $setting['setting_type'] . "',
            '" . $setting['setting_name'] . "',
            '" . $setting['setting_description'] . "',
            '" . $setting['category'] . "'
        )";
        
        if (mysqli_query($conn, $insert_sql)) {
            echo "<p style='color: green;'>✓ 已添加设置: " . $setting['setting_name'] . "</p>";
        } else {
            echo "<p style='color: red;'>✗ 添加设置失败: " . $setting['setting_name'] . " - " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ 设置已存在: " . $setting['setting_name'] . "</p>";
    }
}

echo "<h3>更新完成！</h3>";
echo "<p style='color: green;'><strong>数据库更新成功完成！</strong></p>";
echo "<p>现在系统支持以下功能：</p>";
echo "<ul>";
echo "<li>用户注册和登录</li>";
echo "<li>用户管理自己的短链接</li>";
echo "<li>管理员管理所有短链接</li>";
echo "<li>会话管理</li>";
echo "<li>系统设置管理</li>";
echo "<li>SMTP邮件配置</li>";
echo "<li>注册设置管理</li>";
echo "<li>邮箱验证功能</li>";
echo "</ul>";
echo "<p><strong>重要提示：</strong></p>";
echo "<ol>";
echo "<li>请删除此更新脚本文件以确保安全</li>";
echo "<li>请确保register.php和user_panel.php文件已创建</li>";
echo "<li>测试用户注册和登录功能</li>";
echo "<li>在管理面板的系统设置中配置SMTP设置以启用邮件功能</li>";
echo "<li>测试注册和邮箱验证功能</li>";
echo "</ol>";

mysqli_close($conn);
?>