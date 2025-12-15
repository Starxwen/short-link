<?php
header("content-type:text/html;charset=utf-8");

include 'config.php';
include 'includes/Settings.php';

function generateRandomString($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}
function getClientIp()
{
    $headers = [
        'HTTP_CLIENT_IP',    // 共享互联网访问代理
        'HTTP_X_FORWARDED_FOR', // 经过一个或多个代理服务器
        'HTTP_X_FORWARDED',  // 非标准形式的 X-Forwarded-For
        'HTTP_X_CLUSTER_CLIENT_IP', // 某些负载均衡器或反向代理
        'HTTP_FORWARDED_FOR', // 另一个非标准形式
        'HTTP_FORWARDED',    // 另一个非标准形式
        'REMOTE_ADDR'        // 如果没有代理，直接连接到服务器
    ];

    foreach ($headers as $header) {
        if (isset($_SERVER[$header]) && filter_var($_SERVER[$header], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            $ip = $_SERVER[$header];
            if (strpos($header, 'X_FORWARDED_FOR') !== false) {
                $ip = explode(',', $ip)[0];
                $ip = trim($ip);
            }

            return $ip;
        }
    }

    // 如果没有找到有效的 IP 地址，则返回空字符串或默认的 REMOTE_ADDR
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
}
if (isset($_POST['url'])) {
    if (strpos($_POST['url'], 'http') !== false) {
        echo '';
    } else {
        die('链接必须使用 http://或https:// 开头');
    }

    $conn = mysqli_connect($dbhost, $dbuser, $dbpass);

    if (!$conn) {
        die('连接失败: ' . mysqli_error($conn));
    }

    mysqli_query($conn, "set names utf8");

    $t = addcslashes(mysqli_real_escape_string($conn, base64_encode($_POST['url'])), "%_");
    $ip_t = getClientIp();
    $short = generateRandomString(); // 生成随机字符串

    // 获取系统设置中的网站URL
    $site_url = Settings::getSiteUrl();

    // 获取用户ID，如果用户已登录则使用用户ID，否则为0
    $user_id = 0;
    session_start();
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }

    // 循环直到找到不重复的 $short
    while (true) {
        $sql = "SELECT COUNT(*) FROM go_to_url WHERE short_url = '$short'";
        $checkResult = mysqli_query($conn, $sql);
        if ($checkResult->num_rows == 0) {
            // 生成的 $short 不重复，跳出循环
            break;
        } else {
            // 生成的 $short 已存在，重新生成
            $short = generateRandomString();
        }
    }

    // 检查是否已存在相同的URL（对于同一用户）
    $sql = "select short_url from go_to_url where url='$t' AND uid = $user_id";

    mysqli_select_db($conn, $dbname);
    $retval = mysqli_query($conn, $sql);

    if ($retval->num_rows == 0) {
        $sql = "INSERT INTO go_to_url " .
            "(url,short_url,ip,add_date,uid) " .
            "VALUES " .
            "('$t','$short','$ip_t',NOW(),$user_id)";

        $retval = mysqli_query($conn, $sql);

        if (!$retval) {
            die('无法插入数据: ' . mysqli_error($conn));
        }

        $sql = 'SELECT MAX(num) FROM go_to_url';

        $retval = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_array($retval)) {
            echo $site_url . $short;
        }
    } else {
        while ($row = mysqli_fetch_array($retval)) {
            echo $site_url . $row['short_url'];
        }
    }

    mysqli_close($conn);
} else {
    die('URL参数缺失！');
}
?>