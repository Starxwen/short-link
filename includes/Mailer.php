<?php
/**
 * 邮件发送类
 * 支持SMTP邮件发送
 */

// 防止重复包含
if (class_exists('Mailer')) {
    return;
}

// 加载PHPMailer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

class Mailer
{
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_encryption;
    private $from_address;
    private $from_name;

    public function __construct()
    {
        // 从数据库加载SMTP设置
        $this->loadSettings();
    }

    /**
     * 从数据库加载邮件设置
     */
    private function loadSettings()
    {
        global $dbhost, $dbuser, $dbpass, $dbname;

        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        if (!$conn) {
            return false;
        }

        $result = mysqli_query($conn, "SELECT setting_key, setting_value FROM settings WHERE category = 'email'");
        while ($row = mysqli_fetch_assoc($result)) {
            switch ($row['setting_key']) {
                case 'smtp_host':
                    $this->smtp_host = $row['setting_value'];
                    break;
                case 'smtp_port':
                    $this->smtp_port = $row['setting_value'];
                    break;
                case 'smtp_username':
                    $this->smtp_username = $row['setting_value'];
                    break;
                case 'smtp_password':
                    $this->smtp_password = $row['setting_value'];
                    break;
                case 'smtp_encryption':
                    $this->smtp_encryption = $row['setting_value'];
                    break;
                case 'email_from_address':
                    $this->from_address = $row['setting_value'];
                    break;
                case 'email_from_name':
                    $this->from_name = $row['setting_value'];
                    break;
            }
        }

        mysqli_close($conn);
    }

    /**
     * 发送邮件
     * @param string $to 收件人邮箱
     * @param string $subject 邮件主题
     * @param string $body 邮件内容
     * @param bool $is_html 是否为HTML格式
     * @return bool 发送结果
     */
    public function send($to, $subject, $body, $is_html = false)
    {
        // 检查SMTP设置是否完整
        if (empty($this->smtp_host) || empty($this->smtp_username) || empty($this->smtp_password)) {
            return false;
        }

        // 优先使用阿里企业邮箱专用发送器
        if (file_exists(__DIR__ . '/AliyunMailer.php')) {
            include_once __DIR__ . '/AliyunMailer.php';
            $aliyun_mailer = new AliyunMailer();
            return $aliyun_mailer->send($to, $subject, $body, $is_html);
        }

        // 使用PHPMailer发送邮件（如果可用）
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->sendWithPHPMailer($to, $subject, $body, $is_html);
        }

        // 使用原生PHP mail()函数作为备选方案
        return $this->sendWithMail($to, $subject, $body, $is_html);
    }

    /**
     * 使用PHPMailer发送邮件
     */
    private function sendWithPHPMailer($to, $subject, $body, $is_html)
    {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // 服务器设置
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = $this->smtp_encryption === 'ssl' ? 'ssl' : 'tls';
            $mail->Port = intval($this->smtp_port);

            // 发件人
            $mail->setFrom($this->from_address, $this->from_name);
            $mail->addAddress($to);

            // 内容设置
            $mail->isHTML($is_html);
            $mail->Subject = $subject;
            $mail->Body = $body;

            return $mail->send();
        } catch (Exception $e) {
            error_log("邮件发送失败: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 使用原生mail()函数发送邮件
     */
    private function sendWithMail($to, $subject, $body, $is_html)
    {
        $headers = [];
        $headers[] = 'From: ' . $this->from_name . ' <' . $this->from_address . '>';
        $headers[] = 'Reply-To: ' . $this->from_address;

        if ($is_html) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }

        $headers = implode("\r\n", $headers);

        return mail($to, $subject, $body, $headers);
    }

    /**
     * 发送邮箱验证邮件
     * @param string $to 收件人邮箱
     * @param string $username 用户名
     * @param string $verification_code 验证码
     * @return bool 发送结果
     */
    public function sendVerificationEmail($to, $username, $verification_code)
    {
        // 获取系统设置
        $site_url = Settings::getSiteUrl();
        $site_name = Settings::getSiteName();

        $subject = '邮箱验证 - ' . $site_name;

        $body = "
        <html>
        <head>
            <title>邮箱验证</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #3498db; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .button { display: inline-block; background: #2ecc71; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$site_name}</h1>
                    <p>邮箱验证</p>
                </div>
                <div class='content'>
                    <p>您好，{$username}！</p>
                    <p>感谢您注册{$site_name}服务。请点击下面的按钮验证您的邮箱地址：</p>
                    <p style='text-align: center;'>
                        <a href='{$site_url}verify_email.php?code={$verification_code}&email=" . urlencode($to) . "' class='button'>验证邮箱</a>
                    </p>
                    <p>如果按钮无法点击，请复制以下链接到浏览器地址栏：</p>
                    <p style='word-break: break-all; background: #eee; padding: 10px; border-radius: 4px;'>
                        {$site_url}verify_email.php?code={$verification_code}&email=" . urlencode($to) . "
                    </p>
                    <p>此验证链接将在24小时后过期。</p>
                </div>
                <div class='footer'>
                    <p>此邮件由系统自动发送，请勿回复。</p>
                    <p>如果您没有注册{$site_name}账户，请忽略此邮件。</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->send($to, $subject, $body, true);
    }

    /**
     * 测试邮件发送
     * @param string $to 测试邮箱
     * @return bool 测试结果
     */
    public function testConnection($to)
    {
        // 获取系统设置
        $site_name = Settings::getSiteName();

        $subject = 'SMTP测试邮件 - ' . $site_name;
        $body = "
        <html>
        <body>
            <h2>SMTP测试邮件</h2>
            <p>这是一封测试邮件，用于验证SMTP设置是否正确。</p>
            <p>如果您收到此邮件，说明SMTP配置成功！</p>
            <p>发送时间: " . date('Y-m-d H:i:s') . "</p>
        </body>
        </html>";

        return $this->send($to, $subject, $body, true);
    }

    /**
     * 发送自定义邮件（用于验证码等）
     * @param string $to 收件人邮箱
     * @param string $subject 邮件主题
     * @param string $message 邮件内容
     * @return bool 发送结果
     */
    public function sendCustomEmail($to, $subject, $message)
    {
        return $this->send($to, $subject, $message, false);
    }
}
?>