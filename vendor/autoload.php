<?php
/**
 * 简单的autoload文件
 */

spl_autoload_register(function ($class) {
    // 项目特定的命名空间前缀
    $prefix = 'PHPMailer\\PHPMailer\\';
    
    // 检查类是否使用该前缀
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // 获取相对类名
    $relative_class = substr($class, $len);
    
    // 将命名空间转换为文件路径
    $file = __DIR__ . '/phpmailer/phpmailer/' . $relative_class . '.php';
    
    // 如果文件存在，则加载它
    if (file_exists($file)) {
        require $file;
    }
});