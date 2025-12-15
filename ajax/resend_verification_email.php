<?php
session_start();
include '../config.php';
include '../includes/Settings.php';

header("Content-Type: application/json; charset=utf-8");

// 检查用户是否已登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => '鉴权失败']);
    exit;
}

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

// 确保用户只能为自己发送验证邮件
if ($user_id != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => '权限不足']);
    exit;
}

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    echo json_encode(['success' => false, 'error' => '数据库连接失败']);
    exit;
}

mysqli_query($conn, "set names utf8");

// 获取用户信息
$user_sql = "SELECT username, email, email_verified FROM users WHERE uid = $user_id";
$user_result = mysqli_query($conn, $user_sql);

if (!$user_result || mysqli_num_rows($user_result) == 0) {
    echo json_encode(['success' => false, 'error' => '用户不存在']);
    mysqli_close($conn);
    exit;
}

$user_data = mysqli_fetch_assoc($user_result);

// 检查邮箱是否已验证
if (isset($user_data['email_verified']) && $user_data['email_verified'] == 1) {
    echo json_encode(['success' => false, 'error' => '邮箱已经验证过了']);
    mysqli_close($conn);
    exit;
}

// 检查邮箱是否为空
if (empty($user_data['email'])) {
    echo json_encode(['success' => false, 'error' => '用户未设置邮箱地址']);
    mysqli_close($conn);
    exit;
}

// 检查发送频率（防止频繁发送）
if (isset($_SESSION['verification_email_sent']) && (time() - $_SESSION['verification_email_sent']) < 300) {
    echo json_encode(['success' => false, 'error' => '请等待5分钟后再次发送']);
    mysqli_close($conn);
    exit;
}

// 生成新的验证码和过期时间
$verification_code = md5(uniqid(rand(), true));
$verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

// 更新用户验证信息
$update_sql = "UPDATE users SET verification_code = '$verification_code', verification_expires = '$verification_expires' WHERE uid = $user_id";
if (!mysqli_query($conn, $update_sql)) {
    echo json_encode(['success' => false, 'error' => '更新验证信息失败']);
    mysqli_close($conn);
    exit;
}

// 发送验证邮件
if (file_exists('../includes/Mailer.php')) {
    include '../includes/Mailer.php';
    $mailer = new Mailer();
    
    if ($mailer->sendVerificationEmail($user_data['email'], $user_data['username'], $verification_code)) {
        $_SESSION['verification_email_sent'] = time();
        echo json_encode(['success' => true, 'message' => '验证邮件已重新发送，请查收您的邮箱']);
    } else {
        echo json_encode(['success' => false, 'error' => '邮件发送失败，请稍后重试']);
    }
} else {
    echo json_encode(['success' => false, 'error' => '邮件发送功能不可用']);
}

mysqli_close($conn);
?>