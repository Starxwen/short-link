<?php
session_start();
include '../config.php';

// 检查是否是管理员
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || (!isset($_SESSION['user_group']) || ($_SESSION['user_group'] !== 'admin' && $_SESSION['user_id'] !== 0))) {
    die(json_encode(['error' => '权限不足']));
}

header("Content-Type: application/json; charset=utf-8");

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die(json_encode(['error' => '数据库连接失败']));
}

// 获取操作类型
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'get_settings':
        // 获取所有设置
        $result = mysqli_query($conn, "SELECT * FROM settings ORDER BY category, setting_name");
        $settings = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[] = $row;
        }
        
        echo json_encode(['success' => true, 'settings' => $settings]);
        break;
        
    case 'save_settings':
        // 保存设置
        $settings_data = isset($_POST['settings']) ? $_POST['settings'] : [];
        
        if (empty($settings_data)) {
            die(json_encode(['error' => '没有设置数据']));
        }
        
        $success = true;
        $error_message = '';
        
        foreach ($settings_data as $key => $value) {
            // 转义特殊字符
            $key = mysqli_real_escape_string($conn, $key);
            $value = mysqli_real_escape_string($conn, $value);
            
            // 更新设置
            $update_sql = "UPDATE settings SET setting_value = '$value', updated_at = NOW() WHERE setting_key = '$key'";
            
            if (!mysqli_query($conn, $update_sql)) {
                $success = false;
                $error_message = '更新设置失败: ' . mysqli_error($conn);
                break;
            }
        }
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => '设置保存成功']);
        } else {
            echo json_encode(['error' => $error_message]);
        }
        break;
        
    case 'test_email':
        // 测试邮件发送
        $test_email = isset($_POST['test_email']) ? trim($_POST['test_email']) : '';
        
        if (empty($test_email)) {
            die(json_encode(['error' => '请输入测试邮箱地址']));
        }
        
        if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
            die(json_encode(['error' => '邮箱格式不正确']));
        }
        
        // 包含邮件发送类
        if (file_exists('../includes/Mailer.php')) {
            include '../includes/Mailer.php';
            $mailer = new Mailer();
            
            if ($mailer->testConnection($test_email)) {
                echo json_encode(['success' => true, 'message' => '测试邮件发送成功，请检查收件箱']);
            } else {
                echo json_encode(['error' => '邮件发送失败，请检查SMTP设置']);
            }
        } else {
            echo json_encode(['error' => '邮件发送类不存在']);
        }
        break;
        
    default:
        echo json_encode(['error' => '未知操作']);
        break;
}

mysqli_close($conn);
?>