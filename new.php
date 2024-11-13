<!DOCTYPE html>
<html lang="en">

<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport"
        content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=2.0, user-scalable=no, width=device-width">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>星跃短链接生成器</title>
    <script src="js/jquery.js"></script>
    <link rel="stylesheet" href="https://cdn.staticfile.org/layui/2.5.6/css/layui.min.css" media="all">
    <script src="//cdn.bootcss.com/layer/2.3/layer.js"></script>
    <script>
        $(function () {
            re = /http/;
            $("#b").click(function () {
                if (re.test($('#t').val())) {
                    $.post("add.php", { 'url': $('#t').val() }, function (data, status) {
                        $('#aaa').html("<input class='layui-input' type='text' value='" + data + "' />");
                    });
                } else {
                    alert("链接不规范，必须使用 http:// 或 https:// 开头");
                }
            });
        });
    </script>
    <style>
        /* 媒体查询 - 当边框小于1024px时应用的样式 */
        @media (max-width: 1024px) {
            .container {
                width: 80%;
                /* 或者设置其他你认为合适的宽度 */
            }
        }

        body {
            font-family: 'Arial', sans-serif;
            background-image: url(1.jpg);
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.7);
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 50%;
            /* 调整容器宽度 */
        }

        .container h1 {
            font-size: 24px;
            color: #333;
            margin-top: 20px;
            /* 增加标题与输入框的间距 */
            margin-bottom: 40px;
            /* 增加标题与输入框的间距 */
        }

        .container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 30px;
            box-sizing: border-box;
        }

        .container button {
            background-color: #009688;
            color: #fff;
            border: none;
            width: 100%;
            cursor: pointer;
            border-radius: 3px;
        }

        #aaa {
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>短链接在线生成</h1>
        <input class='layui-input' type='text' id='t' placeholder="请输入你的长链接" value='' />
        <p><button class="layui-btn layui-btn-primary" id='b'>生成短链接</button></p>
        <br>
        <p>
            <a class="layui-btn layui-btn-primary" href="./login.php">登录</a>
            <a class="layui-btn layui-btn-primary" href="./register.php">注册</a>
        </p>
        <p id='aaa'></p>
        <p>© 2023-2025 <a href="https://cloud.xwwen.com" target="_blank">星跃云</a></p>
    </div>

</body>

</html>