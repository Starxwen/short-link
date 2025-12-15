<?php
/**
 * PHPMailer SMTP class.
 * 简化版本，专门用于SMTP连接和发送
 */

namespace PHPMailer\PHPMailer;

class SMTP
{
    const VERSION = '6.6.0';
    const CRLF = "\r\n";
    const DEFAULT_PORT = 25;
    
    // 调试级别常量
    const DEBUG_OFF = 0;
    const DEBUG_CLIENT = 1;
    const DEBUG_SERVER = 2;
    const DEBUG_CONNECTION = 3;
    const DEBUG_LOWLEVEL = 4;
    
    public $do_debug = 0;
    public $Debugoutput = 'echo';
    public $do_verp = false;
    public $Timeout = 300;
    public $Timelimit = 300;
    
    protected $smtp_conn;
    protected $error = [];
    protected $helo_rply = null;
    protected $server_caps = null;
    protected $last_reply = '';
    
    private $phpmailer;
    
    public function __construct($phpmailer = null)
    {
        $this->phpmailer = $phpmailer;
    }
    
    public function connect($host, $port = null, $timeout = 30, $options = [])
    {
        if (is_null($port)) {
            $port = self::DEFAULT_PORT;
        }
        
        $this->setError('');
        
        if ($this->connected()) {
            $this->setError('Already connected to a server');
            return false;
        }
        
        if (empty($host)) {
            $this->setError('No HOST specified');
            return false;
        }
        
        $host = trim($host);
        
        // 连接服务器
        $errno = 0;
        $errstr = '';
        $socket_context = stream_context_create($options);
        
        // SSL连接
        if ($this->phpmailer && $this->phpmailer->SMTPSecure === 'ssl') {
            $host = 'ssl://' . $host;
            
            // 为SSL连接添加特定选项
            $ssl_context = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            if (empty($socket_context)) {
                $socket_context = stream_context_create($ssl_context);
            } else {
                $options = stream_context_get_options($socket_context);
                if (!isset($options['ssl'])) {
                    $options['ssl'] = $ssl_context['ssl'];
                }
                $socket_context = stream_context_create($options);
            }
        }
        
        $this->smtp_conn = @stream_socket_client(
            $host . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $socket_context
        );
        
        if (!$this->smtp_conn) {
            $this->setError(
                'Failed to connect to server',
                $errno,
                $errstr
            );
            return false;
        }
        
        stream_set_timeout($this->smtp_conn, $timeout, $this->Timelimit);
        
        // 获取服务器欢迎消息
        $announce = $this->get_lines();
        $this->edebug('SERVER -> CLIENT: ' . $announce);
        
        // 检查是否成功连接
        if (substr($announce, 0, 3) !== '220') {
            $this->setError('SMTP server did not accept connection');
            return false;
        }
        
        return true;
    }
    
    public function startTLS()
    {
        if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
            return false;
        }
        
        // 加密连接
        $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        
        if (!stream_socket_enable_crypto(
            $this->smtp_conn,
            true,
            $crypto_method
        )) {
            return false;
        }
        
        return true;
    }
    
    public function authenticate($username, $password, $authtype = null, $realm = '', $workstation = '')
    {
        if (!$this->sendCommand('AUTH', 'AUTH LOGIN', 334)) {
            return false;
        }
        
        if (!stream_socket_sendto($this->smtp_conn, base64_encode($username) . self::CRLF)) {
            $this->setError('AUTH LOGIN: send username failed');
            return false;
        }
        
        $reply = $this->get_lines();
        $code = substr($reply, 0, 3);
        $this->edebug('SERVER -> CLIENT: ' . $reply);
        
        if ($code != 334) {
            $this->setError('AUTH LOGIN: username not accepted from server');
            return false;
        }
        
        if (!stream_socket_sendto($this->smtp_conn, base64_encode($password) . self::CRLF)) {
            $this->setError('AUTH LOGIN: send password failed');
            return false;
        }
        
        $reply = $this->get_lines();
        $code = substr($reply, 0, 3);
        $this->edebug('SERVER -> CLIENT: ' . $reply);
        
        if ($code != 235) {
            $this->setError('AUTH LOGIN: password not accepted from server');
            return false;
        }
        
        return true;
    }
    
    public function data($msg_data)
    {
        if (!$this->sendCommand('DATA', 'DATA', 354)) {
            return false;
        }
        
        $lines = explode(self::CRLF, $msg_data);
        
        $field = substr($lines[0], 0, strpos($lines[0], ':'));
        $in_headers = false;
        if (!empty($field) && strpos($field, ' ') === false) {
            $in_headers = true;
        }
        
        foreach ($lines as $line) {
            $lines_out = [];
            if ($in_headers and $line == '') {
                $in_headers = false;
            }
            
            while (isset($line[3]) and $line[3] == '.') {
                $lines_out[] = substr($line, 0, 3);
                $line = substr($line, 3);
            }
            $lines_out[] = $line;
            
            foreach ($lines_out as $line_out) {
                if (!stream_socket_sendto($this->smtp_conn, $line_out . self::CRLF)) {
                    $this->setError('DATA: send data failed');
                    return false;
                }
                $this->edebug('CLIENT -> SERVER: ' . $line_out);
            }
        }
        
        $reply = $this->get_lines();
        $code = substr($reply, 0, 3);
        $this->edebug('SERVER -> CLIENT: ' . $reply);
        
        if ($code != 250) {
            $this->setError('DATA: data not accepted');
            return false;
        }
        
        return true;
    }
    
    public function sendHello($host = '')
    {
        // 首先尝试EHLO
        if (!$this->sendCommand('EHLO', 'EHLO ' . $host, 250)) {
            // 如果EHLO失败，尝试HELO
            return $this->sendCommand('HELO', 'HELO ' . $host, 250);
        }
        return true;
    }
    
    public function sendCommand($command, $commandstring, $expect)
    {
        if (!$this->connected()) {
            $this->setError('Called ' . $command . ' without being connected');
            return false;
        }
        
        $this->edebug('CLIENT -> SERVER: ' . $commandstring);
        
        if (!stream_socket_sendto($this->smtp_conn, $commandstring . self::CRLF)) {
            $this->setError("$command command failed");
            return false;
        }
        
        $reply = $this->get_lines();
        
        // 检查是否有响应
        if (empty($reply)) {
            $this->setError("$command command: no response from server");
            return false;
        }
        
        $code = substr($reply, 0, 3);
        $this->edebug('SERVER -> CLIENT: ' . $reply);
        
        // 处理多行响应
        if (strlen($reply) > 3 && substr($reply, 3, 1) === '-') {
            // 多行响应，继续读取直到最后一行
            while (strlen($reply) > 3 && substr($reply, 3, 1) === '-') {
                $additional_reply = $this->get_lines();
                if (empty($additional_reply)) {
                    break;
                }
                $reply = $additional_reply;
                $this->edebug('SERVER -> CLIENT: ' . $reply);
            }
            $code = substr($reply, 0, 3);
        }
        
        // 检查期望的响应码
        if (is_array($expect)) {
            if (!in_array($code, $expect)) {
                $this->setError("$command command expected code " . implode('/', $expect) . " but got code $code");
                return false;
            }
        } else {
            if ($code != $expect) {
                $this->setError("$command command expected code $expect but got code $code");
                return false;
            }
        }
        
        return true;
    }
    
    public function send($header, $body)
    {
        if (!$this->connected()) {
            $this->setError('Called send() without being connected');
            return false;
        }
        
        // 发送邮件头
        if (!$this->sendCommand('MAIL FROM', 'MAIL FROM:<' . $this->phpmailer->From . '>', 250)) {
            return false;
        }
        
        // 发送收件人
        foreach ($this->phpmailer->to as $to) {
            if (!$this->sendCommand('RCPT TO', 'RCPT TO:<' . $to[0] . '>', [250, 251])) {
                return false;
            }
        }
        
        // 发送数据
        if (!$this->data($header . self::CRLF . self::CRLF . $body)) {
            return false;
        }
        
        return true;
    }
    
    public function connected()
    {
        if (is_resource($this->smtp_conn)) {
            $sock_status = stream_get_meta_data($this->smtp_conn);
            if ($sock_status['eof']) {
                $this->edebug('SMTP NOTICE: EOF caught while checking if connected');
                $this->close();
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function close()
    {
        if (is_resource($this->smtp_conn)) {
            $this->sendCommand('QUIT', 'QUIT', 221);
            fclose($this->smtp_conn);
            $this->smtp_conn = null;
        }
    }
    
    protected function get_lines()
    {
        $data = '';
        $endtime = time() + $this->Timeout;
        $max_attempts = 100; // 防止无限循环
        $attempts = 0;
        
        while (is_resource($this->smtp_conn) && !feof($this->smtp_conn) && $attempts < $max_attempts) {
            $str = @fgets($this->smtp_conn, 515);
            $attempts++;
            
            if ($str === false) {
                // 检查连接是否已关闭
                $meta = stream_get_meta_data($this->smtp_conn);
                if ($meta['eof']) {
                    $this->setError('Connection closed by server');
                    break;
                }
                continue;
            }
            
            $data .= $str;
            
            // 检查是否是完整的响应行
            if (strlen($data) >= 4 && substr($data, 3, 1) == ' ') {
                break;
            }
            
            // 检查超时
            if (time() > $endtime) {
                $this->setError('SMTP timeout');
                break;
            }
            
            // 检查连接状态
            $meta = stream_get_meta_data($this->smtp_conn);
            if ($meta['eof']) {
                $this->setError('Connection closed by server');
                break;
            }
        }
        
        if ($attempts >= $max_attempts) {
            $this->setError('Too many attempts reading response');
        }
        
        return $data;
    }
    
    public function setError($message, $smtp_code = '', $smtp_error_code = '')
    {
        $this->error = [
            'error' => $message,
            'smtp_code' => $smtp_code,
            'smtp_error_code' => $smtp_error_code
        ];
    }
    
    public function getError()
    {
        return $this->error;
    }
    
    protected function edebug($str)
    {
        if ($this->do_debug >= 1) {
            if (is_callable($this->Debugoutput)) {
                call_user_func($this->Debugoutput, $str, $this->do_debug);
            } else {
                echo $str . "\n";
            }
        }
    }
}