<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * 简化版本，专门用于SMTP发送
 */

namespace PHPMailer\PHPMailer;

class PHPMailer
{
    const VERSION = '6.6.0';
    
    public $Priority = 3;
    public $CharSet = 'UTF-8';
    public $ContentType = 'text/plain';
    public $Encoding = '8bit';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $From = 'root@localhost';
    public $FromName = 'Root User';
    public $Sender = '';
    public $ReturnPath = '';
    public $ReplyTo = [];
    public $to = [];
    public $cc = [];
    public $bcc = [];
    public $MessageID = '';
    public $Host = 'localhost';
    public $Port = 25;
    public $Helo = 'localhost.localdomain';
    public $SMTPSecure = '';
    public $SMTPAutoTLS = true;
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $AuthType = '';
    public $SMTPKeepAlive = false;
    public $SingleTo = false;
    public $SingleToArray = [];
    public $do_verp = false;
    public $AllowEmpty = false;
    public $DKIM_selector = '';
    public $DKIM_identity = '';
    public $DKIM_domain = '';
    public $DKIM_private = '';
    public $DKIM_private_string = '';
    public $action_function = '';
    public $XMailer = '';
    
    protected $smtp = null;
    protected $exceptions = true;
    protected $error_count = 0;
    public $SMTPDebug = 0;
    public $Debugoutput = 'echo';
    
    public function __construct($exceptions = true)
    {
        $this->exceptions = (bool)$exceptions;
    }
    
    public function isSMTP()
    {
        $this->smtp = new SMTP($this);
    }
    
    public function isHTML($isHtml = true)
    {
        if ($isHtml) {
            $this->ContentType = 'text/html';
        } else {
            $this->ContentType = 'text/plain';
        }
    }
    
    public function setFrom($address, $name = '', $auto = true)
    {
        $address = trim($address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name));
        if (!$this->validateAddress($address)) {
            $this->setError('Invalid address in from: ' . $address);
            return false;
        }
        $this->From = $address;
        $this->FromName = $name;
        if ($auto) {
            if (empty($this->Sender)) {
                $this->Sender = $address;
            }
        }
        return true;
    }
    
    public function addAddress($address, $name = '')
    {
        return $this->addAnAddress('to', $address, $name);
    }
    
    public function addCC($address, $name = '')
    {
        return $this->addAnAddress('cc', $address, $name);
    }
    
    public function addBCC($address, $name = '')
    {
        return $this->addAnAddress('bcc', $address, $name);
    }
    
    public function addReplyTo($address, $name = '')
    {
        return $this->addAnAddress('Reply-To', $address, $name);
    }
    
    protected function addAnAddress($kind, $address, $name)
    {
        $address = trim($address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name));
        if (!$this->validateAddress($address)) {
            $this->setError('Invalid address in ' . $kind . ': ' . $address);
            return false;
        }
        if ($kind !== 'Reply-To') {
            if (!isset($this->{$kind})) {
                $this->{$kind} = [];
            }
            $this->{$kind}[] = [$address, $name];
        } else {
            $this->ReplyTo[] = [$address, $name];
        }
        return true;
    }
    
    public static function validateAddress($address, $patternselect = null)
    {
        if (strpos($address, '@') === false) {
            return false;
        }
        return filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public function send()
    {
        try {
            if (!$this->preSend()) {
                return false;
            }
            return $this->postSend();
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            if ($this->exceptions) {
                throw $e;
            }
            return false;
        }
    }
    
    public function preSend()
    {
        if ((count($this->to) + count($this->cc) + count($this->bcc)) < 1) {
            throw new Exception($this->lang('provide_address'));
        }
        
        if (!empty($this->ReplyTo)) {
            foreach ($this->ReplyTo as $replyto) {
                if (!$this->validateAddress($replyto[0])) {
                    throw new Exception($this->lang('invalid_address') . ' (Reply-To): ' . $replyto[0]);
                }
            }
        }
        
        if (!$this->validateAddress($this->From)) {
            throw new Exception($this->lang('invalid_address') . ' (From): ' . $this->From);
        }
        
        return true;
    }
    
    public function postSend()
    {
        try {
            $this->header = $this->createHeader();
            $this->body = $this->createBody();
            return $this->smtp->send($this->header, $this->body);
        } catch (Exception $e) {
            throw new Exception($this->lang('smtp_error') . ' ' . $e->getMessage(), 0, $e);
        }
    }
    
    protected function createHeader()
    {
        $header = '';
        $header .= 'From: ' . $this->addrHeader($this->From, $this->FromName) . "\r\n";
        
        if (!empty($this->ReplyTo)) {
            $header .= 'Reply-To: ' . $this->addrHeader($this->ReplyTo[0][0], $this->ReplyTo[0][1]) . "\r\n";
        }
        
        if (!empty($this->to)) {
            $header .= 'To: ' . $this->addrHeader($this->to[0][0], $this->to[0][1]) . "\r\n";
        }
        
        $header .= 'Subject: ' . $this->encodeHeader($this->Subject) . "\r\n";
        
        if ($this->ContentType === 'text/html') {
            $header .= 'MIME-Version: 1.0' . "\r\n";
            $header .= 'Content-Type: text/html; charset=' . $this->CharSet . "\r\n";
        } else {
            $header .= 'Content-Type: text/plain; charset=' . $this->CharSet . "\r\n";
        }
        
        return $header;
    }
    
    protected function createBody()
    {
        return $this->Body;
    }
    
    protected function addrHeader($addr, $name)
    {
        return empty($name) ? $addr : '"' . $name . '" <' . $addr . '>';
    }
    
    protected function encodeHeader($str)
    {
        return $str;
    }
    
    public function getSMTPInstance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new SMTP($this);
        }
        return $this->smtp;
    }
    
    public function setError($msg)
    {
        $this->error_count++;
        $this->ErrorInfo = $msg;
    }
    
    public function lang($key)
    {
        $lang = [
            'provide_address' => 'You must provide at least one recipient email address.',
            'invalid_address' => 'Invalid address',
            'smtp_error' => 'SMTP Error: data not accepted.',
        ];
        return isset($lang[$key]) ? $lang[$key] : $key;
    }
    
    public function smtpConnect($options = null)
    {
        if (is_null($this->smtp)) {
            $this->smtp = new SMTP($this);
        }
        
        // 传递调试设置
        $this->smtp->do_debug = $this->SMTPDebug;
        $this->smtp->Debugoutput = $this->Debugoutput;
        
        // 连接服务器
        if (!$this->smtp->connect($this->Host, $this->Port)) {
            return false;
        }
        
        // 发送EHLO/HELO
        $helo_host = !empty($this->Helo) ? $this->Helo : gethostname();
        if (!$this->smtp->sendHello($helo_host)) {
            return false;
        }
        
        // 如果需要TLS（非SSL）
        if ($this->SMTPSecure === 'tls') {
            if (!$this->smtp->startTLS()) {
                return false;
            }
            // 重新发送EHLO
            if (!$this->smtp->sendHello($helo_host)) {
                return false;
            }
        }
        
        // 如果需要认证
        if ($this->SMTPAuth) {
            if (!$this->smtp->authenticate($this->Username, $this->Password)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function smtpClose()
    {
        if (is_object($this->smtp)) {
            $this->smtp->close();
        }
    }
}

class Exception extends \Exception
{
}