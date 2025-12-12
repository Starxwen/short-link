<?php
header("content-type:text/html;charset=utf-8");

// 系统功能测试脚本
include 'config.php';

echo "<h2>星跃短链接系统 - 功能测试</h2>";
echo "<p>此脚本将测试系统的各项功能是否正常工作</p>";

// 连接数据库
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die('<p style="color: red;">❌ 数据库连接失败: ' . mysqli_error($conn) . '</p>');
}

mysqli_query($conn, "set names utf8");

echo "<h3>1. 数据库表结构检查</h3>";

// 检查users表
$users_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($users_check) > 0) {
    echo "<p style='color: green;'>✓ users表存在</p>";
    
    // 检查字段
    $columns = mysqli_query($conn, "SHOW COLUMNS FROM users");
    $required_fields = ['uid', 'username', 'password', 'email', 'ugroup', 'created_at', 'last_login', 'status'];
    $existing_fields = [];
    
    while ($row = mysqli_fetch_assoc($columns)) {
        $existing_fields[] = $row['Field'];
    }
    
    foreach ($required_fields as $field) {
        if (in_array($field, $existing_fields)) {
            echo "<p style='color: green;'>✓ users表包含$field字段</p>";
        } else {
            echo "<p style='color: red;'>❌ users表缺少$field字段</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ users表不存在</p>";
}

// 检查go_to_url表
$goto_check = mysqli_query($conn, "SHOW TABLES LIKE 'go_to_url'");
if (mysqli_num_rows($goto_check) > 0) {
    echo "<p style='color: green;'>✓ go_to_url表存在</p>";
    
    // 检查uid字段
    $columns = mysqli_query($conn, "SHOW COLUMNS FROM go_to_url");
    $has_uid = false;
    
    while ($row = mysqli_fetch_assoc($columns)) {
        if ($row['Field'] == 'uid') {
            $has_uid = true;
            break;
        }
    }
    
    if ($has_uid) {
        echo "<p style='color: green;'>✓ go_to_url表包含uid字段</p>";
    } else {
        echo "<p style='color: red;'>❌ go_to_url表缺少uid字段</p>";
    }
} else {
    echo "<p style='color: red;'>❌ go_to_url表不存在</p>";
}

// 检查user_sessions表
$sessions_check = mysqli_query($conn, "SHOW TABLES LIKE 'user_sessions'");
if (mysqli_num_rows($sessions_check) > 0) {
    echo "<p style='color: green;'>✓ user_sessions表存在</p>";
} else {
    echo "<p style='color: orange;'>⚠ user_sessions表不存在（可选）</p>";
}

echo "<h3>2. 管理员账户检查</h3>";

$admin_check = mysqli_query($conn, "SELECT * FROM users WHERE ugroup = 'admin'");
if (mysqli_num_rows($admin_check) > 0) {
    echo "<p style='color: green;'>✓ 管理员账户存在</p>";
    while ($row = mysqli_fetch_assoc($admin_check)) {
        echo "<p style='color: blue;'>📋 管理员用户名: " . htmlspecialchars($row['username']) . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ 管理员账户不存在</p>";
}

echo "<h3>3. 文件完整性检查</h3>";

$required_files = [
    'register.php' => '用户注册页面',
    'login.php' => '用户登录页面',
    'user_panel.php' => '用户面板',
    'logout.php' => '退出登录',
    'add.php' => '短链接添加处理',
    'new.php' => '首页',
    'admin.php' => '管理面板',
    'ajax/get_user_data.php' => '用户数据获取',
    'ajax/delete_user_data.php' => '用户数据删除',
    'ajax/get_data.php' => '管理员数据获取',
    'ajax/delete_data.php' => '管理员数据删除'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file ($description)</p>";
    } else {
        echo "<p style='color: red;'>❌ $file ($description) 缺失</p>";
    }
}

echo "<h3>4. 功能测试建议</h3>";

echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>手动测试步骤：</h4>";
echo "<ol>";
echo "<li><strong>用户注册测试</strong>：访问 register.php，尝试注册新用户</li>";
echo "<li><strong>用户登录测试</strong>：使用注册的账户登录，检查是否跳转到用户面板</li>";
echo "<li><strong>短链接创建测试</strong>：登录后创建短链接，检查是否关联到用户</li>";
echo "<li><strong>用户面板测试</strong>：在用户面板中查看和管理自己的短链接</li>";
echo "<li><strong>权限测试</strong>：确保用户只能删除自己的短链接</li>";
echo "<li><strong>管理员测试</strong>：使用管理员账户登录，检查管理面板功能</li>";
echo "<li><strong>匿名用户测试</strong>：未登录状态下创建短链接，检查uid是否为0</li>";
echo "</ol>";
echo "</div>";

echo "<h3>5. 安全检查</h3>";

// 检查更新脚本是否存在
if (file_exists('update_database.php')) {
    echo "<p style='color: orange;'>⚠ 警告：update_database.php 仍然存在，建议删除以确保安全</p>";
} else {
    echo "<p style='color: green;'>✓ update_database.php 已删除（安全）</p>";
}

// 检查配置文件权限
$config_file = 'config.php';
if (file_exists($config_file)) {
    $perms = fileperms($config_file);
    if ($perms & 0x0004) {
        echo "<p style='color: orange;'>⚠ 警告：config.php 对其他用户可读，建议设置更严格的权限</p>";
    } else {
        echo "<p style='color: green;'>✓ config.php 权限设置合理</p>";
    }
}

echo "<h3>6. 数据统计</h3>";

// 统计用户数量
$user_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$user_row = mysqli_fetch_assoc($user_count);
echo "<p>📊 注册用户数量: " . $user_row['count'] . "</p>";

// 统计短链接数量
$link_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM go_to_url");
$link_row = mysqli_fetch_assoc($link_count);
echo "<p>🔗 短链接总数: " . $link_row['count'] . "</p>";

// 统计匿名用户短链接
$anonymous_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM go_to_url WHERE uid = 0");
$anonymous_row = mysqli_fetch_assoc($anonymous_count);
echo "<p>👤 匿名用户短链接: " . $anonymous_row['count'] . "</p>";

// 统计注册用户短链接
$registered_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM go_to_url WHERE uid > 0");
$registered_row = mysqli_fetch_assoc($registered_count);
echo "<p>👥 注册用户短链接: " . $registered_row['count'] . "</p>";

mysqli_close($conn);

echo "<h3>测试完成</h3>";
echo "<p style='color: green;'><strong>系统功能测试已完成！</strong></p>";
echo "<p>请按照上述手动测试步骤进行完整的功能验证。</p>";
echo "<p><a href='new.php'>返回首页</a></p>";
?>