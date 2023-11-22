<?php
header("content-type:text/html;charset=utf-8");

include 'config.php';

function generateRandomString($length = 6)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
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
    $ip_t = $_SERVER["REMOTE_ADDR"];
    $short = generateRandomString(); // 生成随机字符串

    $sql = "select num from go_to_url where url='$t'";

    mysqli_select_db($conn, $dbname);
    $retval = mysqli_query($conn, $sql);

    if ($retval->num_rows == 0) {
        $sql = "INSERT INTO go_to_url " .
            "(url,short_url,ip,add_date,uid) " .
            "VALUES " .
            "('$t','$short','$ip_t',NOW(),0)";

        $retval = mysqli_query($conn, $sql);

        if (!$retval) {
            die('无法插入数据: ' . mysqli_error($conn));
        }

        $sql = 'SELECT MAX(num) FROM go_to_url';

        $retval = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_array($retval)) {
            echo $my_url . $short;
        }
    } else {
        while ($row = mysqli_fetch_array($retval)) {
            echo $my_url . $short;
        }
    }

    mysqli_close($conn);
} else {
    die('URL参数缺失！');
}
?>