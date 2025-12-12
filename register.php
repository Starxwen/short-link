<?php
session_start();
include './config.php';

// 如果用户已登录，重定向到用户面板
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    header('Location: user_panel.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    
    // 验证输入
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = '请填写所有必填字段';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = '用户名长度必须在3-20个字符之间';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = '用户名只能包含字母、数字和下划线';
    } elseif (strlen($password) < 6) {
        $error = '密码长度至少6个字符';
    } elseif ($password !== $confirm_password) {
        $error = '两次输入的密码不一致';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '邮箱格式不正确';
    } else {
        // 连接数据库
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        if (!$conn) {
            $error = '数据库连接失败';
        } else {
            mysqli_query($conn, "set names utf8");
            
            // 检查用户名是否已存在
            $username = mysqli_real_escape_string($conn, $username);
            $check_sql = "SELECT uid FROM users WHERE username = '$username'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error = '用户名已存在';
            } else {
                // 创建新用户
                $password_md5 = md5($password);
                $email = mysqli_real_escape_string($conn, $email);
                
                // 检查表结构，确定是否有email字段
                $columns_check = mysqli_query($conn, "SHOW COLUMNS FROM users");
                $existing_columns = [];
                while ($row = mysqli_fetch_assoc($columns_check)) {
                    $existing_columns[] = $row['Field'];
                }
                
                if (in_array('email', $existing_columns)) {
                    $insert_sql = "INSERT INTO users (username, password, email, ugroup) VALUES ('$username', '$password_md5', '$email', 'user')";
                } else {
                    $insert_sql = "INSERT INTO users (username, password, ugroup) VALUES ('$username', '$password_md5', 'user')";
                }
                
                if (mysqli_query($conn, $insert_sql)) {
                    $success = '注册成功！请登录您的账户';
                } else {
                    $error = '注册失败：' . mysqli_error($conn);
                }
            }
            
            mysqli_close($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - 星跃短链接</title>
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
        
        .register-container {
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
        
        .register-container::before {
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
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .success-message {
            background-color: #e8f5e8;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .message.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .register-form {
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
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
            outline: none;
        }
        
        .btn-register {
            background: linear-gradient(to right, var(--secondary-color), var(--accent-color));
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
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
        }
        
        .btn-register:active {
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
        
        @media (max-width: 480px) {
            .register-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="logo-text">
                <h2>用户注册</h2>
                <p>创建您的星跃短链接账户</p>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="message error-message show"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message success-message show"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
        <form method="post" action="" class="register-form">
            <div class="form-group">
                <label for="username">用户名 *</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="3-20个字符，只能包含字母、数字和下划线" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">邮箱</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="选填，用于找回密码" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">密码 *</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="至少6个字符" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">确认密码 *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="再次输入密码" required>
            </div>
            
            <button type="submit" class="btn-register">注册账户</button>
        </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="new.php"><i class="fas fa-arrow-left"></i> 返回首页</a>
            <?php if ($success): ?>
                <br><br>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> 立即登录</a>
            <?php else: ?>
                <br>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> 已有账户？立即登录</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>