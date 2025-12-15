<?php
session_start();

// 生成4位随机验证码
$code = '';
for ($i = 0; $i < 4; $i++) {
    $code .= rand(0, 9);
}

// 将验证码存储到session中
$_SESSION['captcha_code'] = $code;

// 创建图片
$width = 80;
$height = 30;
$image = imagecreate($width, $height);

// 设置背景颜色
$bg_color = imagecolorallocate($image, 240, 240, 240);
$text_color = imagecolorallocate($image, 50, 50, 50);
$line_color = imagecolorallocate($image, 200, 200, 200);

// 添加干扰线
for ($i = 0; $i < 3; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// 添加干扰点
for ($i = 0; $i < 50; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $line_color);
}

// 将验证码写入图片
$font_size = 5;
$x = ($width - strlen($code) * imagefontwidth($font_size)) / 2;
$y = ($height - imagefontheight($font_size)) / 2;
imagestring($image, $font_size, $x, $y, $code, $text_color);

// 输出图片
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>