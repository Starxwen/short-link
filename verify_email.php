<?php
session_start();
include './config.php';

// 获取验证码和邮箱
$code = isset($_GET['code']) ? trim($_GET['code']) : '';
$email = isset($_GET['email']) ? trim($_GET['email']) : '';

$message = '';
$success = false;

if (!empty($code) && !empty($email)) {
    // 连接数据库
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if (!$conn) {
        $message = '数据库连接失败';
    } else {
        mysqli_query($conn, "set names utf8");
        
        // 查找用户
        $email = mysqli_real_escape_string($conn, $email);
        $code = mysqli_real_escape_string($conn, $code);
        
        $sql = "SELECT uid, username, verification_code, verification_expires FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // 检查验证码是否正确且未过期
            if ($row['verification_code'] === $code && strtotime($row['verification_expires']) > time()) {
                // 验证成功，更新用户状态
                $update_sql = "UPDATE users SET email_verified = 1, verification_code = '', verification_expires = NULL WHERE uid = " . $row['uid'];
                
                if (mysqli_query($conn, $update_sql)) {
                    $success = true;
                    $message = '邮箱验证成功！您的账户已激活，现在可以正常使用了。';
                } else {
                    $message = '验证失败，请稍后重试';
                }
            } elseif (strtotime($row['verification_expires']) <= time()) {
                $message = '验证链接已过期，请重新注册或联系管理员';
            } else {
                $message = '验证码不正确，请检查链接是否完整';
            }
        } else {
            $message = '未找到对应的用户，请检查邮箱地址是否正确';
        }
        
        mysqli_close($conn);
    }
} else {
    $message = '验证链接不完整，请检查邮件中的链接是否完整';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>邮箱验证 - 星跃短链接</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --accent-color: #9b59b6;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --text-color: #333;
            --text-light: #777;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Microsoft YaHei', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB4PSIwIiB5PSIwIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHg9IjAiIHk9IjAiIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjAzKSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIvPjwvc3ZnPg==');
            opacity: 0.3;
        }
        
        .verify-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            animation: fadeIn 0.8s ease;
            overflow: hidden;
        }
        
        .verify-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--secondary-color), var(--accent-color));
        }
        
        .logo {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }
        
        .logo-text h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .logo-text p {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: fadeIn 0.3s ease;
        }
        
        .success-message {
            background-color: #e8f5e8;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .icon-large {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .success-icon {
            color: var(--secondary-color);
        }
        
        .error-icon {
            color: #e74c3c;
        }
        
        .back-link {
            margin-top: 20px;
        }
        
        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 480px) {
            .verify-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="verify-container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="logo-text">
                <h2>邮箱验证</h2>
                <p>星跃短链接</p>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="icon-large success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="message success-message">
                <?php echo $message; ?>
            </div>
        <?php else: ?>
            <div class="icon-large error-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="message error-message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> 前往登录</a>
            <?php if (!$success): ?>
                <br><br>
                <a href="register.php"><i class="fas fa-user-plus"></i> 重新注册</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>