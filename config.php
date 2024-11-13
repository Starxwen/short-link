<?php
//此文件为配置文件
session_start();
//请务必在运行install.php之前，填写本文件，删除install目录后，必须保留本文件，否则程序无法运行
$dbhost = 'localhost';  // mysql服务器主机地址
$dbuser = 'short_url';            // mysql用户名
$dbpass = '';          // mysql密码
$dbname = 'short_url';     //mysql数据库名称
$admin_password = md5('123456');   //admin管理面板的密码，需要填入密码的md5值
$my_url = 'https://u.xwyue.com/';    //当前项目根目录网址，例如：http://xxx.com/short_url/、http://aaa.top/、https://b.cn/、http://a.b.com/s/，记得要加http/https协议，末尾加“/”
?>