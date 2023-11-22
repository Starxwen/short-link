<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=2.0, user-scalable=no, width=device-width">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>后台管理 - 星跃短链接生成器</title>
    <script src="js/jquery.js"></script>
    <link rel="stylesheet" href="https://cdn.staticfile.org/layui/2.5.6/css/layui.min.css" media="all">
    <script src="//cdn.bootcss.com/layer/2.3/layer.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f2f2f2;
            text-align: center;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        .login-container h1 {
            font-size: 24px;
            color: #333;
        }

        .login-container form {
            margin-top: 20px;
        }

        .login-container form p {
            text-align: left;
            margin-bottom: 10px;
        }

        .login-container form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .login-container form button {
            background-color: #009688;
            color: #fff;
            padding: 10px;
            border: none;
            width: 100%;
            cursor: pointer;
            border-radius: 3px;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #ddd;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>

    <?php
    header("content-type:text/html;charset=utf-8");

    include 'config.php';

    if (isset($_POST['password'])) {
        if (md5($_POST['password']) == $admin_password) {
            echo '<div class="container"><h2>admin后台</h2>';
            echo '<table><tr><td>编号</td><td>URL地址</td><td>短链接</td><td>用户IP地址</td><td>添加日期</td><td>用户id</td><td>操作</td></tr>';

            $conn = mysqli_connect($dbhost, $dbuser, $dbpass);

            if (!$conn) {
                die('连接失败: ' . mysqli_error($conn));
            }

            mysqli_query($conn, "set names utf8");

            $sql = 'SELECT num, url, short_url, ip, add_date, uid FROM go_to_url';

            mysqli_select_db($conn, $dbname);
            $retval = mysqli_query($conn, $sql);

            if (!$retval) {
                die('无法读取数据: ' . mysqli_error($conn));
            }

            while ($row = mysqli_fetch_array($retval, MYSQLI_ASSOC)) {
                echo "<tr><td> {$row['num']}</td> " .
                    "<td>" . base64_decode($row['url']) . "</td> " .
                    "<td>" . $my_url . $row['short_url'] . "</td> " .
                    "<td>{$row['ip']} </td> " .
                    "<td>{$row['add_date']} </td> " .
                    "<td>{$row['uid']} </td> " .
                    "<td><button class='delete-btn' data-num='{$row['num']}'>删除</button></td> " .
                    "</tr>";
            }

            echo '</table></div>';

            mysqli_free_result($retval);
            mysqli_close($conn);

        } else {
            echo <<<EOF
<div class="login-container">
    <h1>密码错误</h1>
    <form method="post" action="admin.php">
       <p><input type="password" name="password" id="password"></p>
       <button type="submit">登录</button>
    </form>
    <p class="error-message">密码错误，请重新输入。</p>
</div>
EOF;

            exit();
        }
    } else {
        echo <<<EOF
<div class="login-container">
    <h1>请输入密码</h1>
    <form method="post" action="admin.php">
       <p><input type="password" name="password" id="password"></p>
       <button type="submit">登录</button>
    </form>
</div>
EOF;

        exit();
    }
    ?>
</body>
<script>
    // 使用 jQuery 来处理点击事件
    $(document).ready(function () {
        // 给所有类名为 delete-btn 的按钮添加点击事件
        $('.delete-btn').click(function () {
            // 获取按钮所在行的 num 属性值
            var num = $(this).data('num');

            // 弹窗确认删除
            var isConfirmed = confirm("确认删除编号为 " + num + " 的数据吗？");

            // 如果用户确认删除
            if (isConfirmed) {
                // 发送 Ajax 请求删除数据
                $.ajax({
                    type: "POST",
                    url: "/ajax/delete_data.php",
                    data: { num: num },
                    success: function (response) {
                        location.reload();
                    },
                    error: function (error) {
                        alert("删除失败: " + error);
                    }
                });
            }
        });
    });
</script>

</html>