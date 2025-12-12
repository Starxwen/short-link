<?php
session_start();

// 销毁所有会话数据
session_unset();
session_destroy();

// 重定向到首页
header('Location: new.php');
exit();
?>