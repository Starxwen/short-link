<?php
header("content-type:text/html;charset=utf-8");

// 创建系统设置表的脚本
include 'config.php';

echo "<h2>星跃短链接系统 - 创建系统设置表</h2>";

// 连接数据库
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
if (!$conn) {
    die('数据库连接失败: ' . mysqli_error($conn));
}

mysqli_query($conn, "set names utf8");
mysqli_select_db($conn, $dbname);

echo "<h3>步骤1: 创建系统设置表</h3>";

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

echo "<h3>步骤2: 插入默认系统设置</h3>";

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

echo "<h3>创建完成！</h3>";
echo "<p style='color: green;'><strong>系统设置表创建成功！</strong></p>";
echo "<p>现在系统支持以下功能：</p>";
echo "<ul>";
echo "<li>系统设置管理</li>";
echo "<li>SMTP邮件配置</li>";
echo "<li>注册设置管理</li>";
echo "</ul>";
echo "<p><strong>重要提示：</strong></p>";
echo "<ol>";
echo "<li>请删除此脚本文件以确保安全</li>";
echo "<li>请在管理面板中配置SMTP设置以启用邮件功能</li>";
echo "</ol>";

mysqli_close($conn);
?>