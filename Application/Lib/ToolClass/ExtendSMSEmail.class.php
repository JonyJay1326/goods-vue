<?php

class ExtendSMSEmail extends SMSEmail
{
    public $content = null; // 发送的内容
    public $title   = null; // 标题
    public $addr    = null; // 接收人
    public $cAddr   = null; // 抄送
    public $bAddr   = null; // 密送
    public $address = 'Legal@gshopper.com';
    public $sender = 'erpservice@gshopper.com';
    private $_error;
    
    public function __construct($exceptions = false)
    {
        parent::__construct($exceptions);
        $this->Sender = $this->sender;
    }

    public function sendEmail($address, $title, $content) {
        $is_send_success = true;
        try {
            $this->setSender();
            $this->isHTML(true);
            $this->setReceive($address);
            is_null($this->cAddr) or $this->setCc($this->cAddr);
            is_null($this->bAddr) or $this->setBcc($this->bAddr);
            $this->Subject = $title;
            $this->Body = $content;
            $this->Send();
        } catch (phpmailerException $e) {
            $is_send_success = false;
            $this->_error = $e;
        }
        $this->_catchMe();
        return $is_send_success;
    }
    
    /**
     * 发送者
     * ERP系统发送
     * 
     */
    public function setSender()
    {
        $this->Sender = $this->sender;
    } 
    
    /**
     * 接收者
     * 
     */
    public function setReceive($address)
    {
        !is_null($address) or $address = $this->address;
        $this->address = $address;
        if (is_array($address)) {
            foreach ($address as $key => $value) {
                $this->AddAddress($value);
            }
        } else {
            $this->AddAddress($address);
        }
    }
    
    /**
     * 抄送
     * 
     */
    public function setCc($address, $name)
    {
        $this->cAddr = $address;
        if (is_array($address)) {
            foreach ($address as $key => $value) {
                $this->AddCC($value);
            }
        } else {
            $this->AddCC($address);
        }
    }
    
    /**
     * 密送
     * 
     */
    public function setBcc($address, $name)
    {
        $this->bAddr = $address;
        if (is_array($address)) {
            foreach ($address as $key => $value) {
                $this->AddBCC($value);
            }
        } else {
            $this->AddBCC($address);
        }
    }
    
    public function getErrorMessage()
    {
        if ($this->_error) return $this->_error;
    }
    
    public function getError()
    {
        return $this->getErrorMessage();
    }
    
    /**
     * 记录接口日志
     */
    private function _catchMe()
    {
        $logFilePath = __DIR__ . '/../../Runtime/logs/';
        // 日志对象
        $log = new \stdClass();
        $trace = debug_backtrace(0);
        $logName = date('Y-m-d') .'_'. @$trace[1]['function'] . '.log';
        $txt = "\n------------------------------------------------------------------";
        $txt .= "\n@@@时间：".$log->datetime = date('Y-m-d H:i:s');
        $txt .= "\n@@@来源：".$log->ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
        $txt .= "\n@@@方法：".$log->method = $_SERVER["REQUEST_METHOD"];
        $txt .= "\n@@@调用：".$log->callback = sprintf('%s::%s (line:%s)', @$trace[2]['class'], @$trace[2]['function'], @$trace[1]['line']);
        $txt .= "\n@@@Sender：" . $this->sender;
        $txt .= "\n@@@Receive：\n" . print_r($this->address, true);
        $txt .= "\n@@@ReceiveCC：\n".print_r($this->cAddr, true);
        $txt .= "\n@@@ReceiveBCC：\n".print_r($this->bAddr, true);
        $txt .= "\n@@@Error: " . print_r($this->ErrorInfo, true);
        $txt .= "\n------------------------------------------------------------------";
        // 保存到日志文件
        $file = $logFilePath.$logName;
        fclose(fopen($file, 'a+'));
        $_fo = fopen($file, 'rb');
        $old = fread($_fo, 1024 * 1024);
        fclose($_fo);
        file_put_contents($file, $txt.$old);
    }
}