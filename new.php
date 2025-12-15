<?php
session_start();
include './config.php';
include './includes/Settings.php';

// 获取系统设置
$site_name = Settings::getSiteName();
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($site_name); ?></title>
    <script src="js/jquery.js"></script>
    <link rel="stylesheet" href="https://cdn.staticfile.org/layui/2.5.6/css/layui.min.css" media="all">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="//cdn.bootcss.com/layer/2.3/layer.js"></script>
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

        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 40px;
            width: 100%;
            max-width: 550px;
            text-align: center;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            animation: fadeIn 0.8s ease;
            overflow: hidden;
        }

        .container::before {
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

        .logo-text h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .logo-text p {
            color: var(--text-light);
            font-size: 14px;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: var(--transition);
            background: #f9f9f9;
        }

        .input-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
            background: white;
        }

        .btn-generate {
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-generate:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-generate:active {
            transform: translateY(0);
        }

        .result-container {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            display: none;
            animation: slideDown 0.5s ease;
        }

        .result-container.show {
            display: block;
        }

        .result-title {
            font-size: 16px;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .result-box {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            margin-bottom: 15px;
        }

        .result-box input {
            flex: 1;
            border: none;
            padding: 12px 15px;
            font-size: 15px;
            background: transparent;
        }

        .qr-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .qr-title {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .qr-code {
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .qr-code img {
            display: block;
            max-width: 200px;
            height: auto;
        }

        .qr-tip {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 10px;
            text-align: center;
        }

        .auth-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .auth-buttons a {
            flex: 1;
            padding: 12px;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
            border: 1px solid rgba(52, 152, 219, 0.3);
        }

        .btn-login:hover {
            background: rgba(52, 152, 219, 0.2);
        }

        .btn-register {
            background: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .btn-register:hover {
            background: rgba(46, 204, 113, 0.2);
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: var(--text-light);
            font-size: 14px;
        }

        .footer a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }

            .logo-text h1 {
                font-size: 24px;
            }

            .auth-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-link"></i>
            </div>
            <div class="logo-text">
                <h1><?php echo htmlspecialchars($site_name); ?></h1>
                <p>快速生成简洁易记的短链接</p>
            </div>
        </div>

        <div class="input-group">
            <input class='layui-input' type='text' id='t' placeholder="请输入URL链接或文本内容（URL必须以 http:// 或 https:// 开头）"
                value='' />
        </div>

        <button class="btn-generate" id='b'>
            <i class="fas fa-magic"></i> 生成短链接
        </button>

        <?php
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
            // 用户已登录
            if (isset($_SESSION['user_group']) && $_SESSION['user_group'] === 'admin') {
                // 管理员
                echo '<div class="auth-buttons">';
                echo '<a href="./admin.php" class="btn-login">';
                echo '<i class="fas fa-tachometer-alt"></i> 管理面板';
                echo '</a>';
                echo '<a href="./logout.php" class="btn-register">';
                echo '<i class="fas fa-sign-out-alt"></i> 退出登录';
                echo '</a>';
                echo '</div>';
            } else {
                // 普通用户
                echo '<div class="auth-buttons">';
                echo '<a href="./user_panel.php" class="btn-login">';
                echo '<i class="fas fa-user"></i> 我的链接';
                echo '</a>';
                echo '<a href="./logout.php" class="btn-register">';
                echo '<i class="fas fa-sign-out-alt"></i> 退出登录';
                echo '</a>';
                echo '</div>';
            }
        } else {
            // 用户未登录
            echo '<div class="auth-buttons">';
            echo '<a href="./login.php" class="btn-login">';
            echo '<i class="fas fa-sign-in-alt"></i> 登录';
            echo '</a>';
            echo '<a href="./register.php" class="btn-register">';
            echo '<i class="fas fa-user-plus"></i> 注册';
            echo '</a>';
            echo '</div>';
        }
        ?>

        <div id='aaa' class="result-container">
            <div class="result-title">您的短链接已生成：</div>
            <div class="result-box">
                <!-- 结果将通过JavaScript动态插入 -->
            </div>
            <div class="qr-container" id="qr-container" style="display: none;">
                <div class="qr-title">扫描二维码快速访问：</div>
                <div class="qr-code">
                    <img id="qr-image" src="" alt="二维码">
                </div>
                <div class="qr-tip">使用手机扫描上方二维码即可访问短链接</div>
            </div>
        </div>

        <div class="footer">
            © 2026 <a href="https://www.xwyue.com" target="_blank">星跃云</a> - 专业的云计算服务
        </div>
    </div>

    <script>
        $(function () {
            $("#b").click(function () {
                var inputValue = $('#t').val().trim();

                // 检查输入是否为空
                if (inputValue === '') {
                    layer.msg('输入内容不能为空', { icon: 2 });
                    return;
                }

                // 检查是否为URL格式
                var isUrl = /^https?:\/\//.test(inputValue);

                // 如果是URL，验证格式
                if (isUrl) {
                    try {
                        new URL(inputValue);
                    } catch (e) {
                        layer.msg('URL格式不正确，必须以 http:// 或 https:// 开头', { icon: 2 });
                        return;
                    }
                }

                // 发送请求
                $.post("add.php", { 'url': inputValue }, function (data, status) {
                    // 更新结果显示
                    $('#aaa').find('.result-box').html("<input class='layui-input' type='text' value='" + data + "' readonly />");
                    $('#aaa').addClass('show');

                    // 生成二维码
                    generateQRCode(data);
                });
            });

            // 生成二维码函数
            function generateQRCode(url) {
                // 使用第三方API生成二维码
                var qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(url);

                // 设置二维码图片
                $('#qr-image').attr('src', qrApiUrl);

                // 显示二维码容器
                $('#qr-container').fadeIn();
            }
        });
    </script>
</body>

</html>