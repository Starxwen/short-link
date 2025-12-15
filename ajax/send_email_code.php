<?php
session_start();
include '../config.php';
include '../includes/Settings.php';

header("Content-Type: application/json; charset=utf-8");

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    echo json_encode(['success' => false, 'error' => '邮箱地址不能为空']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => '邮箱格式不正确']);
    exit;
}

// 检查发送频率（防止频繁发送）
if (isset($_SESSION['email_code_sent']) && (time() - $_SESSION['email_code_sent']) < 60) {
    echo json_encode(['success' => false, 'error' => '请等待60秒后再次发送']);
    exit;
}

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    echo json_encode(['success' => false, 'error' => '数据库连接失败']);
    exit;
}

mysqli_query($conn, "set names utf8");

// 生成6位验证码
$verification_code = sprintf('%06d', mt_rand(0, 999999));
$code_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// 将验证码存储到session中
$_SESSION['email_verification_code'] = $verification_code;
$_SESSION['email_verification_code_expires'] = $code_expires;
$_SESSION['email_for_verification'] = $email;
$_SESSION['email_code_sent'] = time();

// 发送验证邮件
if (file_exists('../includes/Mailer.php')) {
    include '../includes/Mailer.php';
    $mailer = new Mailer();

    $site_name = Settings::getSiteName();
    $subject = "【{$site_name}】邮箱验证码";
    $message = "您好！\n\n您正在注册 {$site_name} 账户，您的邮箱验证码是：\n\n{$verification_code}\n\n验证码有效期为10分钟，请及时使用。\n\n如果这不是您本人的操作，请忽略此邮件。\n\n{$site_name} 团队";

    if ($mailer->sendCustomEmail($email, $subject, $message)) {
        echo json_encode(['success' => true, 'message' => '验证码已发送到您的邮箱，请查收']);
    } else {
        echo json_encode(['success' => false, 'error' => '验证码发送失败，请稍后重试']);
    }
} else {
    echo json_encode(['success' => false, 'error' => '邮件发送功能不可用']);
}

mysqli_close($conn);
?>