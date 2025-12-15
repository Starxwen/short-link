<?php
/**
 * 阿里企业邮箱专用邮件发送类
 * 简化版本，专门针对阿里企业邮箱的SMTP特性
 */

// 防止重复包含
if (class_exists('AliyunMailer')) {
    return;
}

class AliyunMailer
{
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_address;
    private $from_name;
    private $socket;
    private $debug = false;

    public function __construct()
    {
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
     * 调试输出
     */
    private function debug($message)
    {
        if ($this->debug) {
            echo "<p style='font-family: monospace; font-size: 12px; color: #666;'>" . htmlspecialchars($message) . "</p>";
        }
    }

    /**
     * 连接到SMTP服务器
     */
    private function connect()
    {
        $this->debug("连接到 {$this->smtp_host}:{$this->smtp_port} (SSL)");

        // 创建SSL上下文
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        // 连接SSL SMTP服务器
        $this->socket = @stream_socket_client(
            'ssl://' . $this->smtp_host . ':' . $this->smtp_port,
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$this->socket) {
            $this->debug("连接失败: $errstr ($errno)");
            return false;
        }

        // 读取欢迎消息
        $welcome = $this->readResponse();
        $this->debug("服务器欢迎: " . $welcome);

        if (substr($welcome, 0, 3) !== '220') {
            $this->debug("连接失败，未收到220响应");
            return false;
        }

        return true;
    }

    /**
     * 发送命令并读取响应
     */
    private function sendCommand($command, $expected_code = '250')
    {
        $this->debug("发送: $command");

        if (!fwrite($this->socket, $command . "\r\n")) {
            $this->debug("发送命令失败");
            return false;
        }

        $response = $this->readResponse();
        $this->debug("响应: " . $response);

        if (substr($response, 0, 3) !== $expected_code) {
            $this->debug("期望响应码 " . $expected_code . "，收到 " . substr($response, 0, 3));
            return false;
        }

        return true;
    }

    /**
     * 读取服务器响应
     */
    private function readResponse()
    {
        $response = '';
        $timeout = 10;
        $start_time = time();

        while (time() - $start_time < $timeout) {
            $str = fgets($this->socket, 512);
            if ($str === false) {
                break;
            }

            $response .= $str;

            // 检查是否是完整的响应
            if (strlen($response) >= 4 && substr($response, 3, 1) == ' ') {
                break;
            }
        }

        return $response;
    }

    /**
     * 认证
     */
    private function authenticate()
    {
        // 发送EHLO
        if (!$this->sendCommand('EHLO ' . gethostname(), '250')) {
            // 如果EHLO失败，尝试HELO
            if (!$this->sendCommand('HELO ' . gethostname(), '250')) {
                return false;
            }
        }

        // 认证
        if (!$this->sendCommand('AUTH LOGIN', '334')) {
            return false;
        }

        // 发送用户名
        if (!$this->sendCommand(base64_encode($this->smtp_username), '334')) {
            return false;
        }

        // 发送密码
        if (!$this->sendCommand(base64_encode($this->smtp_password), '235')) {
            return false;
        }

        return true;
    }

    /**
     * 发送邮件
     */
    public function send($to, $subject, $body, $is_html = false)
    {
        $this->debug = true; // 启用调试

        // 检查设置
        if (empty($this->smtp_host) || empty($this->smtp_username) || empty($this->smtp_password)) {
            $this->debug("SMTP设置不完整");
            return false;
        }

        // 连接服务器
        if (!$this->connect()) {
            return false;
        }

        // 认证
        if (!$this->authenticate()) {
            $this->debug("认证失败");
            fclose($this->socket);
            return false;
        }

        // 设置发件人
        if (!$this->sendCommand('MAIL FROM: <' . $this->from_address . '>', '250')) {
            fclose($this->socket);
            return false;
        }

        // 设置收件人
        if (!$this->sendCommand('RCPT TO: <' . $to . '>', '250')) {
            fclose($this->socket);
            return false;
        }

        // 发送邮件内容
        if (!$this->sendCommand('DATA', '354')) {
            fclose($this->socket);
            return false;
        }

        // 构建邮件头
        $headers = "From: {$this->from_name} <{$this->from_address}>\r\n";
        $headers .= "To: <{$to}>\r\n";
        $headers .= "Subject: " . $this->encodeHeader($subject) . "\r\n";

        if ($is_html) {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }

        // 发送邮件内容
        $message = $headers . "\r\n" . $body . "\r\n.\r\n";
        if (!fwrite($this->socket, $message)) {
            $this->debug("发送邮件内容失败");
            fclose($this->socket);
            return false;
        }

        // 读取最终响应
        $response = $this->readResponse();
        $this->debug("最终响应: " . $response);

        // 发送QUIT
        $this->sendCommand('QUIT', '221');

        fclose($this->socket);

        return substr($response, 0, 3) === '250';
    }

    /**
     * 编码邮件头
     */
    private function encodeHeader($str)
    {
        return '=?UTF-8?B?' . base64_encode($str) . '?=';
    }

    /**
     * 测试连接
     */
    public function testConnection($to)
    {
        $subject = '阿里企业邮箱测试邮件';
        $body = "
        <html>
        <body>
            <h2>SMTP测试成功！</h2>
            <p>这是一封通过阿里企业邮箱发送的测试邮件。</p>
            <p>发送时间: " . date('Y-m-d H:i:s') . "</p>
            <p>SMTP服务器: {$this->smtp_host}</p>
            <p>端口: {$this->smtp_port}</p>
        </body>
        </html>";

        return $this->send($to, $subject, $body, true);
    }
}