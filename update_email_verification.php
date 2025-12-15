<?php
header("content-type:text/html;charset=utf-8");

// 添加邮箱验证功能的数据库更新脚本
include 'config.php';

echo "<h2>星跃短链接系统 - 添加邮箱验证功能</h2>";

// 连接数据库
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
if (!$conn) {
    die('数据库连接失败: ' . mysqli_error($conn));
}

mysqli_query($conn, "set names utf8");
mysqli_select_db($conn, $dbname);

echo "<h3>步骤1: 更新用户表结构</h3>";

// 检查users表结构
$users_columns = mysqli_query($conn, "SHOW COLUMNS FROM users");
$existing_columns = [];
while ($row = mysqli_fetch_assoc($users_columns)) {
    $existing_columns[] = $row['Field'];
}

// 添加邮箱验证相关字段
$fields_to_add = [
    'email_verified' => "TINYINT DEFAULT 0 COMMENT '0:未验证 1:已验证'",
    'verification_code' => "VARCHAR(64) DEFAULT '' COMMENT '邮箱验证码'",
    'verification_expires' => "DATETIME NULL COMMENT '验证码过期时间'"
];

foreach ($fields_to_add as $field => $definition) {
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

echo "<h3>步骤2: 检查系统设置表</h3>";

// 检查settings表是否存在
$settings_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
if (mysqli_num_rows($settings_table_check) == 0) {
    echo "<p style='color: orange;'>⚠ settings表不存在，请先运行 create_settings_table.php</p>";
} else {
    echo "<p style='color: green;'>✓ settings表已存在</p>";
    
    // 检查并添加邮箱验证相关设置
    $email_settings = [
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
    
    foreach ($email_settings as $setting) {
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
}

echo "<h3>更新完成！</h3>";
echo "<p style='color: green;'><strong>邮箱验证功能添加成功！</strong></p>";
echo "<p>现在系统支持以下功能：</p>";
echo "<ul>";
echo "<li>用户注册时发送验证邮件</li>";
echo "<li>邮箱验证链接验证</li>";
echo "<li>管理员可配置是否需要邮箱验证</li>";
echo "<li>管理员可配置是否允许用户注册</li>";
echo "</ul>";
echo "<p><strong>重要提示：</strong></p>";
echo "<ol>";
echo "<li>请删除此更新脚本文件以确保安全</li>";
echo "<li>请在管理面板中配置SMTP设置以启用邮件功能</li>";
echo "<li>测试注册和邮箱验证功能</li>";
echo "</ol>";

mysqli_close($conn);
?>