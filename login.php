<?php
session_start();
include './config.php';
include './includes/Settings.php';

// 获取系统设置
$site_name = Settings::getSiteName();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
    $password_md5 = md5($password);
    
    // 验证验证码
    if (empty($captcha)) {
        $error = '请输入验证码';
    } elseif (!isset($_SESSION['captcha_code']) || strtolower($captcha) !== strtolower($_SESSION['captcha_code'])) {
        $error = '验证码错误';
    } else {
        // 检查是否是管理员登录
    if ($username == $admin_username) {
        if ($password_md5 === $admin_password) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = 0; // 管理员用户ID设为0
            $_SESSION['username'] = $admin_username;
            $_SESSION['user_group'] = 'admin';
            header('Location: admin.php');
            exit();
        } else {
            $error = '密码错误';
        }
    } else {
        // 普通用户登录
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        if ($conn) {
            mysqli_query($conn, "set names utf8");
            $username = mysqli_real_escape_string($conn, $username);
            
            $sql = "SELECT uid, username, password, ugroup, status FROM users WHERE username = '$username'";
            $result = mysqli_query($conn, $sql);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                
                if ($user['status'] == 0) {
                    $error = '账户已被禁用';
                } elseif ($password_md5 === $user['password']) {
                    // 登录成功
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['uid'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_group'] = $user['ugroup'];
                    
                    // 更新最后登录时间
                    $update_sql = "UPDATE users SET last_login = NOW() WHERE uid = " . $user['uid'];
                    mysqli_query($conn, $update_sql);
                    
                    // 根据用户组跳转
                    if ($user['ugroup'] == 'admin') {
                        header('Location: admin.php');
                    } else {
                        header('Location: user_panel.php');
                    }
                    exit();
                } else {
                    $error = '密码错误';
                }
            } else {
                $error = '用户不存在';
            }
            
            mysqli_close($conn);
        } else {
            $error = '数据库连接失败';
        }
    }
    
    // 清除验证码session
    unset($_SESSION['captcha_code']);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - <?php echo htmlspecialchars($site_name); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.staticfile.org/jquery/3.6.0/jquery.min.js"></script>
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
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            animation: fadeIn 0.8s ease;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
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
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
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
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c62828;
            display: none;
        }
        
        .error-message.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        .btn-login {
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 13px 15px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
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
        
        .captcha-refresh {
            font-size: 12px;
            color: var(--text-light);
            text-align: center;
            margin-top: 5px;
        }
        
        .captcha-refresh a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .captcha-refresh a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .form-group div[style*="display: flex"] {
                flex-direction: column;
                align-items: stretch;
            }
            
            #captcha-image {
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="logo-text">
                <h2>用户登录</h2>
                <p><?php echo htmlspecialchars($site_name); ?>系统</p>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message show"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="" class="login-form">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="请输入用户名" required>
            </div>
            
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="请输入密码" required>
            </div>
            
            <div class="form-group">
                <label for="captcha">验证码</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" id="captcha" name="captcha" class="form-control" placeholder="请输入验证码" maxlength="4" required style="flex: 1;">
                    <img id="captcha-image" src="captcha.php" alt="验证码" style="height: 46px; cursor: pointer; border: 1px solid #ddd; border-radius: 4px;" title="点击刷新验证码">
                </div>
            </div>
            
            <button type="submit" class="btn-login">登录系统</button>
        </form>
        
        <div class="back-link">
            <a href="new.php"><i class="fas fa-arrow-left"></i> 返回首页</a>
            <br>
            <a href="register.php"><i class="fas fa-user-plus"></i> 还没有账户？立即注册</a>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            // 点击验证码图片刷新
            $('#captcha-image').click(function() {
                refreshCaptcha();
            });
            
            // 刷新验证码函数
            function refreshCaptcha() {
                var timestamp = new Date().getTime();
                $('#captcha-image').attr('src', 'captcha.php?t=' + timestamp);
            }
        });
    </script>
</body>
</html>