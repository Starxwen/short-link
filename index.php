<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
if (file_exists('install/install.lock')) {
} else {
    exit("<script language='javascript'>window.location.href='install';</script>");
}
include 'config.php';

if (isset($_GET['dest'])) {
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass);

    if (!$conn) {
        die('连接失败: ' . mysqli_error($conn));
    }

    mysqli_query($conn, "set names utf8");


    $sql = 'select num, url, type, short_url, add_date, click_count from go_to_url where short_url="' . $_GET['dest'] . '"';
    mysqli_select_db($conn, $dbname);
    $retval = mysqli_query($conn, $sql);

    if (!$retval) {
        die('无法读取数据: ' . mysqli_error($conn));
    }

    if ($retval->num_rows == 0) {
        include "new.php";
    } else {
        // 获取单条记录
        $row = mysqli_fetch_array($retval, MYSQLI_ASSOC);

        // 只有当click_count字段存在时才增加点击次数
        $update_click_sql = "UPDATE go_to_url SET click_count = COALESCE(click_count, 0) + 1 WHERE num = " . $row['num'];
        mysqli_query($conn, $update_click_sql);
        
        // 根据类型决定是跳转还是显示文本
        if ($row['type'] === 'text') {
            // 显示文本内容
            $text_content = base64_decode($row['url']);
            ?>
            <!DOCTYPE html>
            <html lang="zh-CN">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>文本内容</title>
                <style>
                    body {
                        font-family: 'Segoe UI', 'Microsoft YaHei', sans-serif;
                        line-height: 1.6;
                        margin: 0;
                        padding: 20px;
                        background-color: #f5f5f5;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                    }
                    .container {
                        background: white;
                        border-radius: 10px;
                        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                        padding: 30px;
                        max-width: 800px;
                        width: 100%;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                        color: #333;
                    }
                    .content {
                        background: #f9f9f9;
                        padding: 20px;
                        border-radius: 8px;
                        border-left: 4px solid #3498db;
                        white-space: pre-wrap;
                        word-wrap: break-word;
                        font-size: 16px;
                        color: #333;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 30px;
                        color: #777;
                        font-size: 14px;
                    }
                    .back-link {
                        display: inline-block;
                        margin-top: 20px;
                        padding: 10px 20px;
                        background: #3498db;
                        color: white;
                        text-decoration: none;
                        border-radius: 5px;
                        transition: background 0.3s;
                    }
                    .back-link:hover {
                        background: #2980b9;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>文本内容</h1>
                    </div>
                    <div class="content"><?php echo htmlspecialchars($text_content); ?></div>
                    <div class="footer">
                        <p>访问时间: <?php echo date('Y-m-d H:i:s'); ?></p>
                        <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>" class="back-link">返回首页</a>
                    </div>
                </div>
            </body>
            </html>
            <?php
        } else {
            // 执行重定向
            header("Location: " . base64_decode($row['url']));
        }
    }

    mysqli_close($conn);
    exit; // 确保脚本终止
} else {
    include "new.php";
}
?>